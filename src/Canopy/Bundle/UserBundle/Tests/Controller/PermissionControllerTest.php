<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PermissionControllerTest extends WebTestCase
{
    protected $client;

    public function testGetPermissions()
    {
        $this->client->request(
            'GET',
            '/api/permissions',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 15);
    }

    public function testGetUserPermissions()
    {
        $this->markTestSkipped();

        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleManagerUuid.'/permissions',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 25);
    }

    public function testGetPermission()
    {
        $this->client->request(
            'GET',
            '/api/users/'.$this->sampleManagerUuid.'/permissions/1',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('PERM_LOGIN', $data['name']);
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
