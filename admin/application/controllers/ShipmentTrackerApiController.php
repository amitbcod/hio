<?php

use App\models\Entities\SalesOrderShipmentDetail;
use App\models\ShipmentStatusModel;
use App\Services\Trackers\EasyPostTrackerService;

/**
 * @property WebshopOrdersModel $WebshopOrdersModel
 */
class ShipmentTrackerApiController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('WebshopOrdersModel');
	}

	public function handle(){
		$shipment_details = $this->WebshopOrdersModel->getSingleDataByID('sales_order_shipment_details', ['id'=> $_POST['shipment_detail_id']],'');
//		$shipment_details = SalesOrderShipmentDetail::fromArray($shipment_details);

		if(strpos($shipment_details->tracking_id, '1Z') !== 0){
			// currently only supporting UPS valid tracking nrs
			return;
		}

		$tracker = (new EasyPostTrackerService())->create_tracker($shipment_details->tracking_id);
		(new ShipmentStatusModel($_POST['shop_id']))->insert_tracker($shipment_details, $tracker);

	}

	public function handleExistingShipments(){
		$ids = explode(',',$_POST['ids']);
		foreach($ids as $id){
			$shipment_details = $this->WebshopOrdersModel->getSingleDataByID('sales_order_shipment_details', ['id'=> $id],'');

			if(strpos($shipment_details->tracking_id, '1Z') !== 0){
				return;
			}

			$tracker = (new EasyPostTrackerService())->create_tracker($shipment_details->tracking_id);
			(new ShipmentStatusModel(1))->insert_tracker($shipment_details, $tracker);
		}
	}

}
