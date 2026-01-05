<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WishlistController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('pagination');
        $this->load->library('Ajax_pagination');
        // $this->perPage = 3;
    }

    public function addToWishList()
    {
        $data['PageTitle']= 'Add To Wishlist';
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID']!='') {
            if (isset($_POST['product_id'])) {
                $userID = $_SESSION['LoginID'];
                $product_id = $_POST['product_id'];

                $wishlistArr = array('customer_id'=>$userID,'product_id'=>$product_id);

                
                $addedWishlist =WishlistRepository::addtowishlist($wishlistArr);
                if (!empty($addedWishlist) && isset($addedWishlist) && $addedWishlist->statusCode == '200') {
                    $message = $addedWishlist->message;
                    echo json_encode(array('flag'=>1, 'msg'=>$message));
                    exit;
                } else {
                    $message = $addedWishlist->message;
                    echo json_encode(array('flag'=>0, 'msg'=>$message));
                    exit;
                }
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>'Something wrong!'));
                exit;
            }
        } else {
            $redirect = BASE_URL.'customer/login';
            //redirect('/customer/login','refresh');
            echo json_encode(array('flag'=>2, 'msg'=>'Please login first', 'redirect'=>$redirect));
            exit;
        }
    }

    public function getMyWishlist()
    {
        $data['PageTitle']= 'My Wishlist';
        $data['side_tab'] = 'wishlist';

        $this->template->load('wishlist/my_wishlist', $data);
    }

    public function removeWishlistItem()
    {
        if (isset($_POST) && $_POST['wishlist_id'] != '') {
            $wishlist_id=$_POST['wishlist_id'];

            $response = WishlistRepository::wishlist_deleteproduct($wishlist_id);
            if (!empty($response) && isset($response)) {
                $message = $response->message;
                if ($response->is_success=='true') {
                    echo json_encode(array('flag'=>1, 'msg'=>$message));
                    exit;
                } else {
                    echo json_encode(array('flag'=>0, 'msg'=>$message));
                    exit;
                }
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>'Something wrong!'));
                exit;
            }
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
            exit;
        }
    }
}
