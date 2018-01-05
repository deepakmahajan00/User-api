<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\DataFixturesBundle\Entity\PrototypeGroup;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPrototypeGroupsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // TODO : the prototype group will be added again in the future
        return;

        /* ORGANISATION_DEFAULT_ADMIN */
        $organisationDefaultAdminGroup = new PrototypeGroup();
        $organisationDefaultAdminGroup->setName(PrototypeGroup::ORGANISATION_DEFAULT_ADMIN);
        $organisationDefaultAdminGroup->setDescription(PrototypeGroup::ORGANISATION_DEFAULT_ADMIN);
        $organisationDefaultAdminGroup->setPermissions(array(
            $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW'),
            $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_CREATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_SHOW'),
            $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_DELETE'),
            $this->getReference('Permission.FEAT_ORG_USER_CREATE'),
            $this->getReference('Permission.FEAT_ORG_USER_SHOW'),
            $this->getReference('Permission.FEAT_ORG_USER_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_USER_DELETE'),
            $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW'),
        ));
        $manager->persist($organisationDefaultAdminGroup);

        $perm1 = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');
        $perm1->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm1);

        $perm2 = $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE');
        $perm2->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm2);

        $perm3 = $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE');
        $perm3->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm3);

        $perm4 = $this->getReference('Permission.FEAT_ORG_GROUP_SHOW');
        $perm4->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm4);

        $perm5 = $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE');
        $perm5->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm5);

        $perm6 = $this->getReference('Permission.FEAT_ORG_GROUP_DELETE');
        $perm6->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm6);

        $perm7 = $this->getReference('Permission.FEAT_ORG_USER_CREATE');
        $perm7->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm7);

        $perm8 = $this->getReference('Permission.FEAT_ORG_USER_SHOW');
        $perm8->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm8);

        $perm9 = $this->getReference('Permission.FEAT_ORG_USER_UPDATE');
        $perm9->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm9);

        $perm10 = $this->getReference('Permission.FEAT_ORG_USER_DELETE');
        $perm10->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm10);

        $perm11 = $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW');
        $perm11->addPrototypeGroup($organisationDefaultAdminGroup);
        $manager->persist($perm11);

        $this->setReference('PrototypeGroup.'.$organisationDefaultAdminGroup->getName(), $organisationDefaultAdminGroup);

        /* ORGANISATION_DEFAULT_GROUP */
        $organisationDefaultGroup = new PrototypeGroup();
        $organisationDefaultGroup->setName(PrototypeGroup::ORGANISATION_DEFAULT_GROUP);
        $organisationDefaultGroup->setDescription(PrototypeGroup::ORGANISATION_DEFAULT_GROUP);
        $organisationDefaultGroup->setPermissions(array(
            $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW'),
        ));
        $manager->persist($organisationDefaultGroup);

        $perm1 = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');
        $perm1->addPrototypeGroup($organisationDefaultGroup);
        $manager->persist($perm1);

        $this->setReference('PrototypeGroup.'.$organisationDefaultGroup->getName(), $organisationDefaultGroup);

        /* DEFAULT_GROUP */
        $defaultGroup = new PrototypeGroup();
        $defaultGroup->setName(PrototypeGroup::DEFAULT_GROUP);
        $defaultGroup->setDescription(PrototypeGroup::DEFAULT_GROUP);
        $defaultGroup->setPermissions(array(
            $this->getReference('Permission.FEAT_ORG_JOIN'),
            $this->getReference('Permission.FEAT_ORG_CREATE'),
        ));
        $manager->persist($defaultGroup);

        $perm1 = $this->getReference('Permission.FEAT_ORG_JOIN');
        $perm1->addPrototypeGroup($defaultGroup);
        $manager->persist($perm1);

        $perm2 = $this->getReference('Permission.FEAT_ORG_CREATE');
        $perm2->addPrototypeGroup($defaultGroup);
        $manager->persist($perm2);

        $this->setReference('PrototypeGroup.'.$defaultGroup->getName(), $defaultGroup);

        /* RESTRICTED_GROUP */
        $restrictedGroup = new PrototypeGroup();
        $restrictedGroup->setName(PrototypeGroup::RESTRICTED_GROUP);
        $restrictedGroup->setDescription(PrototypeGroup::RESTRICTED_GROUP);
        $restrictedGroup->setPermissions(array(
            $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE'),
        ));
        $manager->persist($restrictedGroup);

        $perm1 = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW');
        $perm1->addPrototypeGroup($restrictedGroup);
        $manager->persist($perm1);

        $perm2 = $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW');
        $perm2->addPrototypeGroup($restrictedGroup);
        $manager->persist($perm2);

        $perm3 = $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW');
        $perm3->addPrototypeGroup($restrictedGroup);
        $manager->persist($perm3);

        $perm4 = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW');
        $perm4->addPrototypeGroup($restrictedGroup);
        $manager->persist($perm4);

        $perm5 = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE');
        $perm5->addPrototypeGroup($restrictedGroup);
        $manager->persist($perm5);

        $this->setReference('PrototypeGroup.'.$restrictedGroup->getName(), $restrictedGroup);

        /* DEVELOPER_GROUP */
        $developerGroup = new PrototypeGroup();
        $developerGroup->setName(PrototypeGroup::DEVELOPER_GROUP);
        $developerGroup->setDescription(PrototypeGroup::DEVELOPER_GROUP);
        $developerGroup->setPermissions(array(
            $this->getReference('Permission.STEP_PURCHASE_REQUEST'),
        ));
        $manager->persist($developerGroup);

        $perm1 = $this->getReference('Permission.STEP_PURCHASE_REQUEST');
        $perm1->addPrototypeGroup($developerGroup);
        $manager->persist($perm1);

        $this->setReference('PrototypeGroup.'.$developerGroup->getName(), $developerGroup);

        /* PROJECT_MANAGER_GROUP */
        $projectManagerGroup = new PrototypeGroup();
        $projectManagerGroup->setName(PrototypeGroup::PROJECT_MANAGER_GROUP);
        $projectManagerGroup->setDescription(PrototypeGroup::PROJECT_MANAGER_GROUP);
        $projectManagerGroup->setPermissions(array(
            $this->getReference('Permission.STEP_PURCHASE_APPROVAL'),
        ));
        $manager->persist($projectManagerGroup);

        $perm1 = $this->getReference('Permission.STEP_PURCHASE_APPROVAL');
        $perm1->addPrototypeGroup($projectManagerGroup);
        $manager->persist($perm1);

        $this->setReference('PrototypeGroup.'.$projectManagerGroup->getName(), $projectManagerGroup);

        /* PROCUREMENT_GROUP */
        $procurementGroup = new PrototypeGroup();
        $procurementGroup->setName(PrototypeGroup::PROCUREMENT_GROUP);
        $procurementGroup->setDescription(PrototypeGroup::PROCUREMENT_GROUP);
        $procurementGroup->setPermissions(array(
            $this->getReference('Permission.STEP_PURCHASE_PROCUREMENT'),
        ));
        $manager->persist($procurementGroup);

        $perm1 = $this->getReference('Permission.STEP_PURCHASE_PROCUREMENT');
        $perm1->addPrototypeGroup($procurementGroup);
        $manager->persist($perm1);

        $this->setReference('PrototypeGroup.'.$procurementGroup->getName(), $procurementGroup);

        /* OPERATOR_GROUP */
        $operatorGroup = new PrototypeGroup();
        $operatorGroup->setName(PrototypeGroup::OPERATOR_GROUP);
        $operatorGroup->setDescription(PrototypeGroup::OPERATOR_GROUP);
        $operatorGroup->setPermissions(array(
            $this->getReference('Permission.FEAT_PROVISIONING_APPROVAL'),
            $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW'),
            $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_CREATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_SHOW'),
            $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_GROUP_DELETE'),
            $this->getReference('Permission.FEAT_ORG_USER_CREATE'),
            $this->getReference('Permission.FEAT_ORG_USER_SHOW'),
            $this->getReference('Permission.FEAT_ORG_USER_UPDATE'),
            $this->getReference('Permission.FEAT_ORG_USER_DELETE'),
            $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW'),
            $this->getReference('Permission.FEAT_ORG_JOIN'),
            $this->getReference('Permission.FEAT_ORG_CREATE'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_UPDATE'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE'),
            $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_DELETE'),
        ));
        $manager->persist($operatorGroup);

        $perm1 = $this->getReference('Permission.FEAT_PROVISIONING_APPROVAL');
        $perm1->addPrototypeGroup($operatorGroup);
        $manager->persist($perm1);

        $perm2 = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');
        $perm2->addPrototypeGroup($operatorGroup);
        $manager->persist($perm2);

        $perm3 = $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE');
        $perm3->addPrototypeGroup($operatorGroup);
        $manager->persist($perm3);

        $perm4 = $this->getReference('Permission.FEAT_ORG_GROUP_CREATE');
        $perm4->addPrototypeGroup($operatorGroup);
        $manager->persist($perm4);

        $perm5 = $this->getReference('Permission.FEAT_ORG_GROUP_SHOW');
        $perm5->addPrototypeGroup($operatorGroup);
        $manager->persist($perm5);

        $perm6 = $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE');
        $perm6->addPrototypeGroup($operatorGroup);
        $manager->persist($perm6);

        $perm7 = $this->getReference('Permission.FEAT_ORG_GROUP_DELETE');
        $perm7->addPrototypeGroup($operatorGroup);
        $manager->persist($perm7);

        $perm8 = $this->getReference('Permission.FEAT_ORG_USER_CREATE');
        $perm8->addPrototypeGroup($operatorGroup);
        $manager->persist($perm8);

        $perm9 = $this->getReference('Permission.FEAT_ORG_USER_SHOW');
        $perm9->addPrototypeGroup($operatorGroup);
        $manager->persist($perm9);

        $perm10 = $this->getReference('Permission.FEAT_ORG_USER_UPDATE');
        $perm10->addPrototypeGroup($operatorGroup);
        $manager->persist($perm10);

        $perm11 = $this->getReference('Permission.FEAT_ORG_USER_UPDATE');
        $perm11->addPrototypeGroup($operatorGroup);
        $manager->persist($perm11);

        $perm12 = $this->getReference('Permission.FEAT_ORG_USER_DELETE');
        $perm12->addPrototypeGroup($operatorGroup);
        $manager->persist($perm12);

        $perm13 = $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW');
        $perm13->addPrototypeGroup($operatorGroup);
        $manager->persist($perm13);

        $perm14 = $this->getReference('Permission.FEAT_ORG_JOIN');
        $perm14->addPrototypeGroup($operatorGroup);
        $manager->persist($perm14);

        $perm15 = $this->getReference('Permission.FEAT_ORG_CREATE');
        $perm15->addPrototypeGroup($operatorGroup);
        $manager->persist($perm15);

        $perm16 = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW');
        $perm16->addPrototypeGroup($operatorGroup);
        $manager->persist($perm16);

        $perm17 = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_UPDATE');
        $perm17->addPrototypeGroup($operatorGroup);
        $manager->persist($perm17);

        $perm18 = $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW');
        $perm18->addPrototypeGroup($operatorGroup);
        $manager->persist($perm18);

        $perm19 = $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW');
        $perm19->addPrototypeGroup($operatorGroup);
        $manager->persist($perm19);

        $perm20 = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW');
        $perm20->addPrototypeGroup($operatorGroup);
        $manager->persist($perm20);

        $perm21 = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE');
        $perm21->addPrototypeGroup($operatorGroup);
        $manager->persist($perm21);

        $perm22 = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_DELETE');
        $perm22->addPrototypeGroup($operatorGroup);
        $manager->persist($perm22);

        $this->setReference('PrototypeGroup.'.$operatorGroup->getName(), $operatorGroup);

        $manager->flush();
    }

    public function getOrder()
    {
        return 500;
    }
}
