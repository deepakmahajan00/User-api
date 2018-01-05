<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CountryControllerTest extends WebTestCase
{
    protected $client;

    public function testGetAllCountries()
    {
        $this->client->request(
            'GET',
            '/api/countries',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('countries', $data);
        $this->assertCount(241, $data['countries']);
    }

    public function testGetCountriesFiltered()
    {
        $this->client->request(
            'GET',
            '/api/countries',
            [
                'lang' => 'en',
                'q' => 'an',
                '_with_user' => $this->sampleUserUuid,
            ]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('countries', $data);
        $this->assertCount(5, $data['countries']);
    }

    public function testGetCountriesWithBadData()
    {
        $this->client->request(
            'GET',
            '/api/countries',
            [
                'lang' => 'aa',
                'q' => 'francefrance',
                '_with_user' => $this->sampleUserUuid,
            ]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('countries', $data);
        $this->assertCount(0, $data['countries']);
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
