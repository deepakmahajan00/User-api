<?php

namespace Canopy\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Canopy\Bundle\CommonBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Canopy User.
 *
 * @ORM\Table("canopy_user")
 * @ORM\Entity(repositoryClass="Canopy\Bundle\UserBundle\Entity\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User extends BaseUser implements AdvancedUserInterface
{
    /**
     * Default role given at registration on marketplace-ui is ROLE_REGISTERED_USER.
     *
     * @const string
     */
    const DEFAULT_ROLE = 'ROLE_REGISTERED_USER';

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
     */
    private $id;

    /**
     * Internal UUID used by Canopy APIs.
     *
     * @var string
     *
     * @ORM\Column(name="uuid", type="string")
     */
    private $uuid;

    /**
     * External ID used for reference on UnboundID.
     *
     * @var string
     *
     * @ORM\Column(name="unboundid_user_id", type="string")
     */
    private $unboundidUserId;

    /**
     * User's first name.
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string")
     *
     * @Assert\NotBlank(message="Please enter your first name.", groups={"create", "edit"})
     * @Assert\Length(max="35", groups={"create", "edit"})
     * @Assert\Regex(pattern="/[^a-zA-Z'-]+/", match=false, message="First Name contains special characters", groups={"create", "edit"})
     */
    private $firstname;

    /**
     * User's last name.
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string")
     *
     * @Assert\NotBlank(message="Please enter your last name.", groups={"create", "edit"})
     * @Assert\Length(max="35", groups={"create", "edit"})
     * @Assert\Regex(pattern="/[^a-zA-Z'-]+/", match=false, message="Last Name contains special characters", groups={"create", "edit"})
     */
    private $lastname;

    /**
     * User's email address.
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true, unique=true)
     *
     * @Assert\NotBlank(message="Please enter your email.", groups={"create", "edit"})
     * @Assert\Email(message="The email is not valid.", groups={"create", "edit"})
     */
    private $email;

    /**
     * User's profile picture.
     *
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", nullable=true)
     */
    private $avatar;

    /**
     * User's phone dialing code (displayed as a country select).
     *
     * @var string
     *
     * @ORM\Column(name="dialing_code", type="string", nullable=true)
     *
     * @Assert\NotNull(groups={"create", "edit"})
     */
    private $dialingCode;

    /**
     * User's mobile number.
     *
     * @var string
     *
     * @ORM\Column(name="mobile_number", type="string", nullable=true)
     *
     * @Assert\NotBlank(message="Please enter your mobile number.", groups={"create", "edit"})
     * @Assert\Length(max="10", min="10", groups={"create", "edit"})
     * @Assert\Regex(
     *  pattern="/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/",
     *  message="Please enter valid Mobile number",
     *  groups={"create", "edit"}
     * )
     */
    private $mobileNumber;

    /**
     * User's VAT number.
     *
     * @var string
     *
     * @ORM\Column(name="vat_number", type="string", nullable=true)
     *
     * @Assert\Length(max="20", groups={"create", "edit"})
     */
    private $vatNumber;

    /**
     * User's company (as simple text field).
     *
     * @var string
     *
     * @ORM\Column(name="company", type="string", nullable=true)
     *
     * @Assert\Length(max="35", groups={"create", "edit"})
     * @Assert\NotBlank(message="Please enter the company name", groups={"marketplace_new_organisation"})
     */
    private $company;

    /**
     * User's roles list (e.g. Customer Approver). Each role gives specific permissions.
     *
     * @var array
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * User's password (not persisted but sent over to UnboundID which handles the authentication).
     *
     * @var string
     *
     * @Assert\NotBlank(message="Please enter your password.", groups={"marketplace_new_organisation", "marketplace_join_organisation"})
     */
    private $password;

    /**
     * Generated on demand by the user to reset his password on marketplace-ui.
     *
     * @var string
     *
     * @ORM\Column(name="reset_password_token", type="guid", nullable=true)
     */
    private $resetPasswordToken;

    /**
     * Used to check if the resetPasswordToken is not expired (expires after one hour).
     *
     * @var \Datetime
     *
     * @ORM\Column(name="reset_password_token_generated_at", type="datetime", nullable=true)
     */
    private $resetPasswordTokenGeneratedAt;

    /**
     * Whether the user is owner of an organisation, false by default.
     *
     * @var bool
     *
     * @ORM\Column(name="organisation_owner", type="boolean")
     */
    private $organisationOwner;

    /**
     * Implicit property which holds on which front the user registered. Used to brand elements (see brands in config.yml).
     *
     * @var string
     *
     * @ORM\Column(name="from_company", type="string", nullable=true)
     */
    private $fromCompany;

    /**
     * User's address.
     *
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="Address", fetch="EAGER", cascade={"persist", "remove"}, inversedBy="user", orphanRemoval=true)
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     *
     * @Serializer\Type("Canopy\Bundle\UserBundle\Entity\Address")
     *
     * @Assert\Valid()
     */
    private $address;

    /**
     * User's currency.
     *
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="Currency", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     *
     * @Serializer\Type("Canopy\Bundle\UserBundle\Entity\Currency")
     *
     * @Assert\Valid()
     */
    private $currency;

    /**
     * User's organisation.
     *
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="users", fetch="EAGER")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    private $organisation;

    /**
     * On registration the user is not verified yet. He must validate his account with a code sent by email.
     *
     * @var bool
     *
     * @ORM\Column(name="verified", type="boolean", options={"default": false})
     */
    private $verified;

    /**
     * Admins can disable accounts through dashboard-ui.
     *
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": true})
     */
    private $enabled;

    /**
     * User's department (e.g. Finance, HR).
     *
     * @var string
     *
     * @ORM\Column(name="department", type="string", nullable=true)
     */
    private $department;

    /**
     * User's job title.
     *
     * @var string
     *
     * @ORM\Column(name="job_title", type="string", nullable=true)
     * @Assert\Length(max="40", groups={"create", "edit"})
     */
    private $jobTitle;

    /**
     * User's company size (e.g. 100-500).
     *
     * @var string
     *
     * @ORM\Column(name="company_size", type="string", nullable=true)
     */
    private $companySize;

    /**
     * Industry (ex: Banking, Defence).
     *
     * @var string
     *
     * @ORM\Column(name="industry", type="string", nullable=true)
     * @Assert\NotBlank(message="Please select the industry", groups={"marketplace_new_organisation"})
     */
    private $industry;

    /**
     * Answer to How did you know about us?
     *
     * @var string
     *
     * @ORM\Column(name="mode_of_info", type="string", nullable=true)
     */
    private $modeOfInfo;

    /**
     * Unique identifier for an organisation. Used on registration, if the user knows it he does not need filling organisation details.
     *
     * @var string
     *
     * @ORM\Column(name="customer_id", type="string", nullable=true)
     * @Assert\NotBlank(message="Please enter the customer id of your company", groups={"join_organisation"})
     */
    private $customerId;

    /**
     * After registration, the user receives a verification code by email to validate his account.
     *
     * @var string
     *
     * @ORM\Column(name="verification_code", type="string")
     */
    private $verificationCode;

    /**
     * User's latest accepted policy's uuid.
     *
     * @var string
     *
     * @ORM\Column(name="policy_uuid", type="guid", nullable=true, options={"default": null})
     */
    private $policyUuid;

    /**
     * User's latest policy choice.
     *
     * @var int
     *
     * @ORM\Column(name="policy_choice", type="integer", options={"default": 0})
     */
    private $policyChoice;

    /**
     * A cron job is run daily to check if users have the latest policy.
     *
     * @var bool
     *
     * @ORM\Column(name="policy_latest", type="boolean", options={"default": 0})
     */
    private $answeredLatestPolicy;

    /**
     * User's permissions (deducted from user's roles).
     *
     * @var array
     */
    private $permissions;

    /**
     * Admins can force this property so that the user has to reset his password.
     *
     * @var bool
     *
     * @ORM\Column(name="credentials_expired", type="boolean", options={"default": 0})
     */
    private $credentialsExpired;

    /**
     * @param $unboundidUserId
     */
    public function __construct($unboundidUserId)
    {
        $this->uuid = $unboundidUserId; /* Later we will have to use UuidGenerator::generate()*/
        $this->unboundidUserId = $unboundidUserId;
        $this->organisationOwner = false;
        $this->organisation = null;
        $this->currency = null;
        $this->fromCompany = 'canopy';
        $this->email = null;
        $this->verified = false;
        $this->enabled = true;
        $this->credentialsExpired = false;
        $this->verificationCode = $this->generateVerificationCode();
        $this->policyChoice = self::POLICY_ACCEPTED;
        $this->answeredLatestPolicy = false;
        $this->roles = [self::DEFAULT_ROLE];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullname();
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
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnboundidUserId()
    {
        return $this->unboundidUserId;
    }

    /**
     * @param $unboundidUserId
     *
     * @return $this
     */
    public function setUnboundidUserId($unboundidUserId)
    {
        $this->unboundidUserId = $unboundidUserId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     *
     */
    public function getSalt()
    {
    }

    /**
     *
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function getResetPasswordToken()
    {
        return $this->resetPasswordToken;
    }

    /**
     * @param $resetPasswordToken
     *
     * @return $this
     */
    public function setResetPasswordToken($resetPasswordToken)
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getResetPasswordTokenGeneratedAt()
    {
        return $this->resetPasswordTokenGeneratedAt;
    }

    /**
     * @param $resetPasswordTokenGeneratedAt
     *
     * @return $this
     */
    public function setResetPasswordTokenGeneratedAt($resetPasswordTokenGeneratedAt)
    {
        $this->resetPasswordTokenGeneratedAt = $resetPasswordTokenGeneratedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->unboundidUserId;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param $firstname
     *
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param $lastname
     *
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param $avatar
     *
     * @return $this
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * @param $mobileNumber
     *
     * @return $this
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;

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
     * @param $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

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
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        if (0 === count($roles)) {
            $roles = [self::DEFAULT_ROLE];
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     *
     * @return $this
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * @param User $requestUser
     */
    public function updateFrom(User $requestUser)
    {
        $this->firstname = $requestUser->getFirstname();
        $this->lastname = $requestUser->getLastname();
        $this->email = $requestUser->getEmail();
        $this->mobileNumber = $requestUser->getMobileNumber();

        if (($address = $this->getAddress()) && $requestUser->getAddress()) {
            $address->updateFrom($requestUser->getAddress());
        }

        if ($requestUser->getOrganisation()) {
            $this->setOrganisation($requestUser->getOrganisation());
        }

        if ($requestUser->getCurrency()) {
            $this->currency = $requestUser->getCurrency();
        }

        $this->vatNumber = $requestUser->getVatNumber();
        $this->company = $requestUser->getCompany();

        $this->setRoles($requestUser->getRoles());
    }

    /**
     * @return Organisation|null
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     *
     * @return $this
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOrganisationOwner()
    {
        return $this->organisationOwner;
    }

    /**
     * @param $organisationOwner
     *
     * @return $this
     */
    public function setOrganisationOwner($organisationOwner)
    {
        $this->organisationOwner = $organisationOwner;

        return $this;
    }

    /**
     */
    public function getOrganisationId()
    {
        if (null !== $this->organisation) {
            return $this->organisation->getId();
        }

        return;
    }

    /**
     * @return string
     */
    public function getFromCompany()
    {
        return $this->fromCompany;
    }

    /**
     * @param $fromCompany
     *
     * @return $this
     */
    public function setFromCompany($fromCompany)
    {
        $this->fromCompany = $fromCompany;

        return $this;
    }

    /**
     * @return string
     */
    public function generateNewResetPasswordToken()
    {
        $this->setResetPasswordToken((string) \Rhumsaa\Uuid\Uuid::uuid4());
        $this->setResetPasswordTokenGeneratedAt(new \DateTime());

        return $this->getResetPasswordToken();
    }

    /**
     *
     */
    public function eraseResetPasswordToken()
    {
        $this->setResetPasswordToken(null);
        $this->setResetPasswordTokenGeneratedAt(null);
    }

    /**
     * @return bool
     */
    public function isResetPasswordTokenExpired()
    {
        return (time() - $this->getResetPasswordTokenGeneratedAt()->getTimestamp()) > 3600;
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @param $verified
     *
     * @return $this
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return string
     */
    public function getVerificationCode()
    {
        return $this->verificationCode;
    }

    /**
     * @param $verificationCode
     *
     * @return $this
     */
    public function setVerificationCode($verificationCode)
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function generateVerificationCode()
    {
        return strtoupper(substr(md5(rand()), 0, 8));
    }

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param $jobTitle
     *
     * @return $this
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanySize()
    {
        return $this->companySize;
    }

    /**
     * @param $companySize
     *
     * @return $this
     */
    public function setCompanySize($companySize)
    {
        $this->companySize = $companySize;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * @param $industry
     *
     * @return $this
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * @return string
     */
    public function getModeOfInfo()
    {
        return $this->modeOfInfo;
    }

    /**
     * @param $modeOfInfo
     *
     * @return $this
     */
    public function setModeOfInfo($modeOfInfo)
    {
        $this->modeOfInfo = $modeOfInfo;

        return $this;
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
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailHost()
    {
        $email = $this->getEmail();

        return substr($email, strpos($email, '@') + 1);
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

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return !$this->credentialsExpired;
    }

    /**
     * @param $credentialsExpired
     *
     * @return $this
     */
    public function setCredentialsExpired($credentialsExpired)
    {
        $this->credentialsExpired = $credentialsExpired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getDialingCode()
    {
        return $this->dialingCode;
    }

    /**
     * @param $dialingCode
     *
     * @return $this
     */
    public function setDialingCode($dialingCode)
    {
        $this->dialingCode = $dialingCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullMobileNumber()
    {
        return $this->dialingCode.' '.$this->mobileNumber;
    }

    /**
     * Returns which validation groups should be used for a certain state
     * of the object.
     *
     * @return array An array of validation groups
     */
    public function getValidationGroups($source = 'marketplace')
    {
        $mode = $this->getCustomerId() ? 'join_organisation' : 'new_organisation';

        return [$source, $mode, $source.'_'.$mode];
    }
}
