<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/save_search_term', function (Request $request, Response $response) {

	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error = '';
	$msg = "";

	if (empty($search_term)) {
		$error = 'Please enter all mandatory / compulsory fields.';
	} else if (strlen($search_term) < 2) {
		$error = 'Please enter two letters.';
	} else {
		$webshop_obj = new DbSearchFeature();
		$str = strlen($search_term);

		$Record = $webshop_obj->getSearchTermBySearch($search_term);
		if ($Record != false && $Record > 0) {
			$id = $Record['id'];
			$popularity = $Record['popularity'];
			$update_search = $webshop_obj->update_search_term($id, $popularity);
			$msg = "Search updated.";
		} else {
			if ($str >= 2) {
				$save_search = $webshop_obj->save_search_term($search_term);
				$msg = "Search saved.";
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


$app->get('/webshop/get_search_terms/{search_term}', function (Request $request, Response $response, $args) {
	echo $search_term = $args['search_term'];
	$error = '';
	if ($search_term == '') {
		$error = 'Please pass all the mandatory values';
	} else if (strlen($search_term) < 2) {
		$error = 'Please enter two letters.';
	} else {
		$webshop_obj = new DbSearchFeature();
		$common_obj = new DbCommonFeature();
		$product_obj = new DbProductFeature();

		$test_search = urlencode($search_term) . "</br>";
		// echo urldecode($test_search);
		$catesearchId = $product_obj->getCategoryIds($search_term);
		$search_term = $common_obj->custom_filter_input($search_term);
		// echo $search_term;
		// die();
		$get_search_terms = $webshop_obj->get_search_terms_name(urldecode($search_term));

		if ($get_search_terms) {
			foreach ($get_search_terms as $value) {
				$search_term_array[] = $value;
			}
		} else {
			$error = 'No result found';
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
		$message['message'] = 'Search result';
		$message['search_result'] = $search_term_array;
		exit(json_encode($message));
	}
});

/*$app->post('/webshop/get_search_terms_post', function (Request $request, Response $response, $args) {
	$posted_data = $request->getParsedBody();
	extract($posted_data);

	$error = '';
	if ($search_term == '') {
		$error = 'Please pass all the mandatory values';
	} else if (strlen($search_term) < 2) {
		$error = 'Please enter two letters.';
	} else {
		$webshop_obj = new DbSearchFeature();
		$common_obj = new DbCommonFeature();
		$product_obj = new DbProductFeature();

		$categorySearch = $product_obj->getCategoryIds($search_term);
		// echo $test_search = urlencode($search_term) . "</br>";
		// echo urldecode($test_search);
		$categoryIdsarr =  array();
		foreach ($categorySearch as $catKey => $catval) {
			array_push($categoryIdsarr, $catval['id']);
		}
		$search_term = $common_obj->custom_filter_input($search_term);
		// echo $search_term;
		// die();
		$get_search_terms_cat = $webshop_obj->get_search_terms_name_cat(($search_term), $categoryIdsarr);

		$get_search_terms = $webshop_obj->get_search_terms_name(($search_term), $categoryIdsarr);
		if ($get_search_terms) {
			foreach ($get_search_terms as $value) {
				$search_term_array[] = $value;
			}
		} else if ($get_search_terms_cat) {
			foreach ($get_search_terms_cat as $value_cat) {
				$search_term_array[] = $value_cat;
			}
		} else {
			$error = 'No result found';
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
		$message['message'] = 'Search result';
		$message['search_result'] = $search_term_array;
		exit(json_encode($message));
	}
});*/

$app->post('/webshop/get_search_terms_post', function (Request $request, Response $response, $args) {
	$posted_data = $request->getParsedBody();
	extract($posted_data);

	$error = '';
	if ($search_term == '') {
		$error = 'Please pass all the mandatory values';
	} else if (strlen($search_term) < 2) {
		$error = 'Please enter two letters.';
	} else {
		$webshop_obj = new DbSearchFeature();
		$common_obj = new DbCommonFeature();

		// echo $test_search = urlencode($search_term) . "</br>";
		// echo urldecode($test_search);
		$search_term = $common_obj->custom_filter_input($search_term);
		// echo $search_term;
		// die();

		$get_search_terms = $webshop_obj->get_search_terms_name(($search_term));
		// echo $get_search_terms;
		// die();
		if ($get_search_terms) {
			foreach ($get_search_terms as $value) {
				$search_term_array[] = $value;
			}
		} else {
			$error = 'No result found';
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
		$message['message'] = 'Search result';
		$message['search_result'] = $search_term_array;
		exit(json_encode($message));
	}
});
$app->get('/webshop/get_prodcut_nextpre_products/{product_id}', function (Request $request, Response $response, $args) {

	$product_id = $args['product_id'];

	$error = '';
	if ($product_id == '') {
		$error = 'Please pass all the mandatory values';
	} else {
		$webshop_obj = new DbSearchFeature();

		$get_product_details = $webshop_obj->getPrevNextproductDetails($product_id);

		$final_arr = array();
		if ($get_product_details == false) {
			$error = 'No result found';
		} else {
			foreach ($get_product_details as $value) {
				if ($product_id > $value['id']) {

					$arr['prev_arr'] = $value;
				} else if ($product_id < $value['id']) {

					$arr['next_arr'] = $value;
				}
				$final_arr = $arr;
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
		$message['message'] = 'Prev Next Product result';
		$message['ProductPrevNexList'] = $final_arr;
		exit(json_encode($message));
	}
});



$app->get('/webshop/get_prodcut_nextpre_products_Category/{product_id}/{categoryID}', function (Request $request, Response $response, $args) {



	$product_id = $args['product_id'];
	$categoryID = $args['categoryID'];

	$error = '';


	if ($product_id == '') {
		$error = 'Please pass all the mandatory values';
	} else {
		$webshop_obj = new DbSearchFeature();

		//    echo "Sonal";
		//    exit;
		$get_product_details = $webshop_obj->getPrevNextproductDetailsNew($product_id, $categoryID);

		//    echo "Sona";
		//    exit;
		$final_arr = array();
		if ($get_product_details == false) {
			$error = 'No result found';
		} else {
			foreach ($get_product_details as $value) {
				if ($product_id > $value['id']) {

					$arr['prev_arr'] = $value;
				} else if ($product_id < $value['id']) {

					$arr['next_arr'] = $value;
				}
				$final_arr = $arr;
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
		$message['message'] = 'Prev Next Product result';
		$message['ProductPrevNexList'] = $final_arr;
		exit(json_encode($message));
	}
});

$app->get('/webshop/get_product_nextpre_products_newarrivals/{shopcode}/{shop_id}/{product_id}/{customer_type_id}[/{badge}]', function (Request $request, Response $response, $args) {

	$shopcode 	= $args['shopcode'];
	$shop_id 	= $args['shop_id'];
	$product_id = $args['product_id'];
	$badge = (isset($args['badge']) ? $args['badge'] : '');
	$customer_type_id = $args['customer_type_id'];

	$error = '';
	if ($shopcode == '' || $shop_id == '' || $product_id == '') {
		$error = 'Please pass all the mandatory values';
	} else {
		$webshop_obj = new DbSearchFeature();
		$get_product_details = $webshop_obj->getPrevNextproductDetailsNewArrivals($shopcode, $shop_id, $product_id, $customer_type_id, $badge);
		$final_arr = array();
		if ($get_product_details == false) {
			$error = 'No result found';
		} else {
			foreach ($get_product_details as $value) {
				if ($product_id > $value['id']) {

					$arr['prev_arr'] = $value;
				} else if ($product_id < $value['id']) {

					$arr['next_arr'] = $value;
				}
				$final_arr = $arr;
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
		$message['message'] = 'Prev Next Product result';
		$message['ProductPrevNexList'] = $final_arr;
		exit(json_encode($message));
	}
});
