<?php

namespace Canopy\Bundle\UserBundle\Serializer\Handler;

use Canopy\Bundle\CommonBundle\Endpoint\PolicyEndpoint;
use Canopy\Bundle\UserBundle\Entity\Country;
use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\UserBundle\Entity\Address;
use Canopy\Bundle\UserBundle\Service\UnboundIDUserManager;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManager;

class UserHandler implements SubscribingHandlerInterface
{
    private $router;
    private $entityManager;
    private $policyEndpoint;
    private $userManager;

    public function __construct(Router $router, EntityManager $entityManager, PolicyEndpoint $policyEndpoint, UnboundIDUserManager $userManager)
    {
        $this->router         = $router;
        $this->entityManager  = $entityManager;
        $this->policyEndpoint = $policyEndpoint;
        $this->userManager = $userManager;
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Canopy\Bundle\UserBundle\Entity\User',
                'method' => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'Canopy\Bundle\UserBundle\Entity\User',
                'method' => 'deserialize',
            ],
        ];
    }

    public function serialize(JsonSerializationVisitor $visitor, User $user, array $type, Context $context)
    {
        // In case we are serializing is a collection
        $isRoot = null === $visitor->getRoot();

        $url = $this->router->generate('canopy_get_user', ['uuid' => $user->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);

        $country = [];
        if (!is_null($user->getAddress()) && $user->getAddress()->getCountry()) {
            $country = [
                'id'       => (int) $user->getAddress()->getCountry()->getId(),
                'iso_code' => $user->getAddress()->getCountry()->getIsoCode(),
                'en'       => $user->getAddress()->getCountry()->getEn(),
                'fr'       => $user->getAddress()->getCountry()->getFr(),
                'dialing_code' => $user->getAddress()->getCountry()->getDialingCode(),
            ];
        }

        $currency = [];
        if ($user->getCurrency()) {
            $currency = [
                'id'       => (int) $user->getCurrency()->getId(),
                'iso_code' => $user->getCurrency()->getIsoCode(),
                'value'    => $user->getCurrency()->getValue(),
            ];
        }

        $address = [];
        if ($user->getAddress()) {
            $address = [
                'id'      => (int) $user->getAddress()->getId(),
                'state'  => $user->getAddress()->getState(),
                'city'    => $user->getAddress()->getCity(),
                'zipcode' => $user->getAddress()->getZipCode(),
                'country' => $country,
                'street1'   => $user->getAddress()->getStreet1(),
                'street2'   => $user->getAddress()->getStreet2(),
            ];
        }

        /*
         * The user have to accept new policies if :
         * - he is a CA and his organisation haven't got the latest policies and no other CA made a choice
         */
        $hasToAcceptPolicies =
            in_array('ROLE_CUSTOMER_APPROVER', $user->getRoles())
            && $user->getOrganisation()
            && $user->isVerified()
            && (!$user->getOrganisation()->isAnsweredLatestPolicy() || Organisation::POLICY_IGNORED === $user->getOrganisation()->getPolicyChoice())
            ;

        $data = [
            'uuid'                          => $user->getUuid(),
            'fullname'                      => $user->getFullname(),
            'firstname'                     => $user->getFirstname(),
            'lastname'                      => $user->getLastname(),
            'email'                         => $user->getEmail(),
            'dialing_code'                  => $user->getDialingCode(),
            'mobile_number'                 => $user->getMobileNumber(),
            'address'                       => $address,
            'currency'                      => $currency,
            'vat_number'                    => $user->getVatNumber(),
            'company'                       => $user->getCompany(),
            'from_company'                  => $user->getFromCompany(),
            'avatar'                        => $user->getAvatar(),
            'policy_uuid'                   => $user->getPolicyUuid(),
            'policy_choice'                 => $user->getPolicyChoice(),
            'answered_latest_policy'        => $user->isAnsweredLatestPolicy(),
            'has_to_accept_policies'        => $hasToAcceptPolicies,
            'roles'                         => $user->getRoles(),
            'permissions'                   => $user->getPermissions(),
            'organisation_owner'            => $user->getOrganisationOwner(),
            'verified'                      => $user->isVerified(),
            'enabled'                       => $user->isEnabled(),
            'credentials_expired'           => !$user->isCredentialsNonExpired(),
            'industry'                      => $user->getIndustry(),
            'department'                    => $user->getDepartment(),
            'customer_id'                   => $user->getCustomerId(),
            'job_title'                     => $user->getJobTitle(),
            'company_size'                  => $user->getCompanySize(),
            'mode_of_info'                  => $user->getModeOfInfo(),
            '_links'                        => ['self' => ['href' => $url]],
        ];

        if (!is_null($user->getOrganisation())) {
            $data['organisation_id']               = (int) $user->getOrganisation()->getId();
            $data['organisation_name']             = $user->getOrganisation()->getName();
            $data['organisation_answered_latest_policy'] = $user->getOrganisation()->isAnsweredLatestPolicy();
            $data['organisation_policy_choice']    = $user->getOrganisation()->getPolicyChoice();
            $data['organisation'] = $visitor->getNavigator()->accept($user->getOrganisation(), null, $context);
        }

        if ($isRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }

    public function deserialize(JsonDeserializationVisitor $visitor, $data, $type, DeserializationContext $context = null)
    {
        $source = null;
        if ($context && $context->attributes->containsKey('source')) {
            $source = $context->attributes->get('source')->get();
        }

        if (empty($data['uuid'])) {
            $user = new User(null);
            $currency = $this->entityManager->getRepository('CanopyUserBundle:Currency')->findOneByIsoCode('EUR');
            $user->setCurrency($currency);

            if ($source !== 'dashboard' && !isset($data['customer_id'])) {
                /*
                 * Sorry about that, we have to enforce validation of these fields.
                 */
                $user->setAddress(new Address('', '', '', new Country('', '', '', ''), ''));
            }
        } else {
            $user = $this->entityManager->getRepository('CanopyUserBundle:User')->findOneByUuid($data['uuid']);
        }

        $organisationRepository = $this->entityManager->getRepository('CanopyUserBundle:Organisation');

        if (!empty($data['password'])) {
            $user->setPassword($data['password']);
        }

        if (!empty($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }

        if (!empty($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (!empty($data['dialing_code'])) {
            $user->setDialingCode($data['dialing_code']);
        }

        if (!empty($data['mobile_number'])) {
            $user->setMobileNumber($data['mobile_number']);
        }

        if (!empty($data['address']['country']['iso_code'])) {
            $country = $this->entityManager->getRepository('CanopyUserBundle:Country')->findOneByIsoCode($data['address']['country']['iso_code']);
        }

        if (!empty($data['address']['id'])) {
            $existingAddress = $this->entityManager->getRepository('CanopyUserBundle:Address')->findOneById($data['address']['id']);
        }

        if (isset($existingAddress)) {
            if (isset($country)) {
                $existingAddress->setCountry($country);
            }

            if (isset($data['address']['state'])) {
                $existingAddress->setState($data['address']['state']);
            }

            if (isset($data['address']['city'])) {
                $existingAddress->setCity($data['address']['city']);
            }

            if (isset($data['address']['zipcode'])) {
                $existingAddress->setZipcode($data['address']['zipcode']);
            }

            if (isset($data['address']['street1'])) {
                $existingAddress->setStreet1($data['address']['street1']);
            }

            if (isset($data['address']['street2'])) {
                $existingAddress->setStreet2($data['address']['street2']);
            }
        }

        // The address that needs to be updated must be the one from the current user.
        if (
            !empty($data['address']['zipcode'])
            && !isset($existingAddress)
            && isset($country)
        ) {
            $address = new Address($data['address']['state'], $data['address']['city'], $data['address']['zipcode'], $country, $data['address']['street1'], isset($data['address']['street2']) ? $data['address']['street2'] : '');
            $user->setAddress($address);
        }

        if (isset($data['currency']['iso_code'])) {
            $currency = $this->entityManager->getRepository('CanopyUserBundle:Currency')->findOneByIsoCode($data['currency']['iso_code']);
            $user->setCurrency($currency);
        }

        if (isset($data['vat_number'])) {
            $user->setVatNumber($data['vat_number']);
        }

        if (isset($data['company'])) {
            $user->setCompany($data['company']);
        }

        if (isset($data['from_company'])) {
            $user->setFromCompany($data['from_company']);
        }

        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        if (isset($data['organisation_owner'])) {
            $user->setOrganisationOwner($data['organisation_owner']);
        }

        if (isset($data['organisation_id'])) {
            if ($existingOrganisation = $organisationRepository->find($data['organisation_id'])) {
                $user->setOrganisation($existingOrganisation);
            }
        }

        if (!empty($data['customer_id'])) {
            $existingOrganisation = $organisationRepository->findOneByCustomerId($data['customer_id']);

            if (null === $existingOrganisation) {
                throw new BadRequestHttpException('[cust_id.unknown] Customer ID doesn\'t exists');
            }

            if (!$existingOrganisation->canJoin($user)) {
                throw new BadRequestHttpException('[cust_id.invalid] Invalid Customer ID');
            }

            $existingOrganisation->accept($user);
        }

        if (isset($data['verified'])) {
            $user->setVerified($data['verified']);
        }

        if (isset($data['customer_id'])) {
            $user->setCustomerId($data['customer_id']);
        }

        if (isset($data['department'])) {
            $user->setDepartment($data['department']);
        }

        if (isset($data['job_title'])) {
            $user->setJobTitle($data['job_title']);
        }

        if (isset($data['company_size'])) {
            $user->setCompanySize($data['company_size']);
        }

        if (isset($data['industry'])) {
            $user->setIndustry($data['industry']);
        }

        if (isset($data['mode_of_info'])) {
            $user->setModeOfInfo($data['mode_of_info']);
        }

        if (isset($data['policy_uuid'])) {
            $user->setPolicyUuid($data['policy_uuid']);
        }

        if (isset($data['policy_choice'])) {
            $user->setPolicyChoice($data['policy_choice']);
        }

        return $user;
    }
}
