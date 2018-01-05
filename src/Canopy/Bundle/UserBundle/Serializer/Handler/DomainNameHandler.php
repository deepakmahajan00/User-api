<?php

namespace Canopy\Bundle\UserBundle\Serializer\Handler;

use Canopy\Bundle\UserBundle\Entity\DomainName;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\GraphNavigator;

class DomainNameHandler implements SubscribingHandlerInterface
{
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'Canopy\Bundle\UserBundle\Entity\DomainName',
                'method' => 'deserialize',
            ),
        );
    }

    public function deserialize(JsonDeserializationVisitor $visitor, $data)
    {
        $em = $this->entityManager;

        if ($domainName = $em->getRepository('CanopyUserBundle:DomainName')->findOneByValue($data['value'])) {
            $organisation = $em->getRepository('CanopyUserBundle:Organisation')->find($visitor->getResult()->getId());
            if ($organisation === $domainName->getOrganisation()) {
                return $domainName;
            }
        }

        return new DomainName($data['value']);
    }
}
