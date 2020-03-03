<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200219121923 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, booking_id INT DEFAULT NULL, payment_channel VARCHAR(255) DEFAULT NULL, amount VARCHAR(255) DEFAULT NULL, system_transaction_id VARCHAR(255) DEFAULT NULL, provider_transaction_id VARCHAR(255) DEFAULT NULL, payment_status VARCHAR(255) DEFAULT NULL, gateway_confirmation_response JSON DEFAULT NULL, confirmation_meta_data JSON DEFAULT NULL, collector_transaction_reference VARCHAR(255) DEFAULT NULL, collector_customer_reference VARCHAR(255) DEFAULT NULL, collector_payment_status VARCHAR(255) DEFAULT NULL, collector_response_code VARCHAR(50) DEFAULT NULL, collector_response_code_description VARCHAR(255) DEFAULT NULL, collector_metadata JSON DEFAULT NULL, status_info VARCHAR(255) DEFAULT NULL, status_description VARCHAR(255) DEFAULT NULL, instructions_count INT DEFAULT NULL, receipt VARCHAR(255) DEFAULT NULL, line VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, agent_id INT DEFAULT NULL, trade_id VARCHAR(255) DEFAULT NULL, route_id INT DEFAULT NULL, schedule_id INT DEFAULT NULL, bus_id INT DEFAULT NULL, promotion_id INT DEFAULT NULL, referral_code VARCHAR(255) DEFAULT NULL, schedule_code VARCHAR(255) DEFAULT NULL, operator VARCHAR(255) DEFAULT NULL, booking_channel VARCHAR(255) DEFAULT NULL, payment_channel VARCHAR(255) DEFAULT NULL, passengers JSON DEFAULT NULL, total_passengers INT DEFAULT NULL, total_children INT DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, route VARCHAR(255) DEFAULT NULL, paybill VARCHAR(255) DEFAULT NULL, seats JSON DEFAULT NULL, departure_time DATETIME NOT NULL, arrival_time DATETIME NOT NULL, total_amount DOUBLE PRECISION NOT NULL, refunded_amount DOUBLE PRECISION DEFAULT NULL, booking_status VARCHAR(255) DEFAULT NULL, confirmation_response JSON DEFAULT NULL, operator_confirmation_retries INT DEFAULT NULL, operator_query_status_retries INT DEFAULT NULL, gateway_confirmation_response JSON DEFAULT NULL, sms LONGTEXT DEFAULT NULL, sms_confirmation_response JSON DEFAULT NULL, qr_receipt LONGTEXT DEFAULT NULL, qr_response JSON DEFAULT NULL, email_receipt LONGTEXT DEFAULT NULL, email_response JSON DEFAULT NULL, sms_receipts_sent INT DEFAULT NULL, client_confirmation_sent INT DEFAULT NULL, client_confirmations_count INT DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, destination VARCHAR(255) DEFAULT NULL, line VARCHAR(255) DEFAULT NULL, custom_booking_no VARCHAR(255) DEFAULT NULL, user JSON DEFAULT NULL, booking_date DATETIME NOT NULL, date_of_travel DATETIME DEFAULT NULL, referral_source VARCHAR(255) DEFAULT NULL, referral_checked INT DEFAULT NULL, booking_organisation_id VARCHAR(255) DEFAULT NULL, remote_reference VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE booking');
    }
}
