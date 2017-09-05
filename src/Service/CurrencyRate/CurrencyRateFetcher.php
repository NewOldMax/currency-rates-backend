<?php

namespace CurrencyRates\Service\CurrencyRate;

use CurrencyRates\Service\Interfaces\CurrencyRateFetcherInterface;
use Fadion\Fixerio\Exchange;
use Fadion\Fixerio\Result;

class CurrencyRateFetcher implements CurrencyRateFetcherInterface
{

    private $baseCurrency;

    public function __construct(string $baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }

    public function fetch(string $currency, \DateTime $date) : CurrencyRateResult
    {
        return $this->fetchMany([$currency], $date);
    }

    public function fetchMany(array $currencies, \DateTime $date) : CurrencyRateResult
    {
        $exchange = new Exchange();
        $exchange->base($this->baseCurrency);
        $exchange->symbols($currencies);
        $exchange->historical($date->format('Y-m-d'));

        $response = $exchange->getResult();
        $result = $this->prepareResult($response);
        return $result;
    }

    private function prepareResult(Result $response)
    {
        return new CurrencyRateResult($response->getRates(), $response->getDate());
    }
}
