<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\MailBundle\Mailer\BrandedMailer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class UserUpdateListener implements EventSubscriber
{
    protected $mailer;

    public function __construct(BrandedMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof User) {
            return;
        }

        if ($args->hasChangedField('enabled') && false === $args->getNewValue('enabled')) {
            $this->sendUserDisabledEmail($entity);
        }

        if ($args->hasChangedField('credentialsExpired') && true === $args->getNewValue('credentialsExpired')) {
            $this->sendInitiateResetPasswordEmail($entity);
        }
    }

    private function sendUserDisabledEmail(User $user)
    {
        $this->mailer->sendBrandedMail(
            'user_disabled',
            $user->getFromCompany(),
            $user->getEmail(),
            [
                'fullname' => $user->getFullname(),
                'email' => $user->getEmail(),
            ]
        );
    }

    private function sendInitiateResetPasswordEmail(User $user)
    {
        $this->mailer->sendBrandedMail(
            'initiate-reset-password',
            $user->getFromCompany(),
            $user->getEmail(),
            ['email' => $user->getEmail(), 'firstname' => $user->getFirstname()]
        );
    }
}
