<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    protected $client;

    /**
     * @dataProvider usersUuidProvider
     *
     * @param $userUuid
     */
    public function testGetMe($userUuid)
    {
        $this->client->request(
            'GET',
            '/api/me',
            ['_with_user' => $userUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('fullname', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertSame($userUuid, $data['uuid']);
    }

    public function testGetUser()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleAdministratorUuid,
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('uuid', $data);
        $this->assertSame($this->sampleAdministratorUuid, $data['uuid']);
    }

    public function testGetUsers()
    {
        $this->client->request(
            'GET',
            '/api/users',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 5);
    }

    /**
     * @dataProvider usersGoodDataProvider
     *
     * @param $userData
     */
    public function testCreateUser($userData)
    {
        $container = $this->client->getContainer();

        $unboundIdDataviewEndpointMock = $this->getUnboundIdDataviewEndpointMock();

        $unboundIdDataviewEndpointMock->expects($this->once())
            ->method('createUser');

        $this->setMocksInContainer([
            'api.unboundid.dataview' => $unboundIdDataviewEndpointMock,
            'api.unboundid.oauth' => $this->getUnboundIdOAuthEndpointMock(),
        ]);

        $this->client->request(
            'POST',
            '/api/users',
            ['_with_user' => $this->sampleManagerUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('uuid', $data);
        $this->assertRegExp('#'.$container->getParameter('regex_uuid_unbound_id').'#', $data['uuid']);
        $this->assertSame($this->sampleCreatedUuid, $data['uuid']);

        $em = $container->get('doctrine')->getManager();
        $user = $em->getRepository('CanopyUserBundle:User')->findOneByUuid($data['uuid']);

        $this->assertNotEmpty($user);
        $this->assertSame($userData['firstname'], $user->getFirstname());
        $this->assertSame($userData['lastname'], $user->getLastname());
        $this->assertSame($userData['email'], $user->getEmail());
        $this->assertSame($userData['company'], $user->getCompany());
        $this->assertSame($userData['from_company'], $user->getFromCompany());

        $this->reloadFixtures();
    }

    /**
     * @dataProvider usersBadDataProvider
     *
     * @param $userData
     */
    public function testCreateUserWithBadData($userData, $errors)
    {
        $this->client->request(
            'POST',
            '/api/users',
            ['_with_user' => $this->sampleManagerUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);

        foreach ($data['errors'] as $violation) {
            $this->assertTrue(in_array($violation['property_path'], $errors));
        }

        $this->reloadFixtures();
    }

    public function usersUuidProvider()
    {
        return [
            [$this->sampleUserUuid],
            [$this->sampleUser2Uuid],
            [$this->sampleProcurementUuid],
            [$this->sampleManagerUuid],
            [$this->sampleAdministratorUuid],
        ];
    }

    public function usersGoodDataProvider()
    {
        return [
            [[
                'password' => 'Sample Test User',
                'firstname' => 'Sample',
                'lastname' => 'User1',
                'email' => 'sampletestuser1@email.com',
                'mobile_number' => '+33 1 23 45 67 89',
                'address' => [
                        'id' => 1,
                        'state' => 'MH',
                        'street1' => '94-98',
                        'street2' => ' boulevard Victor Hugo',
                        'city' => 'Clichy',
                        'zipcode' => '92110',
                        'country' => [
                                'id' => 74,
                                'iso_code' => 'FR',
                                'en' => 'France',
                                'fr' => 'France',
                            ],
                    ],
                'currency' => [
                        'id' => 1,
                        'iso_code' => 'EUR',
                        'value' => 'Euro',
                    ],
                'vat_number' => 'VAT_NUMBER_SIMPLE_USER_1',
                'company' => 'Atos',
                'from_company' => 'nokia-ui',
                'avatar' => 'http://i.imgur.com/90dbpHF.png',
                'roles' => ['ROLE_REGISTERED_USER_VERIFIED'],
                'organisation_id' => 2,
                'organisation_owner' => false,
            ]],
            [[
                'password' => 'Sample Test User 2',
                'firstname' => 'AAaaaAA',
                'lastname' => 'ploupi',
                'email' => 'sampletestuser2@email.com',
                'mobile_number' => '06 23 45 67 89',
                'address' => [
                        'id' => 1,
                        'state' => 'MH',
                        'street1' => '94-98',
                        'street2' => ' boulevard Victor Hugo',
                        'city' => 'Clichy',
                        'zipcode' => '92110',
                        'country' => [
                                'id' => 74,
                                'iso_code' => 'FR',
                                'en' => 'France',
                                'fr' => 'France',
                            ],
                    ],
                'currency' => [
                        'id' => 1,
                        'iso_code' => 'EUR',
                        'value' => 'Euro',
                    ],
                'vat_number' => 'VAT_NUMBER_SIMPLE_USER_1',
                'company' => 'sensiolabs',
                'from_company' => 'canopy',
                'avatar' => 'http://i.imgur.com/90dbpHF.png',
                'roles' => ['ROLE_REGISTERED_USER_VERIFIED'],
                'organisation_id' => 2,
                'organisation_owner' => false,
            ]],
        ];
    }

    public function usersBadDataProvider()
    {
        return [
            [
                [
                    'password' => null,
                    'firstname' => 'Sample',
                    'lastname' => 'User1',
                    'email' => 'sampemail.com',
                    'mobile_number' => '+33 1 23 45 67 89',
                    'address' => [
                            'id' => 1,
                            'state' => 'MH',
                            'street1' => '94-98',
                            'street2' => ' boulevard Victor Hugo',
                            'city' => 'Clichy',
                            'zipcode' => '92110',
                            'country' => [
                                    'id' => 74,
                                    'iso_code' => 'FR',
                                    'en' => 'France',
                                    'fr' => 'France',
                                ],
                        ],
                    'currency' => [
                            'id' => 1,
                            'iso_code' => 'EUR',
                            'value' => 'Euro',
                        ],
                    'vat_number' => 'VAT_NUMBER_SIMPLE_USER_1',
                    'company' => 'Atos',
                    'from_company' => 'nokia-ui',
                    'avatar' => 'http://i.imgur.com/90dbpHF.png',
                    'roles' => ['ROLE_REGISTERED_USER_VERIFIED'],
                    'organisation_id' => 2,
                    'organisation_owner' => false,
                ],
                [
                    'email',
                    'password',
                ],

            ],
            [
                [
                    'password' => 'Sample Test User 2',
                    'firstname' => null,
                    'lastname' => null,
                    'email' => 'sampletestuser2@email.com',
                    'mobile_number' => '06 23 45 67 89',
                    'address' => [
                            'id' => 1,
                            'state' => 'MH',
                            'street1' => '94-98',
                            'street2' => ' boulevard Victor Hugo',
                            'city' => 'Clichy',
                            'zipcode' => '92110',
                            'country' => [
                                    'id' => 74,
                                    'iso_code' => 'FR',
                                    'en' => 'France',
                                    'fr' => 'France',
                                ],
                        ],
                    'currency' => [
                            'id' => 1,
                            'iso_code' => 'EUR',
                            'value' => 'Euro',
                        ],
                    'vat_number' => 'VAT_NUMBER_SIMPLE_USER_1',
                    'company' => 'sensiolabs',
                    'from_company' => 'canopy',
                    'avatar' => 'http://i.imgur.com/90dbpHF.png',
                    'roles' => ['ROLE_REGISTERED_USER_VERIFIED'],
                    'organisation_id' => 2,
                    'organisation_owner' => false,
                ],
                [
                    'firstname',
                    'lastname',
                ],
            ],
        ];
    }

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }
}
