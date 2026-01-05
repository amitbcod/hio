<?php

namespace App\Controllers\WholesalePlatform;

use DbCheckout;
use DbCommonFeature;
use DbProductFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PlaceWholesaleOrderController
{
    private $dbCheckout;
    private $dbCommonFeature;
    private $dbProductFeature;
    private $order_id;

    public function __construct()
    {
        $this->dbCheckout = new DbCheckout();
        $this->dbCommonFeature = new DbCommonFeature();
        $this->dbProductFeature = new DbProductFeature();
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        // Place order webshop start
        $data = $request->getParsedBody();

        $this->validateRequest($data);

        $billing_address = $this->dbCheckout->get_default_billing_address($data['shopcode'], $data['customer_id']);
        $customer = $this->dbCommonFeature->getCustomerDetailById($data['shopcode'], $data['customer_id']);

        $products = $this->dbProductFeature->getMultipleProductDetails($data['shopcode'], array_column($data['order_items'], 'InternalId'));
        $currencies = $this->dbCommonFeature->get_currency_list($data['shopcode']);

        $data['ship_method_name'] = $this->dbCommonFeature->getShippingMethodName($data['shopcode'], $data['shipping_method_id']);

        $this->createSalesOrder($data, $customer, $currencies[$data['currency']]);

        $this->createOrderItems($data, $products);

        $this->setOrderAddresses($data, $billing_address, $consulation_no ?? '', $res_company_name ?? '', $res_company_address ?? '', $vat_vies_valid_flag ?? '');

        $this->dbCheckout->update_vatDetails_in_sales_order($data['shopcode'], $data['shop_id'], $this->order_id, $company_name ?? '', $vat_no ?? '', $consulation_no ?? '', $res_company_name ?? '', $res_company_address ?? '');

        $FbcData = $this->dbCheckout->getFbcUserIdByShopId($data['shop_id']);

        if ($FbcData !== false) {
            $this->dbCheckout->add_to_order_log(1, $this->order_id, $data['shop_id'], $FbcData['fbc_user_id']);
        }

        $this->dbCheckout->updateOrderStatus($data['shopcode'], $this->order_id, 0);  // set order status to 0 = Processing

        foreach($data['order_items'] as $order_item) {
                $this->dbCheckout->decrementAvailableQty($data['shopcode'], $order_item['InternalId'], $order_item['quantity']);
        }

        $orderData = $this->dbCheckout->getOrderDataById($data['shopcode'], $this->order_id);

        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['order_id'] = $this->order_id;
        $message['increment_id'] = $orderData['increment_id'];
        $message['grand_total'] = $orderData['base_grand_total'];
        $message['message'] = 'Order created successfully.';
        exit(json_encode($message));
    }

    private function validateRequest(array $data): void
    {
        if (empty($data['shopcode']) || empty($data['shop_id']) || empty($data['payment_method']) || empty($data['customer_id'])) {
            abort('Please pass all the mandatory values');
        }

        if (empty($data['shipping_address']['last_name']) ||
            empty($data['shipping_address']['email_id']) ||
            empty($data['shipping_address']['mobile']) ||
            empty($data['shipping_address']['addressline1']) ||
            empty($data['shipping_address']['country']) ||
            empty($data['shipping_address']['pincode']) ||
            empty($data['shipping_address']['city'])
        ) {
            abort('Please pass all the shipping address');
        }
    }

    private function createOrderItems($data, $products): void
    {
        foreach ($data['order_items'] as $order_item) {
            $product_variants = json_encode($this->dbCommonFeature->getProductVariants($data['shopcode'], $order_item['InternalId']));
            $product = $products[$order_item['InternalId']];

            $this->dbCheckout->add_to_sales_order_item(
                $data['shopcode'],
                $data['shop_id'],
                $this->order_id,
                $product['product_type'],
                'buy',
                $order_item['InternalId'],
                $product['name'],
                $product['product_code'] ?? '',
                $order_item['quantity'],
                $product['sku'],
                $product['barcode'],
                $order_item['price'],
                $order_item['quantity'] * $order_item['price'],
                $data['shop_id'],
                null,
                $product['parent_id'],
                $product_variants,
                null,
                '',
                $data['vat_percentage'],
                ($data['vat_percentage'] / 100) * $order_item['price'],
                0,
                0,
                0,
                0
            );
        }
    }

    private function setOrderAddresses($data, ?array $billing_address, $consulation_no, $res_company_name, $res_company_address, $vat_vies_valid_flag): void
    {
        $this->dbCheckout->add_to_sales_order_address(
            $data['shopcode'],
            $data['shop_id'],
            $this->order_id,
            2,
            $data['shipping_address']['first_name'] ?? '',
            $data['shipping_address']['last_name'] ?? '',
            0,
            $data['shipping_address']['mobile'],
            $data['shipping_address']['addressline1'],
            $data['shipping_address']['addressline2'],
            $data['shipping_address']['city'],
            $data['shipping_address']['state'] ?? '',
            $data['shipping_address']['country'],
            $data['shipping_address']['pincode'],
            0
        );

        if(is_null($billing_address)) {

            $this->dbCheckout->add_to_sales_order_address(
                $data['shopcode'],
                $data['shop_id'],
                $this->order_id,
                1,
                $data['shipping_address']['first_name'] ?? '',
                $data['shipping_address']['last_name'] ?? '',
                0,
                $data['shipping_address']['mobile'],
                $data['shipping_address']['addressline1'],
                $data['shipping_address']['addressline2'],
                $data['shipping_address']['city'],
                $data['shipping_address']['state'] ?? '',
                $data['shipping_address']['country'],
                $data['shipping_address']['pincode'],
                0
            );
        } else {
            $this->dbCheckout->add_to_sales_order_address(
                $data['shopcode'],
                $data['shop_id'],
                $this->order_id,
                1,
                $billing_address['first_name'],
                $billing_address['last_name'],
                $billing_address['id'],
                $billing_address['mobile_no'],
                $billing_address['address_line1'],
                $billing_address['address_line2'],
                $billing_address['city'],
                $billing_address['state'],
                $billing_address['country'],
                $billing_address['pincode'],
                0,
                $billing_address['company_name'],
                $billing_address['vat_no'],
                $consulation_no ?? '',
                $res_company_name ?? '',
                $res_company_address ?? '',
                $vat_vies_valid_flag ?? ''
            );
        }
    }

    private function createSalesOrder($data, $customer, $currencies)
    {
        $base_subtotal = 0;
        $total_qty_ordered = 0;
        $base_tax_amount = 0;

        foreach ($data['order_items'] as $order_item) {
            $base_subtotal += $order_item['quantity'] * $order_item['price'];
            $total_qty_ordered += $order_item['quantity'];
            $base_tax_amount += ($order_item['quantity'] * $order_item['price']) * ($data['vat_percentage'] / 100);// Order tax (vat)
        }

        $subtotal = $base_subtotal; // Order total ex tax ex shipping (?)
        $shipping_tax_percent = $data['vat_percentage']; // shipping tax %
        $shipping_tax_amount = round($data['shipping_cost'] * ($shipping_tax_percent / 100), 2); // shipping tax
        $shipping_amount = $data['shipping_cost'] + $shipping_tax_amount; // Order shipping amount incl tax
        $base_grand_total = $base_subtotal + $shipping_amount; // Order Total

        $ship_method_name = $data['ship_method_name']; // Name for the shipping
        $tax_amount = $base_tax_amount; // tax amount ???

        // CREATE sales order
        $this->order_id = $this->dbCheckout->add_to_sales_order(
            $data['shopcode'],
            $data['shop_id'],
            'login',
            $data['customer_id'],
            4,
            $customer['email_id'],
            $customer['first_name'],
            $customer['last_name'],
            '',
            '',
            0,
            $base_grand_total,
            0,
            0,
            $base_subtotal,
            $base_tax_amount,
            0,
            $base_grand_total,
            $shipping_amount,
            $shipping_tax_amount,
            $data['shipping_cost'],
            $shipping_tax_percent,
            $subtotal,
            $tax_amount,
            $total_qty_ordered,
            null,
            0,
            false,
            true,
            0,
            0,
            0,
            0,
            $data['shipping_method_id'],
            $ship_method_name,
            $currencies['name'],
            $data['currency'],
            $currencies['conversion_rate'],
            $currencies['is_default_currency'],
            $currencies['symbol'],
            $data['order_number']
        );


        if ($this->order_id === false) {
            abort('Could not create order');
        }
    }
}