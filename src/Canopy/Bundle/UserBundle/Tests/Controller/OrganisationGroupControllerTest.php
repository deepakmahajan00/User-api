<?php

namespace Canopy\Bundle\UserBundle\Tests\Controller;

use Canopy\Bundle\UserBundle\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganisationGroupControllerTest extends WebTestCase
{
    protected $client;
    protected $em;
    protected $organisation;

    public function testGetOrganisationGroups()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/groups',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertPaginationRepresentation($data, 8);
    }

    public function testGetOrganisationGroupsNotAuthorized()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/groups',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetOrganisationGroup()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/groups/1',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(1, $data['id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('Canopy_Organisation_Default_Admin_Group', $data['name']);
    }

    public function testGetOrganisationGroupNotExist()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/groups/999999',
            ['_with_user' => $this->sampleManagerUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetOrganisationGroupNotAuthorized()
    {
        $this->client->request(
            'GET',
            '/api/organisations/'.$this->organisation->getId().'/groups/1',
            ['_with_user' => $this->sampleUserUuid]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testCreateOrganisationGroup()
    {
        $group = $this->getOrganisationGroupData();

        $this->client->request(
            'POST',
            '/api/organisations/'.$this->organisation->getId().'/groups',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($group)
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->reloadFixtures();
    }

    public function testCreateOrganisationGroupWithBadData()
    {
        $group = $this->getOrganisationGroupData();
        $group['name'] = null;

        $this->client->request(
            'POST',
            '/api/organisations/'.$this->organisation->getId().'/groups',
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($group)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame('name', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    public function testUpdateOrganisationGroup()
    {
        $groupData = $this->getOrganisationGroupData();
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $organisationGroupId = $em->getRepository('CanopyUserBundle:Group')->findOneByName('Canopy_Organisation_Default_Group')->getId();

        $this->client->request(
            'PUT',
            '/api/organisations/'.$this->organisation->getId().'/groups/'.$organisationGroupId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($groupData)
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $em->clear();
        $group = $em->getRepository('CanopyUserBundle:Group')->findOneById($organisationGroupId);
        $this->assertSame($groupData['name'], $group->getName());
        $this->assertSame($groupData['description'], $group->getDescription());

        $this->reloadFixtures();
    }

    public function testUpdateOrganisationGroupWithBadData()
    {
        $groupData = $this->getOrganisationGroupData();
        $groupData['name'] = null;
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $organisationGroupId = $em->getRepository('CanopyUserBundle:Group')->findOneByName('Canopy_Organisation_Default_Admin_Group')->getId();

        $this->client->request(
            'PUT',
            '/api/organisations/'.$this->organisation->getId().'/groups/'.$organisationGroupId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($groupData)
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame('name', $data['errors'][0]['property_path']);

        $this->reloadFixtures();
    }

    public function testDeleteOrganisationGroup()
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();

        $organisationGroup = $em->getRepository('CanopyUserBundle:Group')->findOneByName('Canopy_Organisation_Default_Admin_Group');
        $organisationGroupId = $organisationGroup->getId();

        // Delete users in the group so we can delete it
        $users = $organisationGroup->getUsers();
        foreach ($users as $user) {
            $em->remove($user);
        }
        $em->flush();

        $this->client->request(
            'DELETE',
            '/api/organisations/'.$this->organisation->getId().'/groups/'.$organisationGroupId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $em->clear();
        $group = $em->getRepository('CanopyUserBundle:Group')->findOneById($organisationGroupId);
        $this->assertTrue(empty($group));

        $this->reloadFixtures();
    }

    public function testDeleteOrganisationGroupWithWrongOrganisation()
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $organisationGroupId = $em->getRepository('CanopyUserBundle:Group')->findOneByName('McGrawHill_Organisation_Default_Admin_Group')->getId();

        $this->client->request(
            'DELETE',
            '/api/organisations/'.$this->organisation->getId().'/groups/'.$organisationGroupId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testDeleteOrganisationGroupWithUsersOrganisation()
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $organisationGroupId = $em->getRepository('CanopyUserBundle:Group')->findOneByName('Canopy_Organisation_Default_Admin_Group')->getId();

        $this->client->request(
            'DELETE',
            '/api/organisations/'.$this->organisation->getId().'/groups/'.$organisationGroupId,
            ['_with_user' => $this->sampleUserUuid],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    protected function getOrganisationGroupData()
    {
        return [
            'name'          => 'Canopy_Organisation_TEST_Group',
            'description'   => 'Canopy Organisation TEST Group.',
            'permissions'   => [],
        ];
    }

    protected function setUp()
    {
        $this->markTestSkipped();
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
