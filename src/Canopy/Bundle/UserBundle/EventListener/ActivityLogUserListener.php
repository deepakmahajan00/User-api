<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\CommonBundle\EventListener\ActivityLogListener;
use Canopy\Bundle\UserBundle\Entity\Address;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ActivityLogUserListener extends ActivityLogListener
{
    protected $excludedFields = ['verificationCode'];

    public function onFlush(OnFlushEventArgs $args)
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();

        $alreadyDone = [];

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof $this->class && !($entity instanceof Address && $entity->getUser() instanceof $this->class)) {
                continue;
            }

            if ($entity instanceof $this->class) {
                $user = $entity;
                $address = $entity->getAddress();
            } else {
                $user = $entity->getUser();
                $address = $entity;
            }

            if (in_array($user->getUuid(), $alreadyDone)) {
                continue;
            }

            $alreadyDone[] = $user->getUuid();

            $data = ['uuid' => $this->getUserIdentifier($token->getUser()), 'data' => ['details' => $user->getId()]];

            foreach ([$user, $address] as $e) {
                if (null === $e) {
                    continue;
                }

                foreach ($uow->getEntityChangeSet($e) as $attribute => $value) {
                    if (in_array($attribute, $this->excludedFields)) {
                        continue;
                    }

                    $data['data']['old_value'][$attribute] = $value[0];
                    $data['data']['new_value'][$attribute] = $value[1];
                }
            }

            $this->publishActivityLog($token->getUser(), $data, $this->category);
        }
    }
}
