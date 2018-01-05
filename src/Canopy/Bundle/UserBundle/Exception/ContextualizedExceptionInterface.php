<?php

namespace Canopy\Bundle\UserBundle\Exception;

interface ContextualizedExceptionInterface
{
    public function getContext();
}
