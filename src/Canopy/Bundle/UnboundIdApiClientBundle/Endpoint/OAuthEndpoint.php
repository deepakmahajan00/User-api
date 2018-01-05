<?php

namespace Canopy\Bundle\UnboundIdApiClientBundle\Endpoint;

use Canopy\Bundle\CommonBundle\Endpoint\AbstractEndpoint;
use Doctrine\Common\Cache\PhpFileCache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OAuthEndpoint extends AbstractEndpoint
{
    protected $clientId;
    protected $clientSecret;
    protected $rootDir;

    public function setCredentials($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function setKernelRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Log a user on UnboundID with is username and his password.
     *
     * @param $username
     * @param $password
     *
     * @return array
     */
    public function getAccessTokenByUsername($username, $password)
    {
        $headers = $this->getClientAuthorizationHeaders();

        $query = [
            'grant_type'    => 'password',
            'username'      => $username,
            'password'      => $password,
        ];

        $response = $this->request('POST', 'token', ['query' => $query, 'headers' => $headers]);

        if (is_array($response)) {
            throw new BadRequestHttpException('Invalid credentials.');
        }

        return $response->json();
    }

    /**
     * Retrieve an anonymous access token.
     *
     * @return array
     */
    public function getClientCredentialsAccessToken()
    {
        $headers = $this->getClientAuthorizationHeaders();

        $query = [
            'grant_type' => 'client_credentials',
        ];

        return $this->request('POST', 'token', ['query' => $query, 'headers' => $headers])->json();
    }

    /**
     * Validate an access token on UnboundID.
     *
     * @param $accessToken
     *
     * @return string
     */
    public function validate($accessToken)
    {
        // limits the calls to UnboundID
        $cache = new PhpFileCache($this->rootDir.'/tokens');
        if ($cache->contains($accessToken)) {
            return unserialize($cache->fetch($accessToken));
        }

        $query = [
            'token' => $accessToken,
        ];

        $response = $this->request('POST', 'validate', ['query' => $query]);

        if (is_array($response)) {
            throw new BadRequestHttpException('[token.invalid] Invalid token.');
        }

        $userInfo = $response->json();
        $cache->save($accessToken, serialize($userInfo), $userInfo['expires_in']);

        return $userInfo;
    }

    /**
     * Check if the given account is locked.
     *
     * @param $username
     *
     * @return bool
     */
    public function isAccountLocked($username)
    {
        /*
         * We're setting here the port to avoid creating a new Endpoint just for this method.
         * This method (and an entrypoint) was quickly developed to handle the case where the user has
         * locked her account. Sorry.
         */
        $request = $this->getClient()->createRequest('GET', '/usable', ['query' => ['uid' => $username]]);
        $request->setPort('8082');
        $request->setScheme('http');

        $response = $this->getClient()->send($request)->json();

        if ('ko' === $response['result']) {
            return true;
        }

        return false;
    }

    /**
     * Revoke the token when the user logs out.
     *
     * @param $accessToken
     *
     * @return array
     */
    public function revoke($accessToken)
    {
        if (false !== strpos($accessToken, 'Bearer ')) {
            $accessToken = substr($accessToken, 7);
        }

        // remove token from cache
        $cache = new PhpFileCache($this->rootDir.'/tokens');
        if ($cache->contains($accessToken)) {
            $cache->delete($accessToken);
        }

        $headers = $this->getClientAuthorizationHeaders();
        $body = ['token' => $accessToken];

        return $this->request('POST', 'revoke', ['body' => $body, 'headers' => $headers]);
    }

    /**
     * Format Authorization and Content-Type headers.
     *
     * @return array
     */
    protected function getClientAuthorizationHeaders()
    {
        $headers = [
            'Authorization' => sprintf('Basic %s', base64_encode($this->clientId.':'.$this->clientSecret)),
            'Content-Type' => 'application/w-www-form-urlencoded',
        ];

        return $headers;
    }
}
