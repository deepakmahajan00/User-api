<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\Address;
use Canopy\Bundle\UserBundle\Entity\DomainName;
use Canopy\Bundle\UserBundle\Entity\Organisation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadOrganisationsFixture extends AbstractFixture
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        // Avoid calls to other API
        $this->removeListeners(
            ['canopy.event_listener.organisation_log'],
            $manager
        );

        // Load organisation data from YML
        $conf = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/organisations.yml')));

        foreach ($conf as $orgData) {
            $address = new Address(
                $orgData['address']['state'],
                $orgData['address']['city'],
                $orgData['address']['zipcode'],
                $this->getReference($orgData['address']['country']),
                $orgData['address']['street1'],
                isset($orgData['address']['street2']) ? $orgData['address']['street2'] : null
            );
            $manager->persist($address);

            $organisation = new Organisation();
            $organisation->setName($orgData['name']);
            $organisation->setCustomerId($orgData['customerId']);
            $organisation->setDescription($orgData['description']);
            $organisation->setVatNumber($orgData['vatNumber']);
            $organisation->setPolicyUuid($orgData['policyUuid']);
            $organisation->setAnsweredLatestPolicy($orgData['answeredLatestPolicy']);
            $organisation->setCreatedAt(new \Datetime($orgData['createdAt']));
            $organisation->setAddress($address);

            foreach ($orgData['domainNames'] as $domainName) {
                $organisation->addRestrictedDomainName(new DomainName($domainName));
            }

            $this->setReference('Organisation.'.$organisation->getName(), $organisation);
            $manager->persist($organisation);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 60;
    }

    protected function removeListeners(array $subscribersId, ObjectManager $manager)
    {
        $evm = $manager->getEventManager();
        foreach ($subscribersId as $subscriberId) {
            $evm->removeEventSubscriber($this->container->get($subscriberId));
        }
    }
}
