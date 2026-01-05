<?php



use Psr\Http\Message\ResponseInterface as Response;



use Psr\Http\Message\ServerRequestInterface as Request;



$app->post('/webshop/get_customer_signup_otp', function (Request $request, Response $response) {

	$posted_data = $request->getParsedBody();

	extract($posted_data);

	$error = '';

	$webshop_obj = new DbCommonFeature();

	$customer_signup_otp_data = $webshop_obj->get_customer_signup_otp($mobile_no);

	if ($customer_signup_otp_data == false) {

		$error = 'data not found';

	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Customer Signup Otp';

		$message['customer_signup_otp_data'] = $customer_signup_otp_data;

		exit(json_encode($message));

	}

});



$app->post('/webshop/customer_signup_otp', function (Request $request, Response $response) {

	$posted_data = $request->getParsedBody();

	extract($posted_data);

	$error = '';

	if (empty($mobile_no) || empty($otp)) {

		$error = 'Please enter all mandatory / compulsory fields.';

	} else {

		$webshop_obj = new DbCommonFeature();

		$created_at = time();

		$ip = $_SERVER['REMOTE_ADDR'];



		$table = 'customer_signup_otp';



		$columns = 'mobile_no, otp, created_at, ip';

		$values = '?, ?, ?, ?';

		$params = array($mobile_no, $otp, $created_at, $ip);

		$customer_signup_otp = $webshop_obj->add_row($table, $columns, $values, $params);

		if ($customer_signup_otp == false) {

			$error = 'Error while submit OTP. please try again.';

		} else {

			$msg = 'OTP submit successfully.';

		}

	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = $msg;

		exit(json_encode($message));

	}

});





$app->post('/webshop/customer_feedback', function (Request $request, Response $response) {

	$posted_data = $request->getParsedBody();

	extract($posted_data);

	$error = '';

	if (empty($email)) {

		$error = 'Please enter all mandatory / compulsory fields.';

	} else if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)) {

		$error = 'Please enter a valid Email address.';

	} else {

		$webshop_obj = new DbCommonFeature();

		$customer_id = ((isset($customer_id) && $customer_id != '') ? $customer_id : 0);

		$name = ((isset($name) && $name != '') ? $name : '');

		$email = ((isset($email) && $email != '') ? $email : '');

		$where_here_abou_us = (isset($where_here_abou_us) && $where_here_abou_us != '') ? $where_here_abou_us : '';

		$details = (isset($details) && $details != '') ? $details : '';

		$created_at = time();

		$ip = $_SERVER['REMOTE_ADDR'];



		$table = 'customer_feedback';



		$columns = 'customer_id, name, email, where_here_abou_us, details, created_at, ip';

		$values = '?, ?, ?, ?, ?, ?, ?';

		$params = array($customer_id, $name, $email, $where_here_abou_us, $details, $created_at, $ip);

		$addCustomerAddress = $webshop_obj->add_row($table, $columns, $values, $params);

		if ($addCustomerAddress == false) {

			$error = 'Error while submit Feedback. please try again.';

		} else {

			$msg = 'Feedback submit successfully.';

		}

	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = $msg;

		exit(json_encode($message));

	}

});







$app->post('/webshop/customer_get_personal_info', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);

	$error = '';

	if ($customer_id == '') {

		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();

		$customerData = $webshop_obj->getCustomerDetailById($customer_id);



		if ($customerData == false) {

			$error = 'No data found';

		} else {



			$percentage_point = 0;

			$percentage_point += ($customerData['first_name'] != '') ? 20 : 0;

			$percentage_point += ($customerData['last_name'] != '') ? 20 : 0;

			$percentage_point += ($customerData['email_id'] != '') ? 20 : 0;

			$percentage_point += ($customerData['country_code'] != '') ? 10 : 0;

			$percentage_point += ($customerData['mobile_no'] != '') ? 10 : 0;

			$percentage_point += ($customerData['gender'] != '') ? 10 : 0;

			$percentage_point += ($customerData['dob'] != '') ? 10 : 0;

			$profile_percentage = round($percentage_point);

		}

	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data found';

		$message['customerData'] = $customerData;

		$message['profile_percentage'] = $profile_percentage;

		exit(json_encode($message));

	}

});





