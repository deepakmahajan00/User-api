<?php

namespace Canopy\Bundle\UserBundle\Representation;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     name = "prototype_groups",
 *     embedded = @Hateoas\Embedded(
 *         "expr(service('canopy.prototype_group.repository').findAll())",
 *     )
 * )
 */
class GroupsRepresentation
{
    /**
     * @Serializer\SerializedName("data")
     */
    public $groups;

    public function __construct($groups)
    {
        $this->groups = $groups;
    }
}
