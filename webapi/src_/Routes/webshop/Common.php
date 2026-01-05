<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/check_validity_coupon_code', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' || $shop_id=='' || $coupon_code=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$couponData = $webshop_obj->check_validity_coupon_code($shopcode,$coupon_code);	
		if(isset($couponData) && !empty($couponData)){
			$date = strtotime($couponData['end_date']);
		   $newDate =  strtoupper(date('M-j-Y', $date));
		}
		
		if($couponData == false)
		{
			$error='Invalid coupon code.';
		}
		else if((strtotime($couponData['end_date'])<strtotime('today midnight'))){
			$error='This coupon code expired on date'.' : '.$newDate;
		}
	}
	if($error != ''){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Coupon code valid till'.' : '.$newDate;
		exit(json_encode($message));
	}
});

$app->get('/webshop/fbc_users_shop/{shopid}', function (Request $request, Response $response, $args){
   $db_checkout_model = new DbCheckout();
   $result = $db_checkout_model->get_fbc_user_shop_details($args['shopid']);

   if($result === false){
       abort("No result found");
   }

    $message['statusCode'] = '200';
    $message['is_success'] = 'true';
    $message['message'] = 'Data found';
    $message['result'] = $result;
    exit(json_encode($message));
});

$app->post('/webshop/get_table_data', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($table_name  =='' || $database_flag == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$where = (isset($where) && $where != '')?$where:'';
		$order_by = (isset($order_by) && $order_by != '')?$order_by:'';
		$params = (isset($params) && $params != '')?$params:'';
		$select = (isset($select) && $select != '')?$select:'';
		$tableData = $webshop_obj->getTableData($table_name, $database_flag, $where, $order_by, $params, $select);

		if($tableData == false)
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
		$message['message'] = 'Data found';
		$message['tableData'] = $tableData;
		exit(json_encode($message));
	}

});

$app->post('/webshop/basic_product_detail', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($product_id))
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();

		$webshop_obj = new DbProductFeature();

		$ProductData = $webshop_obj->getproductDetailsById($product_id);

		if($ProductData == false)
		{
			$error='Product not available';
		}else{
			$ProductData=$ProductData;
		}
	}

	if($error != '' ){

		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['ProductData'] = $ProductData;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Product available';
		$message['ProductData'] = $ProductData;
		exit(json_encode($message));
	}

});


$app->post('/webshop/cart_count', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($session_id) )
	{
		$error='Please pass all the mandatory values';

	}else{

		$cart_items_total_count=0;
		//$cart_items_total_qty=0;

		$cart_obj = new DbCart();
		if(!empty($quote_id)){
			if(empty($customer_id)){
				$Row=$cart_obj->getCartCountByQuoteId($shopcode,$quote_id);

			}else{
				$Row=$cart_obj->getCartCountByQuoteId($shopcode,$quote_id,$customer_id);
			}
		}else{
			if(empty($customer_id)){
				$Row=$cart_obj->getCartCountBySessionId($shopcode,$session_id);
			}else{
				$Row=$cart_obj->getCartCountBySessionId($shopcode,$session_id,$customer_id);
			}
		}

		if($Row==false){
			$cart_items_total_count=0;
			$error='Cart is Empty';
		}else{
			$cart_items_total_count = $Row['total_count'];
		}

	}

	if($error != '' ){

		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['cart_items_total_count'] = $cart_items_total_count;
		exit(json_encode($message));
	}else{

		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = '';
		$message['cart_items_total_count'] = $cart_items_total_count;
		exit(json_encode($message));
	}

});


$app->post('/webshop/get_webshop_email_template_by_code', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($email_code) )
	{
		$error='Please pass all the mandatory values';

	}else{

		$webshop_obj = new DbCommonFeature();
		$TemplateData = $webshop_obj->getWebShopEmailTemplateByCode($shopcode,$email_code);

		if($TemplateData == false)
		{
			$error='Template not available';
		}else{
			$TemplateData=$TemplateData;
		}
	}

	if($error != '' ){


		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['TemplateData'] = $TemplateData;
		exit(json_encode($message));
	}else{

		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Template available';
		$message['TemplateData'] = $TemplateData;
		exit(json_encode($message));
	}

});

