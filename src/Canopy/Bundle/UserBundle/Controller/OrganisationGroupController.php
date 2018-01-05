<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\Group;
use Canopy\Bundle\UserBundle\Representation\GroupsRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Hateoas\Representation\PaginatedRepresentation;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Conf\Route("/api")
 */
class OrganisationGroupController extends AbstractController
{
    /**
     * Returns collection of Group.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}/groups", name="canopy_get_organisation_groups")
     * @View(serializerGroups={"group_detail", "Default"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display groups.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Number of elements needed.")
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Group>",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     400="Returned if the group is not in the organisation requested in the url or if there are users in that group.",
     *   }
     * )
     */
    public function getGroupsAction(Organisation $organisation, $page, $limit)
    {
        if ($this->getUser()->getOrganisation() !== $organisation) {
            throw $this->createAccessDeniedException('You are not authorized to see those groups');
        }

        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:Group');
        $elements = $repository->getAllPaginated($page, $limit, array('organisation' => $organisation));

        return new PaginatedRepresentation(
            new GroupsRepresentation($elements),
            'canopy_get_organisation_groups', // route
            array('id' => $organisation->getId()), // route parameters
            $page, // page
            $limit, // limit
            ceil($repository->getTotalCount(array('organisation' => $organisation)) / $limit), // total pages
            'page',
            'limit',
            true,
            $repository->getTotalCount(array('organisation' => $organisation))
        );
    }

    /**
     * Create a Group in an Organisation.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/organisations/{id}/groups", name="canopy_post_organisation_group")
     * @View()
     *
     * @Conf\ParamConverter("group", converter="fos_rest.request_body")
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\Group",
     *  output="Canopy\Bundle\UserBundle\Entity\Group",
     *  statusCodes={
     *     200="Returned when the organisation is found.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function postGroupAction(Organisation $organisation, Group $group)
    {
        $group->setOrganisation($organisation);
        $this->getDoctrine()->getManager()->persist($organisation);
        $organisation->addGroup($group);
        $this->getDoctrine()->getManager()->persist($group);

        $this->getDoctrine()->getManager()->flush();

        return $group;
    }

    /**
     * Update an existing Group in an Organisation.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/organisations/{organisation_id}/groups/{id}", name="canopy_put_organisation_group")
     * @View()
     *
     * @Conf\ParamConverter("requestGroup", converter="fos_rest.request_body")
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\Group",
     *  statusCodes={
     *     200="Returned when the group is found.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function putGroupAction(Group $group, Group $requestGroup)
    {
        $group->updateFrom($requestGroup);

        $this->getDoctrine()->getManager()->persist($group);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Returns a Group in an Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{organisation_id}/groups/{id}", name="canopy_get_organisation_group")
     * @View(serializerGroups={"group_detail"})
     *
     * @Conf\ParamConverter("group", options={"mapping": {"id": "id", "organisation_id": "organisation"}})
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\Group",
     *  statusCodes={
     *     200="Returned when the group is found.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function getGroupAction(Organisation $organisation, Group $group)
    {
        if ($this->getUser()->getOrganisation() !== $organisation) {
            throw $this->createAccessDeniedException('You are not authorized to see those groups');
        }

        return $group;
    }

    /**
     * Delete an existing Group.
     *
     * Careful. Note that this action deletes the Group.
     *
     * @Conf\Method("DELETE")
     * @Conf\Route("/organisations/{organisation_id}/groups/{id}", name="canopy_delete_group")
     * @View()
     *
     * @Conf\ParamConverter("group", class="CanopyUserBundle:Group", options={"id" = "id"})
     * @Conf\ParamConverter("organisation", class="CanopyUserBundle:Organisation", options={"id" = "organisation_id"})
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     400="Returned if the group is not in the organisation requested in the url or if there are users in that group.",
     *   }
     * )
     */
    public function deleteGroupAction(Organisation $organisation, Group $group)
    {
        if (!$group->isInOrganisation($organisation)) {
            throw new BadRequestHttpException('You cannot remove a this group as it is not attached to this Organisation.');
        }

        if ($group->hasUsers()) {
            throw new BadRequestHttpException(sprintf(
                'You cannot remove a this group as there are users attached to it (%s users).',
                count($group->getUsers())
            ));
        }

        $this->getDoctrine()->getManager()->remove($group);
        $this->getDoctrine()->getManager()->flush();
    }
}
