<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\Permission;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadPermissionsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // Load permissions data from YML
        $conf = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/permissions.yml')));

        foreach ($conf as $permissionData) {
            $permission = new Permission();
            $permission->setName($permissionData['name']);
            $permission->setDescription($permissionData['description']);
            $manager->persist($permission);
            $this->setReference('Permission.'.$permission->getName(), $permission);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 30;
    }
}
