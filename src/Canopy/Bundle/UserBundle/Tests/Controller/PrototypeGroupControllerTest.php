<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PrototypeGroupControllerTest extends WebTestCase
{
    protected $client;

    public function testGetPrototypeGroups()
    {
        $this->client->request(
            'GET',
            '/api/prototype-groups',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 8);
    }

    public function testGetPrototypeGroup()
    {
        $this->client->request(
            'GET',
            '/api/prototype-groups',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(8, $data['data']);
        $this->assertArrayHasKey('name', $data['data'][0]);
        $this->assertSame('DEFAULT_GROUP', $data['data'][0]['name']);
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
