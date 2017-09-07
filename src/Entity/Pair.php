<?php
namespace CurrencyRates\Entity;

use CurrencyRates\Service\BasicEntity;

class Pair extends BasicEntity
{
    const DURATIONS = [
        '3 days',
        '1 week',
        '2 weeks',
        '3 weeks',
        '5 weeks',
        '10 weeks',
        '25 weeks',
    ];

    //Properties
    protected $id;
    protected $value;
    protected $baseCurrency;
    protected $targetCurrency;
    protected $duration;
    protected $user;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct($fields)
    {
        $this->value = $fields['value'] ?? 1.0;
        $this->baseCurrency = $fields['baseCurrency'] ?? 'EUR';
        $this->targetCurrency = $fields['targetCurrency'] ?? 'USD';
        $this->duration = $fields['duration'] ?? '25 weeks';
        $this->user = $fields['user'] ?? null;
    }

    public static function createFromArray($fields)
    {
        return new self($fields);
    }

    public function patchable()
    {
        return ['value', 'baseCurrency', 'targetCurrency', 'duration'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'value' => (float) $this->value,
            'base_currency' => $this->baseCurrency,
            'target_currency' => $this->targetCurrency,
            'duration' => $this->duration,
        ];
    }

    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    public function getTargetCurrency()
    {
        return $this->targetCurrency;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
