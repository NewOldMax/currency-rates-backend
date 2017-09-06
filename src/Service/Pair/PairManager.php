<?php

namespace CurrencyRates\Service\Pair;

use CurrencyRates\Entity\Pair;
use CurrencyRates\Service\Manager;
use CurrencyRates\Service\CurrencyRate\CurrencyRateManager;

class PairManager extends Manager
{
    protected $required = ['baseCurrency', 'targetCurrency', 'duration'];
    protected $unique = [];

    protected $rateManager;
    protected $baseCurrency;

    public function setRateManager(CurrencyRateManager $rateManager)
    {
        $this->rateManager = $rateManager;
        return $this;
    }

    public function setBaseCurrency(string $baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
        return $this;
    }

    public function create(array $fields) : Pair
    {
        $this->validate($fields, Pair::class);
        $this->checkFields($fields);
        $pair = Pair::createFromArray($fields);
        $this->isCurrenciesEqual($pair);
        return $pair;
    }

    public function update($id, array $fields) : Pair
    {
        $this->checkFields($fields);
        $pair = $this->get($id);
        $pair->patch($fields);
        $this->isCurrenciesEqual($pair);
        return $pair;
    }

    public function get($id) : Pair
    {
        if ($pair = $this->em->getRepository(Pair::class)->find($id)) {
            return $pair;
        }

        $this->throwException(
            'entity.errors.not_found',
            404,
            ['%entity%' => 'Pair', '%id%' => $id]
        );
    }

    public function delete($id) : Pair
    {
        $pair = $this->get($id);
        $this->em->remove($pair);
        return $pair;
    }

    public function getHistoricalInfo($id) : array
    {
        $result = [];
        $pair = $this->get($id);

        $date = new \DateTime(date('Y-m-d'));
        $date->modify('-'.$pair->getDuration());
        $rates = $this->rateManager->getRatesFromDate(
            $date,
            $pair->getTargetCurrency()
        );
        if ($this->baseCurrency !== $pair->getBaseCurrency()) {
            $rates = $this->calculateRates(
                $rates,
                $date,
                $pair->getBaseCurrency()
            );
        }
        return $rates;
    }

    private function calculateRates(
        array $rates,
        \DateTime $date,
        $currency
    ) {
        $result = [];
        $newRates = $this->rateManager->getRatesFromDate(
            $date,
            $currency
        );
        if ($newRates && $rates && count($newRates) == count($rates)) {
            foreach ($rates as $key => $rate) {
                $value = $newRates[$key]->getValue();
                if ($value == 0) {
                    $value = 1;
                }
                $result[$rate->getFormattedDate()] = round($rate->getValue() / $value, 5);
            }
            $rates = [];
            foreach ($result as $key => $value) {
                $rates []= $this->rateManager->create([
                    'currency' => $currency,
                    'value' => $value,
                    'date' => $key,
                ]);
            }
        }
        return $rates;
    }

    private function checkFields(array $fields) : void
    {
        if (isset($fields['baseCurrency'])) {
            $this->checkCurrency($fields['baseCurrency']);
        }
        if (isset($fields['targetCurrency'])) {
            $this->checkCurrency($fields['targetCurrency']);
        }
        if (isset($fields['duration'])) {
            $this->checkDuration($fields['duration']);
        }
        if (isset($fields['value'])) {
            $this->checkValue($fields['value']);
        }
    }

    private function isCurrenciesEqual(Pair $pair) : void
    {
        if ($pair->getBaseCurrency() === $pair->getTargetCurrency()) {
            $this->throwException(
                'pair.errors.must_not_be_equal',
                400
            );
        }
    }

    private function checkDuration(string $duration)
    {
        if (in_array($duration, Pair::DURATIONS) === false) {
            $this->throwException(
                'entity.errors.unknown_value',
                400,
                ['%value%' => $duration]
            );
        }
    }

    private function checkValue(float $value)
    {
        if ($value <= 0) {
            $this->throwException(
                'pair.errors.value_must_be_greater_zero',
                400
            );
        }
    }
}
