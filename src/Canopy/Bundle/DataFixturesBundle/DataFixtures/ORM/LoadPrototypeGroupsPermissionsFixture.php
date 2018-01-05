<?php

namespace Canopy\Bundle\DataFixturesBundle\DataFixtures\ORM;

use Canopy\Bundle\DataFixturesBundle\Entity\Permission;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPrototypeGroupsPermissionsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // TODO : the prototype group will be added again in the future
        return;

        /* PrototypeGroup: ORGANISATION_DEFAULT_ADMIN */
        $orgDefaultAdminPG = $this->getReference('PrototypeGroup.ORGANISATION_DEFAULT_ADMIN');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_SHOW');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_DELETE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_CREATE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_SHOW');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_UPDATE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_DELETE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_CREATE');
        $orgDefaultAdminPermissions[] = $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW');

        $orgDefaultAdminPG->setPermissions($orgDefaultAdminPermissions);
        $manager->persist($orgDefaultAdminPG);

        /* PrototypeGroup: ORGANISATION_DEFAULT_GROUP */
        $orgDefaultGroupPG = $this->getReference('PrototypeGroup.ORGANISATION_DEFAULT_GROUP');
        $orgDefaultGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');

        $orgDefaultGroupPG->setPermissions($orgDefaultGroupPermissions);
        $manager->persist($orgDefaultGroupPG);

        /* PrototypeGroup: DEFAULT_GROUP */
        $defaultGroupPG = $this->getReference('PrototypeGroup.DEFAULT_GROUP');
        $defaultGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_JOIN');
        $defaultGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_CREATE');

        $defaultGroupPG->setPermissions($defaultGroupPermissions);
        $manager->persist($defaultGroupPG);

        /* PrototypeGroup: RESTRICTED_GROUP */
        $restrictedGroupPG = $this->getReference('PrototypeGroup.RESTRICTED_GROUP');
        $restrictedGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW');
        $restrictedGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW');
        $restrictedGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW');
        $restrictedGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW');
        $restrictedGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE');

        $restrictedGroupPG->setPermissions($restrictedGroupPermissions);
        $manager->persist($restrictedGroupPG);

        /* PrototypeGroup: DEVELOPER_GROUP */
        $developerGroupPG = $this->getReference('PrototypeGroup.DEVELOPER_GROUP');
        $developerGroupPermissions[] = $this->getReference('Permission.STEP_PURCHASE_REQUEST');

        $developerGroupPG->setPermissions($developerGroupPermissions);
        $manager->persist($developerGroupPG);

        /* PrototypeGroup: PROJECT_MANAGER_GROUP */
        $projectManagerGroupPG = $this->getReference('PrototypeGroup.PROJECT_MANAGER_GROUP');
        $projectManagerGroupPermissions[] = $this->getReference('Permission.STEP_PURCHASE_APPROVAL');

        $projectManagerGroupPG->setPermissions($projectManagerGroupPermissions);
        $manager->persist($projectManagerGroupPG);

        /* PrototypeGroup: PROCUREMENT_GROUP */
        $procurementGroupPG = $this->getReference('PrototypeGroup.PROCUREMENT_GROUP');
        $procurementGroupPermissions[] = $this->getReference('Permission.STEP_PURCHASE_PROCUREMENT');

        $procurementGroupPG->setPermissions($procurementGroupPermissions);
        $manager->persist($procurementGroupPG);

        /* PrototypeGroup: OPERATOR_GROUP */
        $operatorGroupPG = $this->getReference('PrototypeGroup.OPERATOR_GROUP');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_PROVISIONING_APPROVAL');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_INFORMATION_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_INFORMATION_UPDATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_CREATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_UPDATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_GROUP_DELETE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_UPDATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_USER_DELETE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_ACTIVITIES_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_JOIN');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_ORG_CREATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_INFORMATION_UPDATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_ACTIVITIES_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_NOTIFICATIONS_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_SHOW');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_UPDATE');
        $operatorGroupPermissions[] = $this->getReference('Permission.FEAT_USER_PERSONAL_FOLLOWED_PRODUCTS_DELETE');

        $operatorGroupPG->setPermissions($operatorGroupPermissions);
        $manager->persist($operatorGroupPG);

        $manager->flush();
    }

    public function getOrder()
    {
        return 500;
    }
}
