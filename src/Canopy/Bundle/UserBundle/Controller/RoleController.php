<?php

namespace Canopy\Bundle\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;

/**
 * @Conf\Route("/api")
 */
class RoleController extends AbstractController
{
    /**
     * Returns all roles.
     *
     * Note that roles comes from the Group entity.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/roles", name="canopy_get_roles")
     * @View
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     200="Returned when successful.",
     *   }
     * )
     */
    public function getRolesAction()
    {
        // TODO : actually, groups are Roles, but it's an hotfix, it will change
        $groups = $this->getDoctrine()->getManager()->getRepository('CanopyUserBundle:Group')->findAll();

        $roles = [];
        foreach ($groups as $group) {
            $roles[] = $group->getName();
        }

        return ['roles' => $roles];
    }
}
