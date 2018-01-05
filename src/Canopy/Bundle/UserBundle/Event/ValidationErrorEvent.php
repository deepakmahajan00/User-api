<?php

namespace Canopy\Bundle\UserBundle\Event;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ValidationErrorEvent extends GetResponseEvent
{
    const VALIDATION_ERROR = 'validation.error';

    private $validationErrors;

    private $statusCode;

    public function __construct($validationErrors, $statusCode)
    {
        $this->validationErrors = $validationErrors;
        $this->statusCode = $statusCode;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
