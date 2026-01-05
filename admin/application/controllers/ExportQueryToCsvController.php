<?php

use League\Csv\Writer;

/**
 * @property CommonModel $CommonModel
 */
class ExportQueryToCsvController extends CI_Controller
{
	private $seller_db;
	private $seller_db_name;
	private $main_db_name;

	public function __construct(){
		parent::__construct();
		$shop_id		=	$this->session->userdata('ShopID');

		$this->main_db_name = DB_DBASE;

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$this->seller_db_name = DB_PREFIX . $FBCData->database_name;
			$config_app = fbc_switch_db_dynamic($this->seller_db_name);
			$this->seller_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}

	}

	public function export_order_commercial_invoice_compressed(){
		$order_id = (int) $this->input->get('order_id');
		if(!$order_id) {
			echo "No Order Id";
			exit;
		}

		$query = "SELECT
			CONCAT(parent.product_code, ' ', soi.product_name) as item,
			'' as empty_column,
			SUM(soi.qty_ordered) as quantity,
			(SELECT attr_value FROM products_attributes WHERE attr_id = 39 AND product_id = soi.parent_product_id) as tariff_code,
			AVG(soi.price) as price,
			'' as total_price,
			(SELECT attr_value FROM products_attributes WHERE attr_id = 41 AND product_id = soi.parent_product_id) as manufacturer_country
		FROM
			sales_order_items soi
		INNER JOIN products AS parent ON soi.parent_product_id = parent.id
		WHERE
			order_id = $order_id
		GROUP BY parent_product_id";

		$result = $this->seller_db->query($query)->result_array();

		$this->outputQueryResultsToCsv($result, "export_order_commercial_invoice_compressed_{$order_id}.csv");
		exit;
	}

	public function export_order_commercial_invoice_detailed(){
		$order_id = (int) $this->input->get('order_id');
		if(!$order_id) {
			echo "No Order Id";
			exit;
		}

		$query = "SELECT
			CONCAT(parent.sku, ' ', soi.product_name) as item,
			'' as empty_column,
			SUM(soi.qty_ordered) as quantity,
			(SELECT attr_value FROM products_attributes WHERE attr_id = 39 AND product_id = soi.parent_product_id) as tariff_code,
			AVG(soi.price) as price,
			'' as total_price,
			(SELECT attr_value FROM products_attributes WHERE attr_id = 38 AND product_id = soi.parent_product_id) as composition,
			(SELECT attr_value FROM products_attributes WHERE attr_id = 41 AND product_id = soi.parent_product_id) as manufacturer_country
		FROM
			sales_order_items soi
		INNER JOIN products AS parent ON soi.product_id = parent.id
		WHERE
			order_id = $order_id
		GROUP BY product_id";

		$result = $this->seller_db->query($query)->result_array();

		$this->outputQueryResultsToCsv($result, "export_order_commercial_invoice_detailed_{$order_id}.csv");
		exit;
	}

	public function export_order_customer_details(){
		$order_id = (int) $this->input->get('order_id');
		if(!$order_id) {
			echo "No Order Id";
			exit;
		}

		$query = "SELECT
			p.id,
			p.sku,
			soi.product_name,
			p.barcode,
			SUM(soi.qty_ordered) AS quantity,
			ROUND(AVG(soi.price),2) AS price,
			(
				SELECT
					attr_options_name
				FROM
					{$this->seller_db_name}.products_attributes pa
					INNER JOIN {$this->main_db_name}.eav_attributes_options ao ON pa.attr_value = ao.id
					INNER JOIN {$this->main_db_name}.eav_attributes a ON pa.attr_id = a.id
				WHERE
					product_id = p.parent_id
					AND a.attr_code = 'collection_name'
			) AS season,
			DATE_FORMAT(FROM_UNIXTIME(p.launch_date), '%d/%m/%Y') AS ReleaseDate,
			(
				SELECT
					attr_value
				FROM
					products_attributes
				WHERE
					attr_id = 39
					AND product_id = soi.parent_product_id) AS tariff_code,
			(
				SELECT
					attr_value
				FROM
					products_attributes
				WHERE
					attr_id = 38
					AND product_id = soi.parent_product_id) AS composition,
			(
				SELECT
					attr_value
				FROM
					products_attributes
				WHERE
					attr_id = 41
					AND product_id = soi.parent_product_id
			) AS manufacturer_country,
			p.price AS euro_base_price,
			(SELECT
			attr_options_name
		FROM
			 products_variants INNER JOIN {$this->main_db_name}.eav_attributes_options ON products_variants.attr_value = {$this->main_db_name}.eav_attributes_options.id
		WHERE
			product_id = p.id AND ({$this->main_db_name}.eav_attributes_options.attr_id = 5 OR  {$this->main_db_name}.eav_attributes_options.attr_id = 6)) as size,
			(SELECT
			attr_options_name
		FROM
			 products_variants INNER JOIN {$this->main_db_name}.eav_attributes_options ON products_variants.attr_value = {$this->main_db_name}.eav_attributes_options.id
		WHERE
			product_id = p.id AND {$this->main_db_name}.eav_attributes_options.attr_id = 4) as color
		FROM
			sales_order_items soi
			INNER JOIN products AS p ON soi.product_id = p.id
		WHERE
			order_id = $order_id
		GROUP BY
			product_id";

		$result = $this->seller_db->query($query)->result_array();

		$this->outputQueryResultsToCsv($result, "export_order_customer_details_{$order_id}.csv");
		exit;
	}

	public function export_sales_order_items_report(){
//		$order_id = (int) $this->input->get('order_id');
//		if(!$order_id) {
//			echo "No Order Id";
//			exit;
//		}

		$query = "SELECT
			o.order_barcode,
			SUBSTR(o.order_barcode,2,1) as customer_type_id,
			FROM_UNIXTIME(o.created_at, '%Y-%m-%d') as `date`,
			o.customer_id,
			CONCAT(o.customer_firstname,' ', o.customer_lastname) as customer_name,
			o.status as order_status,

			i.sku,
			i.barcode,
			i.product_name,
			'' as category,

			i.qty_ordered,
			ROUND(i.price - i.tax_amount - COALESCE(i.discount_amount,0),2) as price_ex_vat,
			ROUND((i.price - i.tax_amount - COALESCE(i.discount_amount,0)) * i.qty_ordered,2) as total_ex_vat


		 FROM sales_order o INNER JOIN sales_order_items i ON o.order_id = i.order_id

		 WHERE `status` NOT IN (3,7);";

		$result = $this->seller_db->query($query)->result_array();

		$this->outputQueryResultsToCsv($result, "sales_order_items_report.csv");
		exit;
	}

	public function export_stock_update_wholesale_princing(){
		$query = "
SELECT
	SUBSTRING_INDEX(sku, ' ', 1) AS `Name`,
	barcode as 'UPC',
	`name` as 'Display Name',
	ROUND(price / 0.85) AS 'Base Price',
	COALESCE((
		SELECT attr_value
		  	FROM products_attributes
			WHERE
				attr_id = 36
				AND products_attributes.product_id = products.parent_id),

				(SELECT
					attr_options_name FROM shopinshop_shop1.products_attributes pa
					INNER JOIN shopinshop.eav_attributes_options ao ON pa.attr_value = ao.id
					INNER JOIN shopinshop.eav_attributes a ON pa.attr_id = a.id
				WHERE
					product_id = products.parent_id
					AND a.attr_code = 'collection_name')) as Season,
					price as consumer_price,
					ROUND(price * 0.8,2) as zin_price
					, '' AS 'Tier 1','' AS 'Tier 1 Reseller', '' AS 'Tier 2', '' AS 'Tier 3', '' AS 'extra discount', 'Euro' AS Currency, '' AS Tags,
					(SELECT
	attr_options_name
FROM
	 products_variants INNER JOIN shopinshop.eav_attributes_options ON products_variants.attr_value = shopinshop.eav_attributes_options.id
WHERE
	product_id = products.id AND shopinshop.eav_attributes_options.attr_id = 4) as Color, '' as Size, '' as 'Shoe Size', date_format(FROM_UNIXTIME(launch_date), '%Y-%m-%d') AS release_date,
					 -- qty,
					 available_qty
FROM
	products AS products
	INNER JOIN products_inventory ON products.id = products_inventory.product_id
WHERE
	barcode != ''
	AND (qty > 0 OR FROM_UNIXTIME(launch_date) > NOW())";

		$result = $this->seller_db->query($query)->result_array();

		$this->outputQueryResultsToCsv($result, "sales_order_items_report.csv");
		exit;
	}

	private function outputQueryResultsToCsv($result, $filename): void
	{
		$writer = Writer::createFromString();
		$writer->insertOne(array_keys($result[0]));
		$writer->insertAll($result);
		$writer->output($filename);
	}

}
