<?php

namespace Canopy\Bundle\UserBundle\Service;

use Canopy\Bundle\CommonBundle\Endpoint\AbstractEndpoint;
use Canopy\Bundle\UserBundle\Entity\User;

class CordysManager extends AbstractEndpoint
{
    protected $login;
    protected $password;

    public function setCredentials($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Update cordys user.
     */
    public function updateCordysUser(User $user)
    {
        $cordysuser = array();
        $cordysuser['source_user_id']    = $user->getUuid();
        $cordysuser['nessie_cust_id']    = ($user->getCustomerId()) ? $user->getCustomerId() : '';
        $cordysuser['validated_user']    = ($user->getCustomerId()) ? 'true' : 'false';
        $cordysuser['username']          = $user->getEmail();
        $cordysuser['email']             = $user->getEmail();
        $cordysuser['role']              = implode(',', $user->getRoles());
        $cordysuser['firstname']         = $user->getFirstname();
        $cordysuser['lastname']          = $user->getLastname();
        $cordysuser['company_name']      = ($user->getOrganisation()) ? $user->getOrganisation()->getName() : $user->getCompany();
        $cordysuser['state']             = ($user->getAddress()) ? $user->getAddress()->getState() : '';
        $cordysuser['street1']           = ($user->getAddress()) ? $user->getAddress()->getStreet1() : '';
        $cordysuser['street2']           = ($user->getAddress()) ? $user->getAddress()->getStreet2() : '';
        $cordysuser['town']              = ($user->getAddress()) ? $user->getAddress()->getCity() : '';
        $cordysuser['postcode']          = ($user->getAddress()) ? $user->getAddress()->getZipcode() : '';
        $cordysuser['country']           = ($user->getAddress()) ? $user->getAddress()->getCountry()->getEn() : '';
        $cordysuser['vat']               = $user->getVatNumber();
        $cordysuser['currency']          = ($user->getCurrency()) ? $user->getCurrency()->getIsoCode() : '';
        $cordysuser['mobile_number']     = $user->getDialingCode().$user->getMobileNumber();
        $cordysuser['country_code']      = ($user->getAddress()) ? $user->getAddress()->getCountry()->getIsoCode() : '';
        $cordysuser['lineofbusiness']    = '';
        $cordysuser['howyouknowus']      = $user->getModeOfInfo();
        $cordysuser['description']       = '';
        $cordysuser['companysize']       = $user->getCompanySize();
        $cordysuser['industry']          = $user->getIndustry();
        $cordysuser['expectedvalue']     = '';
        $cordysuser['source_user_department'] = $user->getDepartment();
        $cordysuser['job_title']         = $user->getJobTitle();

        $headers = [
            'Content-Type' => 'application/json',
        ];

        return $this->request(
            'POST',
            'users',
            [
                'json' => $cordysuser,
                'auth' => [$this->login, $this->password],
                'headers' => $headers,
            ]
        )->json();
    }
}
