<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadUsersFixture extends AbstractFixture
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        // Avoid calls to other API
        $this->removeListeners(
            ['canopy.event_listener.user', 'canopy.event_listener.user_log'],
            $manager
        );

        // Load users data from YML
        $conf = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/users.yml')));

        foreach ($conf as $userData) {
            $user = new User($userData['unboundIdUuid']);
            $user->setEmail($userData['email']);
            $user->setFirstname($userData['firstname']);
            $user->setLastname($userData['lastname']);
            $user->setAvatar($userData['avatar']);
            $user->setDialingCode($userData['dialingCode']);
            $user->setMobileNumber($userData['mobileNumber']);
            $user->setCompany($userData['company']);
            $user->setFromCompany($userData['fromCompany']);
            $user->setVatNumber($userData['vatNumber']);
            $user->setVerified($userData['verified']);
            $user->setPolicyUuid($userData['policyUuid']);
            $user->setAnsweredLatestPolicy($userData['answeredLatestPolicy']);
            $user->setCustomerId($userData['customerId']);
            $user->setCurrency($this->getReference($userData['currency']));
            if ($userData['organisation']) {
                $user->setOrganisation($this->getReference($userData['organisation']));
            }
            $user->setOrganisationOwner($userData['organisationOwner']);
            $user->setRoles($userData['roles']);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 70;
    }

    protected function removeListeners(array $subscribersId, ObjectManager $manager)
    {
        $evm = $manager->getEventManager();
        foreach ($subscribersId as $subscriberId) {
            $evm->removeEventSubscriber($this->container->get($subscriberId));
        }
    }
}
