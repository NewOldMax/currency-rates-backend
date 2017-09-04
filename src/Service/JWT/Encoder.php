<?php
namespace CurrencyRates\Service\JWT;

use JWT\Authentication\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class Encoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    protected $key;

    protected $ttl;

    /**
     * __construct
     */
    public function __construct($key, $ttl)
    {
        $this->key = $key;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        if ($this->ttl) {
            $data['exp'] = time() + $this->ttl;
        }
        return JWT::encode($data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            return (array) JWT::decode($token, $this->key);
        } catch (\Exception $e) {
            return false;
        }
    }
}
