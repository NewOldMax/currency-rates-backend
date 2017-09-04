<?php
namespace CurrencyRates\Entity;

class Token
{
    //Properties
    protected $id;
    protected $token;
    protected $expiredAt;

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
        $this->token = $fields['token'];
        $this->expiredAt = $fields['expiredAt'];
    }

    public function createFromArray($fields)
    {
        return new self($fields);
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getExpiredAt()
    {
        return $this->expiredAt;
    }
}
