<?php

namespace CurrencyRates\Service;

use Doctrine\ORM\EntityManager;
use CurrencyRates\Entity\Event;
use CurrencyRates\Entity\User;
use CurrencyRates\Exception\AppException;
use CurrencyRates\Exception\EntityFieldsValidationException;
use CurrencyRates\Exception\TranslatedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

class Manager
{
    protected $em;
    protected $translator;

    protected $required = [];
    protected $unique = [];
    protected $invalid = [];

    public function __construct($em, $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function validate(array $fields, string $class, $checkEmpty = false)
    {
        try {
            if ($this->required) {
                $this->checkRequired($fields, $this->required, $checkEmpty);
            }
            if ($this->unique) {
                $this->checkUnique($fields, $this->unique, $class);
            }
        } catch (AppException $e) {
            throw new EntityFieldsValidationException($e->getMessage());
        }

        if ($this->invalid) {
            $fields = $this->unsetInvalid($fields, $this->invalid);
        }
        return $fields;
    }

    public function checkCurrency(string $currency) : void
    {
        if (Currency::isValidCurrency($currency) === false) {
            $this->throwException(
                'entity.errors.unknown_value',
                400,
                ['%value%' => $currency]
            );
        }
    }

    public function throwException($msg, $code = 500, $values = [])
    {
        throw new TranslatedException(
            $this->translator,
            $msg,
            $code,
            $values
        );
    }

    protected function isUnique(string $fieldName, $fieldValue, string $entityClass)
    {
        $repository = $this->em->getRepository($entityClass);

        return !$repository->findBy([$fieldName => $fieldValue]);
    }

    protected function prepareDateFields(array $fields, array $dateFieldNames)
    {
        foreach ($dateFieldNames as $date) {
            if (isset($fields[$date]) && !$fields[$date] instanceof \DateTime) {
                $fields[$date] = $this->convertStringToDate($fields[$date]);
            }
        }

        return $fields;
    }

    /**
     * @param $date
     * @return \DateTime|null
     */
    protected function convertStringToDate($date)
    {
        try {
            $date = new \DateTime($date);
        } catch (\Exception $ex) {
            $date = null;
        }
        return $date;
    }

    private function checkRequired(array $fields, array $required, $checkEmpty = false)
    {
        foreach ($required as $key => $value) {
            $exists = isset($fields[$value]);
            $hasValue = true;
            if ($checkEmpty && $exists && !$fields[$value]) {
                $hasValue = false;
            }
            if (!$exists || !$hasValue) {
                throw new AppException($value.' must be provided.');
            }
        }
    }

    private function checkUnique(array $fields, array $unique, string $class)
    {
        foreach ($unique as $key => $value) {
            if (isset($fields[$value]) && !$this->isUnique($value, $fields[$value], $class)) {
                $className = str_replace('CurrencyRates\\Entity\\', '', $class);
                throw new AppException($className.' with '.$value.' '.$fields[$value].' already exists.');
            }
        }
    }

    private function unsetInvalid(array $fields, array $invalid)
    {
        foreach ($invalid as $key => $value) {
            if (isset($fields[$value])) {
                unset($fields[$value]);
            }
        }
        return $fields;
    }
}
