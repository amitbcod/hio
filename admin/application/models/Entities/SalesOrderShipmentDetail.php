<?php

namespace App\models\Entities;

class SalesOrderShipmentDetail
{
	public int $id;
	public int $order_id;
	public int $order_shipment_id;
	public int $box_number;
	public float $weight;
	public string $tracking_id;
	public string $tracking_url;
	public bool $email_sent_flag;
	public string $api_response;
	public bool $pickup_request_status;
	public string $pickup_request_response;
	public int $created_at;
	public int $created_by;
	public int $updated_at;
	public int $updated_by;

	public static function fromArray(array $values): self
	{
		$result = new self;
		foreach($values as $key => $value){
			if(property_exists($result, $key)){
				$result->$key = $value;
			}
		}
		return $result;
	}

}
