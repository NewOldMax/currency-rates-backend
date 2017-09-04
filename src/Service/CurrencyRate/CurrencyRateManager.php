<?php

namespace CurrencyRates\Service\CurrencyRate;

use CurrencyRates\Entity\CurrencyRate;
use CurrencyRates\Service\Manager;
use CurrencyRates\Service\Currency;
use CurrencyRates\Service\Interfaces\CurrencyRateFetcherInterface;
use CurrencyRates\Exception\TranslatedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class CurrencyRateManager extends Manager
{
    protected $required = ['currency', 'date', 'value'];
    protected $unique = [];

    protected $rateFetcher;

    public function setRateFetcher(CurrencyRateFetcherInterface $rateFetcher)
    {
        $this->rateFetcher = $rateFetcher;
    }

    private function create(array $fields) : CurrencyRate
    {
        $this->validate($fields, CurrencyRate::class);
        $fields = $this->prepare($fields);
        $this->checkCurrency($fields['currency']);
        $rate = CurrencyRate::createFromArray($fields);
        return $rate;
    }

    private function update($id, array $fields) : CurrencyRate
    {
        $this->required = ['value'];
        $this->validate($fields, CurrencyRate::class);
        $rate = $this->get($id);
        $rate->patch($fields);
        return $rate;
    }

    private function get($id) : CurrencyRate
    {
        if ($rate = $this->em->getRepository(CurrencyRate::class)->find($id)) {
            return $rate;
        }

        throw new TranslatedException(
            $this->translator,
            'entity.errors.not_found',
            404,
            ['%entity%' => 'CurrencyRate', '%id%' => $id]
        );
    }

    public function fetchRates(array $currencies, \DateTime $date)
    {
        $result = [];
        $response = $this->rateFetcher->fetchMany($currencies, $date);
        if ($rates = $response->getRates()) {
            $responseDate = $response->getDate();
            foreach ($rates as $key => $value) {
                $data = [
                    'currency' => $key,
                    'value' => $value,
                    'date' => $responseDate,
                ];
                $rate = null;
                if ($rate = $this->getRateByCurrencyAndDate($key, $responseDate)) {
                    $rate = $this->update($rate->getId(), $data);
                } else {
                    $rate = $this->create($data);
                    $this->em->persist($rate);
                }
                $result []= $rate;
            }
            $this->em->flush();
        }
        return $result;
    }

    private function getRateByCurrencyAndDate($currency, $date)
    {
        return $this->em->getRepository(CurrencyRate::class)->findOneBy([
            'currency' => $currency,
            'date' => $date,
        ]);
    }

    private function prepare(array $fields) : array
    {
        if ($fields['date'] && !$fields['date'] instanceof \DateTime) {
            $fields['date'] = $this->convertStringToDate($fields['date']);
        }
        return $fields;
    }

    private function checkCurrency(string $currency) : void
    {
        if (in_array($currency, Currency::CURRENCIES) === false) {
            throw new TranslatedException(
                $this->translator,
                'entity.errors.unknown_value',
                400,
                ['%value%' => $currency]
            );
        }
    }
}
