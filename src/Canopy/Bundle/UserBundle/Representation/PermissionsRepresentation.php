<?php

namespace Canopy\Bundle\UserBundle\Representation;

use JMS\Serializer\Annotation as Serializer;

class PermissionsRepresentation
{
    /**
     * @Serializer\SerializedName("data")
     */
    public $permissions;

    public function __construct($permissions)
    {
        $this->permissions = $permissions;
    }
}