$app->post('/webshop/customer_email_exits', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);

	$error = '';



	if ($email_id == '') {

		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();

		$customerData = $webshop_obj->CustomerDetailsByEmailId($email_id);

		if ($customerData == false) {

			$error = 'No customer found';

		}

	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Customer available';

		$message['customerData'] = $customerData;

		exit(json_encode($message));

	}

});





$app->post('/webshop/change_email', function (Request $request, Response $response) {



	$posted_data = $request->getParsedBody();

	extract($posted_data);

	$error = '';

	if (empty($email)) {

		$error = 'Please enter all mandatory / compulsory fields.';

	} else if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)) {

		$error = 'Please enter a valid Email address.';

	} else {

		$webshop_obj = new DbCommonFeature();

		$update_email = $webshop_obj->update_email($email, $customer_id);

	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = "Email changed successfully.";

		exit(json_encode($message));

	}

});







$app->post('/webshop/customer_update_personal_info', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);

	$error = '';

	if ($customer_id == '' || $first_name == '' || $last_name == '') {

		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();

		$time = time();

		$ip = (isset($ip) && $ip != '') ? $ip : $_SERVER['REMOTE_ADDR'];

		$gender = (isset($gender) && $gender != '') ? $gender : '';

		//$mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';

		$country_code = (isset($country_code) && $country_code != '') ? $country_code : '';

		$dob = (isset($dob) && $dob != '') ? $dob : null;

		$company_name = (isset($company_name) && $company_name != '') ? $company_name : '';

		$gst_no = (isset($gst_no) && $gst_no != '') ? $gst_no : '';

		$table = 'customers';



		$update_column = 'first_name = ?,last_name = ?, gender = ?,  country_code = ?, dob = ?,company_name = ?, gst_no = ?, updated_at = ?, ip = ?';

		$where = 'id = ?';

		$params = array($first_name, $last_name, $gender, $country_code, $dob, $company_name, $gst_no, $time, $ip, $customer_id);



		$update_customer = $webshop_obj->update_row($table, $update_column, $where, $params);



		if ($update_customer == 1) {

			$error = 'No data found';

		}

	}





	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data updated successfully';

		exit(json_encode($message));

	}

});







$app->post('/webshop/customer_address_add_edit', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);

	$error = '';

	if ($customer_id == '' || $first_name == '' || $last_name == '' || $address_line1 == '' || $city == '' || $country_code == '' || $pincode == '') {

		$error = 'Please pass all the mandatory values';

	} else {

		$webshop_obj = new DbCommonFeature();

		$time = time();

		$ip = $_SERVER['REMOTE_ADDR'];

		$company_name = ((isset($company_name) && $company_name != '') ? $company_name : '');

		$vat_no = ((isset($vat_no) && $vat_no != '') ? $vat_no : '');

		$customer_address_id = (isset($customer_address_id) && $customer_address_id != '') ? $customer_address_id : '';

		$mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';

		$address_line2 = (isset($address_line2) && $address_line2 != '') ? $address_line2 : '';

		$state = (isset($state) && $state != '') ? $state : '';
		$latitude = (isset($latitude) && $latitude != '') ? $latitude : '';
		$longitude = (isset($longitude) && $longitude != '') ? $longitude : '';

		$table = 'customers_address';

		if (empty($customer_address_id)) {

			$columns = 'customer_id, first_name, last_name, mobile_no, address_line1, address_line2, latitude, longitude, city, state, country, pincode, company_name, vat_no, created_at, ip';

			$values = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';

			$params = array($customer_id, $first_name, $last_name, $mobile_no, $address_line1, $address_line2, $latitude, $longitude, $city, $state, $country_code, $pincode, $company_name, $vat_no, $time, $ip);

			$addCustomerAddress = $webshop_obj->add_row($table, $columns, $values, $params);

			if ($addCustomerAddress == false) {

				$error = 'Error while adding address. please try again.';

			} else {

				$msg = 'Address added successfully.';

			}

		} else {

			$update_column = 'customer_id = ?, first_name = ?, last_name = ?, mobile_no = ?, address_line1 = ?, address_line2 = ?,latitude = ?, longitude = ?, city = ?, state = ?, country = ?,  pincode = ?, company_name = ?, vat_no = ?, updated_at = ?, ip = ?';

			$where = 'id = ?';

			$params = array($customer_id, $first_name, $last_name, $mobile_no, $address_line1, $address_line2, $latitude, $longitude, $city, $state, $country_code, $pincode, $company_name, $vat_no, $time, $ip, $customer_address_id);

			$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);

			if ($updateCustomer == 1) {

				$error = 'No data found';

			} else {

				$msg = 'Address updated successfully.';

			}

		}

	}







	if ($error != '') {



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = $msg;



		exit(json_encode($message));

	}

});