$app->get('/webshop/get_webshop_details', function (Request $request, Response $response, $args){


	$error='';
	$webshop_obj = new DbCommonFeature();
	$getFbcWebshopData = $webshop_obj->getFbcUsersWebShopDetails();

	if($getFbcWebshopData == false)
	{
		$error='No data found';
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Data available';
		$message['FbcWebShopDetails'] = $getFbcWebshopData;
		exit(json_encode($message));
	}

});

$app->get('/webshop/get_website_texts/{shopcode}[/{lang_code}]', function (Request $request, Response $response, $args){
	$shopcode 	= $args['shopcode'];
	$lang_code =  (isset($args['lang_code']) ? $args['lang_code'] : '');

	$error='';
	if($shopcode =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$getFbcWebsiteTexts = $webshop_obj->getFbcUsersWebsiteTexts($shopcode,$lang_code);

		if($getFbcWebsiteTexts == false)
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
		$message['message'] = 'Data available';
		$message['FbcWebsiteTexts'] = $getFbcWebsiteTexts;
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_shop_categories', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $catlog_id=='' || $product_ids_str== '' )
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();
		// $customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

		$webshop_obj = new DbCommonFeature();
		$browseByCategory = $webshop_obj->getAllCategories($shopcode,$shopid,$catlog_id,$product_ids_str); //,$customer_type_id

		if($browseByCategory == false)
		{
			$error='No category found';
		}else{
			foreach ($browseByCategory as $cat) {
				$firstLevelCategory = $webshop_obj->firstLevelCategory($shopcode,$shopid,$cat['id']);

				if($firstLevelCategory != false)
				{
					foreach ($firstLevelCategory as $cat1) {
						$secondLevelCategory = $webshop_obj->secondLevelCategory($shopcode,$shopid,$cat1['id'],$cat['id']);
						if($secondLevelCategory != false)
						{
							foreach ($secondLevelCategory as $cat2) {
								$arr2['category_id'] = $cat2['category_id'];
								$arr2['id'] = $cat2['id'];
								$arr2['cat_name'] = $cat2['cat_name'];
								$arr2['slug'] = $cat2['slug'];
								$arr2['cat_description'] = $cat2['cat_description'];
								$arr2['parent_id'] = $cat2['parent_id'];
								$arr2['main_parent_id'] = $cat2['main_parent_id'];
								$arr2['cat_level'] = $cat2['cat_level'];
								$arr2['shop_id'] = $cat2['shop_id'];

								$cat1['cat_level_2'][] = $arr2;
							}
						}

						$cat['cat_level_1'][] = $cat1;
					}
				}

				$final_arr[] = $cat;
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
		$message['message'] = 'Category available';
		$message['AllCategoryLevels'] = $final_arr;
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_shop_categories1', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $catlog_id=='' || $product_ids_str== '' )
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();
		// $customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

		$webshop_obj = new DbCommonFeature();
		$browseByCategory = $webshop_obj->getAllCategories1($shopcode,$shopid,$catlog_id,$product_ids_str); //,$customer_type_id

		if($browseByCategory == false)
		{
			$error='No category found';
		}else{
			foreach ($browseByCategory as $cat) {
				$firstLevelCategory = $webshop_obj->firstLevelCategory($shopcode,$shopid,$cat['id']);

				if($firstLevelCategory != false)
				{
					foreach ($firstLevelCategory as $cat1) {
						$secondLevelCategory = $webshop_obj->secondLevelCategory($shopcode,$shopid,$cat1['id'],$cat['id']);
						if($secondLevelCategory != false)
						{
							foreach ($secondLevelCategory as $cat2) {
								$arr2['category_id'] = $cat2['category_id'];
								$arr2['id'] = $cat2['id'];
								$arr2['cat_name'] = $cat2['cat_name'];
								$arr2['slug'] = $cat2['slug'];
								$arr2['cat_description'] = $cat2['cat_description'];
								$arr2['parent_id'] = $cat2['parent_id'];
								$arr2['main_parent_id'] = $cat2['main_parent_id'];
								$arr2['cat_level'] = $cat2['cat_level'];
								$arr2['shop_id'] = $cat2['shop_id'];

								$cat1['cat_level_2'][] = $arr2;
							}
						}

						$cat['cat_level_1'][] = $cat1;
					}
				}

				$final_arr[] = $cat;
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
		$message['message'] = 'Category available';
		$message['AllCategoryLevels'] = $final_arr;
		exit(json_encode($message));
	}

});



$app->post('/webshop/get_users_shop_details', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shop_id=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$getFbcWebshopData = $webshop_obj->getFbcUsersShopDetails($shop_id);

		if($getFbcWebshopData == false)
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
		$message['message'] = 'Data available';
		$message['FbcWebShopDetails'] = $getFbcWebshopData;
		exit(json_encode($message));
	}

});


$app->post('/webshop/get_shop_vat_data', function (Request $request, Response $response, $args){


	//echo "scsd";
	//exit;
	$data = $request->getParsedBody();
	extract($data);



	$error='';

	//if($shopcode =='' || $shop_id=='' || $country_code '' )
	if(empty($shopcode) || empty($shop_id) || empty($country_code) )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCommonFeature();
		$getVatData = $webshop_obj->getShopVatDetails($shopcode,$shop_id,$country_code);


	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Data available';
		$message['VatDetails'] = $getVatData;
		exit(json_encode($message));
	}

});

$app->post('/webshop/add_vat_log', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);


	$error='';
	if($shopcode =='' || $shop_id=='' || $vat_no=='' || $request_data =='' ||  $response_data=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$created_at = time();
		$response_data = json_encode($response_data);


		$common_obj=new DbCommonFeature();

		$table ='vat_log';
		$columns = 'type, customer_id, customer_address_id, quote_id, order_id, vat_no, consulation_no, company_name, company_address, request_data, response_data, response_type, created_at, ip';
		$values = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
		$params = array($type,$customer_id,$customer_address_id,$quote_id,$order_id,$vat_no,$consulation_no,$company_name,$company_address,$request_data,$response_data,$response_type,$created_at,$ip);
		$Row = $common_obj->add_row($shopcode, $table, $columns, $values, $params);

		if($Row == false)
		{
			$error='Unable to add vat log.';
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
		$message['message'] = 'Vat Logging done.';
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_default_language', function (Request $request, Response $response, $args) {
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	
	if(empty($shopcode) || empty($shop_id) ) {
		$error='Please pass all the mandatory values';
	} else {
		$final_arr = array();
		$webshop_obj = new DbCommonFeature();
		$table_name = 'multi_languages';
		$database_flag = 'own';
		$where = 'is_default_language = ?';
		$order_by ='';
		$params = array(1);

		$LanguageList = $webshop_obj->getTableData($shopcode, $table_name, $database_flag, $where, $order_by, $params);
		if($LanguageList == false)
		{	
			$error='Language not available';	
		}else {
			$LanguageList=$LanguageList[0];
		}
	}
	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['languagedata'] = $LanguageList;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Language available';
		$message['languagedata'] = $LanguageList;
		exit(json_encode($message));
	}
});

