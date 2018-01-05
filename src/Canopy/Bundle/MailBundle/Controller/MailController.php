<?php

namespace Canopy\Bundle\MailBundle\Controller;

use Canopy\Bundle\UserBundle\Controller\AbstractController;
use Canopy\Bundle\UserBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Conf\Route("/api")
 */
class MailController extends AbstractController
{
    /**
     * Send a reset password mail for a given user email.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/mails/reset-password", name="canopy_post_mail_reset")
     * @View()
     *
     * @RequestParam(name="brand", description="The brand")
     * @RequestParam(name="to", description="Recipients")
     * @RequestParam(name="params", array=true, description="Parameters to build the email")
     *
     * @ApiDoc(
     *   description="Send a reset password email",
     *   statusCodes={
     *     204="Returned if everything goes well.",
     *     400="Returned if mandatory body data are not there.",
     *     404="Returned if some body data does not exists or if user not found.",
     *     500="Returned if the email cannot be send.",
     *   }
     * )
     */
    public function sendResetPasswordAction($brand, $to, $params)
    {
        if (!$user = $this->getDoctrine()->getManager()->getRepository('CanopyUserBundle:User')->findOneByEmail($to)) {
            throw $this->createNotFoundException();
        }

        $type = 'reset-password';
        $params = array_merge(['firstname' => $user->getFirstname()], $params);

        $this->send($type, $brand, $to, $params);
    }

    /**
     * @Conf\Route(
     *   "/mails/{type}",
     *   name="canopy_post_mail",
     *   requirements={"type": "[a-zA-Z0-9-_]+"},
     *   methods="POST",
     * )
     *
     * @RequestParam(name="brand", description="The brand")
     * @RequestParam(name="to", description="Recipients")
     * @RequestParam(name="params", array=true, description="Parameters to build the email")
     *
     * @View
     *
     * @ApiDoc(
     *   description="Send a mail",
     *   statusCodes={
     *     204="Returned if everything goes well.",
     *     400="Returned if mandatory body data are not there.",
     *     404="Returned if some body data does not exists.",
     *     500="Returned if the email cannot be send.",
     *   }
     * )
     */
    public function sendAction($type, $brand, $to, $params)
    {
        $this->send($type, $brand, $to, $params);
    }

    /**
     * @param $type
     * @param $brand
     * @param $to
     * @param $params
     */
    private function send($type, $brand, $to, $params)
    {
        try {
            $this->get('canopy.branded_mailer')->sendBrandedMail($type, $brand, $to, $params);
        } catch (\Exception $e) {
            ob_end_clean(); // In case of error, avoid twig to send the block content
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Cannot send this email', $e);
        }
    }
}
