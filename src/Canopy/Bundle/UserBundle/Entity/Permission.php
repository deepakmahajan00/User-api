<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * Canopy Permission.
 *
 * An event listener (Canopy\Bundle\UserBundle\EventListener\UserListener) listens for the doctrine
 * postLoad's event on the User entity. It uses user's roles to retrieve 'groups' (where group.name = role_name),
 * and 'groups' to retrieve Permissions. Permissions are then set in the "permissions" property of the "User" entity
 *
 * @ORM\Table("canopy_permission")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\PermissionRepository")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "canopy_get_permission",
 *           absolute= true,
 *          parameters = {
 *              "uuid" = "expr(service('security.context').getToken().getUser().getUuid())",
 *              "id"   = "expr(object.getId())"
 *          }
 *      )
 * )
 * @Serializer\ExclusionPolicy("all")
 */
class Permission
{
    /**
     * Static permissions only assigned to verified users.
     *
     * @var array
     */
    public static $mailVerifiedOnly = [
        'PERM_CART',
        'PERM_ORDER',
        'PERM_DASHBOARD_ORGANISATION_USER_EDIT',
        'PERM_DASHBOARD_ORGANISATION_EDIT',
    ];

    /**
     * Internal ID only used for DB references and relations.
     *
     * @var string
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"group_detail"})
     * @Serializer\Expose
     */
    private $id;

    /**
     * Permission's name.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     *
     * @Serializer\Groups({"group_detail"})
     * @Serializer\Expose
     */
    private $name;

    /**
     * Permission's description.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Serializer\Groups({"group_detail"})
     * @Serializer\Expose
     */
    private $description;

    /**
     * Permission's groups (those are the related roles).
     *
     * @var Group[]
     *
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="permissions")
     **/
    private $groups;

    /**
     * Permissions have link to Prototype Group as we need permissions in PrototypeGroup to actually create new groups (not used in v2).
     *
     * @var PrototypeGroup[]
     *
     * @ORM\ManyToMany(targetEntity="PrototypeGroup", mappedBy="permissions")
     **/
    private $prototypeGroups;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return $this
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return bool
     */
    public function removeGroup(Group $group)
    {
        return $this->groups->removeElement($group);
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param PrototypeGroup $prototypeGroup
     *
     * @return $this
     */
    public function addPrototypeGroup(PrototypeGroup $prototypeGroup)
    {
        $this->prototypeGroups[] = $prototypeGroup;

        return $this;
    }

    /**
     * @param PrototypeGroup $prototypeGroup
     *
     * @return bool
     */
    public function removePrototypeGroup(PrototypeGroup $prototypeGroup)
    {
        return $this->prototypeGroups->removeElement($prototypeGroup);
    }

    /**
     * @return PrototypeGroup[]
     */
    public function getPrototypeGroups()
    {
        return $this->prototypeGroups;
    }
}
