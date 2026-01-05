<?php

namespace App\Services\Trackers;

class DhlTrackerService
{
	public const USES_WEBHOOK = false;

	public function track_package($tracking_code, $reference = null){

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api-test.dhl.com/track/shipments?trackingNumber=$tracking_code",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"DHL-API-Key: " . DHL_TRACKING_API_KEY
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}
	}


}
