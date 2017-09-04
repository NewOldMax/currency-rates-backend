<?php

namespace CurrencyRates\Service\CurrencyRate;

class CurrencyRateResult
{
    protected $rates;
    protected $date;

    public function __construct(array $rates, \DateTime $date)
    {
        $this->rates = $rates;
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getRates()
    {
        return $this->rates;
    }
}
