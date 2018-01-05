<?php

namespace Canopy\Bundle\UserBundle\Controller;

use Canopy\Bundle\UserBundle\Entity\Address;
use Canopy\Bundle\UserBundle\Entity\User;
use Canopy\Bundle\UserBundle\Representation\UsersRepresentation;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Conf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Conf\Route("/api")
 */
class UserController extends AbstractController
{
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    protected $roles =  [
        'Customer Approver' => 'ROLE_CUSTOMER_APPROVER',
        'Customer Requestor' => 'ROLE_CUSTOMER_REQUESTOR',
    ];

    /**
     * Returns the current authenticated User.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/me", name="canopy_get_me")
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     200="Returned when successful",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function getMeAction()
    {
        return $this->getUser();
    }

    /**
     * Authenticate a User with given credentials.
     *
     * Returns an array with the access_token and expires_in values.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/authenticate", name="canopy_post_user_authenticate")
     * @View()
     *
     * @RequestParam(name="username", description="User's email.")
     * @RequestParam(name="password", description="User's password.")
     *
     * @ApiDoc(
     *  resource=true,
     *  statusCodes={
     *     200="Returned when the id is valid.",
     *     400="Returned when the username or the password are not present."
     *   }
     * )
     */
    public function postUserAuthenticateAction($username, $password)
    {
        $oAuthEndpoint = $this->get('api.unboundid.oauth');
        $dataviewEndpoint = $this->get('api.unboundid.dataview');

        if ($oAuthEndpoint->isAccountLocked($username)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, '[account.locked] Your account has been locked.');
        }

        try {
            $authenticationData = $oAuthEndpoint->getAccessTokenByUsername($username, $password);
            $userData = $oAuthEndpoint->validate($authenticationData['access_token']);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, $e->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CanopyUserBundle:User')->findOneByUnboundidUserId($userData['user_id']);

        if (!$user) {
            $userInfo = $dataviewEndpoint->getUserInfo($userData['user_id']);

            $address = new Address('', '', '', $em->getRepository('CanopyUserBundle:Country')->findOneBy(['isoCode' => 'FR']), '');

            $em->persist($address);

            $user = new User($userData['user_id']);
            $user->setEmail($userInfo['userName']);
            $user->setFirstname($userInfo['name']['givenName']);
            $user->setLastname($userInfo['name']['familyName']);
            $user->setRoles([]);
            $user->setAddress($address);
            $user->setCurrency($em->getRepository('CanopyUserBundle:Currency')->findOneBy(['isoCode' => 'EUR']));

            $em->persist($user);
        }

        $em->flush();

        $this->get('security.user_checker')->checkPreAuth($user);
        $this->get('security.user_checker')->checkPostAuth($user);

        return [
            'access_token'  => ucfirst($authenticationData['token_type']).' '.$authenticationData['access_token'],
            'expires_in'    => $authenticationData['expires_in'],
        ];
    }

    /**
     * Revoke current User token on UnboundID.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/revoke", name="canopy_post_user_revoke")
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     204="If the user is successfully logged out from unboundID.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function postUserRevokeAction()
    {
        $token = $this->get('security.token_storage')->getToken();
        $this->get('api.unboundid.oauth')->revoke($token->getCredentials());
    }

    /**
     * Returns a User.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users/{uuid}", name="canopy_get_user", requirements={"uuid": "%regex_uuid_unbound_id%"})
     * @View(serializerGroups={"user_view"})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     200="Returned when the user is found.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * Returns all Users.
     *
     * @Conf\Method("GET")
     * @Conf\Route("/users", name="canopy_get_users")
     * @View()
     *
     * @QueryParam(name="role", default="all", description="Filter users with the given role.")
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page asked to displayed.")
     * @QueryParam(name="limit", requirements="\d+", default="5", description="Number of users asked in one page.")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="array<Canopy\Bundle\UserBundle\Entity\User>",
     *  statusCodes={
     *     200="Returned when users are found.",
     *   }
     * )
     */
    public function getUsersAction($role, $page, $limit)
    {
        $repository = $this->getDoctrine()->getRepository('CanopyUserBundle:User');
        $users = $repository->getAllPaginated($page, $limit, array('role' => $role));
        $total = $repository->getTotalCount(array('role' => $role));

        return new PaginatedRepresentation(
            new UsersRepresentation($users),
            'canopy_get_users', // route
            array(), // route parameters
            $page, // page
            $limit, // limit
            ceil($total / $limit), // total pages
            'page',
            'limit',
            true,
            $total
        );
    }

