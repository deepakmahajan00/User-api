<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * DomainName.
 *
 * @ORM\Table("canopy_domain_name")
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class DomainName
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(name="value", type="string")
     * @Serializer\Expose()
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="restrictedDomainNames", fetch="EAGER", cascade={"all"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     **/
    private $organisation;

    private $organisationBackup;

    public function __toString()
    {
        return $this->value;
    }

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getOrganisationBackup()
    {
        return $this->organisationBackup;
    }

    public function setOrganisationBackup($organisationBackup)
    {
        $this->organisationBackup = $organisationBackup;

        return $this;
    }
}
