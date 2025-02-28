<?php


namespace App\Services;


use App\Constants\Env;
use App\Contracts\OrderLifeServiceContract;
use App\Exceptions\OrderLifeNoUserException;
use App\Exceptions\OrderLifeOrderNotFullException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MailerLite\MailerLiteService;
use Carbon\Carbon;

/**
 * how this works:
 * - when order is created: create order life with it
 * - when order is finished: drop order life for order
 * - when order is deleted (aka order became empty): drop order life for order
 * - on created/deleted - send order life to mailer lite, send cart_content on deleted
 * - on adding/removing items - send order life, send cart_content
 * - every hour send active order life to mailer lite
 *
 * Class OrderLifeService
 * @package App\Services
 */
class OrderLifeService implements OrderLifeServiceContract
{
    /**
     * @var Order|null
     */
    private ?Order $order;
    private bool $wasDeleted = false;

    /**
     * @param Order $order
     * @return $this
     * @throws OrderLifeNoUserException|OrderLifeOrderNotFullException
     */
    public function setOrder(Order $order): self
    {
        $this->order = $order->refresh();

        if (!$order->user) {
            throw new OrderLifeNoUserException();
        }

        if ($order->type !== Env::ORDER_TYPE_FULL) {
            throw new OrderLifeOrderNotFullException();
        }

        return $this;
    }

    public function create(): self
    {
        $this->order->life()->firstOrCreate([], ['order_life' => Carbon::now()]);

        return $this;
    }

    public function createFromOrderCreationDate(): self
    {
        $this->order->life()->create(['order_life' => $this->order->created_at]);

        return $this;
    }

    public function deleteFinished(): self
    {
        if ($this->order->status === Env::STATUS_FINISHED) {
            $this->order->life()->delete();
            $this->wasDeleted = true;
        }

        return $this;
    }

    public function delete($forceDelete = false): self
    {
        $this->deleteFinished();

        if ($this->order->items->count() === 0 || $forceDelete) {
            $this->order->life()->delete();

            $this->wasDeleted = true;
        }

        return $this;
    }

    public function getOrderLifeDiff(): int
    {
        if (!$this->order->life) {
            return 0;
        }

        return Carbon::now()->diffInHours($this->order->life->order_life);
    }

    /**
     * @return $this
     */
    public function sendUpdate(): OrderLifeService
    {
        $data = [];

        if ($cartContent = $this->getCartContentMetrics()) {
            $data['cart_content'] = $cartContent;
        }

        $orderLifeDifference = $this->getOrderLifeDiff();

        if ($orderLifeDifference < self::MINIMUM_ORDER_LIFE_FOR_UPDATE && !$this->wasDeleted) {
            return $this->reset();
        }

        if ($orderLifeDifference > self::MAXIMUM_ORDER_LIFE_FOR_DELETE) {
            $this->delete(true);
            dump("order {$this->order->id} in status {$this->order->status} was deleted");
        }

        $data['order_life'] = $orderLifeDifference;

        if ($this->wasDeleted) {
            $data['order_life'] = 0;
            $data['order_status'] = 'empty';
        }

        resolve(MailerLiteService::class)
            ->setUser($this->order->user)
            ->updateFields($data);

        return $this->reset();
    }

    private function getCartContentMetrics(): ?string
    {
        $items = $this->order->items;

        if ($items->isEmpty()) {
            return null;
        }

        $content = [];

        /**
         * @var $item OrderItem
         */
        foreach ($items as $item) {
            if (!$item->getItem()) {
                continue;
            }

            $licenseType = ucfirst(strtolower($item->license->type));

            $content[] = $item->getItemName() . " ({$licenseType} license)";
        }

        return implode(', ', $content);
    }

    private function reset()
    {
        $this->order = null;
        $this->wasDeleted = false;
        return $this;
    }
}
