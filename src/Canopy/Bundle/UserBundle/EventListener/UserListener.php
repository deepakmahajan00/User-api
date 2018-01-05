<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\CommonBundle\Endpoint\EventEndpoint;
use Canopy\Bundle\UserBundle\Entity\Permission;
use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\MailBundle\Mailer\BrandedMailer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class UserListener implements EventSubscriber
{
    protected $endpoint;
    protected $mailer;

    public function __construct(EventEndpoint $endpoint, BrandedMailer $mailer)
    {
        $this->endpoint = $endpoint;
        $this->mailer = $mailer;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postLoad,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof User) {
            return;
        }

        /*
         * Send email
         */
        $this->mailer->sendBrandedMail(
            'registration',
            $entity->getFromCompany(),
            $entity->getEmail(),
            [
                'fullname' => $entity->getFullname(),
                'email' => $entity->getEmail(),
            ]
        );

        /*
         * Send event to Dashboard API
         */
        $content = ['user' => $entity];
        $topics = ['user-new'];

        if ($organisation = $entity->getOrganisation()) {
            $content['organisation'] = $organisation;
            $content['organisation_users'] = $organisation->getUsers();
            $content['organisation_owners'] = $args->getObjectManager()->getRepository('CanopyUserBundle:User')->getOrganisationOwners($organisation);

            $topics[] = 'organisation-new-user';
        }

        $this->endpoint->publish($topics, $content);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof User) {
            return;
        }

        $conn = $args->getEntityManager()->getConnection();

        /*
         * Collect all the permissions for a user
         */
        $query = '
            SELECT
                p.name
            FROM
                canopy_group g
            LEFT JOIN
                canopy_group_permission gp
                ON g.id = gp.group_id
            LEFT JOIN
                canopy_permission p
                ON gp.permission_id = p.id
            WHERE
                g.name IN (?)
        ';

        $paramData = [$entity->getRoles()];
        $paramType = [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY];

        // Specific cases with mail verification
        if (!$entity->isVerified()) {
            $query .= ' AND p.name NOT IN (?)';
            $paramData[] = Permission::$mailVerifiedOnly;
            $paramType[] = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
        }

        $results = $conn->fetchAll($query, $paramData, $paramType);

        $permissions = [];
        foreach ($results as $line) {
            $permissions[] = $line['name'];
        }

        $entity->setPermissions($permissions);
    }
}
