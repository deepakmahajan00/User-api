<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

class PrototypeGroupRepository extends AbstractRepository
{
    /**
     * Constructs a QueryBuilder to get all groups ordered by
     * the fields createdAd then id.
     *
     * @return QueryBuilder
     */
    protected function getAllQuery(array $filters = null)
    {
        $queryBuilder = $this->createQueryBuilder('pg')
            ->orderBy('pg.name', 'ASC')
        ;

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
            ->select('count(pg)')
            ->from('CanopyUserBundle:PrototypeGroup', 'pg')
        ;

        return $this->filterQuery($queryBuilder, $filters);
    }
}
