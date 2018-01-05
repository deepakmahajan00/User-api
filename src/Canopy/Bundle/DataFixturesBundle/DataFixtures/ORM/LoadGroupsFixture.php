<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\UserBundle\Entity\Group;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadGroupsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // Load groups data from YML
        $conf = Yaml::parse(file_get_contents(realpath(dirname(__FILE__).'/../YML/groups.yml')));

        foreach ($conf as $groupData) {
            $group = new Group();
            $group->setName($groupData['name']);
            $group->setDescription($groupData['description']);
            foreach ($groupData['permissions'] as $permissionReference) {
                $group->addPermission($this->getReference('Permission.'.$permissionReference));
            }
            $manager->persist($group);
            $this->setReference('Group.'.$group->getName(), $group);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 40;
    }
}
