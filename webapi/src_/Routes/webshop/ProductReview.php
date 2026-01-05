<?php
use App\Controllers\HomeListingController;
use App\Controllers\ProductFiltersController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/add_product_review', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode=='' || $shopid=='' || $LoginToken=='' || $LoginID=='' || $product_id=='' || $rating=='' || $reviews=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductReviewFeature();
		$addProductReview = $webshop_obj->addProductReview($shopcode,$shopid,$LoginToken,$LoginID,$product_id,$rating,$reviews);

		if($addProductReview == false)
		{
			$error='Error while adding review. please try again.';
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
		$message['message'] = 'Review added successfully, it will show up in 5-10 minutes.';
		$message['lastInsertId'] = $addProductReview;
		exit(json_encode($message));
	}

});

$app->post('/webshop/productReview_notification', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode=='' || $shopid=='' || $product_link=='' || $product_name=='' || $rating=='' || $review_content=='' || $customer_id =='' || $review_date=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$lang_code =  (isset($lang_code) ? $lang_code : '');

		$Common_obj = new DbCommonFeature();
		$webshopName = $Common_obj->getWebShopName($shopcode,$shopid);
		if($webshopName!=false){
			$webshop_name = $webshopName['org_shop_name'];
		}else{
			$webshop_name = '';
		}

		$webshop_obj = new DbEmailFeature();
		$template_code = "product_reviews";

		$customerData = $Common_obj->getCustomerDetailById($shopcode,$customer_id);
		$customer_name = $customerData['first_name']." ".$customerData['last_name'];

		$TempVars=array('##RATING##','##REVIEW##','##PRODUCTNAME##','##PRODUCTLINK##','##CUSTOMERNAME##','##DATE##');
		$DynamicVars=array($rating, $review_content,$product_name,$product_link,$customer_name,$review_date);
		$CommonVars=array($site_logo, $webshop_name);

		$EmailToAdmin=$webshop_obj->get_custom_variable($shopcode,'review_contact_recipient');

		if($EmailToAdmin==false){
			$EmailTo='no-reply@shopinshop.co';
		}else{
			$EmailTo=$EmailToAdmin['value'];
			$commanseperated = (explode(",",$EmailTo));
			$emailNotSentFlag = 0;
			foreach($commanseperated as $email){
				$emailSendStatusFlag=$webmail_obj->get_email_code_status($shopcode,$template_code);
				if($emailSendStatusFlag==1){
					$get_email =  $webshop_obj->sendCommonHTMLEmail($shopcode,$email,$template_code,$TempVars,$DynamicVars,'','',$CommonVars,$lang_code);
					if($get_email == false){
						$emailNotSentFlag = 1;
					}
				}
			}
		}

		if($emailNotSentFlag == 1)
		{
			$error = 'Mail Not Send' ;
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
		$message['message'] = 'Review notification sent.';

		exit(json_encode($message));
	}

});

$app->post('/webshop/get_product_reviews', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	if(empty($shopcode) || empty($shopid) || empty($product_id))
	{
		abort('Please pass all the mandatory values');
	}

    $review_obj = new DbProductReviewFeature();
    $idsByReviewCode = $review_obj->productidsByReviewCode($shopcode,$shopid,$product_id);

    $review_id = (isset($review_id)) ? $review_id : '';
    $limit = (isset($limit)) ? $limit : '';

    if($idsByReviewCode === false){
        abort('There is something wrong!');
    }

    $review_array = [];
    if(empty($idsByReviewCode)){
        $p_id[] = $product_id;
    }else{
        foreach($idsByReviewCode as $ids){
            $p_id[] = $ids['id'];
        }
    }

    $getProductReview = $review_obj->getProductReviews($shopcode, $p_id, $limit, $review_id);
    $review_count = $review_obj->getProductReviewsCount($shopcode, $p_id);

    if($getProductReview === false)
    {
        abort('Error while getting review.');
    }

    foreach($getProductReview as $review){
        $revw_date = date("j F Y",$review['created_at']);
        $rev_arr['id'] = $review['id'];
        $rev_arr['product_id'] = $review['product_id'];
        $rev_arr['rating'] = $review['rating'];
        $rev_arr['review'] = $review['review'];
        $rev_arr['reviwedby'] = $review['first_name'].' '.$review['last_name'];
        $rev_arr['reviewed_on'] = $revw_date;

        $review_array[] = $rev_arr;
    }

    $message['statusCode'] = '200';
    $message['is_success'] = 'true';
    $message['message'] = 'Review data available';
    $message['productReviewsList'] = $review_array;
    $message['productReviewsCount'] = $review_count;
    exit(json_encode($message));
});

$app->post('/webshop/get_product_blocks', HomeListingController::class .':featuredProducts');


$app->post('/webshop/get_catalog_filters', ProductFiltersController::class .':productNavFilters');
