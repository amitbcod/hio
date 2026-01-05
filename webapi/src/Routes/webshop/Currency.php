<?php



use Psr\Http\Message\ResponseInterface as Response;



use Psr\Http\Message\ServerRequestInterface as Request;











$app->post('/webshop/getCurrencyList', function (Request $request, Response $response, $args){







	$data = $request->getParsedBody();



	extract($data);







	$error='';



	if(empty($shopcode) || empty($shop_id) )



	{



		$error='Please pass all the mandatory values';



	}else{





		$final_arr = array();



		$webshop_obj = new DbCommonFeature();



        $table_name = 'multi_currencies';



		$database_flag = 'own';



        $where = 'status = ? and remove_flag = ?';



        $order_by ='';



        $params = array(1,0);



		$CurrencyList = $webshop_obj->getTableData($shopcode, $table_name, $database_flag, $where, $order_by, $params);



		if($CurrencyList == false)

		{

			$error='Currency not available';

		}else{

			$CurrencyList=$CurrencyList;

		}

	}



	if($error != '' ){



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		$message['currencydata'] = $CurrencyList;



		exit(json_encode($message));



	}else{



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = 'Currency available';



		$message['currencydata'] = $CurrencyList;



		exit(json_encode($message));

	}

});















$app->get('/webshop/get_default_currency', function (Request $request, Response $response, $args){
	$error='';
	$final_arr = array();
	$webshop_obj = new DbCommonFeature();
	$table_name = 'multi_currencies';
	$database_flag = 'own';
	$where = 'is_default_currency = ?';
	$order_by ='';
	$params = array(1);
	$CurrencyList = $webshop_obj->getTableData($table_name, $database_flag, $where, $order_by, $params,'');
	if($CurrencyList == false)
	{

		$error='Currency not available';

	}else{

		$CurrencyList=$CurrencyList[0];

	}



	if($error != '' ){



		$message['statusCode'] = '500';



		$message['is_success'] = 'false';



		$message['message'] = $error;



		$message['currencydata'] = $CurrencyList;



		exit(json_encode($message));



	}else{



		$message['statusCode'] = '200';



		$message['is_success'] = 'true';



		$message['message'] = 'Currency available';



		$message['currencydata'] = $CurrencyList;



		exit(json_encode($message));



	}

});















$app->post('/webshop/getCurrencyById', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if(empty($currency_id))
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();
		$webshop_obj = new DbCommonFeature();
        $table_name = 'multi_currencies';
		$database_flag = 'own';
        $where = 'id = ?';
        $order_by ='';
        $params = array($currency_id);
		$CurrencyList = $webshop_obj->getTableData($table_name, $database_flag, $where, $order_by, $params);
		if($CurrencyList == false)
		{
			$error='Currency not available';
		}else{

			$CurrencyList=$CurrencyList[0];
		}
	}
	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['currencydata'] = $CurrencyList;
		exit(json_encode($message));
	}else{

		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Currency available';
		$message['currencydata'] = $CurrencyList;
		exit(json_encode($message));
	}







});



$app->post('/webshop/updateQuoteCurrenyData', function (Request $request, Response $response, $args){



	$data = $request->getParsedBody();

	extract($data);



	$error='';

	if(empty($shopcode) || empty($shop_id) || empty($quote_id))

	{

		$error='Please pass all the mandatory values';

	}else{

		

		$common_obj=new DbCommonFeature();

		$table='sales_quote';

		$columns = 'currency_name = ?, currency_code_session = ?, currency_conversion_rate = ?, currency_symbol = ?, default_currency_flag = ?';

		$where = ' quote_id = ? ';

		$params = array($currency_name,$currency_code_session,$currency_conversion_rate,$currency_symbol,$default_currency_flag,$quote_id);

		$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);

		if($Row == false){

			$error='Unable to update quote';

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

		$message['message'] = 'Quote Currency updated.';

		exit(json_encode($message));

	}

});

