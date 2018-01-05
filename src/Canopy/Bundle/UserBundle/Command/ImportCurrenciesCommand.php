<?php

namespace Canopy\Bundle\UserBundle\Command;

use Canopy\Bundle\UserBundle\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ImportCurrenciesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('canopy:currencies:import')
            ->setDescription('Import currencies in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->get('kernel')->locateResource('@CanopyUserBundle/DataFixtures/Data/currencies.yml');
        $currencies = Yaml::parse(file_get_contents($path));
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($currencies['currencies'] as $data) {
            $currency = $this->getContainer()->get('canopy.currency.repository')->findByIsoCode($data['iso_code']);
            if (!$currency) {
                $currency = new Currency($data['iso_code'], $data['value']);

                $em->persist($currency);
            }
        }

        $em->flush();

        $output->writeln('Import done.');
    }
}
