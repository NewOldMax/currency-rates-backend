<?php

namespace CurrencyRates\Command;

use CurrencyRates\Service\Currency;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetAllCurrencyRatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('currency-rates:rate:get-all')
            ->setDescription('Fetch rates for all currencies from fixer.io')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime($input->getOption('date'));
        $rates = $this->getContainer()
            ->get('app_currency_rate_manager')
            ->fetchRates(Currency::CURRENCIES, $date);

        $output->writeln(count($rates).' rates fetched ('.$date->format('Y-m-d').')');
    }
}
