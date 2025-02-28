<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="OrderResource",
     *     title="OrderResource",
     *     @OA\Property(property="id", type="integer", description="Order ID", example="140391"),
     *     @OA\Property(property="session_id", type="string", example="fff320d3-d4f7-467d-813f-6f06d8e28942"),
     *     @OA\Property(property="user_id", type="string", example="140391"),
     *     @OA\Property(property="status", type="string", example="new"),
     *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *     @OA\Property(property="type", type="string", example="full"),
     *     @OA\Property(property="receipt_url", type="string", example="http://my.paddle.com/receipt/20189914/73698432-chre26f4d95d738-32061adef7"),
     *     @OA\Property(property="deleted_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *     @OA\Property(property="succeeded_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *     @OA\Property(property="refunded_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *     @OA\Property(property="analytics_events", type="string", example="{sent_buy_transaction_at: 1617351636}"),
     *     @OA\Property(property="total", type="integer", example="100"),
     *     @OA\Property(property="promocode_object", type="object",
     *          ref="#/components/schemas/Promocode"
     *     ),
     *     @OA\Property(property="items", type="array", @OA\Items(
     *          @OA\Property(property="id", type="string", example="140391"),
     *          @OA\Property(property="order_id", type="string", example="1202"),
     *          @OA\Property(property="track_id", type="string", example="345"),
     *          @OA\Property(property="license_id", type="string", example="454"),
     *          @OA\Property(property="date", type="string", example="1602418511"),
     *          @OA\Property(property="license_sculpt", type="object",
     *              @OA\Property(property="type", type="string", example="Free"),
     *          ),
     *          @OA\Property(property="track_sculpt", type="object",
     *              @OA\Property(property="name", type="string", example="Joy"),
     *              @OA\Property(property="author_name", type="string", example="Paul Keane"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *              ),
     *              @OA\Property(property="prices", type="array", @OA\Items(
     *                  @OA\Property(property="type", type="string", example="Standard"),
     *                  @OA\Property(property="license_id", type="string", example="1"),
     *                  @OA\Property(property="license", type="object",
     *                      @OA\Property(property="type", type="string", example="Standard"),
     *                      @OA\Property(property="short_description", type="string", example="Standard"),
     *                      @OA\Property(property="description", type="string", example="Standard"),
     *                      @OA\Property(property="list_1", type="string", example="Paid Ads, Education, Audiobooks"),
     *                      @OA\Property(property="list_2", type="string", example="no credits, commercial use, short versions"),
     *                      @OA\Property(property="comments", type="Including the uses covered by the previous licenses"),
     *                  ),
     *                  @OA\Property(property="price", type="string", example="12.00"),
     *              )),
     *          ),
     *          @OA\Property(property="effect_sculpt", type="object",
     *              @OA\Property(property="id", type="string", example="140391"),
     *              @OA\Property(property="name", type="string", example="Accept"),
     *              @OA\Property(property="extension", type="string", example="wav"),
     *              @OA\Property(property="price", type="string", example="14.22"),
     *              @OA\Property(property="duration", type="string", example="4,95"),
     *              @OA\Property(property="link", type="string", example="/sfx/audio/accelerating-spinning-whoosh.wav"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *              @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *               ),
     *           ),
     *           @OA\Property(property="pack_sculpt", type="object",
     *              @OA\Property(property="id", type="string", example="140391"),
     *              @OA\Property(property="name", type="string", example="Accept"),
     *              @OA\Property(property="description", type="string", example="wav"),
     *              @OA\Property(property="price", type="string", example="14.22"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *              @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="deleted_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *              ),
     *           ),
     *           @OA\Property(property="type", type="string", example="order"),
     *           @OA\Property(property="license_number", type="string", example="TTStandard85653898"),
     *           @OA\Property(property="receipt", type="string", example="http://my.paddle.com/receipt/58672618/254897648-chrefd133c8c889-2fbaf6d183"),
     *           @OA\Property(property="item_type", type="string", example="tracks"),
     *           @OA\Property(property="item_id", type="string", example="54"),
     *     )),
     *     @OA\Property(property="sum", type="integer", example="115"),
     *     @OA\Property(property="promocode_info", type="object",
     *           @OA\Property(property="data", type="object",
     *              @OA\Property(property="code", type="string", example="LETSMOTION"),
     *              @OA\Property(property="was_applied", type="boolean", example="true"),
     *              @OA\Property(property="message", type="string", example="The promo code 'LETSMOTION' was applied to this order for tracks"),
     *              @OA\Property(property="prices", type="object", example="{'tracks' : {'200': 12}}"),
     *              @OA\Property(property="total", type="integer", example="80"),
     *              @OA\Property(property="tempPrice", type="integer", example="80"),
     *           )
     *     ),
     * )
     *
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $res Order
         */
        $res = $this->resource;

        $promocode = $res->promocodeObject;

        $promocodeInfo = [
            'code' => optional($promocode)->code,
            'was_applied' => false,
            'message' => '',
            'prices' => [],
            'total' => [],
            'tempPrice' => 0,
        ];

        $filteredItems = $res->items->filter(function (OrderItem $item) {
            return !is_null($item->track_id) || !is_null($item->item_id);
        })->values();

        if ($promocode) {
            $promocodeInfo['was_applied'] = true;

            $promocodeInfo['message'] = $promocode->getPromocodeInfoMessage();

            foreach ($filteredItems as $item) {
                $key = $item->item_type;

                $promocodeInfo['prices'][$key][$item->getItemId()] = $promocode->returnPriceWithDiscountForOrderItem($item);

                $promocodeInfo['tempPrice'] += $promocode->returnPriceWithDiscountForOrderItem($item);
            }

            $total = $promocodeInfo['tempPrice'];

            if ($total === 0) {
                $total = $filteredItems->sum('price');
            }

            $promocodeInfo['total'] = $total;
        }

        return [
            'id' => $res->id,
            $this->merge($res->toArray()),
            'items' => $filteredItems,
            'type' => $res->type,
            'sum' => $filteredItems->sum('price'),
            'promocode_info' => $this->merge($promocodeInfo),
            'additional' => $this->additional,
        ];
    }
}
