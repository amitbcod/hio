<?php

namespace App\Services\Trackers;

class ShipmentStatusDTO
{
	public bool $b2b;
	public string $order_id;
	public string $tracker_vendor;
	public string $tracker_id;
	public bool $uses_webhook;
	public ?int $status;
	public string $response;
}
