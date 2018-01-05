<?php

namespace Canopy\Bundle\UserBundle\Service;

use Canopy\Bundle\CommonBundle\Endpoint\PolicyEndpoint;
use Canopy\Bundle\MailBundle\Mailer\BrandedMailer;
use Canopy\Bundle\UnboundIdApiClientBundle\Endpoint\DataviewEndpoint;
use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UnboundIDUserManager
{
    private $em;
    private $dataviewEndpoint;
    private $policyEndpoint;
    private $mailer;

    public function __construct(ObjectManager $em, DataviewEndpoint $dataviewEndpoint, PolicyEndpoint $policyEndpoint, BrandedMailer $mailer)
    {
        $this->em = $em;
        $this->dataviewEndpoint = $dataviewEndpoint;
        $this->policyEndpoint = $policyEndpoint;
        $this->mailer = $mailer;
    }

    /**
     * User creation.
     *
     * @param User         $user
     * @param Organisation $organisation
     *
     * @return User
     */
    public function createUser(User $user, Organisation $organisation = null)
    {
        if (!$user->getOrganisation() && !is_null($organisation)) {
            $organisation->accept($user);
        }

        if (null === $user->getPassword()) {
            $user->setPassword($this->generatePassword());
        }

        $userData = $this->dataviewEndpoint->createUser($user);

        // Update of the local $user object with UnboundId information
        $user->setUnboundidUserId($userData['id']);
        $user->setUuid($userData['id']);

        if ($user->getOrganisation()) {
            if (!in_array('ROLE_CUSTOMER_APPROVER', $user->getRoles())) {
                $user->setRoles(['ROLE_CUSTOMER_REQUESTOR']);
            }
            $user->setAnsweredLatestPolicy($user->getOrganisation()->isAnsweredLatestPolicy());
            $user->setPolicyChoice($user->getOrganisation()->getPolicyChoice());
            $user->setPolicyUuid($user->getOrganisation()->getPolicyUuid());
        }

        $user = $this->handlePolicy($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Update a user from a request.
     *
     * @param User         $user
     * @param User         $requestUser
     * @param Organisation $organisation
     *
     * @return User
     */
    public function updateUser(User $user, User $requestUser, Organisation $organisation = null)
    {
        $user->updateFrom($requestUser);

        if (!is_null($organisation)) {
            $organisation->accept($user);
        }

        if ($user->getOrganisation() && in_array('ROLE_REGISTERED_USER', $user->getRoles())) {
            $user->setRoles(['ROLE_CUSTOMER_REQUESTOR']);
        }

        $this->dataviewEndpoint->updateUser($user);

        $user = $this->handlePolicy($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Change the password of the user.
     *
     * @param User $user
     * @param $password
     */
    public function setUserPassword(User $user, $password)
    {
        $this->dataviewEndpoint->setUserPassword($user, $password);

        $user->eraseResetPasswordToken();
        $this->em->flush();
    }

    /**
     * Check the user policies.
     *
     * @param User $user
     *
     * @return User
     */
    private function handlePolicy(User $user)
    {
        $policy = $this->policyEndpoint->getLatestGeneralPolicy();
        $user->setAnsweredLatestPolicy($policy['uuid'] === $user->getPolicyUuid());

        if ($user->isAnsweredLatestPolicy()) {
            $user->setPolicyUuid($policy['uuid']);
        }

        // If the user is CA, it accept the policies for his organisation and the CR of the organisation
        if (in_array('ROLE_CUSTOMER_APPROVER', $user->getRoles()) && $user->isVerified() && $user->getOrganisation()) {
            $organisationPolicy = $user->getOrganisation();

            $organisationPolicy->setAnsweredLatestPolicy($policy['uuid'] === $user->getPolicyUuid());
            $organisationPolicy->setPolicyChoice($user->getPolicyChoice());

            if ($organisationPolicy->isAnsweredLatestPolicy()) {
                $organisationPolicy->setPolicyUuid($policy['uuid']);
            }

            foreach ($organisationPolicy->getUsers() as $organisationUser) {
                $organisationUser->setAnsweredLatestPolicy($user->isAnsweredLatestPolicy());
                $organisationUser->setPolicyChoice($user->getPolicyChoice());
                $organisationUser->setPolicyUuid($user->getPolicyUuid());
            }
        }

        return $user;
    }

    public function generatePassword()
    {
        return str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!?#()[]');
    }

    public function resetPassword(User $user)
    {
        $user->setPassword($this->generatePassword());
        $user->setCredentialsExpired(true);

        return $this->mailer->sendBrandedMail(
            'initiate-reset-password',
            $user->getFromCompany(),
            $user->getEmail(),
            ['email' => $user->getEmail(), 'firstname' => $user->getFirstname()]
        );
    }
}
