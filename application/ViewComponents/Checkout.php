<?php

/**

 * @property CI_Controller $ci

*/

class Checkout {
    private $ci;
    private $list_array;

    public function __construct(){
        $this->ci =& get_instance();
    }

    public function billingAddress($getdata){
        $this->list_array='';
        $this->addressListData=$getdata['addressList'];
        $this->restrictedAccess=$getdata['restricted_access'];
        $this->countryData=$getdata['countryList'];
        $this->stateData=$getdata['stateList'];
        $this->billing_address_data=$getdata['billing_address_data'];
        $this->quoteData=$getdata['quoteData'];
        $this->same_as_billing=$getdata['same_as_billing'];
        $this->list_array=array('addressList' => $this->addressListData,'restricted_access' => $this->restrictedAccess,'countryList' => $this->countryData,'stateList' => $this->stateData,'billing_address_data' => $this->billing_address_data,'quoteData'=> $this->quoteData,'same_as_billing'=> $this->same_as_billing);
        $this->ci->template->load('components/checkout/billing_address', $this->list_array);
    }

    public function billingAddressNew($getdata){
        $this->list_array='';
        $this->addressListData=$getdata['addressList'];
        $this->restrictedAccess=$getdata['restricted_access'];
        $this->countryData=$getdata['countryList'];
        $this->stateData=$getdata['stateList'];
        $this->billing_address_data=$getdata['billing_address_data'];
        $this->quoteData=$getdata['quoteData'];
        $this->same_as_billing=$getdata['same_as_billing'];
        $this->list_array=array('addressList' => $this->addressListData,'restricted_access' => $this->restrictedAccess,'countryList' => $this->countryData,'stateList' => $this->stateData,'billing_address_data' => $this->billing_address_data,'quoteData'=> $this->quoteData,'same_as_billing'=> $this->same_as_billing);
        $this->ci->template->load('components/checkout/billing_address_new', $this->list_array);
    }

    public function shippingAddress($getdata){
        $this->list_array='';
        $this->addressListData=$getdata['addressList'];
        $this->countryData=$getdata['countryList'];
        $this->stateData=$getdata['stateList'];
        $this->shipCountryData=$getdata['ShipToCountry'];
        $this->shipping_address_data=$getdata['shipping_address_data'];
        $this->quoteData=$getdata['quoteData'];
        $this->list_array=array('addressList' => $this->addressListData,'countryList' => $this->countryData,'stateList' => $this->stateData,'ShipToCountry' => $this->shipCountryData,'shipping_address_data' => $this->shipping_address_data,'quoteData'=> $this->quoteData);
        $this->ci->template->load('components/checkout/shipping_address', $this->list_array);
    }

    public function shippingAddressNew($getdata){
        $this->list_array='';
        $this->addressListData=$getdata['addressList'];
        $this->countryData=$getdata['countryList'];
        $this->stateData=$getdata['stateList'];
        $this->shipCountryData=$getdata['ShipToCountry'];
        $this->shipping_address_data=$getdata['shipping_address_data'];
        $this->quoteData=$getdata['quoteData'];
        $this->list_array=array('addressList' => $this->addressListData,'countryList' => $this->countryData,'stateList' => $this->stateData,'ShipToCountry' => $this->shipCountryData,'shipping_address_data' => $this->shipping_address_data,'quoteData'=> $this->quoteData);
        $this->ci->template->load('components/checkout/shipping_address_new', $this->list_array);
    }

    public function EUShippingResponse($shopcode,$shop_id,$QuoteId,$vatpercentage){
        $this->list_array='';
        $this->shipping_response_flag=0;
        $this->shipping_result='';
        $this->quote_id=$QuoteId;
        $this->vat_percentage=$vatpercentage;

        $postArr2 = array('quote_id'=>$this->quote_id);
        $this->response_shipping_charge = CheckoutRepository::get_shipping_charges($shopcode,$shop_id,$postArr2);

        if (!empty($this->response_shipping_charge) && isset($this->response_shipping_charge)) {
            if ($this->response_shipping_charge->eu_shipping_response != '') {
                $eu_shipping_response =$this->response_shipping_charge->eu_shipping_response;
                foreach ($eu_shipping_response as $shipping_method) {
                    $estimate_delivery = $this->response_shipping_charge->quote_estimate_delivery + $shipping_method->delivery_days;
                    $add_dates = '+'.$estimate_delivery ."days";
                    $new_date = date('d-m-Y', strtotime($add_dates));

                    $shipping_tax_percent = $this->vat_percentage;
                    $shipping_tax_amount=($shipping_method->ship_rate * $shipping_tax_percent)/100;
                    $shipping_amount=$shipping_method->ship_rate+$shipping_tax_amount;

                    $checked = ($this->ci->session->userdata('shipping_charge_id') == $shipping_method->id ? 'checked':'');

                    $this->shipping_result.= '<label class="radio-label-checkout"><input class="radio-checkout  single-shipping-method" type="radio" value="'.$shipping_method->id.'" name="shipping_method"  '.$checked.' >'.$shipping_method->ship_method_name.' <span class="radio-check"></span> <small>(Rate  '.CURRENCY_TYPE . number_format($shipping_amount, 2).')</small>  - <small>Expected Delivery in '.$estimate_delivery.' days.</small></label>';
                }
                $this->shipping_response_flag=1;
            }else{
                $this->shipping_result='<p><label class="radio-label-checkout">'.$this->shippingMethodNotAvailableMsg().'</label> </p>';;
            }
            $this->list_array=array('eu_shipping_response'=>$this->shipping_result,'shipping_response_flag'=>$this->shipping_response_flag);
        }

        return $this->list_array;
    }

    public function shippingMethodNotAvailableMsg(){
        $this->msg_for_customer = '';
        $identifier='shipping_method_not_available';
        $api_response =  GlobalRepository::get_custom_variable(SHOPCODE, SHOP_ID,$identifier);
        if(!empty($api_response) && $api_response->statusCode=='200'){
            $RowCV=$api_response->custom_variable;
            $this->msg_for_customer = $RowCV->value;
        }
        return $this->msg_for_customer;
    }

    public function checkoutPriceDetails($CartData,$type,$ShipAddress){
        $this->cart_data = $CartData ?? '' ;
        $this->type = $type ?? '' ;
        $this->ship_address = $ShipAddress ?? '' ;
        $this->list_array = array('CartData'=>$this->cart_data,'cartType' => $this->type, 'ShipAddress' => $this->ship_address);
        if(empty($this->list_array)){
           return;
        }
        $this->ci->template->load('components/cart_total', $this->list_array);
    }

}