$app->post('/webshop/add_email_notified', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shop_id=='' || $email=='' || $product_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$created_at = time();
		$updated_at = time();
		$common_obj=new DbCommonFeature();
		$table ='products_keep_me_notify';
		// check already exit
		$where = 'product_id = ? AND email_id = ?';
		$order_by ='';
		$params = array($product_id,$email);
		$database_flag='own';
		$tableData = $common_obj->getTableData($shopcode, $table, $database_flag, $where, $order_by, $params);

		if (!empty($tableData) && $tableData!='') {
			if(isset($tableData) && isset($tableData[0])){
				$notifiedData=$tableData[0];
				$notified_id=$notifiedData['id'];
				$notify_count=$notifiedData['notify_count'] + 1;
				if($notified_id!=''){
					$columnsUpdate = 'notify_count=?, updated_at=?, ip=?';
					$whereUpdate = 'id = ? ';
					$paramsUpdate = array($notify_count,$updated_at,$ip,$notified_id);
					$RowUpdate = $common_obj->update_row($shopcode, $table, $columnsUpdate, $whereUpdate, $paramsUpdate);
				}
			}

		}else{

			$columns = 'email_id, customer_id, product_id, created_at, ip';
			$values = '?, ?, ?, ?, ?';
			$params = array($email,$customer_id,$product_id,$created_at,$ip);
			$Row = $common_obj->add_row($shopcode, $table, $columns, $values, $params);
			//print_r($Row);exit();
			if($Row == false)
			{
				$error='Unable to add notified email.';
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
		$message['message'] = 'Notified email done.';
		exit(json_encode($message));
	}

});
