<?php
// shop3/application/repositories/ProductRepository.php

class ProductRepository
{
    use UsesRestAPI;

    public static function get_featured_products($postArr)
    {
        // static $result;
        // if (isset($result) && THEMENAME != 'theme2') {
        //     return $result;
        // }
        $featuredAPIUrl = '/webshop/get_product_blocks'; //New arrival section

        $final_post_arr = array();
        $final_post_arr = $postArr;
        // echo "<pre>";print_r(value: $final_post_arr);die;

        $featuredProduct = self::post_method($featuredAPIUrl, $final_post_arr, 900);


        if (isset($featuredProduct) && $featuredProduct->statusCode == '200') {
            $result = $featuredProduct->productBlockList;
            return $featuredProduct->productBlockList;
        }

        return '';
    }

    public static function get_new_arrivals($postArr)
    {
        static $result;
        if (isset($result)) {
            return $result;
        }

        $url = '/webshop/new_arrivals'; //New arrival section

        $final_post_arr = array();
        // $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = $postArr;
        // echo "<pre>";
		// print_R($postArr);die();
        $newArrival = self::post_method($url, $postArr, 910);
        if (isset($newArrival) && $newArrival->statusCode == '200') {
            // $result=$newArrival->NewArrivalProduct;
            // return $newArrival->NewArrivalProduct;
            return $newArrival;
        }

        return '';
    }

    public static function get_category_details($categoryArr)
    {
        $final_post_arr = array();
        $final_post_arr =  $categoryArr;

        $categoryApiUrl = '/webshop/get_category_details';

        $category = self::post_method($categoryApiUrl, $final_post_arr);
        if (isset($category)) {
            return $category;
        }
        return '';
    }

    public static function product_listing($productArr)
    {
        $final_post_arr = array();
        $final_post_arr = $productArr;
        $final_post_arr['log'] = @json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $ApiUrl = '/webshop/product_listing';
         //echo "<pre>";
        // print_r($final_post_arr);exit;
        $product_listing = self::post_method($ApiUrl, $final_post_arr);
        // print_r($product_listing);exit;
        if (isset($product_listing)) {
            return $product_listing;
        }
        return '';
    }

    public static function get_product_list($productArr1)
    {

        $final_post_arr = array();
        $final_post_arr =  $productArr1;

        $HomeApiUrl = '/webshop/product_listing';
        $home_product_listing = self::post_method($HomeApiUrl, $final_post_arr);
        if (isset($home_product_listing)) {
            return $home_product_listing;
        }
        return '';
    }

    public static function get_gift_products_list($productArr1)
    {

        $final_post_arr = array();
        $final_post_arr =  $productArr1;
        // print_r($final_post_arr);exit;

        $HomeApiUrl = '/webshop/ProductWithGifts';
        $home_product_listing = self::post_method($HomeApiUrl, $final_post_arr);
        if (isset($home_product_listing)) {
            return $home_product_listing;
        }
        return '';
    }

    public static function get_catalog_filters($catalogArr)
    {
        $final_post_arr = array();
        $final_post_arr = $catalogArr;
        // print_r($final_post_arr);exit;
        $catlogApiUrl = '/webshop/get_catalog_filters';

        $catalogFilter = self::post_method($catlogApiUrl, $final_post_arr);
        if (isset($catalogFilter)) {
            return $catalogFilter;
        }
        return '';
    }

    public static function product_detail($productArr)
    {
        $final_post_arr = array();
        $productApiUrl = '/webshop/product_detail';
        // echo "<pre>";
	    // print_r($productArr);die;
        $product_detail = self::post_method($productApiUrl, $productArr, 950);
        if (isset($product_detail)) {
            return $product_detail;
        }
        return '';
    }

    public static function get_product_category_by_level($product_id, $cat_level)
    {
        $CatRootLevelArr = array('product_id' => $product_id, 'cat_level' => $cat_level);
        $catApiUrl = '/webshop/get_product_category_by_level';
        $product_category = self::post_method($catApiUrl, $CatRootLevelArr, 960);
        if (isset($product_category)) {
            return $product_category;
        }
        return '';
    }

    public static function get_product_categorys($product_id)
    {
        $cat_API_arr = array('product_id' => $product_id);
        $cat_id_ApiUrl = '/webshop/get_product_categorys';
        $product_categorys = self::post_method($cat_id_ApiUrl, $cat_API_arr, 970);
        if (isset($product_categorys)) {
            return $product_categorys;
        }
        return '';
    }

