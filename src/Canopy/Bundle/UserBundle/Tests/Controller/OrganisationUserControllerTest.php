<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganisationUserControllerTest extends WebTestCase
{
    protected $client;
    protected $em;
    protected $organisation;

    public function testGetOrganisationUsers()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/users?_with_user='.$this->sampleManagerUuid
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 5);
    }

    public function testGetOrganisationUsersNotExist()
    {
        $this->client->request(
            'GET',
            '/api/organisations/999999/users?_with_user='.$this->sampleManagerUuid
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetOrganisationUser()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/users/'.$this->sampleManagerUuid.'?_with_user='.$this->sampleManagerUuid
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('uuid', $data);
        $this->assertSame($this->sampleManagerUuid, $data['uuid']);
        $this->assertArrayHasKey('firstname', $data);
        $this->assertSame('ProjectManager', $data['firstname']);
    }

    public function testGetOrganisationUserNotExist()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/users/'. 999999 .'?_with_user='.$this->sampleManagerUuid
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateOrganisationUser()
    {
        $unboundIdDataviewEndpointMock = $this->getUnboundIdDataviewEndpointMock();

        $unboundIdDataviewEndpointMock->expects($this->once())
            ->method('createUser');

        $this->setMocksInContainer([
            'api.unboundid.dataview' => $unboundIdDataviewEndpointMock,
        ]);

        $userData = $this->getOrganisationUserData();

        $this->client->request(
            'POST',
            '/api/organisations/'.$this->organisation->getId().'/users',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->reloadFixtures();
    }

    public function testCreateOrganisationUserWithBadData()
    {
        $userData = $this->getOrganisationUserData();
        $userData['email'] = null;

        $this->client->request(
            'POST',
            '/api/organisations/'.$this->organisation->getId().'/users',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey(0, $data['errors']);
        $this->assertCount(1, $data['errors']);
        $this->assertSame('email', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    public function testUpdateOrganisationUser()
    {
        $unboundIdDataviewEndpointMock = $this->getUnboundIdDataviewEndpointMock();

        $this->setMocksInContainer([
            'api.unboundid.dataview' => $unboundIdDataviewEndpointMock,
        ]);

        $userData = $this->getOrganisationUserData();

        $this->client->request(
            'PUT',
            '/api/organisations/'.$this->organisation->getId().'/users/'.$this->sampleUserUuid,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->em->clear();
        $user = $this->em->getRepository('CanopyUserBundle:User')->findOneByUuid($this->sampleUserUuid);
        $this->assertSame($userData['firstname'], $user->getFirstname());
        $this->assertSame($userData['email'], $user->getEmail());

        $this->reloadFixtures();
    }

    public function testUpdateOrganisationUserWithBadData()
    {
        $unboundIdDataviewEndpointMock = $this->getUnboundIdDataviewEndpointMock();

        $this->setMocksInContainer([
            'api.unboundid.dataview' => $unboundIdDataviewEndpointMock,
        ]);

        $userData = $this->getOrganisationUserData();
        $userData['email'] = '123456789';

        $this->client->request(
            'PUT',
            '/api/organisations/'.$this->organisation->getId().'/users/'.$this->sampleUserUuid,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey(0, $data['errors']);
        $this->assertCount(1, $data['errors']);
        $this->assertSame('email', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    public function testDeleteOrganisationUser()
    {
        $this->client->request(
            'DELETE',
            '/api/organisations/'.$this->organisation->getId().'/users/'.$this->sampleUserUuid,
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->reloadFixtures();
    }

    public function testOrganisationOwnerUser()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/owners',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
        $this->assertArrayHasKey('uuid', $data['data'][0]);
        $this->assertSame($this->sampleAdministratorUuid, $data['data'][0]['uuid']);
    }

    protected function getOrganisationUserData()
    {
        return [
            'name'          => '9f8a23-2783fc5c-9999-9999-9e49-aefbcb5be2b7',
            'firstname'     => 'SampleTest',
            'lastname'      => 'UserTest',
            'email'         => 'test-new-sample@unit.com',
            'mobile_number' => '0699999999',
            'password'      => 'Te5t',
        ];
    }

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->organisation = $this->em->getRepository('CanopyUserBundle:Organisation')->findOneByName('Canopy');
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->em = null;
        $this->organisation = null;
    }
}
