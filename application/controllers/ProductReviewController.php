<?php
defined('BASEPATH') or exit('No direct script access allowed');
class ProductReviewController extends CI_Controller
{
    public function save_review()
    {
        if (isset($_SESSION['LoginID'])) {
            $this->session->unset_userdata('currentPageUrl');
            if (empty($_POST['product_id']) || empty($_POST['rating']) || empty($_POST['review_content'])) {
                echo json_encode(array('flag'=>2, 'msg'=>"Please enter all mandatory / compulsory fields."));
                exit;
            } else {
                $LoginID = $_SESSION['LoginID'];
                $LoginToken = $_SESSION['LoginToken'];
                $product_id = $_POST['product_id'];
                $rating = $_POST['rating'];
                $review_content = $_POST['review_content'];
                $reviewArr = array('LoginToken'=>$LoginToken,'LoginID'=>$LoginID,'product_id'=>$product_id,'rating'=>$rating,'reviews'=>$review_content);
                $responseData = ProductReviewRepository::add_product_review($reviewArr);

                if (!empty($responseData) && isset($responseData) && $responseData->is_success=='true') {

                    $message = $responseData->message;

                    $lastInsertId = $responseData->lastInsertId;



                    //Send Email notification

					$table = 'products';

					$flag = 'own';

					$where = 'id  = ?';

					$params = array($product_id);

					$post_product_array = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'params'=>$params);

					$product_details = CommonRepository::get_table_data($post_product_array);

					$product_slug = $product_details->tableData[0]->url_key;

					$product_link = base_url().'product-detail/'.$product_slug;

					$product_name = $product_details->tableData[0]->name;

					$review_date = date("d/m/Y");



                    $webshopname='';
                    $shop_logo =SITE_LOGO;
    
                $data['webshop_details'] = CommonRepository::get_webshop_details();
                if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success=='true') {
                    // $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                    $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);

                }

					// $shop_logo = SITE_LOGO.'/'.$shop_logo;

					$site_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">

							<img alt="'.$webshopname.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />

						</a>';

                    $lang_code='';

                    if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){

                        $lang_code=$this->session->userdata('lcode');

                    }



					$postArr = array(

						'rating'=>$rating,

						'review_content'=>$review_content,

						'product_link'=>$product_link,

						'product_name'=>$product_name,

						'customer_id'=>$LoginID,

						'review_date'=>$review_date,

						'site_logo'=>$site_logo,

                        'lang_code'=>$lang_code

					);

					$prodReviewResponse= ProductReviewRepository::productReview_notification($postArr);

					//Send Email notification end



                    //Get Reviews

                    $limit_identifier='review_display_limit';

                    $limitResponse=GlobalRepository::get_custom_variable($limit_identifier);

                    if (!empty($limitResponse) && isset($limitResponse) && $limitResponse->statusCode=='200') {

                        $RowLimit=$limitResponse->custom_variable;

                        $data['limit'] = $limit = $RowLimit->value;

                    } else {

                        $data['limit'] = $limit=3;

                    }



                    $getReviewArr = array('product_id'=>$product_id,'limit'=>$limit);

                    $reviewResponse = ProductReviewRepository::get_product_reviews($getReviewArr);

                    if (!empty($reviewResponse) && isset($reviewResponse) && $reviewResponse->statusCode=='200') {

                        $data['reviewResponse']=$reviewResponse->productReviewsList;

                    } else {

                        $data['reviewResponse'] = '';

                    }



                    $reviewCountArr = array('product_id'=>$product_id);

                    $reviewCountResponse = ProductReviewRepository::get_product_reviews($reviewCountArr);

                    if (!empty($reviewCountResponse) && isset($reviewCountResponse) && $reviewCountResponse->statusCode=='200') {

                        $data['reviewCountResponse']= count($reviewCountResponse->productReviewsList);

                    } else {

                        $data['reviewCountResponse'] = 0;

                    }

                    $reviewHtml = $this->load->view('product/customer_new_reviews', $data, true);

                    // End

                    echo json_encode(array('flag' => 1, 'msg' => $message, 'lastInsertId' => $lastInsertId,'reviewData' => $reviewHtml));

                    exit;

                }

            }

        } else {

            $product_slug = $_POST['product_slug'];

            $currentPageUrl = array('currentPageUrl' => 'product-detail/'.$product_slug);

            $this->session->set_userdata($currentPageUrl);



            $message = 'Please login to add your review';

            $redirect = BASE_URL.'customer/login';

            echo json_encode(array('flag' => 0, 'msg' => $message, 'redirect' => $redirect));

            exit;

        }

    }



    public function review_loadmore()

    {



        if (!empty($_POST)) {

            $limit_identifier='review_display_limit';

            $limitResponse = GlobalRepository::get_custom_variable($limit_identifier);

            if (!empty($limitResponse) && isset($limitResponse) && $limitResponse->statusCode=='200') {

                $RowLimit=$limitResponse->custom_variable;

                $limit=$RowLimit->value;

                $data['limit']=$limit;

            } else {

                $data['limit']= $limit=5;

            }



            $reviewArr = array('product_id'=>$_POST['product_id'],'review_id'=>$_POST['offset'],'limit'=>$limit);

            $reviewResponse = ProductReviewRepository::get_product_reviews($reviewArr);

            if (!empty($reviewResponse) && isset($reviewResponse) && $reviewResponse->statusCode=='200') {

                $data['reviewResponse']=$reviewResponse->productReviewsList;

            } else {

                $data['reviewResponse'] = '';

            }



            $reviewCountArr = array('product_id'=>$_POST['product_id'],'review_id'=>$_POST['offset']);

            $reviewCountResponse = ProductReviewRepository::get_product_reviews($reviewCountArr);

            if (!empty($reviewCountResponse) && isset($reviewCountResponse) && $reviewCountResponse->statusCode=='200') {

                $data['reviewCountResponse']= count($reviewCountResponse->productReviewsList);

            } else {

                $data['reviewCountResponse'] = 0;

            }



            $this->template->load('product/loadmore_review_list',$data);

        }

    }

}

