<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\CommonBundle\EventListener\ActivityLogListener;
use Canopy\Bundle\UserBundle\Entity\Address;
use Canopy\Bundle\UserBundle\Entity\DomainName;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ActivityLogOrganisationListener extends ActivityLogListener
{
    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();

        $alreadyDone = [];
        $data = [];

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof $this->class && !($entity instanceof Address && $entity->getOrganisation() instanceof $this->class)) {
                continue;
            }

            if ($entity instanceof $this->class) {
                $organisation = $entity;
                $address = $entity->getAddress();
            } else {
                $organisation = $entity->getOrganisation();
                $address = $entity;
            }

            if (in_array($organisation->getId(), $alreadyDone)) {
                continue;
            }

            $alreadyDone[] = $organisation->getId();

            foreach ([$organisation, $address] as $e) {
                foreach ($uow->getEntityChangeSet($e) as $attribute => $value) {
                    $data[$organisation->getId()]['data']['old_value'][$attribute] = $value[0];
                    $data[$organisation->getId()]['data']['new_value'][$attribute] = $value[1];
                }
            }
        }

        $inserted = [];
        $deleted = [];
        $organisations = [];

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof DomainName) {
                continue;
            }

            $inserted[] = $entity->getValue();
            $organisations[] = $entity->getOrganisation();
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof DomainName) {
                continue;
            }

            $deleted[] = $entity->getValue();
            $organisations[] = $entity->getOrganisationBackup();
        }

        foreach ($organisations as $organisation) {
            $newDomains = $organisation->getRestrictedDomainNames()->toArray();
            $oldDomains = array_diff($newDomains, $inserted);
            $oldDomains = array_merge($oldDomains, $deleted);

            $data[$organisation->getId()]['data']['old_value']['restrictedDomainNames'] = implode(', ', $oldDomains);
            $data[$organisation->getId()]['data']['new_value']['restrictedDomainNames'] = implode(', ', $newDomains);
        }

        foreach ($data as $organisationId => $changeSet) {
            $changeSet['uuid'] = $this->getUserIdentifier($token->getUser());
            $changeSet['data']['details'] = $organisationId;

            $this->publishActivityLog($token->getUser(), $changeSet, $this->category);
        }
    }
}
