<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class OrganisationRepository extends AbstractRepository
{
    /**
     * Constructs a QueryBuilder to get all organisations ordered by name.
     *
     * @return QueryBuilder
     */
    protected function getAllQuery(array $filters = null)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->orderBy('o.name', 'ASC')
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
            ->select('count(o)')
            ->from('CanopyUserBundle:Organisation', 'o')
        ;

        if (isset($filters['user'])) {
            $queryBuilder = $this->filterByUser($queryBuilder, $filters['user']);
        }

        return $queryBuilder;
    }

    private function filterByUser(QueryBuilder $queryBuilder, User $user)
    {
        return $queryBuilder
            ->leftJoin('o.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;
    }

    public function getEligibleOrganisationQb($host)
    {
        return $this->createQueryBuilder('o')
            ->join('o.restrictedDomainNames', 'dn')
            ->where('dn.value = :host')
            ->setParameter('host', $host);
    }

    public function getEligibleOrganisation($host)
    {
        return $this->getEligibleOrganisationQb($host)->getQuery()->getResult();
    }
}
