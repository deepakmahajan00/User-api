<?php

namespace Canopy\Bundle\UserBundle\Security\Authorisation;

use Canopy\Bundle\UserBundle\Entity\Group;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class GroupVoter.
 *
 * @deprecated Should be removed.
 */
class GroupVoter implements VoterInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function supportsAttribute($attribute)
    {
        return 'VIEW_GROUP' === $attribute;
    }

    public function supportsClass($class)
    {
        return $class instanceof Group;
    }

    /**
     * Allows the access if the current user can see the notification.
     *
     * @param TokenInterface $token
     * @param Group          $object
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                return VoterInterface::ACCESS_ABSTAIN;
            }
        }

        if (!$this->supportsClass($object)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $request = $this->requestStack->getCurrentRequest();
        $user = $token->getUser();

        if ($user->getUuid() != $request->get('uuid')) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (!$user->hasPermission('FEAT_ORG_GROUP_SHOW')) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (!$object->hasUser($user)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
