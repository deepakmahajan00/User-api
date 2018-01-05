<?php

namespace Canopy\Bundle\UserBundle\Command;

use Canopy\Bundle\UserBundle\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ImportCountriesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('canopy:country:import')
            ->setDescription('Import countries in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->get('kernel')->locateResource('@CanopyUserBundle/DataFixtures/Data/countries.yml');
        $countries = Yaml::parse(file_get_contents($path));

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($countries['countries'] as $data) {
            $country = $this->getContainer()->get('canopy.country.repository')->findByIsoCode($data['iso_2']);
            if (!$country) {
                $country = new Country($data['iso_2'], $data['en'], $data['fr'], $data['dialing_code']);

                $em->persist($country);
            }
        }

        $em->flush();

        $output->writeln('Import done.');
    }
}
