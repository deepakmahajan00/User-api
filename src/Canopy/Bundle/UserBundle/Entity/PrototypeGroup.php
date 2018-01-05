<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Prototype Group
 * Object used to actually create Group. A Group must be created
 * from a PrototypeGroup.
 *
 * @ORM\Table("canopy_prototype_group")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\PrototypeGroupRepository")
 * @Serializer\ExclusionPolicy("all")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "canopy_get_prototype_group",
 *           absolute= true,
 *          parameters = {
 *              "id"   = "expr(object.getId())"
 *          }
 *      )
 * )
 */
class PrototypeGroup
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string")
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="text")
     * @Serializer\Expose()
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="prototypeGroups", cascade={"persist"})
     * @ORM\JoinTable(name="canopy_prototype_group_permission")
     * @Serializer\Expose()
     **/
    private $permissions;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }
}
