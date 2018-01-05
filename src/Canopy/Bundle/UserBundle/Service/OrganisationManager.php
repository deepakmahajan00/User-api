<?php

namespace Canopy\Bundle\UserBundle\Service;

use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class OrganisationManager
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createFromUser(User $user)
    {
        $organisation = new Organisation();
        $organisation->setCustomerId($user->getCustomerId());
        $organisation->setAddress(clone $user->getAddress());
        $organisation->setName($user->getCompany());
        $organisation->setDescription($user->getCompany());
        $organisation->setVatNumber($user->getVatNumber());

        if (in_array('ROLE_CUSTOMER_APPROVER', $user->getRoles())) {
            $organisation->setPolicyUuid($user->getPolicyUuid());
            $organisation->setPolicyChoice($user->getPolicyChoice());
            $organisation->setAnsweredLatestPolicy($user->isAnsweredLatestPolicy());
        }

        $this->save($organisation);

        return $organisation;
    }

    public function save(Organisation $organisation, $flush = true)
    {
        $this->objectManager->persist($organisation);

        if ($flush) {
            $this->objectManager->flush();
        }
    }
}
