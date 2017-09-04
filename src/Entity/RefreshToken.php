<?php
namespace CurrencyRates\Entity;

class RefreshToken
{
    //Properties
    protected $id;
    protected $token;
    protected $user;
    protected $valid;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct($user)
    {
        $this->user = $user;
        $this->token = bin2hex(openssl_random_pseudo_bytes(64));
        $this->updateValid();
    }

    public function toArray()
    {
        return [
            'token' => $this->token,
            'user_id' => $this->getUserId(),
            'valid' => $this->valid->format('c'),
        ];
    }

    /**
     * Get refreshToken.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    public function updateValid()
    {
        $currentDate = new \DateTime();
        $this->valid = $currentDate->modify('+1 month');
    }

    /**
     * Get valid.
     *
     * @return \DateTime
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Get userId.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user->getId();
    }

    /**
     * Check if is a valid refresh token.
     *
     * @return bool
     */
    public function isValid()
    {
        $datetime = new \DateTime();
        return ($this->valid >= $datetime) ? true : false;
    }

    /**
     * @return string Refresh Token
     */
    public function __toString()
    {
        return $this->getRefreshToken();
    }
}