    /**
     * Cordys callback to approve/block User.
     *
     * Cordys will update role and customer id if user status is approved.
     * If user status is rejected then it will block the user.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/users/{uuid}/update-cordys-user", name="canopy_put_cordys_callback")
     * @View(statusCode=201)
     *
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  output="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     201="Returned when successful",
     *     400="Returned if something failed"
     *   }
     * )
     */
    public function putCordysCallbackAction(User $user, Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        if ($requestData['status'] == self::APPROVED) {
            // Add the role to the user
            if ((isset($requestData['role']) && isset($this->roles[$requestData['role']]))) {
                $user->setRoles([$this->roles[$requestData['role']]]);
            }

            // Set the customer id to the user and create a new (or update existing) Organisation
            if (!empty($requestData['customer_id'])) {
                $user->setCustomerId($requestData['customer_id']);

                $organisation = $em->getRepository('CanopyUserBundle:Organisation')->findOneByCustomerId($requestData['customer_id']);
                if (null === $organisation) {
                    $organisation = $this->get('canopy.organisation.manager')->createFromUser($user);
                }

                $organisation->accept($user);

                $this->get('canopy.domain_name.manager')->createIfNotExist($user, $organisation);
            }

            $this->sendUserApprovedEmail($user);
        } elseif ($requestData['status'] == self::REJECTED) {
            // User is rejected we block him
            $user->setEnabled(false);
            $user->setCustomerId(null);
            $this->sendUserRejectedEmail($user);
        }

        $em->flush();

        return $user;
    }

    /**
     * Update an existing User.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/users/{uuid}", name="canopy_put_user")
     * @View
     *
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  },
     *  statusCodes={
     *     200="Returned when successful",
     *     400="Returned when the id doesn't correspond the requirement or if the notification supplied has a different type from the notification updated.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned when the notification is not found"
     *   }
     * )
     */
    public function putUserAction(User $user, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $source = $request->headers->get('X-Canopy-source', 'marketplace');
        $context = DeserializationContext::create()->setAttribute('source', $source);

        $requestUser = $this->get('serializer')->deserialize($request->getContent(), 'Canopy\Bundle\UserBundle\Entity\User', 'json', $context);

        $validationGroups = array_merge(['edit'], $requestUser->getValidationGroups($source));
        $errors = $this->get('validator')->validate($requestUser, $validationGroups);

        if (count($errors)) {
            return $this->get('canopy.constraint_violation_list.converter')->createResponse($errors);
        }

        $this->get('canopy.api.unboundid.user_management')->updateUser($user, $requestUser);

        if ($this->container->getParameter('cordys_enabled') && $user->isVerified()) {
            return $this->get('canopy.api.cordys.users')->updateCordysUser($user);
        }

        return $user;
    }

    /**
     * Create a new User.
     *
     * Marketplace calls this action to submit a registration form (hence no authentication needed).
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users", name="canopy_post_user")
     * @View(statusCode=201)
     *
     *
     * @ApiDoc(
     *  resource=true,
     *  input="Canopy\Bundle\UserBundle\Entity\User",
     *  statusCodes={
     *     201="Returned when successful",
     *     400="Returned if something failed"
     *   }
     * )
     */
    public function postUserAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $source = $request->headers->get('X-Canopy-source', 'marketplace');
        $context = DeserializationContext::create()->setAttribute('source', $source);

        $user = $this->get('serializer')->deserialize($request->getContent(), 'Canopy\Bundle\UserBundle\Entity\User', 'json', $context);
        $errors = $this->get('validator')->validate($user, array_merge(['create'], $user->getValidationGroups($source)));

        if (count($errors)) {
            return $this->get('canopy.constraint_violation_list.converter')->createResponse($errors);
        }

