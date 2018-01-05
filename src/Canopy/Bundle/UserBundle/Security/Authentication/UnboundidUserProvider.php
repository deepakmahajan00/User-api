<?php

namespace Canopy\Bundle\UserBundle\Security\Authentication;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UnboundidUserProvider.
 *
 * Find the User by its unboundidUserId.
 * Note that this is loaded in the security.yml (as the unboundid provider).
 *
 * @see Canopy/Bundle/UserBundle/Resources/doc/Authentication.md
 * @see http://symfony.com/doc/current/cookbook/security/api_key_authentication.html#the-user-provider
 */
class UnboundidUserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Note that the parameter username is actually the external ID used for reference on UnboundID.
     *
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // Fetch the user from database with the "username"
        $user = $this
            ->entityManager
            ->getRepository('CanopyUserBundle:User')
            ->findOneByUnboundidUserId($username)
        ;

        if (!$user) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unknown user');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'Canopy\Bundle\UserBundle\Entity\User' === $class;
    }
}
