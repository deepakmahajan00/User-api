<?php

namespace Canopy\Bundle\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;

/**
 * @Conf\Route("/api")
 */
class CurrencyController extends AbstractController
{
    /**
     * Returns collection of Currency.
     *
     * Note that the lang and q params were used for auto-complete field before. Not used anymore.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/currencies", name="canopy_get_currencies")
     * @View
     *
     * @QueryParam(name="q", default="all", description="DEPRECATED. Query to search currencies.")
     *
     * @ApiDoc(
     *  resource=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\Currency>",
     *  statusCodes={
     *     200="Returned when successful."
     *   }
     * )
     */
    public function getCurrenciesAction($q)
    {
        $results = $this->getDoctrine()
            ->getRepository('CanopyUserBundle:Currency')
            ->getCurrenciesBy(array('query' => $q))
        ;

        return array('currencies' => $results);
    }
}
