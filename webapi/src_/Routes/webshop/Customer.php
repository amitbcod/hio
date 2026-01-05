<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/customer_get_personal_info', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($customer_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$customerData = $webshop_obj->getCustomerDetailById($customer_id);

		if($customerData == false)
		{
			$error='No data found';
		}else{

			$percentage_point = 0;
			$percentage_point += ($customerData['first_name']!='') ? 20 : 0;
			$percentage_point += ($customerData['last_name']!='') ? 20 : 0;
			$percentage_point += ($customerData['email_id']!='') ? 20 : 0;
			$percentage_point += ($customerData['country_code']!='') ? 10 : 0;
			$percentage_point += ($customerData['mobile_no']!='') ? 10 : 0;
			$percentage_point += ($customerData['gender']!='') ? 10 : 0;
			$percentage_point += ($customerData['dob']!='') ? 10 : 0;
			$profile_percentage = round($percentage_point);
		}

	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Data found';
		$message['customerData'] = $customerData;
		$message['profile_percentage'] = $profile_percentage;
		exit(json_encode($message));
	}

});

$app->post('/webshop/customer_email_exits', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);
	$error='';

	
	if($email_id=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$webshop_obj = new DbCommonFeature();
		$customerData = $webshop_obj->CustomerDetailsByEmailId($email_id);
		if($customerData == false)
		{
			$error='No customer found';
		}
	}
	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
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
	$error='';
	if(empty($email) ){
		$error='Please enter all mandatory / compulsory fields.';
	}else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	}
	else{
		$webshop_obj = new DbCommonFeature();
		$update_email = $webshop_obj->update_email($email,$customer_id);
	}

	if($error != '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['message'] = "Email changed successfully.";
		exit(json_encode($message));
   	}
});

$app->post('/webshop/customer_update_personal_info', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);
	$error='';	
	if($customer_id =='' || $first_name =='' || $last_name =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();

		$time = time();
		$ip=$_SERVER['REMOTE_ADDR'];
		$gender = (isset($gender) && $gender != '') ? $gender : '';
		$mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';
		$country_code = (isset($country_code) && $country_code != '') ? $country_code : '';
		$dob = (isset($dob) && $dob != '' ) ? $dob : null;
		$company_name = (isset($company_name) && $company_name != '' ) ? $company_name : '';
		$gst_no = (isset($gst_no) && $gst_no != '' ) ? $gst_no : '';
		$table = 'customers';

		$update_column = 'first_name = ?,last_name = ?, gender = ?, mobile_no = ?, country_code = ?, dob = ?,company_name = ?, gst_no = ?, updated_at = ?, ip = ?';
		$where = 'id = ?';
		$params = array($first_name, $last_name, $gender, $mobile_no, $country_code, $dob,$company_name,$gst_no, $time, $ip,$customer_id);

		$update_customer = $webshop_obj->update_row($table,$update_column,$where,$params);

		if($update_customer == 1)
		{
			$error='No data found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Data updated successfully';
		exit(json_encode($message));
	}

});

$app->post('/webshop/customer_address_add_edit', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($customer_id =='' || $first_name =='' || $last_name =='' || $address_line1 =='' || $city == '' || $country_code == '' || $pincode == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$time = time();
		$ip=$_SERVER['REMOTE_ADDR'];

		$company_name = ((isset($company_name) && $company_name !='')?$company_name :'');
		$vat_no = ((isset($vat_no) && $vat_no !='')?$vat_no :'');
		$consulation_no = ((isset($consulation_no) && $consulation_no !='')?$consulation_no :'');
		$res_company_name = ((isset($res_company_name) && $res_company_name !='')?$res_company_name :'');
		$res_company_address = ((isset($res_company_address) && $res_company_address !='')?$res_company_address :'');


		$customer_address_id = (isset($customer_address_id) && $customer_address_id != '') ? $customer_address_id : '';
		$mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';
		$address_line2 = (isset($address_line2) && $address_line2 != '') ? $address_line2 : '';
		$table = 'customers_address';
		if(empty($customer_address_id)){

			$columns = 'customer_id, first_name, last_name, mobile_no, address_line1, address_line2, city, state, country, pincode, company_name, vat_no, consulation_no, res_company_name, res_company_address, created_at, ip';
			$values = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
			$params = array($customer_id, $first_name, $last_name, $mobile_no, $address_line1, $address_line2, $city, $state, $country_code, $pincode, $company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $time, $ip);
			$addCustomerAddress = $webshop_obj->add_row($table, $columns, $values, $params);

			if($addCustomerAddress == false){
				$error='Error while adding address. please try again.';
			}else{
				$msg = 'Address added successfully.';
			}
		}else{
			$update_column = 'customer_id = ?, first_name = ?, last_name = ?, mobile_no = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, country = ?,  pincode = ?, company_name = ?, vat_no = ?, consulation_no = ?, res_company_name = ?, res_company_address = ?, updated_at = ?, ip = ?';
			$where = 'id = ?';
			$params = array($customer_id, $first_name, $last_name, $mobile_no, $address_line1, $address_line2, $city, $state, $country_code, $pincode, $company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $time, $ip, $customer_address_id);
			$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);
			if($updateCustomer == 1){
				$error='No data found';
			}else{
				$msg = 'Address updated successfully.';
			}
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = $msg;
		exit(json_encode($message));
	}

});

$app->post('/webshop/customer_address_delete', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($customer_id =='' || $customer_address_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$time = time();
		$ip=$_SERVER['REMOTE_ADDR'];
		$table = 'customers_address';
		$update_column = 'remove_flag = ?, updated_at = ?, ip = ?';
		$where = 'id = ? AND customer_id = ?';
		$params = array(1, $time, $ip, $customer_address_id, $customer_id);
		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);

		if($updateCustomer == 1){
			$error='No data found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Address deleted successfully';
		exit(json_encode($message));
	}

});

$app->post('/webshop/customer_address_setdefault', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($customer_id =='' || $customer_address_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$time = time();
		$ip=$_SERVER['REMOTE_ADDR'];
		$table = 'customers_address';

		$update_column = 'is_default = ?, updated_at = ?, ip = ?';
		$where = 'customer_id = ?';
		$params = array(0, $time, $ip, $customer_id);
		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);

		$update_column = 'is_default = ?, updated_at = ?, ip = ?';
		$where = 'id = ? AND customer_id = ?';
		$params = array(1, $time, $ip, $customer_address_id, $customer_id);
		$updateCustomer = $webshop_obj->update_row($table, $update_column, $where, $params);

		if($updateCustomer == 1){
			$error='No data found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Default set successfully';
		exit(json_encode($message));
	}

});


$app->post('/webshop/update_vatdetails_checkout', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $customer_id =='' || $customer_address_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$time = time();
		$ip=$_SERVER['REMOTE_ADDR'];
		$table = 'customers_address';



		$update_column = 'company_name = ?, vat_no = ?, consulation_no = ?, res_company_name = ?, res_company_address = ?, updated_at = ?';
		$where = 'id = ? AND customer_id = ?';
		$params = array($company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $time, $customer_address_id, $customer_id);
		$updateCustomer = $webshop_obj->update_row($shopcode, $table, $update_column, $where, $params);

		if($updateCustomer == 1){
			$error='No data found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Vat Data Updated successfully';
		exit(json_encode($message));
	}

});
