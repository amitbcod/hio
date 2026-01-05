<?php

namespace App\Services\Trackers;

class ShipmentStatusEnum
{
	public const UNKONWN = 1;
	public const PRE_TRANSIT = 2;
	public const IN_TRANSIT = 3;
	public const OUT_FOR_DELIVERY = 4;
	public const DELIVERED = 5;
	public const AVAILABLE_FOR_PICKUP = 6;
	public const RETURN_TO_SENDER = 7;
	public const FAILURE = 8;
	public const CANCELLED = 9;
	public const ERROR = 10;

	public static function label(int $value): string
	{
		return [
			self::UNKONWN => 'Unknown',
			self::IN_TRANSIT => 'In Transit',
			self::PRE_TRANSIT => 'Pre Transit',
			self::OUT_FOR_DELIVERY => 'Out For Delivery',
			self::DELIVERED => 'Delivered',
			self::AVAILABLE_FOR_PICKUP => 'Available For Pickup',
			self::RETURN_TO_SENDER => 'Return To Sender',
			self::FAILURE => 'Failure',
			self::CANCELLED => 'Cancelled',
			self::ERROR => 'Error',
		][$value] ?? 'Undefined';
	}

	public static function fromValue(?string $status): ?int
	{
		return [
			"unknown" => self::UNKONWN,
			"pre_transit" => self::PRE_TRANSIT,
			"in_transit" => self::IN_TRANSIT,
			"out_for_delivery" => self::OUT_FOR_DELIVERY,
			"delivered" => self::DELIVERED,
			"available_for_pickup" => self::AVAILABLE_FOR_PICKUP,
			"return_to_sender" => self::RETURN_TO_SENDER,
			"failure" => self::FAILURE,
			"cancelled" => self::CANCELLED,
			"error" => self::ERROR,
		][$status] ?? null;
	}

	public static function labelList()
	{
		return [
			self::UNKONWN => 'Unknown',
			self::IN_TRANSIT => 'In Transit',
			self::PRE_TRANSIT => 'Pre Transit',
			self::OUT_FOR_DELIVERY => 'Out For Delivery',
			self::DELIVERED => 'Delivered',
			self::AVAILABLE_FOR_PICKUP => 'Available For Pickup',
			self::RETURN_TO_SENDER => 'Return To Sender',
			self::FAILURE => 'Failure',
			self::CANCELLED => 'Cancelled',
			self::ERROR => 'Error',
		];
	}

}