$app->post('/webshop/customer_address_delete', function (Request $request, Response $response, $args) {







	$data = $request->getParsedBody();



	extract($data);







	$error = '';



	if ($customer_id == '' || $customer_address_id == '') {



		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();



		$time = time();



		$ip = $_SERVER['REMOTE_ADDR'];



		$table = 'customers_address';



		$update_column = 'remove_flag = ?, updated_at = ?, ip = ?';



		$where = 'id = ? AND customer_id = ?';



		$params = array(1, $time, $ip, $customer_address_id, $customer_id);



		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);







		if ($updateCustomer == 1) {



			$error = 'No data found';

		}

	}







	if ($error != '') {



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = 'Address deleted successfully';



		exit(json_encode($message));

	}

});







$app->post('/webshop/customer_address_setdefault', function (Request $request, Response $response, $args) {







	$data = $request->getParsedBody();



	extract($data);







	$error = '';



	if ($customer_id == '' || $customer_address_id == '') {



		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();



		$time = time();



		$ip = $_SERVER['REMOTE_ADDR'];



		$table = 'customers_address';







		$update_column = 'is_default = ?, updated_at = ?, ip = ?';



		$where = 'customer_id = ?';



		$params = array(0, $time, $ip, $customer_id);



		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);







		$update_column = 'is_default = ?, updated_at = ?, ip = ?';



		$where = 'id = ? AND customer_id = ?';



		$params = array(1, $time, $ip, $customer_address_id, $customer_id);



		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);







		if ($updateCustomer == 1) {



			$error = 'No data found';

		}

	}







	if ($error != '') {



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = 'Default set successfully';



		exit(json_encode($message));

	}

});











$app->post('/webshop/update_vatdetails_checkout', function (Request $request, Response $response, $args) {







	$data = $request->getParsedBody();



	extract($data);







	$error = '';



	if ($shopcode == '' || $shopid == '' || $customer_id == '' || $customer_address_id == '') {



		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();



		$time = time();



		$ip = $_SERVER['REMOTE_ADDR'];



		$table = 'customers_address';















		$update_column = 'company_name = ?, vat_no = ?, consulation_no = ?, res_company_name = ?, res_company_address = ?, updated_at = ?';



		$where = 'id = ? AND customer_id = ?';



		$params = array($company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $time, $customer_address_id, $customer_id);



		$updateCustomer = $webshop_obj->update_row($shopcode, $table, $update_column, $where, $params);







		if ($updateCustomer == 1) {



			$error = 'No data found';

		}

	}







	if ($error != '') {



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		exit(json_encode($message));

	} else {



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = 'Vat Data Updated successfully';



		exit(json_encode($message));

	}

});



$app->post('/webshop/get_customer_address', function (Request $request, Response $response, $args) {







	$data = $request->getParsedBody();



	extract($data);







	$error = '';



	if ($customer_address_id == '') {



		$error = 'Please pass all the mandatory values';

	} else {



		$webshop_obj = new DbCommonFeature();



		$customerData = $webshop_obj->getCustomerAddressById($customer_address_id);

		if ($customerData == false) {

			$error = 'No customer found';

		}

	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Customer address found available';

		$message['customerData'] = $customerData;

		exit(json_encode($message));

	}

});

