<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class PermissionRepository extends AbstractRepository
{
    /**
     * Constructs a QueryBuilder to get all permission ordered by name.
     *
     * @return QueryBuilder
     */
    protected function getAllQuery(array $filters = null)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->distinct()
            ->orderBy('p.name', 'ASC')
        ;

        if (isset($filters['user'])) {
            $queryBuilder = $this->getUserPermission($filters['user'], $queryBuilder);
        }

        return $queryBuilder;
    }

    private function getUserPermission(User $user, QueryBuilder $queryBuilder)
    {
        return $queryBuilder
            ->leftJoin('p.groups', 'g')
            ->leftJoin('g.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;
    }

    /**
     * Gets the query builder to count all permissions.
     *
     * @return QueryBuilder
     */
    protected function countAll(array $filters = null)
    {
        $queryBuilder = $this
            ->getEntityManager()->createQueryBuilder()
            ->select('count(DISTINCT p.name)')
            ->from('CanopyUserBundle:Permission', 'p')
        ;

        if (isset($filters['user'])) {
            $queryBuilder = $this->getUserPermission($filters['user'], $queryBuilder);
        }

        return $this->filterQuery($queryBuilder, $filters);
    }
}
