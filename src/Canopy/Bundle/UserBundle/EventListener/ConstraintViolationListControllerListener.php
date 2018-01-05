<?php

namespace Canopy\Bundle\UserBundle\EventListener;

use Canopy\Bundle\UserBundle\Service\ConstraintViolationListToResponse;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListControllerListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $validationErrorsArgument;

    /**
     * @var ConstraintViolationListToResponse
     */
    protected $converter;

    public function __construct(
        ConstraintViolationListToResponse $converter,
        $validationErrorsArgument
    ) {
        $this->validationErrorsArgument = $validationErrorsArgument;
        $this->converter = $converter;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $validationErrors = $request->attributes->get($this->validationErrorsArgument);

        if (!$validationErrors instanceof ConstraintViolationListInterface) {
            return;
        }

        if (0 === count($validationErrors)) {
            return;
        }

        $transformedValidationErrors = $this->transformConstraintViolationListToUnderscore($validationErrors);
        $response = $this->converter->createResponse($transformedValidationErrors);

        $event->setController(
            function () use ($response) {
                return $response;
            }
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -255),
        );
    }

    protected function transformConstraintViolationListToUnderscore(ConstraintViolationListInterface $validationErrors)
    {
        $constraintViolationList = new ConstraintViolationList();
        foreach ($validationErrors as $violation) {
            $constraintViolationList->add(
                new ConstraintViolation(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getRoot(),
                    Inflector::tableize($violation->getPropertyPath()),
                    $violation->getInvalidValue(),
                    $violation->getPlural(),
                    $violation->getCode()
                )
            );
        }

        return $constraintViolationList;
    }
}
