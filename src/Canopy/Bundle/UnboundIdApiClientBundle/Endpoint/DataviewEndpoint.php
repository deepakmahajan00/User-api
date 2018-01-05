<?php

namespace Canopy\Bundle\UnboundIdApiClientBundle\Endpoint;

use Canopy\Bundle\CommonBundle\Endpoint\AbstractEndpoint;
use Canopy\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DataviewEndpoint extends AbstractEndpoint
{
    protected $oauthEndpoint;

    public function setOAuthEndpoint(OAuthEndpoint $oauthEndpoint)
    {
        $this->oauthEndpoint = $oauthEndpoint;
    }

    protected function handleErrors($response)
    {
        if (isset($response['Errors'])) {
            // TODO : the API return could be analysed to get better error msg, need better api error exchanges

            if ((!is_array($response['Errors'])) || (!isset($response['Errors'][0]))) {
                throw new BadRequestHttpException('An error occurred.');
            }

            // Try to return the correct error message
            if (preg_match('#password history#', $response['Errors'][0]['description'])) {
                throw new BadRequestHttpException('Your new password must be different from your last three passwords.');
            } elseif (preg_match('#(rejected by a password validator|did not contain enough characters)#', $response['Errors'][0]['description'])) {
                throw new BadRequestHttpException('Your password is not strong enough, it should contain: Min 8 Characters Alpha Numeric Min 1 Uppercase Min 1 Special Character.');
            } elseif (preg_match('#entry already exists#', $response['Errors'][0]['description'])) {
                throw new BadRequestHttpException('Cannot proceed with new request as your account should be already available.');
            } else {
                throw new BadRequestHttpException('An error occurred.');
            }
        }
    }

    protected function userToDataview(User $user)
    {
        return [
            'schemas'  => ['urn:unboundid:oidc:1.0', 'urn:scim:schemas:core:1.0'],
            'name' => [
                'formatted'  => $user->getFullName(),
                'familyName' => $user->getLastname(),
                'givenName'  => $user->getFirstname(),
            ],
        ];
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function createUser(User $user)
    {
        $dataToken = $this->oauthEndpoint->getClientCredentialsAccessToken();

        $headers = [
            'Authorization' => ucfirst($dataToken['token_type']).' '.$dataToken['access_token'],
        ];

        $dataview = $this->userToDataview($user);
        $dataview['userName'] = $user->getEmail();
        $dataview['password'] = $user->getPassword();

        $response = $this->request('POST', 'Users', ['headers' => $headers, 'json' => $dataview], true)->json();

        $this->handleErrors($response);

        return $response;
    }

    public function updateUser(User $user)
    {
        $dataview = $this->userToDataview($user);

        $response = $this->request('PATCH', 'Users/'.$user->getUnboundidUserId(), ['json' => $dataview], true)->json();

        $this->handleErrors($response);

        return $response;
    }

    public function setUserPassword(User $user, $password)
    {
        $dataToken = $this->oauthEndpoint->getClientCredentialsAccessToken();

        $headers = [
            'Authorization' => ucfirst($dataToken['token_type']).' '.$dataToken['access_token'],
        ];

        $dataview = [
            'schemas'  => ['urn:unboundid:oidc:1.0', 'urn:scim:schemas:core:1.0'],
            'password' => $password,
        ];

        $response = $this->request('PATCH', 'Users/'.$user->getUnboundidUserId(), ['headers' => $headers, 'json' => $dataview], true)->json();

        $this->handleErrors($response);

        return $response;
    }

    public function getUserInfo($userId)
    {
        $dataToken = $this->oauthEndpoint->getClientCredentialsAccessToken();

        $headers = [
            'Authorization' => ucfirst($dataToken['token_type']).' '.$dataToken['access_token'],
        ];

        $response = $this->request('GET', 'Users/'.$userId, ['headers' => $headers], true)->json();

        $this->handleErrors($response);

        return $response;
    }
}
