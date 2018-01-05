<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Canopy Organisation.
 *
 * @ORM\Table("canopy_organisation")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\OrganisationRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "canopy_get_organisation",
 *           absolute= true,
 *          parameters = {
 *              "id"   = "expr(object.getId())"
 *          }
 *      )
 * )
 */
class Organisation
{
    /**
     * By default, the user accepts policies by ticking the checkbox on the registration page.
     *
     * @const integer
     */
    const POLICY_ACCEPTED = 0;

    /**
     * When policies are updated, the user can ignore them which prevents him from placing an order.
     *
     * @const integer
     */
    const POLICY_IGNORED = 1;

    /**
     * When policies are updated, the user can reject them which prevents him from placing an order.
     *
     * @const integer
     */
    const POLICY_REJECTED = 2;

    /**
     * Internal ID only used for DB references and relations.
     *
     * @var string
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"user_view", "group_detail"})
     * @Serializer\Expose()
     */
    private $id;

    /**
     * Organisation's name.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     *
     * @Assert\NotBlank()
     * @Serializer\Expose()
     */
    private $name;

    /**
     * Organisation's description.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank()
     * @Serializer\Expose()
     */
    private $description;

    /**
     * Organisation's logo.
     *
     * @var string
     *
     * @ORM\Column(name="logo", type="string", nullable=true)
     *
     * @Serializer\Expose()
     */
    private $logo;

    /**
     * Organisation's address.
     *
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address", fetch="EAGER", cascade={"persist", "remove"}, inversedBy="organisation", orphanRemoval=true)
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     *
     * @Assert\Valid()
     * @Serializer\Expose()
     */
    private $address;

    /**
     * Organisation's VAT number.
     *
     * @var string
     *
     * @ORM\Column(name="vat_number", type="string", nullable=true)
     *
     * @Serializer\Expose()
     */
    private $vatNumber;

    /**
     * Organisation's creation date.
     *
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Organisation's customer ID, unique identifier used by users on registration.
     *
     * @var string
     *
     * @ORM\Column(name="customer_id", type="string", nullable=true)
     *
     * @Serializer\Expose()
     */
    private $customerId;

    /**
     * Organisation's latest accepted policy's uuid.
     *
     * @var string
     *
     * @ORM\Column(name="policy_uuid", type="guid", nullable=true, options={"default": null})
     */
    private $policyUuid;

    /**
     * Organisation's latest policy choice.
     *
     * @var int
     *
     * @ORM\Column(name="policy_choice", type="integer", options={"default": 0})
     */
    private $policyChoice;

    /**
     * A cron job is run daily to check if organisations have the latest policy.
     *
     * @var bool
     *
     * @ORM\Column(name="policy_latest", type="boolean", options={"default": 0})
     */
    private $answeredLatestPolicy;

    /**
     * Users emails need to match one of those domains names to be able to join the organisation.
     *
     * @var DomainName[]
     *
     * @ORM\OneToMany(targetEntity="DomainName", mappedBy="organisation", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     *
     * @Serializer\Expose()
     */
    private $restrictedDomainNames;

    /**
     * Organisation's related users.
     *
     * @var User[]
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="organisation", fetch="EAGER", cascade={"persist"})
     **/
    private $users;

    /**
     * Organisation's related groups.
     *
     * @var Group[]
     *
     * @ORM\OneToMany(targetEntity="Group", mappedBy="organisation", cascade={"persist"})
     **/
    private $groups;

    /**
     *
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->restrictedDomainNames = new ArrayCollection();
        $this->policyChoice = self::POLICY_ACCEPTED;
        $this->answeredLatestPolicy = false;
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
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param $logo
     *
     * @return $this
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     *
     * @return $this
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * @param $vatNumber
     *
     * @return $this
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRestrictedDomainNames()
    {
        return $this->restrictedDomainNames;
    }

    /**
     * @param Collection $restrictedDomainNames
     *
     * @return $this
     */
    public function setRestrictedDomainNames(Collection $restrictedDomainNames)
    {
        foreach ($this->restrictedDomainNames as $domain) {
            if (!$restrictedDomainNames->contains($domain)) {
                $this->removeRestrictedDomainName($domain);
            }
        }

        foreach ($restrictedDomainNames as $domain) {
            if (!$this->restrictedDomainNames->contains($domain)) {
                $this->addRestrictedDomainName($domain);
            }
        }

        return $this;
    }

