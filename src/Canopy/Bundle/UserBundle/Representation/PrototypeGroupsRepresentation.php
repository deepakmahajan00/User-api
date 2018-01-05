<?php

namespace Canopy\Bundle\UserBundle\Representation;

use JMS\Serializer\Annotation as Serializer;

class PrototypeGroupsRepresentation
{
    /**
     * @Serializer\SerializedName("data")
     */
    public $prototypeGroups;

    public function __construct($prototypeGroups)
    {
        $this->prototypeGroups = $prototypeGroups;
    }
}
