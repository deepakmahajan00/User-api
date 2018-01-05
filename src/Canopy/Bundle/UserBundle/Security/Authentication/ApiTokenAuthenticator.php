<?php

namespace Canopy\Bundle\UserBundle\Security\Authentication;

use Canopy\Bundle\UnboundIdApiClientBundle\Endpoint\OAuthEndpoint;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\User\UserChecker;

/**
 * Class ApiTokenAuthenticator.
 *
 * Provides authentication based on the Authorization request header.
 * Note that this is loaded in the security.yml (as the secured_area firewall).
 *
 * @see Canopy/Bundle/UserBundle/Resources/doc/Authentication.md
 * @see http://symfony.com/doc/current/cookbook/security/api_key_authentication.html
 */
class ApiTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var OAuthEndpoint
     */
    private $oauthEndpoint;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserChecker
     */
    private $userChecker;

    /**
     * @param OAuthEndpoint   $oauthEndpoint
     * @param LoggerInterface $logger
     * @param UserChecker     $userChecker
     */
    public function __construct(OAuthEndpoint $oauthEndpoint, LoggerInterface $logger, UserChecker $userChecker)
    {
        $this->oauthEndpoint = $oauthEndpoint;
        $this->logger = $logger;
        $this->userChecker = $userChecker;
    }

    /**
     * Check the Authorization header of the request.
     *
     * @param Request $request
     * @param $providerKey
     *
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $accessToken = $request->headers->get('Authorization');

        if (!$accessToken) {
            $message = 'No access token provided in Authorization header.';
            $this->logger->notice(
                $message,
                array(
                    'HTTP_CODE_STATUS' => Response::HTTP_UNAUTHORIZED,
                    'REQUEST_HEADERS'  => $request->headers,
                    'REQUEST_CONTENT'  => $request->getContent(),
                )
            );

            throw new HttpException(Response::HTTP_UNAUTHORIZED, $message);
        }

        return new PreAuthenticatedToken('anon.', $accessToken, $providerKey);
    }

    /**
     * Use the $token (Authorization header) to query UnboundID and load the user with
     * the given $userProvider (UnboundidUserProvider) and return a PreAuthenticatedToken.
     *
     * @see Canopy\Bundle\UserBundle\Security\Authentication\UnboundidUserProvider
     *
     * {@inheritdoc}
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $accessTokenData = $token->getCredentials();

        try {
            $accessToken = mb_substr($accessTokenData, 7);
            $userInfo = $this->oauthEndpoint->validate($accessToken);
        } catch (\Exception $e) {
            $message = sprintf('[token.invalid] Invalid token provided. Given : %s', $accessTokenData);
            $this->logger->notice($message, array('status_code' => Response::HTTP_UNAUTHORIZED));

            throw new HttpException(Response::HTTP_UNAUTHORIZED, $message);
        }

        $user = $userProvider->loadUserByUsername($userInfo['user_id']);

        $this->userChecker->checkPreAuth($user);
        $this->userChecker->checkPostAuth($user);

        return new PreAuthenticatedToken($user, $accessTokenData, $providerKey, $user->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = $exception instanceof DisabledException ?
            '[user.blocked] '.$exception->getMessageKey()
            : $exception->getMessageKey();

        return new JsonResponse(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }
}
