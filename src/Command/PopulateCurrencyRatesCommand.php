<?php

namespace CurrencyRates\Command;

use CurrencyRates\Entity\CurrencyRate;
use CurrencyRates\Service\Currency;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateCurrencyRatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('currency-rates:rate:populate')
            ->setDescription('Fetch rates for all currencies from fixer.io from last 25 weeks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastDate = new \DateTime(date('Y-m-d'));
        $firstDate = clone $lastDate;
        $firstDate->modify('-25 weeks');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($firstDate, $interval, $lastDate);

        $format = 'Y-m-d';

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($period as $date) {
            if (!$em->getRepository(CurrencyRate::class)->findOneByDate($date)) {
                try {
                    $rates = $this->getContainer()
                        ->get('app_currency_rate_manager')
                        ->fetchRates(Currency::CURRENCIES, $date);
                    if ($rates) {
                        $output->writeln('Populated for '.$rates[0]->getDate()->format($format));
                    }
                } catch (\Exception $ex) {
                    $output->writeln(
                        'An error occured for rate populating ('.$date->format($format).'). '.
                        'Please run currency-rates:rate:get-all --date='.$date->format($format).' '.
                        'to handle it manually.'
                    );
                }
            }
        }
        $output->writeln('Populating is finished.');
    }
}
