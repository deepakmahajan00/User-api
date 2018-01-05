<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\UserBundle\Representation\UsersRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Conf\Route("/api")
 */
class OrganisationUserController extends AbstractController
{
    /**
     * Returns collection of User in an Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}/users", requirements={"id" = "\d+"}, name="canopy_get_organisation_users")
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display groups.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Number of elements needed.")
     * @QueryParam(name="filters", default=null, nullable=true, array=true, description="Filters that should be applied.")
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_SHOW')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\User>",
     *  filters={
     *      {"name"="page", "dataType"="int"},
     *      {"name"="limit", "dataType"="int"},
     *      {"name"="filters", "dataType"="array"}
     *  },
     *  statusCodes={
     *     200="Returned when the list of users is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation supplied in the url is not found."
     *   }
     * )
     */
    public function getUsersAction(Organisation $organisation, $page, $limit, $filters)
    {
        $page = (int) ($page ?: 1);
        $limit = (int) ($limit ?: 20);
        $filters['organisation'] = $organisation;

        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:User');
        $elements = $repository->getAllPaginated($page, $limit, $filters);
        $total = $repository->getTotalCount($filters);

        return new PaginatedRepresentation(
            new UsersRepresentation($elements),
            'canopy_get_organisation_groups',
            array('id' => $organisation->getId()),
            $page,
            $limit,
            ceil($total / $limit),
            'page',
            'limit',
            true,
            $total
        );
    }

    /**
     * Returns one User.
     *
     * Note that this action also checks that the User is in the said Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}/users/{user_uuid}", name="canopy_get_organisation_user")
     * @View()
     *
     * @Conf\ParamConverter("user", options={"mapping": {"user_uuid": "uuid", "id": "organisation"}})
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_SHOW')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user supplied in the url is not found."
     *   }
     * )
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * Create a new User in the Organisation.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/organisations/{id}/users", name="canopy_post_organisation_user")
     * @View(statusCode=201)
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_EDIT')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     201="Returned when the user is created.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user supplied in the url is not found."
     *   }
     * )
     */
    public function postUserAction(Request $request, Organisation $organisation)
    {
        $context = DeserializationContext::create()->setAttribute('source', 'dashboard');
        $user = $this->get('serializer')->deserialize($request->getContent(), 'Canopy\Bundle\UserBundle\Entity\User', 'json', $context);
        $errors = $this->get('validator')->validate($user, array_merge(['create'], $user->getValidationGroups('dashboard')));

        if (count($errors)) {
            return $this->get('canopy.constraint_violation_list.converter')->createResponse($errors);
        }

        if (!$organisation->canJoin($user)) {
            throw new BadRequestHttpException('Email doesn\'t match restricted domain.');
        }

        $this->get('canopy.api.unboundid.user_management')->createUser($user, $organisation);
        
        /*
         * users from dashboard-ui are created without password,
         * so just after user creation, we are sending email to reset their password
         */
        $this->get('canopy.api.unboundid.user_management')->resetPassword($user);
    }

    /**
     * Update a User in the organisation.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/organisations/{id}/users/{uuid}", name="canopy_put_organisation_user")
     * @View()
     *
     * @Conf\ParamConverter("requestUser", converter="fos_rest.request_body", options={"validator"={"groups"={"edit"}}})
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_EDIT')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     200="Returned when the user is updated.",
     *     400="Returned if information provided in the json are wrong",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user or the organisation supplied in the url is not found."
     *   }
     * )
     */
    public function putUserAction(Organisation $organisation, User $requestUser, User $user)
    {
        $this->get('canopy.api.unboundid.user_management')->updateUser($user, $requestUser, $organisation);
    }

    /**
     * Remove a User from Organisation.
     *
     * Note that this action only set the User's Organisation to null.
     * It does not delete the User.
     *
     * @Conf\Method("DELETE")
     * @Conf\Route("/organisations/{id}/users/{uuid}", name="canopy_delete_organisation_user")
     * @View()
     *
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_EDIT')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     204="Returned when the user is updated.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user or the organisation supplied in the url is not found."
     *   }
     * )
     */
    public function deleteUserAction(Organisation $organisation, User $user)
    {
        $user->setOrganisation(null);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Returns a collection of User (owners) of an Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}/owners", requirements={"id" = "\d+"}, name="canopy_get_organisation_owners")
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_SHOW')")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\User>",
     *  statusCodes={
     *     200="Returned when the user owners is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation supplied in the url is not found."
     *   }
     * )
     */
    public function getOrganisationOwnersAction(Organisation $organisation)
    {
        $owners = $this->getDoctrine()
            ->getRepository('CanopyUserBundle:User')
            ->getOrganisationOwners($organisation);

        return new UsersRepresentation($owners);
    }

    /**
     * Link the current User to the given Organisation.
     *
     * Note that this action will check that the User does not have a customer ID
     * and that the Users's email match with one of the Organisation restricted domain names.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/organisations/{id}/join", requirements={"id" = "\d+"}, name="canopy_post_organisation_join")
     * @View(statusCode=201)
     *
     * @Conf\Security("null === user.getCustomerId()")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     201="Returned when the user is created.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation supplied in the url is not found."
     *   }
     * )
     */
    public function postJoinAction(Organisation $organisation)
    {
        $user = $this->getUser();
        $organisation->accept($user);

        $user->setRoles(['ROLE_CUSTOMER_REQUESTOR']);

        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Enable/Disable a User.
     *
     * This action is restricted to Organisation owners.
     * A similar action is available to admin Users.
     *
     * @Conf\Method("PUT")
     * @Conf\Route(
     *  "/organisations/{id}/users/{uuid}/{state}",
     *  requirements={
     *      "uuid": "%regex_uuid_unbound_id%",
     *      "state": "enable|disable"
     *  },
     *  name="canopy_put_organisation_user_enabled"
     * )
     * @View
     *
     * @Conf\ParamConverter("organisationUser", options={"mapping": {"uuid" = "uuid"}})
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ORGANISATION_USER_EDIT') && user.getUuid() !== organisationUser.getUuid() && organisation.getId() === user.getOrganisation().getId() && organisation.getId() === organisationUser.getOrganisation().getId()")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     204="Returned when successful.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function putUserEnableAction(Organisation $organisation, User $organisationUser, $state)
    {
        $organisationUser->setEnabled('enable' === $state);

        $this->getDoctrine()->getManager()->flush();
    }
}
