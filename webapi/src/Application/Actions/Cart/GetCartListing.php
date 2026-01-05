<?php

namespace App\Application\Actions\Cart;
use DbProductFeature;
use DbCommonFeature;
use DbLibrary;
use DbCart;
use DbWishlistFeature;
class GetCartListing {
    /**

     * @var DbLibrary

     */

    private $dbl;
    public function __construct()
    {
        require_once __DIR__ . '/../../../Model/Config/DbLibrary.php';
        $this->dbl = new DbLibrary();
    }
    public function __invoke($session_id,$quote_id = '',$customer_id = '')
    {
        $webshop_obj = new DbProductFeature();
        $comn_obj = new DbCommonFeature();
		$cart_obj = new DbCart();
		$wishlist_obj = new DbWishlistFeature();
        if(!empty($customer_id)){
            $param = [$quote_id, $customer_id]; 
            $query = "SELECT SQ.quote_id, SQ.base_grand_total, SQ.base_subtotal, SQ.grand_total, SQ.subtotal, SQ.total_qty_ordered, SQ.tax_amount,SQ.coupon_code,SQ.base_discount_amount,SQ.shipping_amount,SQ.voucher_code,SQ.voucher_amount,SQ.payment_final_charge,SQ.discount_percent FROM sales_quote as SQ WHERE (SQ.quote_id = ? AND SQ.customer_id = ?) ";
        }else{

            $param = [$session_id];
            $query = "SELECT SQ.quote_id, SQ.base_grand_total, SQ.base_subtotal, SQ.grand_total, SQ.subtotal, SQ.total_qty_ordered, SQ.tax_amount,SQ.coupon_code,SQ.base_discount_amount,SQ.shipping_amount,SQ.voucher_code,SQ.voucher_amount,SQ.payment_final_charge,SQ.discount_percent FROM sales_quote as SQ WHERE SQ.session_id = ? ";

        }
        $cartDetails = $this->dbl->dbl_conn->rawQueryOne($query,$param);
      

        if ($this->dbl->dbl_conn->getLastErrno() !== 0 || $this->dbl->dbl_conn->count === 0){

            return false;

        }
        $cartItems = $this->dbl->dbl_conn->rawQuery(
            "SELECT SQI.*,

	                    prod.base_image as base_image,
						CASE WHEN SQI.product_type = 'conf-simple' and prod.media_variant_id > 0  THEN

							(SELECT image FROM products_media_gallery WHERE product_id = SQI.parent_product_id AND is_default_variant = 1 AND attr_option_id = (SELECT attr_value FROM products_variants WHERE parent_id = prod.id AND product_id = SQI.product_id AND attr_id = prod.media_variant_id))

						WHEN SQI.product_type = 'conf-simple' and prod.media_variant_id = 0  THEN

							(SELECT image FROM products_media_gallery WHERE product_id = SQI.parent_product_id AND child_id = SQI.product_id ORDER BY id LIMIT 1)

						END as child_base_image,

	                    prod.estimate_delivery_time as prod_estimate_delivery_time,

                        prod.url_key as url_key,

	                    PI.available_qty as prod_available_qty

                FROM

                    sales_quote_items AS SQI

                INNER JOIN products AS prod

                    ON prod.id = IF(SQI.product_type = 'conf-simple', SQI.parent_product_id, SQI.product_id)

                LEFT JOIN products_inventory as PI ON SQI.product_id = PI.product_id

                WHERE SQI.quote_id = ?",

            [

                $cartDetails['quote_id']

            ]

        );

        if ($this->dbl->dbl_conn->getLastErrno() !== 0 || $this->dbl->dbl_conn->count === 0) {

            return false;

        }



        $cartList = [];

        $total_quantity = 0;

        foreach ($cartItems as $value) {

            $total_quantity += $value['qty_ordered'];

            $quantity = 0;

            $product_id = $value['product_id'];

			$bundleProductData='';

			if($value['product_type'] === 'bundle'){

				$bundleData=json_decode($value['bundle_child_details']);

				$product_variants_str='';

				$arr['variants'] = '';

				$product_variants_strFinal='';

				foreach($bundleData as $bdkey=>$bdval){

					$b_parent_id=$bdval->parent_id;

					$b_product_id=$bdval->product_id;

					$b_default_qty=$bdval->default_qty;

					if($b_parent_id > 0 ){





						$productDetails = $wishlist_obj->getproductDetailsById($b_parent_id);

						$value['bundleData'] = '';
						$VariantInfo=$cart_obj->get_product_variant_details($b_parent_id,$b_product_id);

								if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
									$product_variants_str = '';
									foreach($VariantInfo as $values){

										$attr_id=$values['attr_id'];

										$attr_value=$values['attr_value'];

										$AttrData=$cart_obj->getAttributeDetails($attr_id);

										if($AttrData==false){

											$attr_name='';

										}else{

											$attr_name=$AttrData['attr_name'];

										}



										$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);

										if($AttrOptionData==false){

											$attr_option_name='';

										}else{

											$attr_option_name=$AttrOptionData['attr_options_name'];

										}



										if($attr_name!='' && $attr_option_name!=''){

											$product_variants_str .= $attr_name.' : ' .$attr_option_name.', ';





										}

									}





								}



								if($product_variants_str!=''){

									$product_variants_strFinal = substr($product_variants_str,0,-2);

								}



                                



						$bundleProductData.=$productDetails['name'].' ( '.$product_variants_strFinal.' ) <b>X</b> '.$b_default_qty.'<br/>';





					}else{







						$productDetails = $wishlist_obj->getproductDetailsById($b_product_id);

						$bundleProductData.=$productDetails['name'].' <b>X</b> '.$b_default_qty.'<br/>';



					}
				}

			}

			if($bundleProductData!=''){

				$value['bundleData'] = substr($bundleProductData,0,-5);

			}else{

				$value['bundleData']='';

			}
            if ($value['product_type'] === 'conf-simple') {

                if(!empty($value['child_base_image'])){

                    $value['base_image'] = $value['child_base_image'];

                }
                if (is_numeric($value['prod_available_qty']) && $value['prod_available_qty'] > 0) {

                    $quantity = $value['prod_available_qty'];

                }

                $value['estimate_delivery_time'] = $value['prod_estimate_delivery_time'];
                // $delay_warehouse_time = $comn_obj->getDelayWarehouseTime();
                // $delay_warehouse_timing = !empty($delay_warehouse_time['value']) ? $delay_warehouse_time['value'] : 0;
                // $value['estimate_delivery_time'] += $delay_warehouse_timing;
            }
             else {

                if (is_numeric($value['prod_available_qty']) && $value['prod_available_qty'] > 0) {

                    $quantity = $value['prod_available_qty'];

                }
                $ProDelTime1 = ($value['prod_estimate_delivery_time'] != '') ? $value['prod_estimate_delivery_time'] : 0;

                // $delay_warehouse_time = $comn_obj->getDelayWarehouseTime();

                // $delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

                $value['estimate_delivery_time'] = $ProDelTime1 ;
            }

            $value['available_qty'] = $quantity;

            $cartList['cartItems'][] = $value;

        }
        $cartList['cartCount'] = $total_quantity;

        $cartList['cartDetails'] = $cartDetails;

        return $cartList;

    }

}

