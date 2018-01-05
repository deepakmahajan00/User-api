<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\CommonBundle\Endpoint\EventEndpoint;
use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class UserJoinOrganisationListener implements EventSubscriber
{
    protected $eventEndpoint;

    public function __construct(EventEndpoint $eventEndpoint)
    {
        $this->eventEndpoint = $eventEndpoint;
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

        if (!$args->hasChangedField('organisation') || null === $args->getNewValue('organisation')) {
            return;
        }

        $organisation = $args->getNewValue('organisation');

        /*
         * Send event to Dashboard API
         */
        $topics = ['organisation-new-user'];
        $content = [
            'user' => $entity,
            'organisation' => $organisation,
            'organisation_users' => $organisation->getUsers(),
            'organisation_owners' => $args->getObjectManager()->getRepository('CanopyUserBundle:User')->getOrganisationOwners($organisation),
        ];

        $this->eventEndpoint->publish($topics, $content);
    }
}
