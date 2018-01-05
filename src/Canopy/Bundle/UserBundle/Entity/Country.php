<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address.
 *
 * @ORM\Table("canopy_country")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\CountryRepository")
 */
class Country
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Assert\NotBlank(message="The country *id* must not be blank.")
     */
    private $id;

    /**
     * @ORM\Column(name="iso_code", type="string", length=2)
     *
     * @Assert\NotBlank(message="Please select your country", groups={"new_organisation"})
     */
    private $isoCode;

    /**
     * @ORM\Column(name="en", type="string")
     */
    private $en;

    /**
     * @ORM\Column(name="fr", type="string")
     */
    private $fr;

    /**
     * @ORM\Column(name="dialing_code", type="string")
     */
    private $dialingCode;

    public function __construct($isoCode, $en, $fr, $dialingCode)
    {
        $this->isoCode = $isoCode;
        $this->en = $en;
        $this->fr = $fr;
        $this->dialingCode = $dialingCode;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIsoCode()
    {
        return $this->isoCode;
    }

    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    public function getEn()
    {
        return $this->en;
    }

    public function setEn($en)
    {
        $this->en = $en;

        return $this;
    }

    public function getFr()
    {
        return $this->fr;
    }

    public function setFr($fr)
    {
        $this->fr = $fr;

        return $this;
    }

    public function getDialingCode()
    {
        return $this->dialingCode;
    }

    public function setDialingCode($dialingCode)
    {
        $this->dialingCode = $dialingCode;

        return $this;
    }
}
