<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150518104941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE canopy_address (id BIGINT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state LONGTEXT NOT NULL, city VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, street1 VARCHAR(255) DEFAULT NULL, street2 VARCHAR(255) DEFAULT NULL, INDEX IDX_4640AC85F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_country (id INT AUTO_INCREMENT NOT NULL, iso_code VARCHAR(2) NOT NULL, en VARCHAR(255) NOT NULL, fr VARCHAR(255) NOT NULL, dialing_code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_currency (id INT AUTO_INCREMENT NOT NULL, iso_code VARCHAR(3) NOT NULL, en VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_domain_name (id BIGINT AUTO_INCREMENT NOT NULL, organisation_id BIGINT DEFAULT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_90F959B99E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_group (id BIGINT AUTO_INCREMENT NOT NULL, organisation_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_7A2D464B9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_group_permission (group_id BIGINT NOT NULL, permission_id BIGINT NOT NULL, INDEX IDX_ABE16332FE54D947 (group_id), INDEX IDX_ABE16332FED90CCA (permission_id), PRIMARY KEY(group_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_organisation (id BIGINT AUTO_INCREMENT NOT NULL, address_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, logo VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, customer_id VARCHAR(255) DEFAULT NULL, policy_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', policy_choice INT DEFAULT 0 NOT NULL, policy_latest TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_6E8A6E58F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_permission (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_prototype_group (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_prototype_group_permission (prototypegroup_id BIGINT NOT NULL, permission_id BIGINT NOT NULL, INDEX IDX_2D00A669E0838D5F (prototypegroup_id), INDEX IDX_2D00A669FED90CCA (permission_id), PRIMARY KEY(prototypegroup_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE canopy_user (id BIGINT AUTO_INCREMENT NOT NULL, address_id BIGINT DEFAULT NULL, currency_id INT DEFAULT NULL, organisation_id BIGINT DEFAULT NULL, uuid VARCHAR(255) NOT NULL, unboundid_user_id VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, dialing_code VARCHAR(255) DEFAULT NULL, mobile_number VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', reset_password_token CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', reset_password_token_generated_at DATETIME DEFAULT NULL, organisation_owner TINYINT(1) NOT NULL, from_company VARCHAR(255) DEFAULT NULL, verified TINYINT(1) DEFAULT \'0\' NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, department VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, company_size VARCHAR(255) DEFAULT NULL, industry VARCHAR(255) DEFAULT NULL, mode_of_info VARCHAR(255) DEFAULT NULL, customer_id VARCHAR(255) DEFAULT NULL, verification_code VARCHAR(255) NOT NULL, policy_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', policy_choice INT DEFAULT 0 NOT NULL, policy_latest TINYINT(1) DEFAULT \'0\' NOT NULL, credentials_expired TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_D72F1BD7E7927C74 (email), UNIQUE INDEX UNIQ_D72F1BD7F5B7AF75 (address_id), INDEX IDX_D72F1BD738248176 (currency_id), INDEX IDX_D72F1BD79E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE canopy_address ADD CONSTRAINT FK_4640AC85F92F3E70 FOREIGN KEY (country_id) REFERENCES canopy_country (id)');
        $this->addSql('ALTER TABLE canopy_domain_name ADD CONSTRAINT FK_90F959B99E6B1585 FOREIGN KEY (organisation_id) REFERENCES canopy_organisation (id)');
        $this->addSql('ALTER TABLE canopy_group ADD CONSTRAINT FK_7A2D464B9E6B1585 FOREIGN KEY (organisation_id) REFERENCES canopy_organisation (id)');
        $this->addSql('ALTER TABLE canopy_group_permission ADD CONSTRAINT FK_ABE16332FE54D947 FOREIGN KEY (group_id) REFERENCES canopy_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE canopy_group_permission ADD CONSTRAINT FK_ABE16332FED90CCA FOREIGN KEY (permission_id) REFERENCES canopy_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE canopy_organisation ADD CONSTRAINT FK_6E8A6E58F5B7AF75 FOREIGN KEY (address_id) REFERENCES canopy_address (id)');
        $this->addSql('ALTER TABLE canopy_prototype_group_permission ADD CONSTRAINT FK_2D00A669E0838D5F FOREIGN KEY (prototypegroup_id) REFERENCES canopy_prototype_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE canopy_prototype_group_permission ADD CONSTRAINT FK_2D00A669FED90CCA FOREIGN KEY (permission_id) REFERENCES canopy_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE canopy_user ADD CONSTRAINT FK_D72F1BD7F5B7AF75 FOREIGN KEY (address_id) REFERENCES canopy_address (id)');
        $this->addSql('ALTER TABLE canopy_user ADD CONSTRAINT FK_D72F1BD738248176 FOREIGN KEY (currency_id) REFERENCES canopy_currency (id)');
        $this->addSql('ALTER TABLE canopy_user ADD CONSTRAINT FK_D72F1BD79E6B1585 FOREIGN KEY (organisation_id) REFERENCES canopy_organisation (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE canopy_organisation DROP FOREIGN KEY FK_6E8A6E58F5B7AF75');
        $this->addSql('ALTER TABLE canopy_user DROP FOREIGN KEY FK_D72F1BD7F5B7AF75');
        $this->addSql('ALTER TABLE canopy_address DROP FOREIGN KEY FK_4640AC85F92F3E70');
        $this->addSql('ALTER TABLE canopy_user DROP FOREIGN KEY FK_D72F1BD738248176');
        $this->addSql('ALTER TABLE canopy_group_permission DROP FOREIGN KEY FK_ABE16332FE54D947');
        $this->addSql('ALTER TABLE canopy_domain_name DROP FOREIGN KEY FK_90F959B99E6B1585');
        $this->addSql('ALTER TABLE canopy_group DROP FOREIGN KEY FK_7A2D464B9E6B1585');
        $this->addSql('ALTER TABLE canopy_user DROP FOREIGN KEY FK_D72F1BD79E6B1585');
        $this->addSql('ALTER TABLE canopy_group_permission DROP FOREIGN KEY FK_ABE16332FED90CCA');
        $this->addSql('ALTER TABLE canopy_prototype_group_permission DROP FOREIGN KEY FK_2D00A669FED90CCA');
        $this->addSql('ALTER TABLE canopy_prototype_group_permission DROP FOREIGN KEY FK_2D00A669E0838D5F');
        $this->addSql('DROP TABLE canopy_address');
        $this->addSql('DROP TABLE canopy_country');
        $this->addSql('DROP TABLE canopy_currency');
        $this->addSql('DROP TABLE canopy_domain_name');
        $this->addSql('DROP TABLE canopy_group');
        $this->addSql('DROP TABLE canopy_group_permission');
        $this->addSql('DROP TABLE canopy_organisation');
        $this->addSql('DROP TABLE canopy_permission');
        $this->addSql('DROP TABLE canopy_prototype_group');
        $this->addSql('DROP TABLE canopy_prototype_group_permission');
        $this->addSql('DROP TABLE canopy_user');
    }
}
