<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends AbstractRepository
{
    /**
     * Constructs a QueryBuilder to get all groups ordered by
     * the fields createdAd then id.
     *
     * @return QueryBuilder
     */
    protected function getAllQuery(array $filters = null)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->orderBy('g.name', 'ASC')
            ->addOrderBy('g.description', 'ASC')
        ;

        if (isset($filters['user'])) {
            $queryBuilder = $this->filterByUser($queryBuilder, $filters['user']);
        }

        return $queryBuilder;
    }

    /**
     * Gets the query builder to count all groups.
     *
     * @return QueryBuilder
     */
    protected function countAll(array $filters = null)
    {
        $queryBuilder = $this
            ->getEntityManager()->createQueryBuilder()
            ->select('count(g)')
            ->from('CanopyUserBundle:Group', 'g')
        ;

        if (isset($filters['user'])) {
            $queryBuilder = $this->filterByUser($queryBuilder, $filters['user']);
        }

        return $queryBuilder;
    }

    private function filterByUser(QueryBuilder $queryBuilder, User $user)
    {
        return $queryBuilder
            ->leftJoin('g.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;
    }
}