    /**
     * @param DomainName $domainName
     */
    public function removeRestrictedDomainName(DomainName $domainName)
    {
        $domainName->setOrganisationBackup($this);
        $domainName->setOrganisation(null);
        $this->restrictedDomainNames->removeElement($domainName);
    }

    /**
     * @param DomainName $domainName
     */
    public function addRestrictedDomainName(DomainName $domainName)
    {
        $domainName->setOrganisation($this);

        $this->restrictedDomainNames[] = $domainName;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
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
     * @param $name
     *
     * @return bool|Group
     */
    public function getGroupByName($name)
    {
        foreach ($this->groups as $group) {
            if ($group->getName() == $name) {
                return $group;
            }
        }

        return false;
    }

    /**
     * @param $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->getAddress()->setState($state);

        return $this;
    }

    /**
     * @param $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->getAddress()->setCity($city);

        return $this;
    }

    /**
     * @param $zipcode
     *
     * @return $this
     */
    public function setZipcode($zipcode)
    {
        $this->getAddress()->setZipcode($zipcode);

        return $this;
    }

    /**
     * @param Country $country
     *
     * @return $this
     */
    public function setCountry(Country $country)
    {
        $this->getAddress()->setCountry($country);

        return $this;
    }

    /**
     * @param $street1
     *
     * @return $this
     */
    public function setStreet1($street1)
    {
        $this->getAddress()->setStreet1($street1);

        return $this;
    }

    /**
     * @param $street2
     *
     * @return $this
     */
    public function setStreet2($street2)
    {
        $this->getAddress()->setStreet2($street2);

        return $this;
    }

    /**
     * @param Organisation $organisation
     */
    public function updateFrom(Organisation $organisation)
    {
        $this->name = $organisation->getName();
        $this->description = $organisation->getDescription();
        $this->logo = $organisation->getLogo();
        $this->vatNumber = $organisation->getVatNumber();

        $this->setRestrictedDomainNames($organisation->getRestrictedDomainNames());

        if (empty($this->address)) {
            $this->address = new Address(
                $organisation->getAddress()->getState(),
                $organisation->getAddress()->getCity(),
                $organisation->getAddress()->getZipcode(),
                $organisation->getAddress()->getCountry(),
                $organisation->getAddress()->getStreet1(),
                $organisation->getAddress()->getStreet2()
            );
        } else {
            $this
                ->setState($organisation->getAddress()->getState())
                ->setCity($organisation->getAddress()->getCity())
                ->setZipcode($organisation->getAddress()->getZipcode())
                ->setStreet1($organisation->getAddress()->getStreet1())
                ->setStreet2($organisation->getAddress()->getStreet2())
            ;
        }
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return bool
     */
    public function hasRestrictedDomains()
    {
        return !$this->restrictedDomainNames->isEmpty();
    }

    /**
     * @param $host
     *
     * @return bool
     */
    public function isAllowedDomain($host)
    {
        $closure = function ($key, $domainName) use ($host) {
            return $host === $domainName->getValue();
        };

        return $this->restrictedDomainNames->exists($closure);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canJoin(User $user)
    {
        $host = $user->getEmailHost();

        if ($this->hasRestrictedDomains() && !$this->isAllowedDomain($host)) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     */
    public function accept(User $user)
    {
        if ($this->canJoin($user)) {
            $user->setOrganisation($this);
            $user->setCustomerId($this->getCustomerId());
        }
    }

    /**
     * @return string
     */
    public function getPolicyUuid()
    {
        return $this->policyUuid;
    }

    /**
     * @param $policyUuid
     *
     * @return $this
     */
    public function setPolicyUuid($policyUuid)
    {
        $this->policyUuid = $policyUuid;

        return $this;
    }

    /**
     * @return int
     */
    public function getPolicyChoice()
    {
        return $this->policyChoice;
    }

    /**
     * @param $policyChoice
     *
     * @return $this
     */
    public function setPolicyChoice($policyChoice)
    {
        if (!in_array($policyChoice, [self::POLICY_ACCEPTED, self::POLICY_IGNORED, self::POLICY_REJECTED])) {
            throw new \LogicException('The policy choice is invalid.');
        }

        $this->policyChoice = $policyChoice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAnsweredLatestPolicy()
    {
        return $this->answeredLatestPolicy;
    }

    /**
     * @param $answeredLatestPolicy
     *
     * @return $this
     */
    public function setAnsweredLatestPolicy($answeredLatestPolicy)
    {
        $this->answeredLatestPolicy = $answeredLatestPolicy;

        return $this;
    }
}
