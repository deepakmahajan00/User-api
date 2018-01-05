<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Representation\OrganisationsRepresentation;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Hateoas\Representation\PaginatedRepresentation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;
use Canopy\Bundle\UserBundle\Representation\UsersRepresentation;

/**
 * @Conf\Route("/api")
 */
class OrganisationController extends AbstractController
{
    /**
     * Returns collection of Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations", name="canopy_get_organisations")
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page requested to display groups.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Maximum of elements needed .")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Organisation>",
     *  statusCodes={
     *     200="Returned when the organisation is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation is not found.",
     *   }
     * )
     */
    public function getOrganisationsAction($page, $limit)
    {
        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:Organisation');
        $elements = $repository->getAllPaginated($page, $limit);

        return new PaginatedRepresentation(
            new OrganisationsRepresentation($elements),
            'canopy_get_organisations', // route
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
     * Returns collection of Organisation which the User could join.
     *
     * Note that this action check the email of the current User
     * and Organisations with matching restricted domain names.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/eligible-for-user", name="canopy_get_eligible_organisations")
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Organisation>",
     *  statusCodes={
     *     200="Returned when the organisation is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation is not found.",
     *   }
     * )
     */
    public function getEligibleOrganisationsAction()
    {
        $user = $this->getUser();
        $host = $user->getEmailHost();

        $organisations = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanopyUserBundle:Organisation')
            ->getEligibleOrganisation($host);

        return new OrganisationsRepresentation($organisations);
    }

    /**
     * Returns one Organisation.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}", requirements={"id" = "\d+"}, name="canopy_get_organisation")
     * @View()
     *
     * @Conf\ParamConverter("organisation")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\Organisation",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getOrganisationAction(Organisation $organisation)
    {
        return $organisation;
    }

    /**
     * Create a new Organisation.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/organisations", name="canopy_post_organisation")
     * @View()
     *
     * @Conf\ParamConverter("requestOrganisation", converter="fos_rest.request_body")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\Organisation",
     *  statusCodes={
     *     200="Returned when the organisation is created, with the new object created.",
     *   }
     * )
     */
    public function postOrganisationAction(Organisation $requestOrganisation)
    {
        $em = $this->getDoctrine()->getManager();

        $organisation = new Organisation();
        $organisation->updateFrom($requestOrganisation);

        $em->persist($organisation);
        $em->flush();

        return $organisation;
    }

    /**
     * Update of an organisation.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/organisations/{id}", name="canopy_put_organisation")
     * @View()
     *
     * @Conf\ParamConverter("requestOrganisation", converter="fos_rest.request_body")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  input="Canopy\Bundle\UserBundle\Entity\Organisation",
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  },
     *  statusCodes={
     *     200="Returned when successful",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned when the organisation is not found"
     *   }
     * )
     */
    public function putOrganisationAction(Organisation $organisation, Organisation $requestOrganisation)
    {
        $em = $this->getDoctrine()->getManager();

        $organisation->updateFrom($requestOrganisation);

        $em->persist($organisation);
        $em->flush();
    }

    /**
     * Upload a logo for an Organisation.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/organisations/{id}/logo", name="canopy_post_organisation_upload_logo")
     * @View()
     *
     * @QueryParam(name="uploadType", default="formData", description="Defines which process is used to upload the file.")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     200="Returned when the file is uploaded.",
     *     400="Returned if the UploadHandler fails to retrieve the file in the request.",
     *   }
     * )
     */
    public function uploadAction(Request $request, Organisation $organisation)
    {
        $mediaPath = $this->get('canopy.api.upload')->uploadFile();

        $organisation->setLogo($mediaPath);

        $em = $this->getDoctrine()->getManager();
        $em->persist($organisation);
        $em->flush();

        return $organisation;
    }

    /**
     * Returns collection of User from Organisation which are customer approver.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/organisations/{id}/customer-approvers", name="canopy_get_organisation_customer_approvers")
     * @View()
     *
     * @QueryParam(name="limit", requirements="\d+", default="20", description="Number of elements needed.")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\User>",
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"},
     *  },
     *  statusCodes={
     *     200="Returned when the customer appprover is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the organisation is not found.",
     *   }
     * )
     */
    public function getOrganisationCustomerApproversAction(Organisation $organisation, $limit)
    {
        $customerApprover = $this->getDoctrine()
        ->getRepository('CanopyUserBundle:User')
        ->getOrganisationCustomerApprovers($organisation, 'ROLE_CUSTOMER_APPROVER', $limit);

        return new UsersRepresentation($customerApprover);
    }
}