    public static function scanned_products_listing_new($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/scanned_products_listing_new';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function getProductSku($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/getProductSku';

        $response = self::post_method($ApiUrl, $final_post_arr, 980);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function getAvailableProducts($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/getAvailableProducts';

        $response = self::post_method($ApiUrl, $final_post_arr, 990);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function insert_scanned_products($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/insert_scanned_products';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function deleteAllScannedProduct($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/deleteAllScannedProduct';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function update_scanned_qty($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/update_scanned_qty';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function deleteScannedProduct($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/deleteScannedProduct';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function add_to_catlog_builder($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/add_to_catlog_builder';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function add_to_catlog_builder_items($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/add_to_catlog_builder_items';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function base_color_data($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);
        $ApiUrl = '/webshop/base_color_data';
        $response = self::post_method($ApiUrl, $final_post_arr, 945);
        if (isset($response) && !empty($response)) {
            return $response;
        }
        return '';
    }

    public static function get_gallery_images_by_variants($shopcode, $shop_id, $productArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $productArr);
        $galleryImageByVariantApiUrl = '/webshop/get_gallery_images_by_variants';
        $galleryImageByVariant = self::post_method($galleryImageByVariantApiUrl, $final_post_arr);
        if (isset($galleryImageByVariant)) {
            return $galleryImageByVariant;
        }
        return '';
    }

    public static function getBundleChildValidateQty($BundleQtyArr)
    {
        $getBundleChildValidateQtyApiUrl = '/webshop/getBundleChildValidateQty';
        $getBundleChildValidateQty = self::post_method($getBundleChildValidateQtyApiUrl, $BundleQtyArr);
        if (isset($getBundleChildValidateQty)) {
            return $getBundleChildValidateQty;
        }
        return '';
    }

    public static function getSimpleValidateQty($PostArr)
    {
        $final_post_arr = array();
        $final_post_arr = $PostArr;
        $getSimpleValidateQtyApiUrl = '/webshop/getSimpleValidateQty';
        $getSimpleValidateQty = self::post_method($getSimpleValidateQtyApiUrl, $final_post_arr);
        if (isset($getSimpleValidateQty)) {
            return $getSimpleValidateQty;
        }
        return '';
    }

    public static function get_conf_simprod_by_variants($PostArr)
    {
        $final_post_arr = array();
        $final_post_arr = $PostArr;
        $confsimpleApiUrl = '/webshop/get_conf_simprod_by_variants';
        $CSResponse = self::post_method($confsimpleApiUrl, $final_post_arr);
        if (isset($CSResponse)) {
            return $CSResponse;
        }
        return '';
    }

    public static function get_conf_simprod_by_variants_new($PostArr)
    {
        $final_post_arr = array();
        $final_post_arr = $PostArr;
        $confsimpleApiUrl = '/webshop/get_conf_simprod_by_variants_new';
        $CSResponse = self::post_method($confsimpleApiUrl, $final_post_arr);
        if (isset($CSResponse)) {
            return $CSResponse;
        }
        return '';
    }


    public static function get_custom_variable($identifier)
    {
        //static $result;
        // if (isset($result)) {
        //     return $result;
        // }
        $customVariableApiUrl = '/webshop/get_custom_variable/' . $identifier;
        $result = self::get_method($customVariableApiUrl);
        if (isset($result)) {
            return $result;
        }
        return '';
        // return $result;
    }
    public static function geSearchtCategoryIds($PostArr)
    {
        $final_post_arr = array();
        $final_post_arr = $PostArr;
        $confsimpleApiUrl = '/webshop/geSearchtCategoryIds';
        $CSResponse = self::post_method($confsimpleApiUrl, $final_post_arr);
        if (isset($CSResponse)) {
            return $CSResponse;
        }
        return '';
    }


    public static function get_hindi_magazines_products($postArr)
    {

        $final_post_arr = array();
        $final_post_arr =  $postArr;
        // echo "<pre>";
        // print_r($final_post_arr);

        $HomeApiUrl = '/webshop/hindi_magazines';
        $home_product_listing = self::post_method($HomeApiUrl, $final_post_arr);
        if (isset($home_product_listing)) {
            return $home_product_listing;
        }
        return '';
    }


    public static function get_regional_magazine_products($postArr)
    {

        $final_post_arr = array();
        $final_post_arr =  $postArr;
        // echo "<pre>";
        // print_r($final_post_arr);exit;

        
        $HomeApiUrl = '/webshop/regional_magazine';
        $home_product_listing = self::post_method($HomeApiUrl, $final_post_arr);
        if (isset($home_product_listing)) {
            return $home_product_listing;
        }
        return '';
    }

    
}
