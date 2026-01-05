<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/testimonial_listing', function (Request $request, Response $response, $args) {

    $posted_data = $request->getParsedBody();	
    extract($posted_data);

    $page = $page ?? 0;
	$page_size = $page_size ?? 0;

	if ($page > 0) {
		$page = ($page - 1) * $page_size;
	}
    
    $testimonial_obj = new DbTestimonialsFeature();

    $error='';

    $getTestimonialCount = $testimonial_obj->getTestimonialsCount($page,$page_size);

    $getTestimonialDetails = $testimonial_obj->getTestimonialsListing($page,$page_size);
    
    if($getTestimonialDetails == false)
	{
		$error='No Testimonials found';
	}

    if (empty($getTestimonialDetails)) {
        if ($page > 1) {
            $message['statusCode'] = '500';
            $message['is_success'] = 'false';
            $message['message'] = 'No Testimonials!';
            $message['ProductList'] = [];
            $message['ProductListCount'] = $getTestimonialCount;
            exit(json_encode($message));
        }
        abort($error);
    }

    $message['statusCode'] = '200';
	$message['is_success'] = 'true';
	$message['message'] = 'Testimonials available';
	$message['TestimonialsList'] = $getTestimonialDetails;
	$message['TestimonialsListCount'] = $getTestimonialCount;
	exit(json_encode($message));
});


?>