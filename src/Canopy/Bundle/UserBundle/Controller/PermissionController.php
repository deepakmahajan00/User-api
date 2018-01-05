<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Hateoas\Representation\PaginatedRepresentation;
use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\UserBundle\Representation\PermissionsRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Conf\Route("/api")
 */
class PermissionController extends AbstractController
{
    /**
     * Returns collection of Permission.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/permissions", name="canopy_get_all_permissions")
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display permissions.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Number of permissions displayed in one page.")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Permission>",
     *  statusCodes={
     *     200="Returned when the permissions are found.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function getPermissionsAction($page, $limit)
    {
        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:Permission');
        $permissions = $repository->getAllPaginated($page, $limit);

        return new PaginatedRepresentation(
            new PermissionsRepresentation($permissions),
            'canopy_get_all_permissions', // route
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
     * Returns User's Permission.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users/{uuid}/permissions", name="canopy_get_permissions")
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display permissions.")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Number of permissions displayed in one page.")
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @ApiDoc(
     *  deprecated=true,
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Permission>",
     *  statusCodes={
     *     200="Returned when the permissions are found.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function getUserPermissionsAction(User $user, Request $request, $page, $limit)
    {
        if ($this->getUser()->getUuid() != $request->get('uuid')) {
            throw new AccessDeniedException('You are not authorized to see those permissions.');
        }

        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:Permission');
        $permissions = $repository->getAllPaginated($page, $limit, array('user' => $user));

        return new PaginatedRepresentation(
            new PermissionsRepresentation($permissions),
            'canopy_get_all_permissions', // route
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
     * Returns one Permission.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users/{uuid}/permissions/{id}", name="canopy_get_permission")
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\Permission",
     *  statusCodes={
     *     200="Returned when the permission is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the permission is not found.",
     *   }
     * )
     */
    public function getPermissionAction(Request $request)
    {
        if ($this->getUser()->getUuid() != $request->get('uuid')) {
            throw new AccessDeniedException('You are not authorized to see this permission.');
        }

        $permission = $this->getDoctrine()->getRepository('CanopyUserBundle:Permission')->findOneById($request->get('id'));

        if (!$permission) {
            throw new NotFoundHttpException('The requested permission with the id "'.$request->get('id').'" does not exist.');
        }

        return $permission;
    }
}
