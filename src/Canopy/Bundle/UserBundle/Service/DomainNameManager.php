<?php

namespace Canopy\Bundle\UserBundle\Service;

use Canopy\Bundle\UserBundle\Entity\DomainName;
use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class DomainNameManager
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createIfNotExist(User $user, Organisation $organisation)
    {
        $domain = $user->getEmailHost();

        if (!$domainName = $this->objectManager->getRepository('CanopyUserBundle:DomainName')->findOneBy(['value' => $domain, 'organisation' => $organisation])) {
            $domainName = new DomainName($domain);
            $organisation->addRestrictedDomainName($domainName);

            $this->objectManager->persist($domainName);
            $this->objectManager->flush($domainName);
        }
    }
}
