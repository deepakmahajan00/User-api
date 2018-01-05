<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractRepository extends EntityRepository
{
    /**
     * Gets all elements paginated according to the page provided, number of elements
     * by page ($maxPerPage) and an array of filters.
     *
     * @param int          $page       Index of the page needed to get groups.
     * @param int          $maxPerPage Maximum number of groups by page.
     * @param array | null $filters    Filters (string) needed to get certain groups.
     *
     * @return ArrayCollection
     */
    public function getAllPaginated($page = 1, $maxPerPage = 10, array $filters = null)
    {
        $offset = ($page * $maxPerPage) - $maxPerPage;

        $queryBuilder = $this->getAllQuery($filters);

        return $this
            ->filterQuery($queryBuilder, $filters)
            ->setFirstResult($offset)
            ->setMaxResults($maxPerPage)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Completes a queryBuilder according to an array of filters provided: the point
     * is to filter the collection of elements that will be taken.
     *
     * @param QueryBuilder $queryBuilder
     * @param array | null $filters
     *
     * @return QueryBuilder
     */
    public function filterQuery(QueryBuilder $queryBuilder, array $filters = null)
    {
        $alias = $queryBuilder->getRootAlias();

        if (!empty($filters)) {
            foreach ($filters as $name => $filter) {
                if (!empty($filter) && property_exists($this->getClassName(), $name) && !isset($filters['users']) && !isset($filters['role'])) {
                    $queryBuilder
                        ->andWhere($alias.'.'.$name.' = :'.$name)
                        ->setParameter($name, $filter)
                    ;
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * Gets the total of records according to the filters provided.
     *
     * @param array | null $filters Filters (string) needed to get certain elements.
     *
     * @return int
     */
    public function getTotalCount(array $filters = null)
    {
        $queryBuilder = $this->countAll($filters);

        return $this
            ->filterQuery($queryBuilder, $filters)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * This method must be implemented to return a queryBuilder to
     * return all elements in database according to the filters passed.
     * This method has to contain join and orderby statement if needed.
     * The filtering is already done, no need to implement it.
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    abstract protected function getAllQuery();

    /**
     * This method must be implemented to return a queryBuilder to
     * count all elements.
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    abstract protected function countAll();
}
