<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Representation\PrototypeGroupsRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Conf\Route("/api")
 */
class PrototypeGroupController extends AbstractController
{
    /**
     * Returns collection of PrototypeGroup.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/prototype-groups", name="canopy_get_prototype_groups")
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display prototype groups.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Number of prototype groups displayed in one page.")
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\PrototypeGroup>",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getPrototypeGroupsAction($page, $limit)
    {
        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:PrototypeGroup');
        $elements = $repository->getAllPaginated($page, $limit);

        return new PaginatedRepresentation(
            new PrototypeGroupsRepresentation($elements),
            'canopy_get_prototype_groups', // route
            array(), // route parameters
            $page, // page
            $limit, // limit
            ceil($repository->getTotalCount() / $limit), // total pages
            'page',
            'limit',
            true,
            $repository->getTotalCount()
        );
    }

    /**
     * Returns one prototype group.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/prototype-groups/{id}", name="canopy_get_prototype_group")
     * @View()
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\PrototypeGroup",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getPrototypeGroupAction(Request $request)
    {
        return $this->getDoctrine()->getRepository('CanopyUserBundle:PrototypeGroup')->findOneById($request->get('id'));
    }
}
