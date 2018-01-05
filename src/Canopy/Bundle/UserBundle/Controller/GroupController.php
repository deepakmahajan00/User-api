<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Entity\Group;
use Canopy\Bundle\UserBundle\Representation\GroupsRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Hateoas\Representation\PaginatedRepresentation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Conf\Route("/api")
 */
class GroupController extends AbstractController
{
    /**
     * Returns collection of Group attached to a User.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users/{uuid}/groups", name="canopy_get_groups")
     * @View(serializerGroups={"Default"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display groups.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Maximum of elements needed .")
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Group>",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getGroupsAction(Request $request, $page, $limit)
    {
        if ($this->getUser()->getUuid() != $request->get('uuid')) {
            throw $this->createAccessDeniedException('You are not authorized to see those groups.');
        }

        $user = $this->getDoctrine()->getRepository('CanopyUserBundle:User')->findOneByUuid($request->get('uuid'));

        if (!$user) {
            throw $this->createNotFoundException(sprintf('The user "%s" does not exist.', $request->get('uuid')));
        }

        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:Group');
        $elements = $repository->getAllPaginated($page, $limit, array('user' => $this->getUser()));

        return new PaginatedRepresentation(
            new GroupsRepresentation($elements),
            'canopy_get_groups', // route
            array('uuid' => $request->get('uuid')), // route parameters
            $page, // page
            $limit, // limit
            ceil($repository->getTotalCount(array('user' => $this->getUser())) / $limit), // total pages
            'page',
            'limit',
            true,
            $repository->getTotalCount(
                array('user' => $this->getUser())
            )
        );
    }

    /**
     * Returns one Group attached to a User.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users/{uuid}/groups/{id}", name="canopy_get_group")
     * @View(serializerGroups={"group_detail", "Default"})
     *
     * @Conf\ParamConverter("group", options={"mapping": {"id" = "id"}})
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\Group",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getGroupAction(Group $group)
    {
        if (!$this->get('security.context')->isGranted('VIEW_GROUP', $group)) {
            throw $this->createAccessDeniedException('You are not authorized to see this group.');
        }

        return $group;
    }
}
