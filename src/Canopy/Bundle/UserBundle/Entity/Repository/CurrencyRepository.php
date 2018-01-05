<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CurrencyRepository extends EntityRepository
{
    public function getCurrenciesBy(array $parameters)
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if ('all' != $parameters['query']) {
            $queryBuilder
                ->where('LOWER(c.value) LIKE :query')
                ->orWhere('LOWER(c.isoCode) LIKE :query')
                ->setParameter('query', strtolower($parameters['query']).'%')
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
