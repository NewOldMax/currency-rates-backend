<?php

namespace features\CurrencyRates\Context;

use CurrencyRates\Entity\User;

class PairsContext extends CommonContext
{

    protected $pairs = [];
    protected $form = [];
    protected $foreignPair;

    /**
     * @Given Pairs exist
     */
    public function pairsExist()
    {
        $manager = $this->getContainer()->get('app_pair_manager');
        $em = $this->getEntityManager();
        $currencies = [
            'USD',
            'CAD',
        ];
        for ($i = 0; $i < 2; $i++) {
            $pair = $manager->create([
                'baseCurrency' => 'EUR',
                'targetCurrency' => $currencies[$i],
                'value' => ($i + 1) * 10,
                'duration' => '5 weeks',
                'user' => $this->authorized_user,
            ]);
            $em->persist($pair);
            $this->pairs []= $pair;
        }
        $otherUser = $this->createUser('foreignPair@example.com', User::ROLE_USER);
        $pair = $manager->create([
            'baseCurrency' => 'EUR',
            'targetCurrency' => 'USD',
            'value' => 100.5,
            'duration' => '1 week',
            'user' => $otherUser,
        ]);
        $em->persist($pair);
        $this->foreignPair = $pair;

        $em->flush();
    }

    /**
     * @Given Rates exist
     */
    public function ratesExist()
    {
        $manager = $this->getContainer()->get('app_currency_rate_manager');
        $em = $this->getEntityManager();
        for ($i = 1; $i < 11; $i++) {
            $rate = $manager->create([
                'currency' => 'USD',
                'value' => ($i + 1) * 0.01,
                'date' => date('Y-m-'.$i),
            ]);
            $em->persist($rate);
        }
        $em->flush();
    }

    /**
     * @Given I want to get list of pairs
     */
    public function iWantToGetListOfPairs()
    {
        $this->get('/api/pairs');
    }

    /**
     * @Then I see list of pairs
     */
    public function iSeeListOfPairs()
    {
        try {
            $this
                ->responseCodeShouldBe(200)
                ->jsonResponse()
                ->equal('[
                {
                    "value": 20,
                    "base_currency": "EUR",
                    "target_currency": "CAD",
                    "duration": "5 weeks"
                },
                {
                    "value": 10,
                    "base_currency": "EUR",
                    "target_currency": "USD",
                    "duration": "5 weeks"
                }
                ]', ['at' => 'pairs']);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Given I want to get one pair
     */
    public function iWantToGetOnePair()
    {
        $this->get('/api/pairs/'.$this->pairs[0]->getId());
    }

    /**
     * @Then I see requested pair
     */
    public function iSeeRequestedPair()
    {
        try {
            $this
                ->responseCodeShouldBe(200)
                ->jsonResponse()
                ->equal('
                {
                    "value": 10,
                    "base_currency": "EUR",
                    "target_currency": "USD",
                    "duration": "5 weeks"
                }', ['at' => 'pair']);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Given I want to get foreign pair
     */
    public function iWantToGetForeignPair()
    {
        $this->get('/api/pairs/'.$this->foreignPair->getId());
    }

    /**
     * @Given I fill form field :field with :value
     */
    public function iFillFormFieldWith($field, $value)
    {
        $this->form[$field] = $value;
    }

    /**
     * @Given Submit filled form
     */
    public function submitFilledForm()
    {
        $this->patch('/api/pairs/'.$this->pairs[0]->getId(), $this->form);
    }

    /**
     * @Then I see field :field filled with :value
     */
    public function iSeeFieldFilledWith($field, $value)
    {
        if ($field != 'value') {
            $value = json_encode($value);
        }
        try {
            $this->jsonResponse()
                ->equal($value, ['at' => "pair/$field"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Given I want to create new pair
     */
    public function iWantToCreateNewPair()
    {
        $this->post('/api/pairs', [
            'baseCurrency' => 'CAD',
            'targetCurrency' => 'USD',
            'value' => 99.999,
            'duration' => '25 weeks',
        ]);
    }

    /**
     * @Then I see created pair
     */
    public function iSeeCreatedPair()
    {
        try {
            $this
                ->responseCodeShouldBe(201)
                ->jsonResponse()
                ->equal('
                {
                    "value": 99.999,
                    "base_currency": "CAD",
                    "target_currency": "USD",
                    "duration": "25 weeks"
                }', ['at' => 'pair']);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @Given I want to delete my pair
     */
    public function iWantToDeleteMyPair()
    {
        $this->delete('/api/pairs/'.$this->pairs[0]->getId());
    }

    /**
     * @Given I want to get rates for pair
     */
    public function iWantToGetRatesForPair()
    {
        $this->get('/api/pairs/'.$this->pairs[0]->getId().'/historical');
    }

    /**
     * @Then I see rates
     */
    public function iSeeRates()
    {
        try {
            $this
                ->responseCodeShouldBe(200)
                ->jsonResponse()
                ->equal('[
                    {
                        "currency":"USD",
                        "value":0.11,
                        "date":"'.date('Y-m-10').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.1,
                        "date":"'.date('Y-m-09').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.09,
                        "date":"'.date('Y-m-08').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.08,
                        "date":"'.date('Y-m-07').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.07,
                        "date":"'.date('Y-m-06').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.06,
                        "date":"'.date('Y-m-05').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.05,
                        "date":"'.date('Y-m-04').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.04,
                        "date":"'.date('Y-m-03').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.03,
                        "date":"'.date('Y-m-02').'"
                    },
                    {
                        "currency":"USD",
                        "value":0.02,
                        "date":"'.date('Y-m-01').'"
                    }
                ]', ['at' => 'rates']);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }
}
