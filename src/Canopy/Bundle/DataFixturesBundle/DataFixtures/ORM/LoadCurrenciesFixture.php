<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\Currency;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadCurrenciesFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $currencies = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/currencies.yml')));

        foreach ($currencies as $data) {
            $currency = new Currency($data['iso_code'], $data['value']);

            $manager->persist($currency);
            $this->setReference('Currency.'.$currency->getIsoCode(), $currency);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }
}
