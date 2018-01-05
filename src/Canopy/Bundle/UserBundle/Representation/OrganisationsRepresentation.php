<?php

namespace Canopy\Bundle\UserBundle\Representation;

use JMS\Serializer\Annotation as Serializer;

class OrganisationsRepresentation
{
    /**
     * @Serializer\SerializedName("data")
     */
    public $organisations;

    public function __construct($organisations)
    {
        $this->organisations = $organisations;
    }
}
