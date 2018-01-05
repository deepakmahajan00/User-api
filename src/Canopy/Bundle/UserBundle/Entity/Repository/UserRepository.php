<?php

namespace Canopy\Bundle\UserBundle\Entity\Repository;

use Canopy\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Canopy\Bundle\UserBundle\Entity\Organisation;

class UserRepository extends AbstractRepository
{
    /**
     * Constructs a QueryBuilder to get all groups ordered by
     * the fields createdAd then id.
     *
     * @return QueryBuilder
     */
    protected function getAllQuery(array $filters = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->orderBy('u.firstname', 'ASC')
        ;

        if (isset($filters['role']) && 'all' !== $filters['role']) {
            $queryBuilder = $this->filterByRole($queryBuilder, $filters['role']);
        }

        return $queryBuilder;
    }

    /**
     * Gets the query builder to count all users.
     *
     * @return QueryBuilder
     */
    protected function countAll(array $filters = null)
    {
        $queryBuilder = $this
            ->getEntityManager()->createQueryBuilder()
            ->select('count(u)')
            ->from('CanopyUserBundle:User', 'u')
        ;

        if (isset($filters['role']) && 'all' !== $filters['role']) {
            $queryBuilder = $this->filterByRole($queryBuilder, $filters['role']);
        }

        return $queryBuilder;
    }

    public function filterByRole(QueryBuilder $queryBuilder, $role)
    {
        return $queryBuilder
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%'.$role.'%')
        ;
    }

    /**
     * Return the owners of an organisation.
     *
     * @param $organisation
     *
     * @return QueryBuilder
     */
    public function getOrganisationOwners($organisation)
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->leftJoin('u.organisation', 'o')
            ->andWhere('o.id = :organisationId')
            ->andWhere('u.organisationOwner = :isOwner')
            ->orderBy('u.firstname', 'ASC')
            ->setParameter('organisationId', $organisation->getId())
            ->setParameter('isOwner', true)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Return customer approver.
     *
     * @param $organisation
     * @param String $role
     */
    public function getOrganisationCustomerApprovers(Organisation $organisation, $role, $limit)
    {
        $queryBuilder = $this
        ->createQueryBuilder('u')
        ->leftJoin('u.organisation', 'o')
        ->andWhere('o.id = :organisationId')
        ->andWhere('u.roles LIKE :roles')
        ->andWhere('u.enabled = :enabled')
        ->andWhere('u.verified = :verified')
        ->orderBy('u.firstname', 'ASC')
        ->setParameter('organisationId', $organisation->getId())
        ->setParameter('roles', '%"'.$role.'"%')
        ->setParameter('enabled', true)
        ->setParameter('verified', true)
        ;

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getOrganisationCustomerApproversByUserUuid($userUuid, $role = 'ROLE_CUSTOMER_APPROVER')
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->join('u.organisation', 'o')
            ->join('o.users', 'u2')
            ->where('u2.uuid = :uuid')
            ->andWhere('u.roles LIKE :roles')
            ->andWhere('u.enabled = :enabled')
            ->andWhere('u.verified = :verified')
            ->orderBy('u.firstname', 'ASC')
            ->setParameter('uuid', $userUuid)
            ->setParameter('roles', '%"'.$role.'"%')
            ->setParameter('enabled', true)
            ->setParameter('verified', true);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getByUuids(array $uuids)
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.uuid IN (:uuids)')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('uuids', $uuids)
            ->setParameter('enabled', true);

        return $queryBuilder->getQuery()->getResult();
    }
}
