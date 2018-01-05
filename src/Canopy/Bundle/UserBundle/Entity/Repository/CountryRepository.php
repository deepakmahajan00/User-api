<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
    public function getCountriesBy(array $parameters)
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if ('all' != $parameters['query']) {
            $queryBuilder
                ->where('LOWER(c.'.$parameters['lang'].') LIKE :query')
                ->setParameter('query', strtolower($parameters['query']).'%')
            ;
        }

        return $queryBuilder->orderBy('c.en', 'ASC')->getQuery()->getResult();
    }
}
