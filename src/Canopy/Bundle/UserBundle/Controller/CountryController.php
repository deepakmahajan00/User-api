<?php

namespace Canopy\Bundle\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;

/**
 * @Conf\Route("/api")
 */
class CountryController extends AbstractController
{
    /**
     * Returns collection of Country.
     *
     * Note that the lang and q params were used for auto-complete field before. Not used anymore.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/countries", name="canopy_get_countries")
     * @View
     *
     * @QueryParam(name="lang", default="en", requirements="en|fr", description="DEPRECATED. Language needed to look for countries.")
     * @QueryParam(name="q", default="all", description="DEPRECATED. Query to search countries.")
     *
     * @ApiDoc(
     *  resource=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Country>",
     *  statusCodes={
     *     200="Returned when successful."
     *   }
     * )
     */
    public function getCountriesAction($lang, $q)
    {
        $results = $this->getDoctrine()
            ->getRepository('CanopyUserBundle:Country')
            ->getCountriesBy(array('lang' => $lang, 'query' => $q))
        ;

        return array('countries' => $results);
    }
}
