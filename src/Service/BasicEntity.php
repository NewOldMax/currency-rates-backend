<?php
namespace CurrencyRates\Service;

abstract class BasicEntity implements \Serializable
{
    /**
     * Array of properties, that may be updated directly with PATCH method.
     * @return array
     */
    abstract protected function patchable();

    abstract public function toArray();

    /**
     * @param $updates array
     * @return self
     */
    public function patch($updates)
    {
        $updates = array_intersect_key($updates, array_flip($this->patchable()));
        foreach ($updates as $update => $value) {
            $this->$update = $value;
        }
        return $this;
    }

    protected function formatDate($date = null, $format = 'c')
    {
        if ($date) {
            if (!($date instanceof \DateTime)) {
                $date = new \DateTime($date);
            }
            $date = $date->format($format);
        }
        return $date;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([$this->id]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list ($this->id) = unserialize($serialized);
    }
}
