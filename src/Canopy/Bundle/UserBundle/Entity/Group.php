<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Canopy Group.
 *
 * Groups are mainly used to get ROLE_NAME
 *
 * @ORM\Table("canopy_group")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\GroupRepository")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "canopy_get_group",
 *          absolute   = true,
 *          parameters = {
 *              "uuid" = "expr(service('security.context').getToken().getUser().getUuid())",
 *              "id"   = "expr(object.getId())"
 *          }
 *      )
 * )
 */
class Group
{
    /**
     * Internal ID only used for DB references and relations.
     *
     * @var string
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"group_detail", "Default"})
     */
    private $id;

    /**
     * Group's name.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"group_detail", "Default"})
     */
    private $name;

    /**
     * Group's description.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"group_detail", "Default"})
     */
    private $description;

    /**
     * Group's organisation (not used in v2).
     *
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="groups", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\Valid()
     *
     * @Serializer\Type("Canopy\Bundle\UserBundle\Entity\Organisation")
     * @Serializer\Groups({"group_detail"})
     */
    private $organisation;

    /**
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="groups", cascade={"persist"})
     * @ORM\JoinTable(name="canopy_group_permission")
     *
     * @Serializer\Type("ArrayCollection<Canopy\Bundle\UserBundle\Entity\Permission>")
     * @Serializer\Groups({"group_detail"})
     */
    private $permissions;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function isInOrganisation(Organisation $organisation)
    {
        if ($this->organisation === $organisation) {
            return true;
        }

        return false;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param Permission $permission
     */
    public function addPermission(Permission $permission)
    {
        $this->permissions[] = $permission;
    }

    /**
     * @param Permission $permission
     *
     * @return Boolean
     */
    public function removePermission(Permission $permission)
    {
        return $this->permissions->removeElement($permission);
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    public function removeUser(User $user)
    {
        return $this->users->removeElement($user);
    }

    public function hasUser(User $userProvided)
    {
        foreach ($this->users as $user) {
            if ($userProvided === $user) {
                return true;
            }
        }

        return false;
    }

    public function hasUsers()
    {
        return ($this->getNbUsers() > 0);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "group_detail"})
     */
    public function getNbUsers()
    {
        if ($this->users) {
            return $this->users->count();
        }

        return 0;
    }

    /**
     * @param Group $group
     */
    public function updateFrom(Group $group)
    {
        $this->name = $group->getName();
        $this->description = $group->getDescription();
        $this->permissions = $group->getPermissions();
    }
}
