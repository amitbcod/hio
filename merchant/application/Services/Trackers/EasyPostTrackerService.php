<?php

namespace App\Services\Trackers;

use EasyPost\EasyPost;
use EasyPost\Tracker;

class EasyPostTrackerService
{
	public const USES_WEBHOOK = true;
	public const VENDOR = 'EasyPost';

	public function create_tracker($tracking_code, $reference = null)
	{
		EasyPost::setApiKey(EASY_POST_API_KEY);

		$tracker_request = [
			"tracking_code" => $tracking_code,
		];

		if(strpos($tracking_code, '1Z') === 0){
			$tracker_request['carrier'] = 'UPS';
		}

		if(!empty($reference)){
			$tracker_request['reference'] = $reference;
		}

		/** @var Tracker $tracker */
		$tracker = Tracker::create($tracker_request);

		return $this->dto($tracker);
	}

	public function parse_tracker_webhook(string $body){
		$tracker = Tracker::retrieve(json_decode($body)->result->id, EASY_POST_API_KEY);

		return $this->dto($tracker);
	}

	private function dto(Tracker $tracker)
	{
		$dto = new ShipmentStatusDTO();

		$dto->response = json_encode($tracker);
		$dto->status = ShipmentStatusEnum::fromValue($tracker->status);
		$dto->tracker_id = $tracker->id;
		$dto->tracker_vendor = self::VENDOR;
		$dto->uses_webhook = self::USES_WEBHOOK;

		return $dto;
	}
}
