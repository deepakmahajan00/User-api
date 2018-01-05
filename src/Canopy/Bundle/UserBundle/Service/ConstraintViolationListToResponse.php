<?php

namespace Canopy\Bundle\UserBundle\Service;

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListToResponse
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var ExceptionWrapperHandlerInterface
     */
    protected $exceptionWrapperHandler;

    /**
     * @param ViewHandlerInterface             $viewHandler
     * @param ExceptionWrapperHandlerInterface $exceptionWrapperHandler
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        ExceptionWrapperHandlerInterface $exceptionWrapperHandler
    ) {
        $this->viewHandler = $viewHandler;
        $this->exceptionWrapperHandler = $exceptionWrapperHandler;
    }

    /**
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return Response
     */
    public function createResponse(ConstraintViolationListInterface $validationErrors)
    {
        $data = $this->exceptionWrapperHandler->wrap(
            array(
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Validation Failed',
                'errors' => $validationErrors,
            )
        );

        return $this->viewHandler->handle(View::create($data, $data->getCode()));
    }
}
