<?php
namespace CurrencyRates\Entity;

use CurrencyRates\Service\BasicEntity;

class CurrencyRate extends BasicEntity
{
    //Properties
    protected $id;
    protected $currency;
    protected $value;
    protected $date;

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
        $this->currency = $fields['currency'];
        $this->value = $fields['value'];
        $this->date = $fields['date'];
    }

    public function createFromArray($fields)
    {
        return new self($fields);
    }

    public function patchable()
    {
        return ['value'];
    }

    public function toArray()
    {
        return [
            'currency' => $this->currency,
            'value' => (float) $this->value,
            'date' => $this->formatDate($this->date, 'Y-m-d'),
        ];
    }

    public function getDate()
    {
        return $this->date;
    }
}
