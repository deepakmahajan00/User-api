<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\Country;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadCountriesFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $countries = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/countries.yml')));

        foreach ($countries as $data) {
            $country = new Country($data['iso_2'], $data['en'], $data['fr'], $data['dialing_code']);

            $manager->persist($country);
            $this->setReference('Country.'.$country->getIsoCode(), $country);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}
