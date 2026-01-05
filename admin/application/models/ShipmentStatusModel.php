<?php

namespace App\models;

use App\models\Entities\SalesOrderShipmentDetail;
use App\Services\Trackers\ShipmentStatusDTO;
use CI_Model;

class ShipmentStatusModel extends CI_Model
{
	private $seller_db;

	public function __construct($shop_id)
	{
		parent::__construct();

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop', ['shop_id'=>$shop_id],'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);
			if(!$this->seller_db->conn_id) {
				redirect(base_url());
			}
		} else {
			redirect(base_url());
		}
	}

	public function insert_tracker($shipment_detail, ShipmentStatusDTO $trackerStatus, $b2b = false) {
		$this->seller_db->insert('shipment_detail_status', [
			'b2b' => $b2b,
			'order_id' => $shipment_detail->order_id,
			'shipment_id' => $shipment_detail->order_shipment_id,
			'shipment_detail_id' => $shipment_detail->id,
			'tracker_vendor' => $trackerStatus->tracker_vendor,
			'tracker_id' => $trackerStatus->tracker_id,
			'uses_webhook' => $trackerStatus->uses_webhook,
			'status' => $trackerStatus->status,
			'response' => $trackerStatus->response,
		]);
	}

	public function update_tracker(ShipmentStatusDTO $trackerStatus, $original_body) {
		$this->seller_db->where(['tracker_id' => $trackerStatus->tracker_id]);
		$this->seller_db->update('shipment_detail_status', [
			'status' => $trackerStatus->status,
			'response' => $original_body,
		]);
	}
}
