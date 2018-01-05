<?php

namespace Canopy\Bundle\UserBundle\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected $sampleUserUuid           = '9f8a23-2783fc5c-bfd7-4870-9e49-aefbcb5be2b7';
    protected $sampleUser2Uuid          = '9f8a23-ebacab14-ef23-43ff-b0db-36a7001177f3';
    protected $sampleAdministratorUuid  = 'd9b48c-b84a86d1-918a-48a4-8923-36486a2cc374';
    protected $sampleProcurementUuid    = '9f8a23-53abd07a-a78a-440c-9566-9a4e3165fb2c';
    protected $sampleManagerUuid        = '9f8a23-6a406c3d-a195-4e77-9641-361eef10e098';
    protected $sampleCreatedUuid        = '9f8a23-6a406c3d-a195-5e99-9641-361eef10e098';

    /**
     * Force phpunit to use the AppTest Kernel.
     *
     * @return string
     */
    protected static function getKernelClass()
    {
        if (isset($_SERVER['KERNEL_DIR'])) {
            $dir = $_SERVER['KERNEL_DIR'];

            if (!is_dir($dir)) {
                $phpUnitDir = static::getPhpUnitXmlDir();
                if (is_dir("$phpUnitDir/$dir")) {
                    $dir = "$phpUnitDir/$dir";
                }
            }
        } else {
            $dir = static::getPhpUnitXmlDir();
        }
        $kernelName = 'TestKernel';
        require_once sprintf('%s/%s.php', $dir, $kernelName);

        return $kernelName;
    }

    /**
     * Override an existing service in the container by the $mockedServiceObject.
     *
     * @param array $services Array of mocked services ['serviceId' => $mockedServiceObject]
     */
    protected function setMocksInContainer($services)
    {
        static::$kernel->setKernelModifier(
            function ($kernel) use ($services) {
                $container = $kernel->getContainer();
                foreach ($services as $serviceId => $serviceMock) {
                    $container->set($serviceId, $serviceMock);
                }
            }
        );
    }

    /**
     * Keep the mocked services between two request.
     *
     * @param bool $resetKernelModifier
     */
    protected function setResetKernelModifier($resetKernelModifier)
    {
        static::$kernel->setResetKernelModifier($resetKernelModifier);
    }

    /**
     * Empty DB & load all fixtures.
     */
    protected function reloadFixtures()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($em);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);
        }
        $this->postFixtureSetup();

        $classes = array(
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadCurrenciesFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadCountriesFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadAddressesFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadDomainNamesFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadPermissionsFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadPrototypeGroupsFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadGroupsFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadPrototypeGroupsPermissionsFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadOrganisationsFixture',
            'Canopy\Bundle\UserBundle\DataFixtures\ORM\LoadUsersFixture',
        );
        $this->loadFixtures($classes);
    }

    /**
     * Check the paginated representation.
     *
     * @param $data
     * @param $total
     */
    protected function assertPaginationRepresentation($data, $total)
    {
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertEquals($total, $data['total']);
    }

    /**
     * UnboundiId oauth endpoint mock.
     */
    protected function getUnboundIdOAuthEndpointMock()
    {
        $oauthEndpoint = $this->getMockBuilder('Canopy\Bundle\UnboundIdApiClientBundle\Endpoint\OAuthEndpoint')
            ->disableOriginalConstructor()
            ->getMock();

        $oauthEndpoint->method('getAccessTokenByUsername')
            ->willReturn(
                [
                    'access_token'  => 'MF2AAQGBBmRZVnNqd4JQkrk0659LO1Todqhn3_6akWrh-CZYd_-qh_0MVa-4HicPvaHsz9J4zFLE_7Zu1L8AMDcMNIj9KDewQXMBhnS-J3gqZwiiUXulKCmlRlOeWow',
                    'token_type'    => 'bearer',
                    'expires_in'    => 31472,
                    'scope'         => 'ConsumerData openid phone address profile user_schema',
                ]
            );

        $oauthEndpoint->method('getAnonymousAccessToken')
            ->willReturn(
                [
                    'access_token'  => 'MC2AAQGBBmRZVnNqd4IgBnVkaz2d-ZKXm6DQs06vK-hGdldHjGR2qYBOnLFGeOY',
                    'token_type'    => 'bearer',
                    'expires_in'    => 37554,
                    'scope'         => 'ConsumerData openid phone address profile user_schema',
                ]
            );

        $oauthEndpoint->method('validate')
            ->willReturn(
                [
                    'scope' => [
                        'ConsumerData',
                        'openid',
                        'phone',
                        'address',
                        'profile',
                        'user_schema',
                    ],
                    'nonce'                 => null,
                    'user_id'               => $this->sampleUserUuid,
                    'client_id'             => 'f0660032-4edc-4492-bb71-87cc454ecd05',
                    'issued_at'             => '20141127092101Z',
                    'expires_in'            => 24650,
                    'auth_time'             => null,
                    'id_token_issued_at'    => null,
                ]
            );

        $oauthEndpoint->method('isAccountLocked')
            ->willReturn(true);

        return $oauthEndpoint;
    }

    /**
     * UnboundiId dataview endpoint mock.
     */
    protected function getUnboundIdDataviewEndpointMock()
    {
        $dataviewEndpoint = $this->getMockBuilder('Canopy\Bundle\UnboundIdApiClientBundle\Endpoint\DataviewEndpoint')
            ->disableOriginalConstructor()
            ->getMock();

        $dataviewEndpoint->method('createUser')
            ->willReturn(
                [
                    'schemas' => [
                            'urn:unboundid:oidc:1.0',
                            'urn:scim:schemas:core:1.0',
                        ],
                    'name' => [
                        'givenName' => 'sampleuser1',
                        'familyName' => 'sampleuser1',
                        'formatted' => 'sampleuser1 sampleuser1',
                    ],
                    'id' => $this->sampleCreatedUuid,
                    'userName' => 'sampletestuser1@email.com',
                    'meta' => [
                        'created' => '2014-11-27T14:58:26.418Z',
                        'lastModified' => '2014-11-27T14:58:26.418Z',
                        'location' => 'https://canopy.arnaudlacour.com:9443/dataview/v1/Users/'.$this->sampleCreatedUuid,
                    ],
                    'entitlements' => [
                        ['value' => 'READ_OWN_CONSENT'],
                        ['value' => 'CREATE_OWN_CONSENT'],
                        ['value' => 'READ_OWN_ACCESSHISTORY'],
                        ['value' => 'READ_OWN_CONSENTHISTORY'],
                        ['value' => 'DELETE_OWN_CONSENT'],
                    ],
                ]
            );

        return $dataviewEndpoint;
    }
}
