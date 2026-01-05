<?php

namespace App\Controllers\Webshop;

use DbCart;
use DbCheckout;
use DbCommonFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaveQuoteAddressController
{
    public const ADDRESSTYPE_BILLING = 1;
    public const ADDRESSTYPE_SHIPPING = 2;

    public function __invoke(Request $request, Response $response, $args)
    {
        {
            $data = $request->getParsedBody();
            extract($data);

            if (empty($quote_id)) {
                abort('Please pass all the mandatory values');
            }

            $address_type = (int) $address_type;


            if ($address_type === self::ADDRESSTYPE_SHIPPING && empty($address_options)) {
                abort('Please select proper shipping address option');
            }

            if ($address_type === self::ADDRESSTYPE_BILLING && empty($billing_address_options)) {
                abort('Please select proper billing address option');
            }

            $ch_obj = new DbCheckout();
            $webshop_obj = new DbCommonFeature();
            $cart_obj = new DbCart();

            if ($customer_id > 0) {
                $CustomerInfo = $webshop_obj->getCustomerDetailById($customer_id);

                if ($CustomerInfo !== false) {
                    $billing_email_id = $CustomerInfo['email_id'];
                    $customer_email_id = $CustomerInfo['email_id'];
                }
            }

            $company_name = $company_name ?? '';
            $vat_no = $vat_no ?? '';
            // $consulation_no = $consulation_no ?? '';
            // $res_company_name = $res_company_name ?? '';
            // $res_company_address = $res_company_address ?? '';
            // $vat_vies_valid_flag = $vat_vies_valid_flag ?? '';


            $save_in_address_book = (bool) ($save_in_address_book ?? 0);
            $same_as_billing = (bool) ($same_as_billing ?? 0);

            if ($address_type === self::ADDRESSTYPE_SHIPPING) {

                [$customer_address_id, $country_code] = $this->processShippingAddress($address_options, $save_in_address_book, $same_as_billing, $customer_id, $shipping_first_name, $shipping_last_name, $shipping_mobile_no, $shipping_address, $shipping_address_1, $shipping_city, $shipping_state, $shipping_country, $shipping_pincode, $webshop_obj, $ch_obj, $quote_id, $address_type, $company_name);
            } else if ($address_type === self::ADDRESSTYPE_BILLING) {


                [$customer_address_id, $country_code] = $this->processBillingAddress($billing_address_options, $save_in_address_book, $customer_id, $billing_first_name, $billing_last_name, $billing_mobile_no, $billing_address, $billing_address_1, $billing_city, $billing_state, $billing_country, $billing_pincode, $company_name, $vat_no, $webshop_obj, $ch_obj, $quote_id, $billing_email_id, $same_as_billing);

            }

            $tax_shipping_response = $ch_obj->updateTaxAndShippingCharges($quote_id);

            $quote_expected_date = $ch_obj->get_sales_quote_items($quote_id);

            $cart_obj->UpateQuoteTotal( $quote_id);

            $message['statusCode'] = '200';
            $message['is_success'] = 'true';
            $message['email_id'] = $customer_email_id ?? '';
            $message['mobile_no'] = '9999999999'; //$customer_mobile;
            $message['customer_address_id'] = $customer_address_id;
            $message['country_code'] = $country_code;
            $message['eu_shipping_response'] = $tax_shipping_response;
            $message['quote_estimate_delivery'] = $quote_expected_date[0]['estimate_delivery_time'];
            $message['message'] = 'Checkout address Updated.';
            exit(json_encode($message));
        }
    }

    private function processShippingAddress($address_options, bool $save_in_address_book, bool $same_as_billing, $customer_id, $shipping_first_name, $shipping_last_name, $shipping_mobile_no, $shipping_address, $shipping_address_1, $shipping_city, $shipping_state, $shipping_country, $shipping_pincode, DbCommonFeature $webshop_obj, DbCheckout $ch_obj, $quote_id, int $address_type, string $company_name): array
    {
        if ($address_options === 'new' && $save_in_address_book && !$same_as_billing) {
            $table = 'customers_address';
            $columns = 'customer_id, first_name, last_name, mobile_no, address_line1, address_line2, city, state, country, pincode, created_at, ip, company_name';
            $values = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
            $params = [$customer_id, $shipping_first_name, $shipping_last_name, $shipping_mobile_no, $shipping_address, $shipping_address_1, $shipping_city, $shipping_state, $shipping_country, $shipping_pincode, time(), $_SERVER['REMOTE_ADDR'], $company_name];
            $customer_address_id = $webshop_obj->add_row($table, $columns, $values, $params);
        } elseif($address_options !== 'new' && ((int) $address_options > 0)) {
            $customer_address_id = $address_options;
            $AddressInfo = $webshop_obj->getCustomerAddressById($customer_address_id);
            if ($AddressInfo === false) {
                abort('Error while adding address. please try again.');
            }

            $shipping_first_name = $AddressInfo['first_name'];
            $shipping_last_name = $AddressInfo['last_name'];
            $shipping_mobile_no = $AddressInfo['mobile_no'];
            $shipping_address = $AddressInfo['address_line1'];
            $shipping_address_1 = (isset($AddressInfo['address_line2'])) ? $AddressInfo['address_line2'] : '';
            $shipping_city = $AddressInfo['city'];
            $shipping_state = $AddressInfo['state'];
            $shipping_country = $AddressInfo['country'];
            $shipping_pincode = $AddressInfo['pincode'];
            $shipping_company_name = $AddressInfo['company_name'];
        } else {
            $customer_address_id = 0;
        }

        $QuoteAddress = $ch_obj->getQuoteShippingAddressById($quote_id, $address_type);

        if ($QuoteAddress === false) {
            $table = 'sales_quote_address';
            $columns = 'quote_id, address_type, save_in_address_book,customer_address_id, first_name, last_name, mobile_no, address_line1, address_line2, city, state, country, pincode, same_as_billing, created_at, ip, company_name';
            $values = '?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
            $params = [$quote_id, $address_type, $save_in_address_book, $customer_address_id, $shipping_first_name, $shipping_last_name, $shipping_mobile_no, $shipping_address, $shipping_address_1, $shipping_city, $shipping_state, $shipping_country, $shipping_pincode, $same_as_billing, time(), $_SERVER['REMOTE_ADDR'], $company_name];
            $webshop_obj->add_row($table, $columns, $values, $params);
        } else {
            $ch_obj->updateShippingQuoteAddress($quote_id, $address_type, $shipping_first_name, $shipping_last_name, $address_options, $shipping_mobile_no, $shipping_address, $shipping_address_1, $shipping_city, $shipping_state, $shipping_country, $shipping_pincode, $save_in_address_book,$company_name,'', $same_as_billing);
        }
        return [$customer_address_id, $shipping_country];
    }

    private function processBillingAddress($billing_address_options, bool $save_in_address_book, $customer_id, $billing_first_name, $billing_last_name, $billing_mobile_no, $billing_address, $billing_address_1, $billing_city, $billing_state, $billing_country, $billing_pincode, string $company_name, string $vat_no, DbCommonFeature $webshop_obj, DbCheckout $ch_obj, $quote_id, $billing_email_id, bool $same_as_billing): array
    {
        if (($billing_address_options === 'new') && $save_in_address_book) {

            $table = 'customers_address';
            $columns = 'customer_id, first_name, last_name, mobile_no, address_line1, address_line2, city, state, country, pincode,company_name, vat_no, created_at, ip';
            $values = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
            $params = [$customer_id, $billing_first_name, $billing_last_name, $billing_mobile_no, $billing_address, $billing_address_1, $billing_city, $billing_state, $billing_country, $billing_pincode, $company_name, $vat_no, time(), $_SERVER['REMOTE_ADDR']];
            $customer_address_id = $webshop_obj->add_row($table, $columns, $values, $params);


        } elseif($billing_address_options !== 'new' && ((int) $billing_address_options > 0)) {
            $customer_address_id = $billing_address_options;
            $AddressInfo = $webshop_obj->getCustomerAddressById( $customer_address_id);
            if ($AddressInfo === false) {
                abort('Error while adding address. please try again.');
            }

            $billing_first_name = $AddressInfo['first_name'];
            $billing_last_name = $AddressInfo['last_name'];
            $billing_mobile_no = $AddressInfo['mobile_no'];
            $billing_address = $AddressInfo['address_line1'];
            $billing_address_1 = (isset($AddressInfo['address_line2'])) ? $AddressInfo['address_line2'] : '';
            $billing_city = $AddressInfo['city'];
            $billing_state = $AddressInfo['state'];
            $billing_country = $AddressInfo['country'];
            $billing_pincode = $AddressInfo['pincode'];
            $billing_company_name = $AddressInfo['company_name'];
        } else {

            $customer_address_id = 0;
        }



        $QuoteAddress = $ch_obj->getQuoteShippingAddressById($quote_id, self::ADDRESSTYPE_BILLING);

        if ($QuoteAddress === false) {

            $table = 'sales_quote_address';
            $columns = 'quote_id, address_type, save_in_address_book,customer_address_id, first_name, last_name, mobile_no, address_line1, address_line2, city, state, country, pincode, same_as_billing,  company_name, vat_no, created_at, ip';
            $values = '?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';

            $params = [$quote_id, self::ADDRESSTYPE_BILLING, $save_in_address_book, $customer_address_id, $billing_first_name, $billing_last_name, $billing_mobile_no, $billing_address, $billing_address_1, $billing_city, $billing_state, $billing_country, $billing_pincode, $same_as_billing, $company_name, $vat_no, time(), $_SERVER['REMOTE_ADDR']];

            $webshop_obj->add_row( $table, $columns, $values, $params);


        } else {


            $ch_obj->updateShippingQuoteAddress( $quote_id, self::ADDRESSTYPE_BILLING, $billing_first_name, $billing_last_name, $billing_address_options, $billing_mobile_no, $billing_address, $billing_address_1, $billing_city, $billing_state, $billing_country, $billing_pincode, $save_in_address_book, $company_name, $vat_no, $same_as_billing);
        }



        if (!empty($billing_email_id)) {
            $ch_obj->updateQuoteCustomerInfo($quote_id, $billing_email_id, $billing_first_name, $billing_last_name);
        }

        return [$customer_address_id ?? 0, $billing_country];
    }
}
