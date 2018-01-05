<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganisationControllerTest extends WebTestCase
{
    protected $client;
    protected $em;

    public function testGetOrganisations()
    {
        $this->client->request(
            'GET',
            '/api/organisations',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 3);
    }

    public function testGetOrganisation()
    {
        $organisation = $this->em->getRepository('CanopyUserBundle:Organisation')->findOneByName('Canopy');

        $this->client->request(
            'GET',
            '/api/organisations/'.$organisation->getId(),
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($organisation->getId(), $data['id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame($organisation->getName(), $data['name']);
    }

    public function testGetOrganisationNotExist()
    {
        $this->client->request(
            'GET',
            '/api/organisations/9999',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateOrganisation()
    {
        $this->client->request(
            'POST',
            '/api/organisations',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($this->getOrganisationData())
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(4, $data['id']);

        $this->reloadFixtures();
    }

    public function testCreateOrganisationWithBadData()
    {
        $data = $this->getOrganisationData();
        $data['name'] = null;

        $this->client->request(
            'POST',
            '/api/organisations',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame('name', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    public function testUpdateOrganisation()
    {
        $organisationId = $this->em->getRepository('CanopyUserBundle:Organisation')->findOneByName('Canopy')->getId();
        $organisationData = $this->getOrganisationData();

        $this->client->request(
            'PUT',
            '/api/organisations/'.$organisationId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($organisationData)
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->em->clear();
        $organisation = $this->em->getRepository('CanopyUserBundle:Organisation')->findOneById($organisationId);
        $this->assertSame($organisationData['name'], $organisation->getName());
        $this->assertSame($organisationData['description'], $organisation->getDescription());
        $this->assertSame($organisationData['vat_number'], $organisation->getVatNumber());

        $this->reloadFixtures();
    }

    public function testUpdateOrganisationWithBadData()
    {
        $organisationData = $this->getOrganisationData();
        $organisationData['name'] = null;

        $organisationId = $this->em->getRepository('CanopyUserBundle:Organisation')->findOneByName('Canopy')->getId();

        $this->client->request(
            'PUT',
            '/api/organisations/'.$organisationId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($organisationData)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame('name', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    protected function getOrganisationData()
    {
        return [
            'name'          => 'Canopy TEST UNIT',
            'description'   => 'Canopy TEST UNIT, an Atos company partnered with two of the biggest leaders in IT—EMC and VMware, is a one-stop-shop solution for Cloud-computing. Built upon a tight ecosystem of best-of-breed technology, we are focused on bringing the benefits of Next Generation IT and cloud delivery to large organizations. Security sits at the height of the Canopy solution philosophy and is based on pre-built environments on the strongest technology foundation. Using industry leading automation tools, Canopy allows enterprise companies to achieve their business goals and service levels in a multi-tenant or a dedicated single-tenant environment.',
            'vat_number'    => 'Canopy-Vat-Number-TEST',
            'address'       => [
                'state'    => 'MH',
                'street1'    => '4 Triton Square',
                'street2'    => 'Regents Place',
                'city'      => 'London',
                'zipcode'   => 'NW1 3HQ',
                'line1'     => 'line1',
            ],
            'restricted_domain_names' => [],
        ];
    }

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->em = null;
    }
}
