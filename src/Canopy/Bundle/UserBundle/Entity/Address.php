<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address.
 *
 * @ORM\Table("canopy_address")
 * @ORM\Entity()
 */
class Address
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="state", type="text")
     *
     * @Assert\NotBlank(message="Please enter your state.", groups={"new_organisation"})
     * @Assert\Regex(
     *  pattern="/^[a-zA-Z]+(?:[\s-][a-zA-Z]+)*$/",
     *  message="Please enter alphabets up to a maximum of 50 characters",
     *  groups={"edit", "new_organisation"}
     * )
     * @Assert\Length(max="50", groups={"edit", "new_organisation"})
     */
    private $state;

    /**
     * @ORM\Column(name="city", type="string")
     *
     * @Assert\NotBlank(message="Please enter your city.", groups={"new_organisation"})
     * @Assert\Regex(
     *  pattern="/^[a-zA-Z]+(?:[\s-][a-zA-Z]+)*$/",
     *  message="Please enter alphabets up to a maximum of 35 characters",
     *  groups={"edit", "new_organisation"}
     * )
     * @Assert\Length(max="35", groups={"edit", "new_organisation"})
     */
    private $city;

    /**
     * @ORM\Column(name="zipcode", type="string")
     *
     * @Assert\NotBlank(message="Please enter your postcode.", groups={"new_organisation"})
     * @Assert\Regex(
     *  pattern="/^[A-Za-z0-9_ ]+$/i",
     *  message="Please enter alphanumeric characters only",
     *  groups={"edit", "new_organisation"}
     * )
     * @Assert\Length(max="10", groups={"edit", "new_organisation"})
     */
    private $zipcode;

    /**
     * @ORM\ManyToOne(targetEntity="Country", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     *
     * @Assert\Valid()
     */
    private $country;

    /**
     * @ORM\Column(name="street1", type="string", nullable=true)
     *
     * @Assert\NotBlank(message="Please enter your address line 1.", groups={"new_organisation"})
     * @Assert\Length(max="30", groups={"edit", "new_organisation"})
     */
    private $street1;

    /**
     * @ORM\Column(name="street2", type="string", nullable=true)
     *
     * @Assert\Length(max="30", groups={"edit","new_organisation"})
     */
    private $street2;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="address")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Organisation", mappedBy="address")
     */
    private $organisation;

    public function __construct($state, $city, $zipcode, $country, $street1, $street2 = null)
    {
        $this->state = $state;
        $this->city = $city;
        $this->zipcode = $zipcode;
        $this->country = $country;
        $this->street1 = $street1;
        $this->street2 = $street2;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode()
    {
        return $this->zipcode;
    }

    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getStreet1()
    {
        return $this->street1;
    }

    public function setStreet1($street1)
    {
        $this->street1 = $street1;

        return $this;
    }

    public function getStreet2()
    {
        return $this->street2;
    }

    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    public function updateFrom(Address $address = null)
    {
        if (null === $address) {
            return;
        }

        if ($address->getState() != $this->state) {
            $this->state = $address->getState();
        }

        if ($address->getCity() != $this->city) {
            $this->city = $address->getCity();
        }

        if ($address->getZipcode() != $this->zipcode) {
            $this->zipcode = $address->getZipcode();
        }

        if ($address->getCountry()) {
            $this->country = $address->getCountry();
        }

        if ($address->getStreet1()) {
            $this->street1 = $address->getStreet1();
        }

        if ($address->getStreet2()) {
            $this->street2 = $address->getStreet2();
        }

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }
}
