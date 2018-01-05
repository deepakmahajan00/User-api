<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AbstractController extends Controller
{
    /**
     * Convert a ConstraintViolationListInterface to a Response (with HTTP_BAD_REQUEST code)
     * through fosrest handlers.
     *
     * Note: Use it only when fos_rest.body_converter is not used on your action
     *
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResponseFromConstraintViolationList(ConstraintViolationListInterface $validationErrors)
    {
        return $this->get('canopy.constraint_violation_list.converter')->createResponse($validationErrors);
    }
}
