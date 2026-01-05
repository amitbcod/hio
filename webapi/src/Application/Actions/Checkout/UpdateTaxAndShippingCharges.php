<?php

namespace App\Application\Actions\Checkout;

use DbCheckout;
use DbCommonFeature;

class UpdateTaxAndShippingCharges
{
    private $DbCheckout;

    public function __construct(){
        $this->DbCheckout = new DbCheckout();
    }

    public function execute($quote_id){

        $ShippingAddress=$this->DbCheckout->getQuoteShippingAddressById($quote_id,2);


        if($ShippingAddress === false) {
            return null;
        }

            /**--------------Shipping Rules Apply only For Zumbashop India-------le shop_flag=2---------------------------------**/
/*
            $shipping_tax_percent=0.00;
            $TaxRow=$this->DbCheckout->getMaxProductsTaxFromQuoteItems($quote_id);
            if($TaxRow!=false){
                if(isset($TaxRow) && $TaxRow['tax_percent']!='' && $TaxRow['tax_percent']>0){
                    $shipping_tax_percent=$TaxRow['tax_percent'];
                }
            }

            $footwear_category_id='';
            $FootwearRow=$this->DbCheckout->getFootwearCatId();
            if($FootwearRow!=false){
                if(isset($FootwearRow) && $FootwearRow['value']!=''){
                    $footwear_category_id=$FootwearRow['value'];
                }
            }

            $footwear_count = 0;
            $other_count = 0;
            $cod_charges=0;
            $OrderItems=$this->DbCheckout->get_sales_quote_items($quote_id);

            $total_qty = count($OrderItems);

            foreach($OrderItems as $item){
                if($item['product_type']==='conf-simple'){
                    $product_id=$item['parent_product_id'];
                }else{
                    $product_id=$item['product_id'];
                }
                $IsFootwear=$this->DbCheckout->checkIsFootwear($product_id,$footwear_category_id);
                if($IsFootwear==true){
                    $footwear_count = $footwear_count+$item['qty_ordered'];
                }else{
                    $other_count = $other_count+$item['qty_ordered'];
                }
            }

            if($footwear_count >= 1){

                $newPrice = 250;   // fixed for Footwear Category

                if($other_count > 3){

                    $newPrice = $newPrice * $footwear_count;
                    $final_qty = $other_count - 3;
                    $additional_price = ($final_qty*25);
                    $total_Shipping_charges = ($newPrice+$additional_price+$cod_charges);

                    $shipping_tax_amount=($total_Shipping_charges * $shipping_tax_percent)/100;
                    $shipping_amount=$total_Shipping_charges+$shipping_tax_amount;
                }else{
                    $additional_price = ($newPrice * $footwear_count);
                    $total_Shipping_charges = ($additional_price+$cod_charges);
                    $shipping_tax_amount=($total_Shipping_charges * $shipping_tax_percent)/100;
                    $shipping_amount=$total_Shipping_charges+$shipping_tax_amount;
                }
            }else{
                $newPrice = 100;     // fixed for others category

                if($other_count > 3){
                    $final_qty = $other_count - 3;
                    $additional_price = ($final_qty*25);
                    $total_Shipping_charges = ($newPrice+$additional_price+$cod_charges);


                }else{
                    $total_Shipping_charges = ($newPrice+$cod_charges);
                }

                $shipping_tax_amount=($total_Shipping_charges * $shipping_tax_percent)/100;
                $shipping_amount=$total_Shipping_charges+$shipping_tax_amount;
            }

            $this->DbCheckout->finalupdateQuoteShippingChargeAndTax($quote_id,$total_Shipping_charges,$shipping_amount,$shipping_tax_percent,$shipping_tax_amount);
        */
        return null;
    }
}
