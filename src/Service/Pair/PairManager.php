<?php

namespace CurrencyRates\Service\Pair;

use CurrencyRates\Entity\Pair;
use CurrencyRates\Service\Manager;

class PairManager extends Manager
{
    protected $required = ['baseCurrency', 'targetCurrency', 'duration'];
    protected $unique = [];

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
}
