<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for the REQUEST event. Patches the HeaderBag because the
 * "Authorization" header is not included in $_SERVER.
 */
class AuthenticationHeaderListener implements EventSubscriberInterface
{
    /**
     * Handles REQUEST event.
     *
     * @param GetResponseEvent $event the event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->fixAuthHeader($event->getRequest()->headers);
    }
    /**
     * PHP does not include HTTP_AUTHORIZATION in the $_SERVER array, so this header is missing.
     * We retrieve it from apache_request_headers().
     *
     * @param HeaderBag $headers
     */
    public function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (isset($all['Authorization'])) {
                $headers->set('Authorization', $all['Authorization']);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 256),
        );
    }
}
