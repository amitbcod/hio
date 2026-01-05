<?php

use App\models\ShipmentStatusModel;
use App\Services\Trackers\EasyPostTrackerService;

class ShipmentTrackerWebhookController extends CI_Controller
{
	public function easypost_webhook(){
		$shop_id =  $this->uri->segment(4);
		$body = $this->input->raw_input_stream;

		$tracker_result = (new EasyPostTrackerService())->parse_tracker_webhook($body);

		(new ShipmentStatusModel($shop_id))->update_tracker($tracker_result, $body);
	}
}
