<?php

namespace CurrencyRates\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TranslatedException extends HttpException
{
    protected $errorCode;
    protected $code;

    public function __construct($translator, string $errorCode, $code = 400, array $placeholders = [])
    {
        $this->errorCode = $errorCode;
        $this->code = $code;
        parent::__construct($code, $translator->trans($errorCode, $placeholders));
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getStatusCode()
    {
        return $this->code;
    }
}
