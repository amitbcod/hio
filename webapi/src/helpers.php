<?php

function abort(string $errorMessage)
{
	exit(
		json_encode([
			'statusCode' => '500',
			'is_success' => 'false',
			'message' => $errorMessage,
		])
	);
}

function calculate_price_with_vat($price, $vat_percentage)
{
	return $price * (1 + ($vat_percentage / 100));
}

function array_group($input_array, $column_name) : array
{
	$output_array = [];

	foreach ($input_array as $array_element) {
		$output_array[$array_element[$column_name]][] = $array_element;
	}

	return $output_array;
}

function array_key_by($input_array, $column_name) : array
{
	$output_array = [];

	foreach ($input_array as $array_element) {
		$output_array[$array_element[$column_name]] = $array_element;
	}

	return $output_array;

}

function getOrderIdwithExtrazero($numbers) {	
  for($i = $numbers; $i <= $numbers; $i++){
    $numbers = str_pad($i, 4, '0', STR_PAD_LEFT);
   }
   return $numbers;
} 

function getRandomLetters($n) {	
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}
