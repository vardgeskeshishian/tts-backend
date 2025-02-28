<?php

namespace App\Services\DTO;

use Illuminate\Http\Request;

class TransactionDTO
{
    /**
     * @var mixed
     */
    private $earnings;
    /**
     * @var mixed
     */
    private $tax;
    /**
     * @var mixed
     */
    private $fee;
    /**
     * @var mixed
     */
    private $gross;
    /**
     * @var mixed
     */
    private $decrease;

    public function __construct(Request $request)
    {
        $this->earnings = $request->get('balance_earnings');
        $this->tax = $request->get('balance_tax', 0);
        $this->fee = $request->get('balance_fee', 0);
        $this->gross = $request->get('balance_gross', 0);
        $this->decrease = $request->get('earnings_decrease');
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @return mixed
     */
    public function getEarnings()
    {
        return $this->earnings;
    }

    public function calculateCleanPrice($price)
    {
        if ($price === 0) {
            return 0;
        }

        if ($this->gross === 0 || $this->gross === "0") {
            $this->gross = $price;
        }

        return $price - ($price * ($this->tax / $this->gross)) - ($price * ($this->fee / $this->gross));
    }

    /**
     * @return mixed
     */
    public function getDecrease()
    {
        return $this->decrease;
    }
}
