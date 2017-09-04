<?php

namespace CurrencyRates\Service\Interfaces;

use CurrencyRates\Service\CurrencyRate\CurrencyRateResult;

interface CurrencyRateFetcherInterface
{
    public function fetch(string $currency, \DateTime $date) : CurrencyRateResult;
    public function fetchMany(array $currencies, \DateTime $date) : CurrencyRateResult;
}
