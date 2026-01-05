<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CronController extends CI_Controller
{
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel');
	}

	public function fbcusers_dbcreation()
	{
		$userData = $this->UserModel->getActiveUsersWithoutDB();
		//echo '<pre>'.print_r($userData, '\n').'</pre>';
		if(is_array($userData) && count($userData) > 0){
			foreach($userData as $value){
				$servername = DB_HOST;
				$username = DB_USER;
				$password = DB_PASS;

				// Create connection
				$connection = new mysqli($servername, $username, $password);
				// Check connection
				if ($connection->connect_error) {
				  die("Connection failed: " . $connection->connect_error);
				}

				echo $db_name = DB_NAME_PREFIX.$value->shop_id;

				// Create database
				$sql = "CREATE DATABASE IF NOT EXISTS ".$db_name." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
				if ($connection->query($sql) === TRUE) {
					echo "Database <b>$db_name</b> created successfully";

					$updateData = $this->UserModel->updateDBName($value->shop_id,"shopinshop_shop".$value->shop_id);

					/*$db_user = USER_NAME;
					$db_pass = PASSWORD;
					//$db_name = 'parkmosp_shopinshop_shop7';
					$db_host = SERVER_NAME;

					// Create connection
					$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
					// Check connection
					if ($conn->connect_error) {
					  die("Connection failed: " . $conn->connect_error);
					}

					// Create table
					$b2b_customers_tbl = "CREATE TABLE `b2b_customers` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `shop_id` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL DEFAULT '0',
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_customers_tbl) === TRUE) {
						echo "Table <b>b2b_customers</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_tbl = "CREATE TABLE `b2b_orders` (
										  `order_id` int(11) NOT NULL AUTO_INCREMENT,
										  `increment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `order_barcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `applied_order_id` int(11) NOT NULL DEFAULT '0',
										  `shipment_type` tinyint(4) NOT NULL COMMENT '1-Buy In(directshop), 2-Dropship(othershopcustomer)',
										  `webshop_order_id` int(11) DEFAULT NULL,
										  `status` tinyint(4) NOT NULL COMMENT '0-to be processed, 1-processing, 2-complete, 3-cancelled, 4- Tracking Missing, 5- Tracking  Incomplete, 6- Tracking Complete, 7 - Pending',
										  `main_parent_id` int(11) NOT NULL DEFAULT '0',
										  `parent_id` int(11) NOT NULL DEFAULT '0',
										  `shop_id` int(11) DEFAULT NULL,
										  `customer_firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
										  `customer_lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
										  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
										  `base_discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_grand_total` decimal(12,2) NOT NULL,
										  `base_shiping_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_shipping_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_subtotal` decimal(12,2) NOT NULL,
										  `base_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `discount_percent` decimal(12,2) DEFAULT '0.00',
										  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `grand_total` decimal(12,2) NOT NULL,
										  `shipping_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `shipping_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `subtotal` decimal(12,2) NOT NULL,
										  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `total_qty_ordered` int(11) NOT NULL,
										  `customer_is_guest` tinyint(4) DEFAULT NULL COMMENT '0-no 1-yes',
										  `email_sent` tinyint(1) DEFAULT '0' COMMENT '0-n0,1-yes',
										  `is_split` tinyint(4) NOT NULL DEFAULT '0',
										  `system_generated_split_order` tinyint(4) DEFAULT NULL COMMENT '0-Can Not split, 1 -Can split',
										  `created_at` int(11) NOT NULL,
										  `updated_at` int(11) DEFAULT NULL,
										  `created_by` int(11) NOT NULL,
										  `updated_by` int(11) DEFAULT NULL,
										  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
										  PRIMARY KEY (`order_id`)
										) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_tbl) === TRUE) {
						echo "Table <b>b2b_orders</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_applied_tbl = "CREATE TABLE `b2b_orders_applied` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `supplier_shop_id` int(11) DEFAULT NULL,
												  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
												  `total_categories_count` int(11) DEFAULT NULL,
												  `total_products_count` int(11) DEFAULT NULL,
												  `total_buyin_products` int(11) DEFAULT NULL,
												  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
												  `total_virtual_products_withqty` int(11) DEFAULT NULL,
												  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
												  `total_virtual_products` int(11) DEFAULT NULL,
												  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
												  `total_dropship_products` int(11) DEFAULT NULL,
												  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
												  `dropship_discount` decimal(12,2) NOT NULL COMMENT 'in %',
												  `buyin_discount` decimal(12,2) NOT NULL COMMENT 'in %',
												  `total_buyin_net_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
												  `buyin_discount_amount` decimal(12,2) DEFAULT '0.00',
												  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0- pending, 1- accepted, 2-declined',
												  `created_by` int(11) DEFAULT NULL,
												  `created_at` int(11) DEFAULT NULL,
												  `updated_at` int(11) DEFAULT NULL,
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_applied_tbl) === TRUE) {
						echo "Table <b>b2b_orders_applied</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_applied_details_tbl = "CREATE TABLE `b2b_orders_applied_details` (
														  `id` int(11) NOT NULL AUTO_INCREMENT,
														  `applied_order_id` int(11) DEFAULT NULL,
														  `product_id` int(11) DEFAULT NULL,
														  `parent_id` int(11) DEFAULT NULL,
														  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
														  `quantity` int(11) DEFAULT NULL,
														  `price` decimal(12,2) DEFAULT NULL,
														  `product_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
														  `product_variants` text COLLATE utf8_unicode_ci,
														  PRIMARY KEY (`id`)
														) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_applied_details_tbl) === TRUE) {
						echo "Table <b>b2b_orders_applied_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$b2b_orders_draft_tbl = "CREATE TABLE `b2b_orders_draft` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `supplier_shop_id` int(11) DEFAULT NULL,
											  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
											  `total_categories_count` int(11) DEFAULT NULL,
											  `total_products_count` int(11) DEFAULT NULL,
											  `total_buyin_products` int(11) DEFAULT NULL,
											  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
											  `total_virtual_products_withqty` int(11) DEFAULT NULL,
											  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
											  `total_virtual_products` int(11) DEFAULT NULL,
											  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
											  `total_dropship_products` int(11) DEFAULT NULL,
											  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
											  `created_by` int(11) DEFAULT NULL,
											  `created_at` int(11) DEFAULT NULL,
											  `updated_at` int(11) DEFAULT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_draft_tbl) === TRUE) {
						echo "Table <b>b2b_orders_draft</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$b2b_orders_draft_details_tbl = "CREATE TABLE `b2b_orders_draft_details` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `draft_order_id` int(11) DEFAULT NULL,
													  `product_id` int(11) DEFAULT NULL,
													  `parent_id` int(11) DEFAULT NULL,
													  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
													  `quantity` int(11) DEFAULT NULL,
													  `price` decimal(12,2) DEFAULT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_draft_details_tbl) === TRUE) {
						echo "Table <b>b2b_orders_draft_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_saved_tbl = "CREATE TABLE `b2b_orders_saved` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `supplier_shop_id` int(11) DEFAULT NULL,
											  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
											  `total_categories_count` int(11) DEFAULT NULL,
											  `total_products_count` int(11) DEFAULT NULL,
											  `total_buyin_products` int(11) DEFAULT NULL,
											  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
											  `total_virtual_products_withqty` int(11) DEFAULT NULL,
											  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
											  `total_virtual_products` int(11) DEFAULT NULL,
											  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
											  `total_dropship_products` int(11) DEFAULT NULL,
											  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
											  `created_by` int(11) DEFAULT NULL,
											  `created_at` int(11) DEFAULT NULL,
											  `updated_at` int(11) DEFAULT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_saved_tbl) === TRUE) {
						echo "Table <b>b2b_orders_saved</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_saved_details_tbl = "CREATE TABLE `b2b_orders_saved_details` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `saved_order_id` int(11) DEFAULT NULL,
													  `product_id` int(11) DEFAULT NULL,
													  `parent_id` int(11) DEFAULT NULL,
													  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
													  `quantity` int(11) DEFAULT NULL,
													  `price` decimal(12,2) DEFAULT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($b2b_orders_saved_details_tbl) === TRUE) {
						echo "Table <b>b2b_customers</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_address_tbl = "CREATE TABLE `b2b_order_address` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `order_id` int(11) NOT NULL,
											  `address_type` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1-billing,2-shipping',
											  `first_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `last_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `mobile_no` int(11) DEFAULT '0',
											  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
											  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
											  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_order_address_tbl) === TRUE) {
						echo "Table <b>b2b_order_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_items_tbl = "CREATE TABLE `b2b_order_items` (
											  `item_id` int(11) NOT NULL AUTO_INCREMENT,
											  `order_id` int(11) NOT NULL,
											  `product_id` int(11) NOT NULL,
											  `parent_product_id` int(11) NOT NULL,
											  `product_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'conf-simple, simple',
											  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `product_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `product_variants` mediumtext COLLATE utf8_unicode_ci,
											  `qty_ordered` int(11) NOT NULL,
											  `qty_scanned` int(11) NOT NULL DEFAULT '0',
											  `price` decimal(12,2) NOT NULL,
											  `total_price` decimal(12,2) NOT NULL,
											  `applied_rule_ids` text COLLATE utf8_unicode_ci,
											  `tax_percent` decimal(12,2) NOT NULL DEFAULT '0.00',
											  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
											  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT NULL,
											  `created_by` int(11) NOT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
											  PRIMARY KEY (`item_id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_order_items_tbl) === TRUE) {
						echo "Table <b>b2b_order_items</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_shipment_tbl = "CREATE TABLE `b2b_order_shipment` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `order_id` int(11) NOT NULL,
											  `shipment_id` int(11) NOT NULL,
											  `message` mediumtext COLLATE utf8_unicode_ci,
											  `created_by` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($b2b_order_shipment_tbl) === TRUE) {
						echo "Table <b>b2b_order_shipment</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$b2b_order_shipment_details_tbl = "CREATE TABLE `b2b_order_shipment_details` (
														  `id` int(11) NOT NULL AUTO_INCREMENT,
														  `order_id` int(11) NOT NULL,
														  `order_shipment_id` int(11) NOT NULL,
														  `box_number` int(11) NOT NULL,
														  `weight` float(10,2) DEFAULT NULL,
														  `tracking_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
														  `created_at` int(11) NOT NULL,
														  `created_by` int(11) NOT NULL,
														  `updated_at` int(11) DEFAULT NULL,
														  `updated_by` int(11) DEFAULT NULL,
														  PRIMARY KEY (`id`)
														) ENGINE=InnoDB";

					if ($conn->query($b2b_order_shipment_details_tbl) === TRUE) {
						echo "Table <b>b2b_order_shipment_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$banners_tbl = "CREATE TABLE `banners` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `static_block_id` int(11) NOT NULL,
									  `position` int(5) DEFAULT '0',
									  `heading` mediumtext COLLATE utf8_unicode_ci,
									  `description` text COLLATE utf8_unicode_ci,
									  `type` smallint(2) NOT NULL COMMENT '1-home,2-category,3-others',
									  `category_ids` mediumtext COLLATE utf8_unicode_ci,
									  `button_text` mediumtext COLLATE utf8_unicode_ci,
									  `link_button_to` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `banner_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
									  `created_by` int(11) NOT NULL,
									  `created_at` int(11) NOT NULL,
									  `updated_at` int(11) DEFAULT '0',
									  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
									  PRIMARY KEY (`id`)
									) ENGINE=InnoDB";

					if ($conn->query($banners_tbl) === TRUE) {
						echo "Table <b>banners</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$cms_pages_tbl = "CREATE TABLE `cms_pages` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
									  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
									  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
									  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `meta_keywords` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `meta_description` text COLLATE utf8_unicode_ci,
									  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-published,2-on hold',
									  `remove_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-not removed, 1- removed',
									  `created_by` int(11) NOT NULL,
									  `created_at` int(11) NOT NULL,
									  `updated_at` int(11) DEFAULT NULL,
									  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
									  PRIMARY KEY (`id`)
									) ENGINE=InnoDB";

					if ($conn->query($cms_pages_tbl) === TRUE) {
						echo "Table <b>cms_pages</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$contact_us_tbl = "CREATE TABLE `contact_us` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_id` int(11) DEFAULT '0',
										  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `message` text COLLATE utf8_unicode_ci NOT NULL,
										  `mobile_no` int(11) DEFAULT '0',
										  `created_at` int(11) NOT NULL,
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=InnoDB";

					if ($conn->query($contact_us_tbl) === TRUE) {
						echo "Table <b>contact_us</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$country_master_tbl = "CREATE TABLE `country_master` (
											  `country_code` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
											  `country_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`country_code`),
											  UNIQUE KEY `country_code` (`country_code`),
											  KEY `idx_country_code` (`country_code`)
											) ENGINE=InnoDB";

					if ($conn->query($country_master_tbl) === TRUE) {
						echo "Table <b>country_master</b> created successfully.<br>";

						$country_master_insert_tbl = "INSERT INTO `country_master` (`country_code`, `country_name`) VALUES
														('AD', 'Andorra'),
														('AE', 'United Arab Emirates'),
														('AF', 'Afghanistan'),
														('AG', 'Antigua and Barbuda'),
														('AI', 'Anguilla'),
														('AL', 'Albania'),
														('AM', 'Armenia'),
														('AO', 'Angola'),
														('AQ', 'Antarctica'),
														('AR', 'Argentina'),
														('AS', 'American Samoa'),
														('AT', 'Austria'),
														('AU', 'Australia'),
														('AW', 'Aruba'),
														('AX', 'Aland Islands'),
														('AZ', 'Azerbaijan'),
														('BA', 'Bosnia and Herzegovina'),
														('BB', 'Barbados'),
														('BD', 'Bangladesh'),
														('BE', 'Belgium'),
														('BF', 'Burkina Faso'),
														('BG', 'Bulgaria'),
														('BH', 'Bahrain'),
														('BI', 'Burundi'),
														('BJ', 'Benin'),
														('BL', 'Saint Barthelemy'),
														('BM', 'Bermuda'),
														('BN', 'Brunei Darussalam'),
														('BO', 'Bolivia (Plurinational State of)'),
														('BQ', 'Bonaire, Sint Eustatius and Saba'),
														('BR', 'Brazil'),
														('BS', 'Bahamas'),
														('BT', 'Bhutan'),
														('BV', 'Bouvet Island'),
														('BW', 'Botswana'),
														('BY', 'Belarus'),
														('BZ', 'Belize'),
														('CA', 'Canada'),
														('CC', 'Cocos (Keeling) Islands'),
														('CD', 'Congo (Democratic Republic of the)'),
														('CF', 'Central African Republic'),
														('CG', 'Congo'),
														('CH', 'Switzerland'),
														('CI', 'Cote d\'Ivoire'),
														('CK', 'Cook Islands'),
														('CL', 'Chile'),
														('CM', 'Cameroon'),
														('CN', 'China'),
														('CO', 'Colombia'),
														('CR', 'Costa Rica'),
														('CU', 'Cuba'),
														('CV', 'Cabo Verde'),
														('CW', 'Curaçao'),
														('CX', 'Christmas Island'),
														('CY', 'Cyprus'),
														('CZ', 'Czechia'),
														('DE', 'Germany'),
														('DJ', 'Djibouti'),
														('DK', 'Denmark'),
														('DM', 'Dominica'),
														('DO', 'Dominican Republic'),
														('DZ', 'Algeria'),
														('EC', 'Ecuador'),
														('EE', 'Estonia'),
														('EG', 'Egypt'),
														('EH', 'Western Sahara'),
														('ER', 'Eritrea'),
														('ES', 'Spain'),
														('ET', 'Ethiopia'),
														('FI', 'Finland'),
														('FJ', 'Fiji'),
														('FK', 'Falkland Islands (Malvinas)'),
														('FM', 'Micronesia (Federated States of)'),
														('FO', 'Faroe Islands'),
														('FR', 'France'),
														('GA', 'Gabon'),
														('GB', 'United Kingdom of Great Britain and Northern Ireland'),
														('GD', 'Grenada'),
														('GE', 'Georgia'),
														('GF', 'French Guiana'),
														('GG', 'Guernsey'),
														('GH', 'Ghana'),
														('GI', 'Gibraltar'),
														('GL', 'Greenland'),
														('GM', 'Gambia'),
														('GN', 'Guinea'),
														('GP', 'Guadeloupe'),
														('GQ', 'Equatorial Guinea'),
														('GR', 'Greece'),
														('GS', 'South Georgia and the South Sandwich Islands'),
														('GT', 'Guatemala'),
														('GU', 'Guam'),
														('GW', 'Guinea-Bissau'),
														('GY', 'Guyana'),
														('HK', 'Hong Kong'),
														('HM', 'Heard Island and McDonald Islands'),
														('HN', 'Honduras'),
														('HR', 'Croatia'),
														('HT', 'Haiti'),
														('HU', 'Hungary'),
														('ID', 'Indonesia'),
														('IE', 'Ireland'),
														('IL', 'Israel'),
														('IM', 'Isle of Man'),
														('IN', 'India'),
														('IO', 'British Indian Ocean Territory'),
														('IQ', 'Iraq'),
														('IR', 'Iran (Islamic Republic of)'),
														('IS', 'Iceland'),
														('IT', 'Italy'),
														('JE', 'Jersey'),
														('JM', 'Jamaica'),
														('JO', 'Jordan'),
														('JP', 'Japan'),
														('KE', 'Kenya'),
														('KG', 'Kyrgyzstan'),
														('KH', 'Cambodia'),
														('KI', 'Kiribati'),
														('KM', 'Comoros'),
														('KN', 'Saint Kitts and Nevis'),
														('KP', 'Korea (Democratic People\'s Republic of)'),
														('KR', 'Korea (Republic of)'),
														('KW', 'Kuwait'),
														('KY', 'Cayman Islands'),
														('KZ', 'Kazakhstan'),
														('LA', 'Lao People\'s Democratic Republic'),
														('LB', 'Lebanon'),
														('LC', 'Saint Lucia'),
														('LI', 'Liechtenstein'),
														('LK', 'Sri Lanka'),
														('LR', 'Liberia'),
														('LS', 'Lesotho'),
														('LT', 'Lithuania'),
														('LU', 'Luxembourg'),
														('LV', 'Latvia'),
														('LY', 'Libya'),
														('MA', 'Morocco'),
														('MC', 'Monaco'),
														('MD', 'Moldova (Republic of)'),
														('ME', 'Montenegro'),
														('MF', 'Saint Martin (French Part)'),
														('MG', 'Madagascar'),
														('MH', 'Marshall Islands'),
														('MK', 'North Macedonia'),
														('ML', 'Mali'),
														('MM', 'Myanmar'),
														('MN', 'Mongolia'),
														('MO', 'Macao'),
														('MP', 'Northern Mariana Islands'),
														('MQ', 'Martinique'),
														('MR', 'Mauritania'),
														('MS', 'Montserrat'),
														('MT', 'Malta'),
														('MU', 'Mauritius'),
														('MV', 'Maldives'),
														('MW', 'Malawi'),
														('MX', 'Mexico'),
														('MY', 'Malaysia'),
														('MZ', 'Mozambique'),
														('NA', 'Namibia'),
														('NC', 'New Caledonia'),
														('NE', 'Niger'),
														('NF', 'Norfolk Island'),
														('NG', 'Nigeria'),
														('NI', 'Nicaragua'),
														('NL', 'Netherlands'),
														('NO', 'Norway'),
														('NP', 'Nepal'),
														('NR', 'Nauru'),
														('NU', 'Niue'),
														('NZ', 'New Zealand'),
														('OM', 'Oman'),
														('PA', 'Panama'),
														('PE', 'Peru'),
														('PF', 'French Polynesia'),
														('PG', 'Papua New Guinea'),
														('PH', 'Philippines'),
														('PK', 'Pakistan'),
														('PL', 'Poland'),
														('PM', 'Saint Pierre and Miquelon'),
														('PN', 'Pitcairn'),
														('PR', 'Puerto Rico'),
														('PS', 'Palestine, State of'),
														('PT', 'Portugal'),
														('PW', 'Palau'),
														('PY', 'Paraguay'),
														('QA', 'Qatar'),
														('RE', 'Reunion'),
														('RO', 'Romania'),
														('RS', 'Serbia'),
														('RU', 'Russian Federation'),
														('RW', 'Rwanda'),
														('SA', 'Saudi Arabia'),
														('SB', 'Solomon Islands'),
														('SC', 'Seychelles'),
														('SD', 'Sudan'),
														('SE', 'Sweden'),
														('SG', 'Singapore'),
														('SH', 'Saint Helena, Ascension and Tristan da Cunha'),
														('SI', 'Slovenia'),
														('SJ', 'Svalbard and Jan Mayen'),
														('SK', 'Slovakia'),
														('SL', 'Sierra Leone'),
														('SM', 'San Marino'),
														('SN', 'Senegal'),
														('SO', 'Somalia'),
														('SR', 'Suriname'),
														('SS', 'South Sudan'),
														('ST', 'Sao Tome and Principe'),
														('SV', 'El Salvador'),
														('SX', 'Sint Maarten (Dutch Part)'),
														('SY', 'Syrian Arab Republic'),
														('SZ', 'Eswatini'),
														('TC', 'Turks and Caicos Islands'),
														('TD', 'Chad'),
														('TF', 'French Southern Territories'),
														('TG', 'Togo'),
														('TH', 'Thailand'),
														('TJ', 'Tajikistan'),
														('TK', 'Tokelau'),
														('TL', 'Timor-Leste'),
														('TM', 'Turkmenistan'),
														('TN', 'Tunisia'),
														('TO', 'Tonga'),
														('TR', 'Turkey'),
														('TT', 'Trinidad and Tobago'),
														('TV', 'Tuvalu'),
														('TW', 'Taiwan (Province of China)'),
														('TZ', 'Tanzania, United Republic of'),
														('UA', 'Ukraine'),
														('UG', 'Uganda'),
														('UM', 'United States Minor Outlying Islands'),
														('US', 'United States of America'),
														('UY', 'Uruguay'),
														('UZ', 'Uzbekistan'),
														('VA', 'Holy See'),
														('VC', 'Saint Vincent and the Grenadines'),
														('VE', 'Venezuela (Bolivarian Republic of)'),
														('VG', 'Virgin Islands (British)'),
														('VI', 'Virgin Islands (U.S.)'),
														('VN', 'Viet Nam'),
														('VU', 'Vanuatu'),
														('WF', 'Wallis and Futuna'),
														('WS', 'Samoa'),
														('YE', 'Yemen'),
														('YT', 'Mayotte'),
														('ZA', 'South Africa'),
														('ZM', 'Zambia'),
														('ZW', 'Zimbabwe')";

						if ($conn->query($country_master_insert_tbl) === TRUE) {
						  echo "<b>country_master</b> New records inserted successfully.<br>";
						} else {
						  echo "Error insert record: " . $conn->error;
						}
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$customers_tbl = "CREATE TABLE `customers` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
									  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
									  `email_id` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
									  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
									  `mobile_no` bigint(20) DEFAULT '0',
									  `gender` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `country_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `dob` date DEFAULT NULL,
									  `customer_type_id` int(11) DEFAULT '2',
									  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-Active 2-Blocked',
									  `last_login_at` int(11) DEFAULT '0',
									  `password_reset_date` int(11) DEFAULT '0',
									  `created_at` int(11) NOT NULL DEFAULT '0',
									  `updated_at` int(11) DEFAULT '0',
									  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
									  PRIMARY KEY (`id`)
									) ENGINE=InnoDB";

					if ($conn->query($customers_tbl) === TRUE) {
						echo "Table <b>customers</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$customers_address_tbl = "CREATE TABLE `customers_address` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `customer_id` int(11) NOT NULL,
											  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
											  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
											  `mobile_no` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
											  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
											  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
											  `state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
											  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
											  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
											  `is_default` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
											  `is_default_billing` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
											  `is_default_shipping` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
											  `remove_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($customers_address_tbl) === TRUE) {
						echo "Table <b>customers_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$customers_type_master_tbl = "CREATE TABLE `customers_type_master` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `created_at` int(11) DEFAULT '0',
												  `updated_at` int(11) DEFAULT '0',
												  `created_by` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($customers_type_master_tbl) === TRUE) {
						echo "Table <b>customers_type_master</b> created successfully.<br>";

						$customers_type_master_insert_tbl = "INSERT INTO `customers_type_master` (
															  `id`, `name`, `created_at`, `updated_at`, `created_by`, `ip`) VALUES
															  (1, 'Not Logged In', '0', '0', '0', NULL),
															  (2, 'General', '0', '0', '0', NULL)";

						if ($conn->query($customers_type_master_insert_tbl) === TRUE) {
						  echo "<b>customers_type_master</b> New records inserted successfully.<br>";
						} else {
						  echo "Error insert record: " . $conn->error;
						}
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$custom_variables_tbl = "CREATE TABLE `custom_variables` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `value` text COLLATE utf8_unicode_ci NOT NULL,
											  `created_by` int(11) DEFAULT '0',
											  `created_by_type` int(11) DEFAULT '0',
											  `created_at` int(11) DEFAULT '0',
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($custom_variables_tbl) === TRUE) {
						echo "Table <b>custom_variables</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$email_template_tbl = "CREATE TABLE `email_template` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `email_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
											  `status` tinyint(1) NOT NULL,
											  `created_by` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($email_template_tbl) === TRUE) {
						echo "Table <b>email_template</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$fbc_users_attributes_visibility_tbl = "CREATE TABLE `fbc_users_attributes_visibility` (
															  `id` int(11) NOT NULL AUTO_INCREMENT,
															  `attr_id` int(11) NOT NULL,
															  `display_on_frontend` tinyint(4) NOT NULL DEFAULT '0',
															  `filterable_with_results` tinyint(4) NOT NULL DEFAULT '0',
															  `created_at` int(11) NOT NULL,
															  `updated_at` int(11) DEFAULT NULL,
															  `created_by` int(11) NOT NULL,
															  PRIMARY KEY (`id`)
															) ENGINE=InnoDB";

					if ($conn->query($fbc_users_attributes_visibility_tbl) === TRUE) {
						echo "Table <b>fbc_users_attributes_visibility</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$fbc_users_category_b2b_tbl = "CREATE TABLE `fbc_users_category_b2b` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `category_id` int(11) NOT NULL,
													  `level` tinyint(4) NOT NULL COMMENT '0,1,2',
													  `fbc_user_id` int(11) NOT NULL,
													  `b2b_enabled` tinyint(4) NOT NULL DEFAULT '0',
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($fbc_users_category_b2b_tbl) === TRUE) {
						echo "Table <b>fbc_users_category_b2b</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$login_session_tbl = "CREATE TABLE `login_session` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `sessionid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `user_id` int(11) NOT NULL,
										  `login_time` int(11) NOT NULL,
										  `logout_time` int(11) DEFAULT '0',
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=InnoDB";

					if ($conn->query($login_session_tbl) === TRUE) {
						echo "Table <b>login_session</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$newsletter_subscriber_tbl = "CREATE TABLE `newsletter_subscriber` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
													  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - subscribed, 2 - un subscribed',
													  `created_at` int(11) NOT NULL,
													  `updated_at` int(11) DEFAULT '0',
													  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($newsletter_subscriber_tbl) === TRUE) {
						echo "Table <b>newsletter_subscriber</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$notifications_tbl = "CREATE TABLE `notifications` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `from_shop_id` int(11) NOT NULL,
										  `from_fbc_user_id` int(11) NOT NULL,
										  `to_shop_id` int(11) NOT NULL,
										  `to_fbc_user_id` int(11) NOT NULL,
										  `shop_id` int(11) NOT NULL,
										  `area_id` int(11) NOT NULL COMMENT 'order_id',
										  `notification_text` mediumtext COLLATE utf8_unicode_ci,
										  `notification_type` tinyint(2) NOT NULL COMMENT '1-b2b_order_request, 2-b2b_order_confirmed,3-b2b_order_rejected',
										  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-Pending, 1-Accepted, 2-Declined',
										  `read_flag` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-unread, 1-read',
										  `visited_flag` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-not visited, 1-visited',
										  `created_at` int(11) NOT NULL,
										  `updated_at` int(11) NOT NULL,
										  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=InnoDB";

					if ($conn->query($notifications_tbl) === TRUE) {
						echo "Table <b>notifications</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_tbl = "CREATE TABLE `products` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `parent_id` int(11) DEFAULT '0',
									  `product_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'simple;configurable,conf-simple',
									  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship',
									  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `highlights` mediumtext COLLATE utf8_unicode_ci,
									  `description` longtext COLLATE utf8_unicode_ci,
									  `product_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `base_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `meta_description` text COLLATE utf8_unicode_ci,
									  `meta_keyword` text COLLATE utf8_unicode_ci,
									  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `price` decimal(12,2) DEFAULT NULL,
									  `cost_price` decimal(12,2) DEFAULT NULL,
									  `tax_percent` decimal(12,2) DEFAULT '0.00',
									  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
									  `webshop_price` decimal(12,2) DEFAULT '0.00',
									  `special_price` decimal(12,2) DEFAULT NULL,
									  `special_price_from` int(11) DEFAULT NULL,
									  `special_price_to` int(11) DEFAULT NULL,
									  `estimate_delivery_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `product_return_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `product_drop_shipment` tinyint(4) DEFAULT NULL COMMENT '1-Allow, 0 - Deny',
									  `product_reviews_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `supplier_id` int(11) DEFAULT NULL COMMENT 'No in use now',
									  `status` tinyint(4) NOT NULL COMMENT '1-Enabled, 2 - Disabled',
									  `url_key` mediumtext COLLATE utf8_unicode_ci,
									  `launch_date` int(11) DEFAULT NULL,
									  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `shop_id` int(11) DEFAULT '0',
									  `shop_product_id` int(11) DEFAULT NULL,
									  `shop_price` decimal(12,2) DEFAULT NULL,
									  `shop_cost_price` decimal(12,2) DEFAULT NULL,
									  `shop_currency` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `shop_dropship_discount_percent` decimal(12,0) NOT NULL DEFAULT '0',
									  `shop_dropship_del_time_in_days` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `shop_buyin_discount_percent` int(11) NOT NULL DEFAULT '0',
									  `shop_buyin_del_time_in_days` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
									  `shop_display_catalog_overseas` tinyint(4) DEFAULT '0' COMMENT '1- Yes,0- No',
									  `shop_perm_to_change_price` tinyint(4) DEFAULT '0' COMMENT '1- Yes,0- No',
									  `shop_can_increase_price` tinyint(4) DEFAULT '0' COMMENT '1- Yes,0- No',
									  `shop_can_decrease_price` tinyint(4) DEFAULT '0' COMMENT '1- Yes,0- No',
									  `fbc_user_id` int(11) DEFAULT '0',
									  `created_at` int(11) NOT NULL,
									  `updated_at` int(11) DEFAULT NULL,
									  `imported_from` int(11) DEFAULT NULL COMMENT 'Same as shop id',
									  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
									  PRIMARY KEY (`id`)
									) ENGINE=InnoDB";

					if ($conn->query($products_tbl) === TRUE) {
						echo "Table <b>products</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_attributes_tbl = "CREATE TABLE `products_attributes` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `product_id` int(11) NOT NULL,
												  `attr_id` int(11) NOT NULL,
												  `attr_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($products_attributes_tbl) === TRUE) {
						echo "Table <b>products_attributes</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_block_details_tbl = "CREATE TABLE `products_block_details` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `pb_master_id` int(11) NOT NULL,
													  `assigned_products` longtext COLLATE utf8_unicode_ci NOT NULL,
													  `created_at` int(11) DEFAULT '0',
													  `updated_at` int(11) DEFAULT '0',
													  `created_by` int(11) DEFAULT '0',
													  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($products_block_details_tbl) === TRUE) {
						echo "Table <b>products_block_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_block_master_tbl = "CREATE TABLE `products_block_master` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `block_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
													  `block_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($products_block_master_tbl) === TRUE) {
						echo "Table <b>products_block_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_category_tbl = "CREATE TABLE `products_category` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `product_id` int(11) NOT NULL,
												  `category_ids` mediumtext COLLATE utf8_unicode_ci,
												  `level` int(11) DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($products_category_tbl) === TRUE) {
						echo "Table <b>products_category</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_inventory_tbl = "CREATE TABLE `products_inventory` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `product_id` int(11) NOT NULL,
												  `qty` int(11) NOT NULL,
												  `min_qty` int(11) DEFAULT NULL,
												  `is_in_stock` tinyint(4) DEFAULT NULL COMMENT '1-Yes,2-No',
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($products_inventory_tbl) === TRUE) {
						echo "Table <b>products_inventory</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_media_gallery_tbl = "CREATE TABLE `products_media_gallery` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `product_id` int(11) NOT NULL,
													  `child_id` int(11) DEFAULT NULL,
													  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
													  `image_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
													  `image_position` int(11) DEFAULT NULL,
													  `is_default` tinyint(4) DEFAULT NULL,
													  `is_base_image` tinyint(4) DEFAULT NULL,
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($products_media_gallery_tbl) === TRUE) {
						echo "Table <b>products_media_gallery</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_reviews_tbl = "CREATE TABLE `products_reviews` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `product_id` int(11) NOT NULL,
											  `customer_id` int(11) NOT NULL,
											  `rating` float NOT NULL,
											  `review` longtext COLLATE utf8_unicode_ci NOT NULL,
											  `status` tinyint(1) NOT NULL COMMENT '0-Pending,1-Active,2-Blocked',
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($products_reviews_tbl) === TRUE) {
						echo "Table <b>products_reviews</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_variants_tbl = "CREATE TABLE `products_variants` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `product_id` int(11) NOT NULL,
												  `parent_id` int(11) NOT NULL,
												  `attr_id` int(11) NOT NULL,
												  `attr_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($products_variants_tbl) === TRUE) {
						echo "Table <b>products_variants</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_variants_master_tbl = "CREATE TABLE `products_variants_master` (
													  `id` int(11) NOT NULL AUTO_INCREMENT,
													  `product_id` int(11) NOT NULL,
													  `attr_id` int(11) NOT NULL,
													  `position` int(11) NOT NULL DEFAULT '0',
													  PRIMARY KEY (`id`)
													) ENGINE=InnoDB";

					if ($conn->query($products_variants_master_tbl) === TRUE) {
						echo "Table <b>products_variants_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_order_tbl = "CREATE TABLE `sales_order` (
										  `order_id` int(11) NOT NULL AUTO_INCREMENT,
										  `increment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `parent_id` int(11) DEFAULT '0',
										  `main_parent_id` int(11) DEFAULT '0',
										  `checkout_method` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'guest,register,login',
										  `payment_method` int(11) NOT NULL,
										  `status` int(11) NOT NULL COMMENT '0-to be processed, 1-processing, 2-complete, 3-cancelled, 4- Tracking Missing, 5- Tracking Incomplete, 6- Tracking Complete,7-Pending',
										  `customer_id` int(11) DEFAULT '0',
										  `customer_group_id` int(11) DEFAULT '0',
										  `customer_email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_firstname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_lastname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `applied_rule_ids` text COLLATE utf8_unicode_ci,
										  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
										  `base_discount_amount` decimal(12,2) DEFAULT '0.00',
										  `base_grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_shipping_amount` decimal(12,2) DEFAULT '0.00',
										  `base_shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `base_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `discount_amount` decimal(12,2) DEFAULT '0.00',
										  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `shipping_amount` decimal(12,2) DEFAULT '0.00',
										  `shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `tax_amount` decimal(12,2) DEFAULT '0.00',
										  `total_qty_ordered` int(11) NOT NULL DEFAULT '0',
										  `customer_is_guest` tinyint(1) DEFAULT '0' COMMENT '0-no,1-yes',
										  `email_sent` tinyint(1) DEFAULT '0' COMMENT '0-n0,1-yes',
										  `dropship_order` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1- Yes,0- No',
										  `created_at` int(11) NOT NULL DEFAULT '0',
										  `updated_at` int(11) DEFAULT '0',
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`order_id`)
										) ENGINE=InnoDB";

					if ($conn->query($sales_order_tbl) === TRUE) {
						echo "Table <b>sales_order</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_order_address_tbl = "CREATE TABLE `sales_order_address` (
												  `address_id` int(11) NOT NULL AUTO_INCREMENT,
												  `order_id` int(11) NOT NULL,
												  `address_type` tinyint(1) NOT NULL COMMENT '1-billing,2-shipping',
												  `save_in_address_book` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
												  `customer_address_id` int(11) DEFAULT '0',
												  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `mobile_no` int(11) DEFAULT '0',
												  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
												  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
												  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
												  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
												  `same_as_billing` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
												  `created_at` int(11) DEFAULT NULL,
												  `updated_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`address_id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_order_address_tbl) === TRUE) {
						echo "Table <b>sales_order_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_order_items_tbl = "CREATE TABLE `sales_order_items` (
												  `item_id` int(11) NOT NULL AUTO_INCREMENT,
												  `shop_id` int(11) NOT NULL,
												  `order_id` int(11) NOT NULL,
												  `product_id` int(11) NOT NULL,
												  `parent_product_id` int(11) DEFAULT '0',
												  `parent_item_id` int(11) DEFAULT '0',
												  `product_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'conf-simple, simple',
												  `product_inv_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'buy,virtual,dropship',
												  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `product_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `barcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `product_variants` mediumtext COLLATE utf8_unicode_ci,
												  `qty_ordered` int(11) NOT NULL,
												  `qty_scanned` int(11) DEFAULT '0',
												  `price` decimal(12,2) NOT NULL,
												  `total_price` decimal(12,2) NOT NULL,
												  `applied_rule_ids` text COLLATE utf8_unicode_ci,
												  `tax_percent` decimal(12,2) DEFAULT NULL,
												  `tax_amount` decimal(12,2) DEFAULT NULL,
												  `discount_amount` decimal(12,2) DEFAULT NULL,
												  `created_at` int(11) NOT NULL,
												  `updated_at` int(11) DEFAULT NULL,
												  `created_by` int(11) DEFAULT NULL,
												  `created_by_type` tinyint(1) DEFAULT '0' COMMENT '0-frontend, 1-fbc user panel',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`item_id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_order_items_tbl) === TRUE) {
						echo "Table <b>sales_order_items</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_order_payment_tbl = "CREATE TABLE `sales_order_payment` (
												  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
												  `order_id` int(11) NOT NULL,
												  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
												  `payment_method_id` int(11) NOT NULL,
												  `payment_method` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `payment_method_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `payment_type` tinyint(1) NOT NULL,
												  `split_fbc_percentage` decimal(12,2) DEFAULT '0.00',
												  `fbc_payment_amount` decimal(12,2) DEFAULT '0.00',
												  `webshop_payment_amount` decimal(12,2) DEFAULT '0.00',
												  `currency_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
												  `status` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
												  `request_data` longtext COLLATE utf8_unicode_ci,
												  `response_data` longtext COLLATE utf8_unicode_ci,
												  `created_at` int(11) DEFAULT '0',
												  `updated_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`payment_id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_order_payment_tbl) === TRUE) {
						echo "Table <b>sales_order_payment</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_tbl = "CREATE TABLE `sales_quote` (
										  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
										  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `checkout_method` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'guest,register,login',
										  `customer_id` int(11) DEFAULT '0',
										  `customer_group_id` int(11) DEFAULT '0',
										  `customer_email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_firstname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_lastname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `applied_rule_ids` text COLLATE utf8_unicode_ci,
										  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
										  `base_discount_amount` decimal(12,2) DEFAULT '0.00',
										  `base_grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_shipping_amount` decimal(12,2) DEFAULT '0.00',
										  `base_shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `base_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `base_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `discount_amount` decimal(12,2) DEFAULT '0.00',
										  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `shipping_amount` decimal(12,2) DEFAULT '0.00',
										  `shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
										  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
										  `tax_amount` decimal(12,2) DEFAULT '0.00',
										  `total_qty_ordered` int(11) NOT NULL DEFAULT '0',
										  `customer_is_guest` tinyint(1) DEFAULT '0' COMMENT '0-no,1-yes',
										  `email_sent` tinyint(1) DEFAULT '0' COMMENT '0-n0,1-yes',
										  `created_at` int(11) NOT NULL DEFAULT '0',
										  `updated_at` int(11) DEFAULT '0',
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`quote_id`)
										) ENGINE=InnoDB";

					if ($conn->query($sales_quote_tbl) === TRUE) {
						echo "Table <b>sales_quote</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_address_tbl = "CREATE TABLE `sales_quote_address` (
												  `address_id` int(11) NOT NULL AUTO_INCREMENT,
												  `quote_id` int(11) NOT NULL,
												  `address_type` tinyint(1) NOT NULL COMMENT '1-billing,2-shipping',
												  `save_in_address_book` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
												  `customer_address_id` int(11) DEFAULT '0',
												  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `mobile_no` int(11) DEFAULT '0',
												  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
												  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
												  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
												  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
												  `same_as_billing` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
												  `created_at` int(11) DEFAULT NULL,
												  `updated_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`address_id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_quote_address_tbl) === TRUE) {
						echo "Table <b>sales_quote_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_items_tbl = "CREATE TABLE `sales_quote_items` (
												  `item_id` int(11) NOT NULL AUTO_INCREMENT,
												  `shop_id` int(11) NOT NULL,
												  `quote_id` int(11) NOT NULL,
												  `product_id` int(11) NOT NULL,
												  `parent_product_id` int(11) DEFAULT '0',
												  `parent_item_id` int(11) DEFAULT '0',
												  `product_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'conf-simple, simple',
												  `product_inv_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'buy,virtual,dropship',
												  `virtual_shop_flag` int(11) NOT NULL,
												  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `product_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `barcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `product_variants` mediumtext COLLATE utf8_unicode_ci,
												  `qty_ordered` int(11) NOT NULL,
												  `qty_scanned` int(11) DEFAULT '0',
												  `price` decimal(12,2) NOT NULL,
												  `total_price` decimal(12,2) NOT NULL,
												  `applied_rule_ids` text COLLATE utf8_unicode_ci,
												  `tax_percent` decimal(12,2) DEFAULT NULL,
												  `tax_amount` decimal(12,2) DEFAULT NULL,
												  `discount_amount` decimal(12,2) DEFAULT NULL,
												  `created_at` int(11) NOT NULL,
												  `updated_at` int(11) DEFAULT NULL,
												  `created_by` int(11) DEFAULT NULL,
												  `created_by_type` tinyint(1) DEFAULT '0' COMMENT '0-frontend, 1-fbc user panel',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`item_id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_quote_items_tbl) === TRUE) {
						echo "Table <b>sales_quote_items</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_payment_tbl = "CREATE TABLE `sales_quote_payment` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `quote_id` int(11) NOT NULL,
												  `payment_method_id` int(11) NOT NULL,
												  `payment_method` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
												  `payment_method_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `payment_type` tinyint(1) NOT NULL COMMENT '1-Direct,2-Split',
												  `gateway_details` text COLLATE utf8_unicode_ci NOT NULL,
												  `created_at` int(11) DEFAULT '0',
												  `updated_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($sales_quote_payment_tbl) === TRUE) {
						echo "Table <b>sales_quote_payment</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$search_terms_tbl = "CREATE TABLE `search_terms` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `search_term` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `popularity` int(11) NOT NULL DEFAULT '1',
										  `created_at` int(11) DEFAULT '0',
										  `updated_at` int(11) DEFAULT '0',
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=InnoDB";

					if ($conn->query($search_terms_tbl) === TRUE) {
						echo "Table <b>search_terms</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$shipment_master_tbl = "CREATE TABLE `shipment_master` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `status` tinyint(4) NOT NULL COMMENT '1-Active, 2- Disabled',
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($shipment_master_tbl) === TRUE) {
						echo "Table <b>shipment_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$static_blocks_tbl = "CREATE TABLE `static_blocks` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `type` smallint(2) NOT NULL COMMENT '1-block,2-header_scripts,3-footer_scripts,4-menus,5-banner,6-other',
											  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
											  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
											  `status` tinyint(1) NOT NULL COMMENT '1-published,2-on hold',
											  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
											  `menu_type` tinyint(1) DEFAULT '0' COMMENT '0-None, 1-Category, 2-Custom',
											  `created_by` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($static_blocks_tbl) === TRUE) {
						echo "Table <b>static_blocks</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$themes_webshops_tbl = "CREATE TABLE `themes_webshops` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `theme_id` int(11) NOT NULL,
											  `theme_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
											  `theme_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
											  `created_by` int(11) NOT NULL,
											  `current_theme` tinyint(1) DEFAULT NULL,
											  `created_at` int(11) NOT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($themes_webshops_tbl) === TRUE) {
						echo "Table <b>themes_webshops</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$webshop_cat_menus_tbl = "CREATE TABLE `webshop_cat_menus` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `static_block_id` int(11) NOT NULL,
												  `category_id` int(11) NOT NULL,
												  `created_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($webshop_cat_menus_tbl) === TRUE) {
						echo "Table <b>webshop_cat_menus</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$webshop_custom_menus_tbl = "CREATE TABLE `webshop_custom_menus` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `static_block_id` int(11) NOT NULL,
												  `menu_parent_id` int(11) NOT NULL,
												  `menu_main_parent_id` int(11) NOT NULL,
												  `menu_level` tinyint(1) NOT NULL,
												  `menu_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
												  `menu_type` tinyint(1) NOT NULL,
												  `menu_custom_url` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
												  `page_id` int(11) DEFAULT '0',
												  `category_id` int(11) DEFAULT '0',
												  `status` tinyint(1) NOT NULL,
												  `created_at` int(11) NOT NULL,
												  `updated_at` int(11) DEFAULT '0',
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($webshop_custom_menus_tbl) === TRUE) {
						echo "Table <b>webshop_custom_menus</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$webshop_order_shipment_tbl = "CREATE TABLE `webshop_order_shipment` (
												  `id` int(11) NOT NULL AUTO_INCREMENT,
												  `order_id` int(11) NOT NULL,
												  `shipment_id` int(11) NOT NULL,
												  `message` mediumtext COLLATE utf8_unicode_ci,
												  `created_by` int(11) NOT NULL,
												  `created_at` int(11) NOT NULL,
												  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
												  PRIMARY KEY (`id`)
												) ENGINE=InnoDB";

					if ($conn->query($webshop_order_shipment_tbl) === TRUE) {
						echo "Table <b>webshop_order_shipment</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$webshop_order_shipment_details_tbl = "CREATE TABLE `webshop_order_shipment_details` (
															  `id` int(11) NOT NULL AUTO_INCREMENT,
															  `order_id` int(11) NOT NULL,
															  `order_shipment_id` int(11) NOT NULL,
															  `box_number` int(11) NOT NULL,
															  `weight` float(10,2) DEFAULT NULL,
															  `tracking_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
															  `created_at` int(11) NOT NULL,
															  `created_by` int(11) NOT NULL,
															  `updated_at` int(11) DEFAULT NULL,
															  `updated_by` int(11) DEFAULT NULL,
															  PRIMARY KEY (`id`)
															) ENGINE=InnoDB";

					if ($conn->query($webshop_order_shipment_details_tbl) === TRUE) {
						echo "Table <b>webshop_order_shipment_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$webshop_payments_tbl = "CREATE TABLE `webshop_payments` (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `payment_id` int(11) NOT NULL,
											  `status` tinyint(1) NOT NULL COMMENT '0-details incomplete, 1-details completed',
											  `integrate_with_ws` tinyint(1) DEFAULT '0',
											  `payment_type_details` text COLLATE utf8_unicode_ci NOT NULL,
											  `gateway_details` text COLLATE utf8_unicode_ci NOT NULL,
											  `created_by` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL,
											  `updated_at` int(11) DEFAULT '0',
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`id`)
											) ENGINE=InnoDB";

					if ($conn->query($webshop_payments_tbl) === TRUE) {
						echo "Table <b>webshop_payments</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$wishlist_items_tbl = "CREATE TABLE `wishlist_items` (
											  `wishlist_id` int(11) NOT NULL AUTO_INCREMENT,
											  `customer_id` int(11) NOT NULL,
											  `product_id` int(11) NOT NULL,
											  `created_at` int(11) NOT NULL,
											  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`wishlist_id`)
											) ENGINE=InnoDB";

					if ($conn->query($wishlist_items_tbl) === TRUE) {
						echo "Table <b>wishlist_items</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$conn->close(); */
				} else {
					echo "Error creating database: " . $connection->error;
				}

				$connection->close();
			}
		} else {
			echo "No records found.";
		}
	}


	public function fbcusers_dbcreation_old()
	{
		$userData = $this->UserModel->getActiveUsersWithoutDB();
		//echo '<pre>'.print_r($userData, '\n').'</pre>';
		if(is_array($userData) && count($userData) > 0){
			foreach($userData as $value){
				$servername = "localhost";
				$username = "parkmosp_sisfbcu";
				$password = "rXR*j4oYe&H4";

				// Create connection
				$connection = new mysqli($servername, $username, $password);
				// Check connection
				if ($connection->connect_error) {
				  die("Connection failed: " . $connection->connect_error);
				}

				$db_name = "parkmosp_shopinshop_shop".$value->shop_id;
				// Create database
				$sql = "CREATE DATABASE IF NOT EXISTS ".$db_name." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
				if ($connection->query($sql) === TRUE) {
					echo "Database <b>$db_name</b> created successfully";

					$updateData = $this->UserModel->updateDBName($value->shop_id,"shopinshop_shop".$value->shop_id);

					$db_user = 'parkmosp_sisfbcu';
					$db_pass = 'rXR*j4oYe&H4';
					//$db_name = 'parkmosp_shopinshop_shop7';
					$db_host = 'localhost';

					// Create connection
					$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
					// Check connection
					if ($conn->connect_error) {
					  die("Connection failed: " . $conn->connect_error);
					}

					// Create table
					$b2b_orders_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders` (
							  `order_id` int(11) NOT NULL AUTO_INCREMENT,
							  `increment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `order_barcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `applied_order_id` int(11) NOT NULL DEFAULT '0',
							  `shipment_type` tinyint(4) NOT NULL COMMENT '1-Buy In(directshop), 2-Dropship(othershopcustomer)',
							  `status` tinyint(4) NOT NULL COMMENT '0-to be processed, 1-processing, 2-complete, 3-cancelled, 4- Tracking Missing, 5- Tracking  Incomplete, 6- Tracking Complete',
							  `main_parent_id` int(11) NOT NULL DEFAULT '0',
							  `parent_id` int(11) NOT NULL DEFAULT '0',
							  `shop_id` int(11) DEFAULT NULL,
							  `customer_firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `customer_lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `base_discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `base_grand_total` decimal(12,2) NOT NULL,
							  `base_shiping_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `base_shipping_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `base_subtotal` decimal(12,2) NOT NULL,
							  `base_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `grand_total` decimal(12,2) NOT NULL,
							  `shipping_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `shipping_tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `subtotal` decimal(12,2) NOT NULL,
							  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `total_qty_ordered` int(11) NOT NULL,
							  `customer_is_guest` tinyint(4) DEFAULT NULL COMMENT '0-no 1-yes',
							  `email_sent` tinyint(1) DEFAULT '0' COMMENT '0-n0,1-yes',
							  `is_split` tinyint(4) NOT NULL DEFAULT '0',
							  `system_generated_split_order` tinyint(4) DEFAULT NULL COMMENT '0-Can Not split, 1 -Can split',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `created_by` int(11) NOT NULL,
							  `updated_by` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`order_id`)
							) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_tbl) === TRUE) {
						echo "Table <b>b2b_orders</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_applied_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders_applied` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `supplier_shop_id` int(11) DEFAULT NULL,
							  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
							  `total_categories_count` int(11) DEFAULT NULL,
							  `total_products_count` int(11) DEFAULT NULL,
							  `total_buyin_products` int(11) DEFAULT NULL,
							  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products_withqty` int(11) DEFAULT NULL,
							  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products` int(11) DEFAULT NULL,
							  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
							  `total_dropship_products` int(11) DEFAULT NULL,
							  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
							  `dropship_discount` decimal(12,2) NOT NULL COMMENT 'in %',
							  `buyin_discount` decimal(12,2) NOT NULL COMMENT 'in %',
							  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0- pending, 1- accepted, 2-declined',
							  `created_by` int(11) DEFAULT NULL,
							  `created_at` int(11) DEFAULT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_applied_tbl) === TRUE) {
						echo "Table <b>b2b_orders_applied</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_applied_details_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders_applied_details` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `applied_order_id` int(11) DEFAULT NULL,
							  `product_id` int(11) DEFAULT NULL,
							  `parent_id` int(11) DEFAULT NULL,
							  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
							  `quantity` int(11) DEFAULT NULL,
							  `price` decimal(12,2) DEFAULT NULL,
							  `product_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `product_variants` text COLLATE utf8_unicode_ci,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_applied_details_tbl) === TRUE) {
						echo "Table <b>b2b_orders_applied_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_draft_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders_draft` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `supplier_shop_id` int(11) DEFAULT NULL,
							  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
							  `total_categories_count` int(11) DEFAULT NULL,
							  `total_products_count` int(11) DEFAULT NULL,
							  `total_buyin_products` int(11) DEFAULT NULL,
							  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products_withqty` int(11) DEFAULT NULL,
							  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products` int(11) DEFAULT NULL,
							  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
							  `total_dropship_products` int(11) DEFAULT NULL,
							  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
							  `created_by` int(11) DEFAULT NULL,
							  `created_at` int(11) DEFAULT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_draft_tbl) === TRUE) {
						echo "Table <b>b2b_orders_draft</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_draft_details_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders_draft_details` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `draft_order_id` int(11) DEFAULT NULL,
							  `product_id` int(11) DEFAULT NULL,
							  `parent_id` int(11) DEFAULT NULL,
							  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
							  `quantity` int(11) DEFAULT NULL,
							  `price` decimal(12,2) DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_draft_details_tbl) === TRUE) {
						echo "Table <b>b2b_orders_draft_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_saved_tbl = "CREATE TABLE IF NOT EXISTS `b2b_orders_saved` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `supplier_shop_id` int(11) DEFAULT NULL,
							  `total_categories_ids` mediumtext COLLATE utf8_unicode_ci,
							  `total_categories_count` int(11) DEFAULT NULL,
							  `total_products_count` int(11) DEFAULT NULL,
							  `total_buyin_products` int(11) DEFAULT NULL,
							  `total_buyin_cost` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products_withqty` int(11) DEFAULT NULL,
							  `total_virtual_cost_withqty` decimal(12,2) DEFAULT NULL,
							  `total_virtual_products` int(11) DEFAULT NULL,
							  `total_virtual_cost` decimal(12,2) DEFAULT NULL,
							  `total_dropship_products` int(11) DEFAULT NULL,
							  `total_dropship_cost` decimal(12,2) DEFAULT NULL,
							  `created_by` int(11) DEFAULT NULL,
							  `created_at` int(11) DEFAULT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_saved_tbl) === TRUE) {
						echo "Table <b>b2b_orders_saved</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_orders_saved_details_tbl = "CREATE TABLE `b2b_orders_saved_details` (
							  `id` int(11) NOT NULL,
							  `saved_order_id` int(11) DEFAULT NULL,
							  `product_id` int(11) DEFAULT NULL,
							  `parent_id` int(11) DEFAULT NULL,
							  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship	',
							  `quantity` int(11) DEFAULT NULL,
							  `price` decimal(12,2) DEFAULT NULL
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_orders_saved_details_tbl) === TRUE) {
						echo "Table <b>b2b_orders_saved_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_address_tbl = "CREATE TABLE IF NOT EXISTS `b2b_order_address` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `order_id` int(11) NOT NULL,
							  `address_type` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1-billing,2-shipping',
							  `first_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `last_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `mobile_no` int(11) DEFAULT '0',
							  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
							  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_order_address_tbl) === TRUE) {
						echo "Table <b>b2b_order_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_items_tbl = "CREATE TABLE IF NOT EXISTS `b2b_order_items` (
							  `item_id` int(11) NOT NULL AUTO_INCREMENT,
							  `order_id` int(11) NOT NULL,
							  `product_id` int(11) NOT NULL,
							  `parent_product_id` int(11) NOT NULL,
							  `product_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'conf-simple, simple',
							  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `product_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `product_variants` mediumtext COLLATE utf8_unicode_ci,
							  `qty_ordered` int(11) NOT NULL,
							  `qty_scanned` int(11) NOT NULL DEFAULT '0',
							  `price` decimal(12,2) NOT NULL,
							  `total_price` decimal(12,2) NOT NULL,
							  `applied_rule_ids` text COLLATE utf8_unicode_ci,
							  `tax_percent` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `created_by` int(11) NOT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`item_id`)
							) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_order_items_tbl) === TRUE) {
						echo "Table <b>b2b_order_items</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_shipment_tbl = "CREATE TABLE IF NOT EXISTS `b2b_order_shipment` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `order_id` int(11) NOT NULL,
							  `shipment_id` int(11) NOT NULL,
							  `message` mediumtext COLLATE utf8_unicode_ci,
							  `created_by` int(11) NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_order_shipment_tbl) === TRUE) {
						echo "Table <b>b2b_order_shipment</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$b2b_order_shipment_details_tbl = "CREATE TABLE IF NOT EXISTS `b2b_order_shipment_details` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `order_id` int(11) NOT NULL,
							  `order_shipment_id` int(11) NOT NULL,
							  `box_number` int(11) NOT NULL,
							  `weight` float(10,2) DEFAULT NULL,
							  `tracking_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `created_at` int(11) NOT NULL,
							  `created_by` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `updated_by` int(11) DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($b2b_order_shipment_details_tbl) === TRUE) {
						echo "Table <b>b2b_order_shipment_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$banners_tbl = "CREATE TABLE IF NOT EXISTS `banners` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `static_block_id` int(11) NOT NULL,
							  `position` int(5) DEFAULT '0',
							  `heading` mediumtext COLLATE utf8_unicode_ci,
							  `description` text COLLATE utf8_unicode_ci,
							  `type` smallint(2) NOT NULL COMMENT '1-home,2-category,3-others',
							  `category_ids` mediumtext COLLATE utf8_unicode_ci,
							  `button_text` mediumtext COLLATE utf8_unicode_ci,
							  `link_button_to` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `banner_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `created_by` int(11) NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($banners_tbl) === TRUE) {
						echo "Table <b>banners</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$cms_pages_tbl = "CREATE TABLE IF NOT EXISTS `cms_pages` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
							  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `meta_keywords` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `meta_description` text COLLATE utf8_unicode_ci,
							  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-published,2-on hold',
							  `remove_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-not removed, 1- removed',
							  `created_by` int(11) NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($cms_pages_tbl) === TRUE) {
						echo "Table <b>cms_pages</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$contact_us_tbl = "CREATE TABLE IF NOT EXISTS `contact_us` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
										  `customer_id` int(11) DEFAULT '0',
										  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
										  `message` text COLLATE utf8_unicode_ci NOT NULL,
										  `mobile_no` int(11) DEFAULT '0',
										  `created_at` int(11) NOT NULL,
										  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
										  PRIMARY KEY (`id`)
										) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($contact_us_tbl) === TRUE) {
						echo "Table <b>contact_us</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$country_master_tbl = "CREATE TABLE IF NOT EXISTS `country_master` (
											  `country_code` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
											  `country_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
											  PRIMARY KEY (`country_code`),
											  UNIQUE KEY `country_code` (`country_code`),
											  KEY `idx_country_code` (`country_code`)
											) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($country_master_tbl) === TRUE) {
						echo "Table <b>country_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$customers_tbl = "CREATE TABLE IF NOT EXISTS `customers` (
							  `id` int(11) NOT NULL,
							  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `email_id` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `mobile_no` bigint(20) DEFAULT '0',
							  `gender` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `country_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `dob` date DEFAULT NULL,
							  `customer_type_id` int(11) DEFAULT '0',
							  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-Active 2-Blocked',
							  `last_login_at` int(11) DEFAULT '0',
							  `password_reset_date` int(11) DEFAULT '0',
							  `created_at` int(11) NOT NULL DEFAULT '0',
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($customers_tbl) === TRUE) {
						echo "Table <b>customers</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$customers_address_tbl = "CREATE TABLE IF NOT EXISTS `customers_address` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `customer_id` int(11) NOT NULL,
							  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `mobile_no` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
							  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  `is_default` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
							  `is_default_billing` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
							  `is_default_shipping` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
							  `remove_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($customers_address_tbl) === TRUE) {
						echo "Table <b>customers_address</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$customers_type_master_tbl = "CREATE TABLE IF NOT EXISTS `customers_type_master` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `created_by` int(11) NOT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($customers_type_master_tbl) === TRUE) {
						echo "Table <b>customers_type_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$custom_variables_tbl = "CREATE TABLE IF NOT EXISTS `custom_variables` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `value` text COLLATE utf8_unicode_ci NOT NULL,
							  `created_by` int(11) DEFAULT '0',
							  `created_by_type` int(11) DEFAULT '0',
							  `created_at` int(11) DEFAULT '0',
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($custom_variables_tbl) === TRUE) {
						echo "Table <b>custom_variables</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$email_template_tbl = "CREATE TABLE IF NOT EXISTS `email_template` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `email_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
							  `status` tinyint(1) NOT NULL,
							  `created_by` int(11) NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($email_template_tbl) === TRUE) {
						echo "Table <b>email_template</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$fbc_users_attributes_visibility_tbl = "CREATE TABLE IF NOT EXISTS `fbc_users_attributes_visibility` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `attr_id` int(11) NOT NULL,
							  `display_on_frontend` tinyint(4) NOT NULL DEFAULT '0',
							  `filterable_with_results` tinyint(4) NOT NULL DEFAULT '0',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `created_by` int(11) NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($fbc_users_attributes_visibility_tbl) === TRUE) {
						echo "Table <b>fbc_users_attributes_visibility</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$fbc_users_category_b2b_tbl = "CREATE TABLE IF NOT EXISTS `fbc_users_category_b2b` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `category_id` int(11) NOT NULL,
							  `level` tinyint(4) NOT NULL COMMENT '0,1,2',
							  `fbc_user_id` int(11) NOT NULL,
							  `b2b_enabled` tinyint(4) NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($fbc_users_category_b2b_tbl) === TRUE) {
						echo "Table <b>fbc_users_category_b2b</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$login_session_tbl = "CREATE TABLE IF NOT EXISTS `login_session` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `sessionid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `user_id` int(11) NOT NULL,
							  `login_time` int(11) NOT NULL,
							  `logout_time` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($login_session_tbl) === TRUE) {
						echo "Table <b>login_session</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$notifications_tbl = "CREATE TABLE IF NOT EXISTS `notifications` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `from_shop_id` int(11) NOT NULL,
							  `from_fbc_user_id` int(11) NOT NULL,
							  `to_shop_id` int(11) NOT NULL,
							  `to_fbc_user_id` int(11) NOT NULL,
							  `shop_id` int(11) NOT NULL,
							  `area_id` int(11) NOT NULL,
							  `notification_text` mediumtext COLLATE utf8_unicode_ci,
							  `notification_type` tinyint(2) NOT NULL COMMENT '1-b2b_order_request, 2-b2b_order_confirmed',
							  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-Pending, 1-Accepted, 2-Declined',
							  `read_flag` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-unread, 1-read',
							  `visited_flag` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-not visited, 1-visited',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) NOT NULL,
							  `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;;";

					if ($conn->query($notifications_tbl) === TRUE) {
						echo "Table <b>notifications</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_tbl = "CREATE TABLE IF NOT EXISTS `products` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `highlights` mediumtext COLLATE utf8_unicode_ci,
							  `description` longtext COLLATE utf8_unicode_ci,
							  `product_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `barcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `base_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `meta_description` text COLLATE utf8_unicode_ci,
							  `meta_keyword` text COLLATE utf8_unicode_ci,
							  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `price` decimal(12,2) DEFAULT NULL,
							  `cost_price` decimal(12,2) DEFAULT NULL,
							  `special_price` decimal(12,2) DEFAULT NULL,
							  `special_price_from` int(11) DEFAULT NULL,
							  `special_price_to` int(11) DEFAULT NULL,
							  `product_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'simple;configurable,conf-simple',
							  `product_inv_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'buy,virtual,dropship',
							  `parent_id` int(11) DEFAULT '0',
							  `estimate_delivery_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `product_return_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `product_drop_shipment` tinyint(4) DEFAULT NULL COMMENT '1-Allow, 0 - Deny',
							  `product_reviews_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `supplier_id` int(11) DEFAULT NULL,
							  `status` tinyint(4) NOT NULL COMMENT '1-Enabled, 2 - Disabled',
							  `url_key` mediumtext COLLATE utf8_unicode_ci,
							  `launch_date` int(11) DEFAULT NULL,
							  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `shop_id` int(11) DEFAULT '0',
							  `shop_product_id` int(11) DEFAULT NULL,
							  `shop_price` decimal(12,2) DEFAULT NULL,
							  `shop_cost_price` decimal(12,2) DEFAULT NULL,
							  `shop_currency` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `fbc_user_id` int(11) DEFAULT '0',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `imported_from` int(11) DEFAULT NULL,
							  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_tbl) === TRUE) {
						echo "Table <b>products</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_attributes_tbl = "CREATE TABLE IF NOT EXISTS `products_attributes` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `attr_id` int(11) NOT NULL,
							  `attr_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_attributes_tbl) === TRUE) {
						echo "Table <b>products_attributes</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_block_details_tbl = "CREATE TABLE IF NOT EXISTS `products_block_details` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `pb_master_id` int(11) NOT NULL,
							  `assigned_products` longtext COLLATE utf8_unicode_ci NOT NULL,
							  `created_at` int(11) DEFAULT '0',
							  `updated_at` int(11) DEFAULT '0',
							  `created_by` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_block_details_tbl) === TRUE) {
						echo "Table <b>products_block_details</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}


					$products_block_master_tbl = "CREATE TABLE IF NOT EXISTS `products_block_master` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `block_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
							  `block_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_block_master_tbl) === TRUE) {
						echo "Table <b>products_block_master</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_category_tbl = "CREATE TABLE IF NOT EXISTS `products_category` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `category_ids` mediumtext COLLATE utf8_unicode_ci,
							  `level` int(11) DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_category_tbl) === TRUE) {
						echo "Table <b>products_category</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_inventory_tbl = "CREATE TABLE IF NOT EXISTS `products_inventory` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `qty` int(11) NOT NULL,
							  `min_qty` int(11) DEFAULT NULL,
							  `is_in_stock` tinyint(4) DEFAULT NULL COMMENT '1-Yes,2-No',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_inventory_tbl) === TRUE) {
						echo "Table <b>products_inventory</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_media_gallery_tbl = "CREATE TABLE IF NOT EXISTS `products_media_gallery` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `child_id` int(11) DEFAULT NULL,
							  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `image_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `image_position` int(11) DEFAULT NULL,
							  `is_default` tinyint(4) DEFAULT NULL,
							  `is_base_image` tinyint(4) DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_media_gallery_tbl) === TRUE) {
						echo "Table <b>products_media_gallery</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_reviews_tbl = "CREATE TABLE IF NOT EXISTS `products_reviews` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `customer_id` int(11) NOT NULL,
							  `rating` float NOT NULL,
							  `review` longtext COLLATE utf8_unicode_ci NOT NULL,
							  `status` tinyint(1) NOT NULL COMMENT '0-Pending,1-Active,2-Blocked',
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_reviews_tbl) === TRUE) {
						echo "Table <b>products_reviews</b> created successfully.<br>";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_variants_tbl = "CREATE TABLE IF NOT EXISTS `products_variants` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `parent_id` int(11) NOT NULL,
							  `attr_id` int(11) NOT NULL,
							  `attr_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_variants_tbl) === TRUE) {
						echo "Table <b>products_variants</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$products_variants_master_tbl = "CREATE TABLE IF NOT EXISTS `products_variants_master` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `product_id` int(11) NOT NULL,
							  `attr_id` int(11) NOT NULL,
							  `position` int(11) NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($products_variants_master_tbl) === TRUE) {
						echo "Table <b>products_variants_master</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_tbl = "CREATE TABLE IF NOT EXISTS `sales_quote` (
							  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
							  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `checkout_method` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'guest,register,login',
							  `customer_id` int(11) DEFAULT '0',
							  `customer_group_id` int(11) DEFAULT '0',
							  `customer_email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `customer_firstname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `customer_lastname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `applied_rule_ids` text COLLATE utf8_unicode_ci,
							  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `base_discount_amount` decimal(12,2) DEFAULT '0.00',
							  `base_grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `base_shipping_amount` decimal(12,2) DEFAULT '0.00',
							  `base_shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
							  `base_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `base_tax_amount` decimal(12,2) DEFAULT '0.00',
							  `discount_amount` decimal(12,2) DEFAULT '0.00',
							  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `shipping_amount` decimal(12,2) DEFAULT '0.00',
							  `shipping_tax_amount` decimal(12,2) DEFAULT '0.00',
							  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
							  `tax_amount` decimal(12,2) DEFAULT '0.00',
							  `total_qty_ordered` int(11) NOT NULL DEFAULT '0',
							  `customer_is_guest` tinyint(1) DEFAULT '0' COMMENT '0-no,1-yes',
							  `email_sent` tinyint(1) DEFAULT '0' COMMENT '0-n0,1-yes',
							  `created_at` int(11) NOT NULL DEFAULT '0',
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`quote_id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($sales_quote_tbl) === TRUE) {
						echo "Table <b>sales_quote</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_address_tbl = "CREATE TABLE `sales_quote_address` (
							  `address_id` int(11) NOT NULL,
							  `quote_id` int(11) NOT NULL,
							  `address_type` tinyint(1) NOT NULL COMMENT '1-billing,2-shipping',
							  `save_in_address_book` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
							  `customer_address_id` int(11) DEFAULT '0',
							  `first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `mobile_no` int(11) DEFAULT '0',
							  `address_line1` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
							  `address_line2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
							  `city` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
							  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  `pincode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
							  `same_as_billing` tinyint(1) DEFAULT '0' COMMENT '0-no, 1-yes',
							  `created_at` int(11) DEFAULT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($sales_quote_address_tbl) === TRUE) {
						echo "Table <b>sales_quote_address</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$sales_quote_items_tbl = "CREATE TABLE `sales_quote_items` (
							  `item_id` int(11) NOT NULL,
							  `quote_id` int(11) NOT NULL,
							  `product_id` int(11) NOT NULL,
							  `parent_product_id` int(11) DEFAULT '0',
							  `parent_item_id` int(11) DEFAULT '0',
							  `product_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'conf-simple, simple',
							  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `product_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `barcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `product_variants` mediumtext COLLATE utf8_unicode_ci,
							  `qty_ordered` int(11) NOT NULL,
							  `qty_scanned` int(11) DEFAULT '0',
							  `price` decimal(12,2) NOT NULL,
							  `total_price` decimal(12,2) NOT NULL,
							  `applied_rule_ids` text COLLATE utf8_unicode_ci,
							  `tax_percent` decimal(12,2) DEFAULT NULL,
							  `tax_amount` decimal(12,2) DEFAULT NULL,
							  `discount_amount` decimal(12,2) DEFAULT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT NULL,
							  `created_by` int(11) DEFAULT NULL,
							  `created_by_type` tinyint(1) DEFAULT '0' COMMENT '0-frontend, 1-fbc user panel',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($sales_quote_items_tbl) === TRUE) {
						echo "Table <b>sales_quote_items</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$shipment_master_tbl = "CREATE TABLE IF NOT EXISTS `shipment_master` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `status` tinyint(4) NOT NULL COMMENT '1-Active, 2- Disabled',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($shipment_master_tbl) === TRUE) {
						echo "Table <b>shipment_master</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$static_blocks_tbl = "CREATE TABLE IF NOT EXISTS `static_blocks` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `type` smallint(2) NOT NULL COMMENT '1-block,2-header_scripts,3-footer_scripts,4-menus,5-banner,6-other',
							  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
							  `status` tinyint(1) NOT NULL COMMENT '1-published,2-on hold',
							  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-no, 1-yes',
							  `created_by` int(11) NOT NULL,
							  `created_at` int(11) NOT NULL,
							  `updated_at` int(11) DEFAULT '0',
							  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($static_blocks_tbl) === TRUE) {
						echo "Table <b>static_blocks</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$themes_webshops_tbl = "CREATE TABLE IF NOT EXISTS `themes_webshops` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `theme_id` int(11) NOT NULL,
						  `theme_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
						  `theme_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
						  `created_by` int(11) NOT NULL,
						  `current_theme` tinyint(1) DEFAULT NULL,
						  `created_at` int(11) NOT NULL,
						  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($themes_webshops_tbl) === TRUE) {
						echo "Table <b>themes_webshops</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$webshop_payments_tbl = "CREATE TABLE IF NOT EXISTS `webshop_payments` (
						  `id` int(11) NOT NULL,
						  `payment_id` int(11) NOT NULL,
						  `status` tinyint(1) NOT NULL COMMENT '0-details incomplete, 1-details completed',
						  `payment_type_details` text COLLATE utf8_unicode_ci NOT NULL,
						  `gateway_details` text COLLATE utf8_unicode_ci NOT NULL,
						  `created_by` int(11) NOT NULL,
						  `created_at` int(11) NOT NULL,
						  `updated_at` int(11) DEFAULT '0',
						  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

					if ($conn->query($webshop_payments_tbl) === TRUE) {
						echo "Table <b>webshop_payments</b> created successfully.";
					} else {
						echo "Error creating table: " . $conn->error;
					}

					$conn->close();
				} else {
					echo "Error creating database: " . $connection->error;
				}

				$connection->close();
			}
		} else {
			echo "No records found.";
		}
	}

	public function cleanup_sales_quote_data(){

		$shop_id=$this->uri->segment(3);
		$shopData=$this->CommonModel->getShopOwnerData($shop_id);
		//echo "<pre>"; print_r($shopData);

		if(isset($shopData)){
			
			$shop_database_name=$shopData->database_name;
			if(isset($shop_database_name) && $shop_database_name!='')
			{
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$shop_database_name);		
				$this->db = $this->load->database($config_app,TRUE);
				if($this->db->conn_id) {
					//echo 'connected';
				} else {
					redirect(base_url());
				}
			}
			else{
				redirect(base_url());
			}			

			$deleteFlag = 0;
			$substract_days = strtotime('-30 days');
			$guest_sale_quote = $this->UserModel->getSaleQuote($flag = 0, $substract_days);
			
			if(isset($guest_sale_quote) && !empty($guest_sale_quote)){

				$deleteFlag = 1;
				$quote_ids = array();
				foreach($guest_sale_quote as $data){
					$quote_id = $data->quote_id;
					array_push($quote_ids,$quote_id);
				}
				$deletedata = $this->UserModel->deleteData($quote_ids);

			}

			$substract_sixty_days = strtotime('-60 days');
			$Login_sale_quote = $this->UserModel->getSaleQuote($flag = 1, $substract_sixty_days);

			if(isset($Login_sale_quote) && !empty($Login_sale_quote)){

				$deleteFlag = 1;
				$quote_ids = array();
				foreach($Login_sale_quote as $data){
					$quote_id = $data->quote_id;
					array_push($quote_ids,$quote_id);
				}
				$deletedata = $this->UserModel->deleteData($quote_ids);
				
			}
			if($deleteFlag == 1){
				echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted"));
	
			}
			else{
				echo json_encode(array('flag' => 0, 'msg' => "Nothing To Delete"));
			}
		}
		else{
			echo json_encode(array('flag' => 0, 'msg' => "Something Went Wrong!"));
			exit;
		}

	}

	public function login_session(){

		$shop_id=$this->uri->segment(3);
		$shopData=$this->CommonModel->getShopOwnerData($shop_id);

		if(isset($shopData)){
			
			$shop_database_name=$shopData->database_name;
			if(isset($shop_database_name) && $shop_database_name!='')
			{
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$shop_database_name);		
				$this->db = $this->load->database($config_app,TRUE);
				if($this->db->conn_id) {
				} else {
					redirect(base_url());
				}
			}
			else{
				redirect(base_url());
			}
 
			$login_time = strtotime('-30 days');
			$logout_time = strtotime('-7 days');

			$deletedata = $this->UserModel->deleteLoginSession($login_time,$logout_time);
			if($deletedata == true){
				echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted"));
			}
			else{
				echo json_encode(array('flag' => 0, 'msg' => "Nothing To Delete"));
			}			
		}
		else{
			echo json_encode(array('flag' => 0, 'msg' => "Something Went Wrong")); 
			exit();
		}
	}

}
