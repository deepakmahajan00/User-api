<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\CommonBundle\Endpoint\EventEndpoint;
use Canopy\Bundle\CommonBundle\Endpoint\PolicyEndpoint;
use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class PolicyAcceptedListener implements EventSubscriber
{
    protected $eventEndpoint;
    protected $policyEndpoint;

    public function __construct(EventEndpoint $eventEndpoint, PolicyEndpoint $policyEndpoint)
    {
        $this->eventEndpoint = $eventEndpoint;
        $this->policyEndpoint = $policyEndpoint;
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

        if (!$args->hasChangedField('policyUuid')) {
            return;
        }

        $policy = $this->policyEndpoint->getLatestGeneralPolicy();
        $data = ['policy' => $policy];

        /*
         * Send event to Dashboard API
         */
        $topics = ['policy-approved'];
        $content = [
            'user' => $entity,
            'data' => $data,
        ];

        $this->eventEndpoint->publish($topics, $content);
    }
}
