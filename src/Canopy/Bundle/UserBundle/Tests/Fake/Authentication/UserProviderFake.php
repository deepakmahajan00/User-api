<?php

namespace Canopy\Bundle\UserBundle\Tests\Fake\Authentication;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderFake implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($username)
    {
        // Fetch the user fixture from database
        $user = $this
            ->entityManager
            ->getRepository('CanopyUserBundle:User')
            ->findOneByUnboundidUserId($username)
        ;

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return;
    }

    public function supportsClass($class)
    {
        return true;
    }
}