        $this->get('canopy.api.unboundid.user_management')->createUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return ['uuid' => $user->getUuid()];
    }

    /**
     * Generate and returns a token to reset password.
     *
     * This action will only generate and set the token to the given User.
     * It is your responsibility to send en email to the User with this token.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/reset-password-token", name="canopy_post_user_reset_password_token")
     * @View()
     *
     * @RequestParam(name="email", description="The email of the user who wants the token.")
     *
     * @ApiDoc(
     *  resource=true,
     *  statusCodes={
     *     200="Returned when the id is valid.",
     *     404="No user for this email."
     *   }
     * )
     */
    public function postUserResetPasswordTokenAction(User $user)
    {
        $token = $user->generateNewResetPasswordToken();

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return ['token' => $token];
    }

    /**
     * Set a new password for a User.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/reset-password", name="canopy_post_user_reset_password")
     * @View()
     *
     * @RequestParam(name="token", description="The resetPasswordToken which identify the User.")
     * @RequestParam(name="password", description="The new password.")
     *
     * @ApiDoc(
     *  resource=true,
     *  statusCodes={
     *     200="Returned when the id is valid.",
     *     400="If the token is invalid or expired (1H long).",
     *     404="No user for this token."
     *   }
     * )
     */
    public function postUserResetPasswordAction($token, $password)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('CanopyUserBundle:User')->findOneBy(['resetPasswordToken' => $token]);

        if (!$user) {
            throw new BadRequestHttpException('[reset_password.token.invalid] Invalid reset password token.');
        }

        if ($user->isResetPasswordTokenExpired()) {
            $user->eraseResetPasswordToken();
            $em->flush();

            throw new BadRequestHttpException('Expired token.');
        }

        $user->setCredentialsExpired(false);
        $this->get('canopy.api.unboundid.user_management')->setUserPassword($user, $password);
    }

    /**
     * Initiate the reset password.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/{uuid}/initiate-reset-password", name="canopy_post_user_initiate_reset_password")
     * @View
     * @Conf\Security("(is_granted('PERM_DASHBOARD_ORGANISATION_USER_EDIT') && user.getOrganisation() == editUser.getOrganisation()) || is_granted('ROLE_ADMIN')")
     *
     * @Conf\ParamConverter("editUser", options={"mapping": {"uuid" = "uuid"}})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     200="Returned when successful.",
     *     403="Forbidden, the user has not the permissions.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function postUserInitiateResetPasswordAction(User $editUser)
    {
        $editUser->setCredentialsExpired(true);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Verify User's account with a verification code.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/verify-account", name="canopy_post_user_verify_account")
     * @View
     *
     * @RequestParam(name="uuid", description="User uuid.", requirements="^[0-9a-f]{6}-[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$")
     * @RequestParam(name="code", description="Verification code.")
     *
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     204="Returned when successful.",
     *     400="Returned when the verification code doesn't match.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function postUserVerifyAccountAction(User $user, $code)
    {
        if ($user->getVerificationCode() === $code) {
            $user->setVerified(true);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->sendUserVerifiedEmail($user);
            if ($user->getCustomerId()) {
                $this->sendUserApprovedEmail($user);
            }
           
            if ($this->container->getParameter('cordys_enabled')) {
                return $this->get('canopy.api.cordys.users')->updateCordysUser($user);
            }
        } else {
            throw new BadRequestHttpException('Invalid code.');
        }
    }

    /**
     * Send email with verification code.
     *
     * @Conf\Method("POST")
     * @Conf\Route("/users/send-verification-code", name="canopy_post_user_send_verification_code")
     * @View
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     200="Returned when successful.",
     *     401="Returned if the access_token provided is invalid.",
     *   }
     * )
     */
    public function postSendVerificationCodeAction()
    {
        $verificationCode = $this->getUser()->generateVerificationCode();
        $this->getUser()->setVerificationCode($verificationCode);

        $this->getDoctrine()->getManager()->flush();

        try {
            $this->get('canopy.branded_mailer')->sendBrandedMail(
                'verification-code',
                $this->getUser()->getFromCompany(),
                $this->getUser()->getEmail(),
                [
                    'fullname' => $this->getUser()->getFullname(),
                    'verificationCode' => $verificationCode,
                ]
            );
        } catch (\Exception $e) {
            ob_end_clean(); // In case of error, avoid twig to send the block content
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Cannot send this email', $e);
        }
    }

    /**
     * Enable/Disable a User.
     *
     * This action is restricted to admin Users.
     * A similar action is available to Organisation owners.
     *
     * @Conf\Method("POST")
     * @Conf\Route(
     *  "/users/{uuid}/{state}",
     *  requirements={
     *      "uuid": "%regex_uuid_unbound_id%",
     *      "state": "enable|disable"
     *  },
     *  name="canopy_post_user_enabled"
     * )
     * @View
     *
     * @Conf\ParamConverter("editUser", options={"mapping": {"uuid" = "uuid"}})
     *
     * @Conf\Security("is_granted('PERM_DASHBOARD_ADMIN_USER_EDIT') && user.getUuid() !== editUser.getUuid()")
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  statusCodes={
     *     204="Returned when successful.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned if the user is not found.",
     *   }
     * )
     */
    public function postUserEnableAction(User $editUser, $state)
    {
        $editUser->setEnabled('enable' === $state);

        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Send email notification to user if approved.
     *
     * @param User $user
     */
    private function sendUserApprovedEmail(User $user)
    {
        $this->get('canopy.branded_mailer')->sendBrandedMail(
                'user_approved',
                $user->getFromCompany(),
                $user->getEmail(),
                [
                    'firstName' => $user->getFirstname(),
                    'customerId' => $user->getCustomerId(),
                ]
        );
    }

    /**
     * Send email notification to user if rejected.
     *
     * @param User $user
     */
    private function sendUserRejectedEmail(User $user)
    {
        $this->get('canopy.branded_mailer')->sendBrandedMail(
                'user_rejected',
                $user->getFromCompany(),
                $user->getEmail(),
                [
                'fullname' => $user->getFullname(),
                ]
        );
    }

    /**
     * Send email notification to user after verified email.
     *
     * @param User $user
     */
    private function sendUserVerifiedEmail(User $user)
    {
        $this->get('canopy.branded_mailer')->sendBrandedMail(
            'email-verified',
            $user->getFromCompany(),
            $user->getEmail(),
            [
                'fullname' => $user->getFullname(),
            ]
        );
    }

    /**
     * Update an existing User's policy choice.
     *
     * @Conf\Method("PUT")
     * @Conf\Route("/updatePolicyChoice/{uuid}", name="canopy_put_user_policy_choice")
     * @View
     *
     * @Conf\ParamConverter("user", options={"mapping": {"uuid" = "uuid"}})
     * @Conf\ParamConverter("requestUser", converter="fos_rest.request_body", options={"validator"={"groups"={"policy"}}})
     *
     * @ApiDoc(
     *  resource=true,
     *  authentication=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  },
     *  statusCodes={
     *     200="Returned when successful",
     *     400="Returned when the id doesn't correspond the requirement.",
     *     401="Returned if the access_token provided is invalid.",
     *     404="Returned when the record is not found"
     *   }
     * )
     */
    public function putUserPolicyChoiceAction(User $user, User $requestUser, Request $request)
    {
    	$validationGroups = ['policy'];
    	$errors = $this->get('validator')->validate($requestUser, $validationGroups);
    	$em = $this->getDoctrine()->getManager();
    	
    	if (count($errors)) {
    		return $this->get('canopy.constraint_violation_list.converter')->createResponse($errors);
    	}
    	 
    	$user->setPolicyChoice($requestUser->getPolicyChoice());
    	$organisation = $user->getOrganisation();
    	$orgUsers = $organisation->getUsers();
    	if($requestUser->getPolicyUuid()!==null) {
    		$user->setAnsweredLatestPolicy(true);
    		$user->setPolicyUuid($requestUser->getPolicyUuid());
    		
    		//update the organisation's policy selections
    		if($user->getOrganisationId()!== null)
	        {
	        	$organisation->setPolicyChoice($requestUser->getPolicyChoice());
	        	$organisation->setAnsweredLatestPolicy(true);
	        	$organisation->setPolicyUuid($requestUser->getPolicyUuid());
	        	
	        	//set the policy flags for all the users in that organisation
	        	foreach ($orgUsers as $key=>$orgUser)
	        	{
	        		$orgUser->setPolicyChoice($requestUser->getPolicyChoice());
	        		$orgUser->setAnsweredLatestPolicy(true);
	        		$orgUser->setPolicyUuid($requestUser->getPolicyUuid());
	        	}
	        	
	        }    		
    	}
    	else {
            $user->setAnsweredLatestPolicy(false); 
    		if(null !== $organisation && null !== $organisation->getPolicyUuid()) {    		    
    		    $organisation->setAnsweredLatestPolicy(false);
    		    foreach ($orgUsers as $key=>$orgUser)
    		    {
    		    	$orgUser->setAnsweredLatestPolicy(false);
    		    }
    		}
    	}
        $em->flush();
    	 
    	if ($this->container->getParameter('cordys_enabled') && $user->isVerified()) {
    		return $this->get('canopy.api.cordys.users')->updateCordysUser($user);
    	}
    
    	return $user;
    }
    
}
