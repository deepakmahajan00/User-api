<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Address.
 *
 * @ORM\Table("canopy_currency")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\CurrencyRepository")
 */
class Currency
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"user_view"})
     */
    private $id;

    /**
     * @ORM\Column(name="iso_code", type="string", length=3)
     *
     * @Serializer\Groups({"user_view"})
     *
     * @NotBlank(message="Please select your currency", groups={"join_organisation"})
     */
    private $isoCode;

    /**
     * @ORM\Column(name="en", type="string")
     *
     * @Serializer\Groups({"user_view"})
     */
    private $value;

    public function __construct($isoCode, $value)
    {
        $this->isoCode = $isoCode;
        $this->value = $value;
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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
