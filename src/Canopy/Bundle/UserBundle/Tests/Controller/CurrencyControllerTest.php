<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CurrencyControllerTest extends WebTestCase
{
    protected $client;

    public function testGetAllCurrencies()
    {
        $this->client->request(
            'GET',
            '/api/currencies',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('currencies', $data);
        $this->assertGreaterThan(0, count($data['currencies']));
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
