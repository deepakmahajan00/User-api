<?php

namespace Canopy\Bundle\UserBundle\Representation;

use JMS\Serializer\Annotation as Serializer;

class UsersRepresentation
{
    /**
     * @Serializer\SerializedName("data")
     */
    public $users;

    public function __construct($users)
    {
        $this->users = $users;
    }
}
