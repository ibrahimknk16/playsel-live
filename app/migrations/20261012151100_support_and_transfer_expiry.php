<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Support_And_Transfer_Expiry extends CI_Migration
{
	public function up()
	{
		if (!$this->db->field_exists('tickets_order_id', 'tickets')) {
			$this->db->query('ALTER TABLE `tickets` ADD `tickets_order_id` INT NULL AFTER `tickets_user_id`, ADD INDEX `idx_tickets_order_id` (`tickets_order_id`)');
		}

		if (!$this->db->table_exists('order_cancellation_reasons')) {
			$this->db->query('
				CREATE TABLE `order_cancellation_reasons` (
					`reason_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					`reason_text` VARCHAR(255) NOT NULL,
					`reason_sort` INT NOT NULL DEFAULT 0,
					`reason_status` TINYINT NOT NULL DEFAULT 1,
					`reason_created_at` DATETIME NOT NULL,
					PRIMARY KEY (`reason_id`),
					INDEX `idx_reason_status_sort` (`reason_status`, `reason_sort`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
			');
		}
	}

	public function down()
	{
		if ($this->db->table_exists('order_cancellation_reasons')) {
			$this->db->query('DROP TABLE `order_cancellation_reasons`');
		}
		if ($this->db->field_exists('tickets_order_id', 'tickets')) {
			$this->db->query('ALTER TABLE `tickets` DROP INDEX `idx_tickets_order_id`, DROP COLUMN `tickets_order_id`');
		}
	}
}
