<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerTest extends WebTestCase
{
    protected $client;

    public function testGetUserGroups()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleUserUuid.'/groups',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 8);
    }

    public function testGetUserGroupsNotAuthorized()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleUser2Uuid.'/groups',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetGroup()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleUserUuid.'/groups/11',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(11, $data['id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('McGrawHill_Default_Group', $data['name']);
    }

    public function testGetGroupNotAuthorized()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleUserUuid.'/groups/'. 1,
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    protected function setUp()
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }
}
