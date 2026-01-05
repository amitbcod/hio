<?php

class CommonModel extends CI_Model

{

    public function GetProducts()

    {

        $sql = $this->db->get('products');

        if ($sql->num_rows() > 0) {

            $result = $sql->result_array();

            return $result;
        } else {

            return false;
        }
    }

    public function getCitiesByState($state_id)
    {
        return $this->db
            ->select('*')
            ->from('city_master')
            ->where('state_id', $state_id)
            ->get()
            ->result();
    }


    public function getFaqData()
    {
        return $this->db
            ->select('*')
            ->from('faqs')
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->get()
            ->result();
    }

    public function get_orders_by_date($date)
    {
        $start = strtotime($date . ' 00:00:00');
        $end   = strtotime($date . ' 23:59:59');

        $this->db->select('*');
        $this->db->from('b2b_orders');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->db->where('reveiw_sent', "0");

        // Print the SQL query without executing
        // echo $this->db->get_compiled_select(); 
        // die();

        $query = $this->db->get();

        // Debug: print the query
        // echo $this->db->last_query();
        // die();

        return $query->row(); // comment out while debugging
    }


    public function get_order_with_items($order_id)
    {
        $this->db->select('oi.*, p.name as product_name');
        $this->db->from('b2b_order_items oi');
        $this->db->join('products p', 'oi.product_id = p.id');
        $this->db->where('oi.order_id', $order_id);
        return $this->db->get()->result();
        //  $query = $this->db->get();

        // Debug: print the query
        // echo $this->db->last_query();
        // die();
    }

    public function getEmailTemplateByIdentifier($identifier)
    {
        $result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();
        return $result;
    }
    public function get_b2b_orders($order_id)
    {
        return $this->db
            ->select('*')
            ->from('b2b_orders')
            ->where('order_id', $order_id)
            ->get()
            ->row();
    }
    public function get_sale_orders($order_id)
    {
        return $this->db
            ->select('*')
            ->from('sales_order')
            ->where('order_id', $order_id)
            ->get()
            ->row();
    }

    public function get_order_by_id($order_id)
    {
        return $this->db->select('order_id, increment_id, created_at, grand_total, status')
            ->from('sales_order')
            ->where('order_id', $order_id)
            ->get()
            ->row();
    }

    public function get_product_by_order($order_id, $product_id)
    {
        return $this->db->select('product_id, product_name')
            ->from('sales_order_items')
            ->where('order_id', $order_id)
            ->where('product_id', $product_id)
            ->get()
            ->row();
    }

    public function get_customer_data($email_id)
    {
        return $this->db
            ->select('*')
            ->from('customers')
            ->where('email_id', $email_id)
            ->get()
            ->row();
    }
    public function driver_edit($data)
    {
        $id = $data['id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $mobile_no = $data['mobile_no'];

        $updateTrip = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'mobile_no' => $mobile_no,
            'updated_at' => strtotime(date('Y-m-d H:i:s')),
        );
        $this->db->where('id', $id);
        return $this->db->update('driver_details', $updateTrip);
    }
    public function get_driver_details($driver_id)
    {
        return $this->db
            ->select('id,first_name,last_name,mobile_no,email,profile_photo,driver_licence_no,licence_plate_no')
            ->from('driver_details')
            ->where('id', $driver_id)
            ->get()
            ->row();
    }
    public function get_pickup_listing($driver_id, $date = null)
    {
        $this->db
            ->select('
                bopd.order_id,
                bo.order_barcode,
                m.id as merchant_id,
                m.publication_name,
                m.company_address,
                m.phone_no,
                m.latitude,
                m.longitude
            ')
            ->from('b2b_orders_pickup_details as bopd')
            ->join('b2b_orders as bo', 'bopd.order_id = bo.order_id', 'left')
            ->join('publisher as m', 'bo.publisher_id = m.id', 'left')
            ->where('bopd.pickup_status', 2)
            ->where('bopd.driver_id', $driver_id);

        // Filter by date if provided
        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(bopd.created_at)', $converted_date);
        }

        // Order by order_id descending
        $this->db->order_by('bopd.order_id', 'DESC');

        $query = $this->db->get();

        // For debugging, uncomment the next line:
        // echo $this->db->last_query(); die();

        return $query->result();
    }

    public function get_delivery_listing($driver_id, $date = null)
    {
        // Step 1: Get delivery records
        $this->db->select('order_id, is_parent_level, webshop_order_id');
        $this->db->from('b2b_orders_delivery_details as b2bodd');
        $this->db->where('driver_id', $driver_id);
        $this->db->where('delivery_type', '2');
        $this->db->where_in('delivery_status', [1, 3]);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(delivery_date)', $converted_date);
        }

        $delivery_query = $this->db->get();
        $deliveries = $delivery_query->result_array();

        if (empty($deliveries)) {
            return [];
        }

        $final = []; // Will hold the flattened results

        // Step 2: Get product/details for each delivery
        foreach ($deliveries as $delivery) {
            $is_parent_level = $delivery['is_parent_level'];

            if ($is_parent_level == 1) {
                $this->db->select('
                    DISTINCT b2bodd.webshop_order_id AS order_id,
                    b.order_barcode,
                    b2bodd.is_parent_level,
                    soa.first_name,
                    soa.last_name,
                    soa.mobile_no,
                    soa.address_line1,
                    soa.address_line2,
                    soa.city,
                    soa.state,
                    soa.country,
                    soa.pincode
                ', false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                    ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                    ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);
            } else {
                $this->db->select('
                    DISTINCT b2bodd.order_id AS order_id,
                    b.order_barcode,
                    b2bodd.is_parent_level,
                    soa.first_name,
                    soa.last_name,
                    soa.mobile_no,
                    soa.address_line1,
                    soa.address_line2,
                    soa.city,
                    soa.state,
                    soa.country,
                    soa.pincode
                ', false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                    ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                    ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.order_id', $delivery['order_id']);
            }

            $details = $this->db->get()->row_array(); // Get single row
            if (!empty($details)) {
                $final[] = $details; // Add directly to final array
            }
        }

        return $final;
    }


    public function get_pickup_order_details($driver_id, $order_id)
    {
        $this->db->select('
                bopd.order_id AS order_id,
                bopd.image,
                bopd.thumbnail,
                bopd.remarks,
                bopd.product_details,
                b.order_barcode,
                b.grand_total,
                bo.product_id,
                bo.product_name,
                bo.qty_ordered,
                bo.price,
                bo.total_price,
                bo.is_fragile_flag,
                p.base_image
            ')
            ->from('b2b_orders_pickup_details as bopd')
            ->join('b2b_orders as b', 'bopd.order_id = b.order_id', 'left')
            ->join('b2b_order_items as bo', 'bopd.order_id = bo.order_id', 'left')
            ->join('products as p', 'p.id = bo.product_id', 'left')
            ->where('bopd.driver_id', $driver_id)
            ->where('bopd.order_id', $order_id)
            ->group_by('bo.product_id');

        $query = $this->db->get();
        $result = $query->result_array();

        if (empty($result)) {
            return [];
        }

        // Order-level data
        $order = [
            'order_id' => $result[0]['order_id'],
            'order_barcode' => $result[0]['order_barcode'],
            'grand_total' => $result[0]['grand_total'],
            'image' => !empty($result[0]['image'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/' . $result[0]['image']
                : null,
            'thumbnail' => !empty($result[0]['thumbnail'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $result[0]['thumbnail']
                : null,
            'remark' => $result[0]['remarks'],
            'products' => []
        ];
        $productDetailsJson = json_decode($result[0]['product_details'], true) ?? [];
        // Build products array with nested product_details
        // Build products array
        foreach ($result as $row) {
            $productId = $row['product_id'];
            $details = $productDetailsJson[$productId] ?? [];

            // Prepare image URLs if present in product_details
            $pickupImage = !empty($details['pickup_image'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/' . $details['pickup_image']
                : null;

            $pickupThumb = !empty($details['pickup_thumb'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $details['pickup_thumb']
                : null;

            $order['products'][] = [
                'product_id' => $productId,
                'product_name' => $row['product_name'],
                'qty_ordered' => $row['qty_ordered'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
                'is_fragile_flag' => $row['is_fragile_flag'],
                'base_image' => !empty($row['base_image'])
                    ? IMAGE_URL . '/uploads/products/thumb/' . $row['base_image']
                    : IMAGE_URL . '/uploads/products/thumb/no_image.png',
                // Only add pickup images if they exist in product_details
                'pickup_image' => $pickupImage,
                'pickup_thumb' => $pickupThumb
            ];
        }


        return (object)['order' => (object)$order];
    }


    public function get_delivery_order_details($driver_id, $order_id, $is_parent_level = null)
    {
        if ($is_parent_level == 1) {
            // Parent order: join webshop_order_id with sales_order_items
            $this->db->select('
                DISTINCT b2bodd.webshop_order_id AS order_id,
                b2bodd.is_parent_level,
                b2bodd.delivery_attempt_no,
                b2bodd.delivery_status,
                b2bodd.image,
                b2bodd.thumbnail,
                b2bodd.remarks,
                b.order_barcode,
                so.grand_total,
                soi.product_id,
                soi.product_name,
                soi.qty_ordered,
                soi.price,
                soi.total_price,
                soi.is_fragile_flag,
                p.base_image
            ', false)
                ->from('b2b_orders_delivery_details as b2bodd')
                ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                ->join('sales_order_items as soi', 'b2bodd.webshop_order_id = soi.order_id', 'left')
                ->join('products as p', 'p.id = soi.product_id', 'left')
                ->where('b2bodd.delivery_type', '2')
                ->where('b2bodd.driver_id', $driver_id)
                ->where('b2bodd.webshop_order_id', $order_id)
                ->group_by('soi.product_id');  // ensures no duplicates
        } else {
            // Child order: join with b2b_order_items
            $this->db->select('
                DISTINCT b2bodd.order_id AS order_id,
                b2bodd.is_parent_level,
                b2bodd.delivery_attempt_no,
                b2bodd.delivery_status,
                b2bodd.image,
                b2bodd.thumbnail,
                b2bodd.remarks,
                b.order_barcode,
                b.grand_total,
                bo.product_id,
                bo.product_name,
                bo.qty_ordered,
                bo.price,
                bo.total_price,
                bo.is_fragile_flag,
                p.base_image
            ', false)
                ->from('b2b_orders_delivery_details as b2bodd')
                ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                ->join('products as p', 'p.id = bo.product_id', 'left')
                ->where('b2bodd.delivery_type', '2')
                ->where('b2bodd.driver_id', $driver_id)
                ->where('b2bodd.order_id', $order_id)
                ->group_by('bo.product_id'); // ensures no duplicates
        }

        $query = $this->db->get();
        $result = $query->result_array();
        // echo $this->db->last_query(); die();

        if (empty($result)) {
            return [];
        }

        // Extract order-level info
        $order = [
            'order_id' => $result[0]['order_id'],
            'is_parent_level' => $result[0]['is_parent_level'],
            'delivery_attempt_no' => $result[0]['delivery_attempt_no'],
            'delivery_status' => $result[0]['delivery_status'],
            'grand_total' => $result[0]['grand_total'],
            'order_barcode' => $result[0]['order_barcode'],
            'image' => !empty($result[0]['image'])
                ? IMAGE_URL . 'admin/admin/public/images/delivery/' . $result[0]['image']
                : null,
            'thumbnail' => !empty($result[0]['thumbnail'])
                ? IMAGE_URL . 'admin/admin/public/images/delivery/thumbnail/' . $result[0]['thumbnail']
                : null,
            'remark' => $result[0]['remarks'],
            'products' => []
        ];

        // Use product_id as key to avoid duplicates
        $added = [];

        foreach ($result as $row) {
            if (isset($added[$row['product_id']])) continue; // skip duplicate

            $order['products'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'qty_ordered' => $row['qty_ordered'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
                'is_fragile_flag' => $row['is_fragile_flag'],
                'base_image' => !empty($row['base_image'])
                    ? IMAGE_URL . '/uploads/products/thumb/' . $row['base_image']
                    : IMAGE_URL . '/uploads/products/thumb/no_image.png'
            ];
            $added[$row['product_id']] = true;
        }

        return (object)['order' => (object)$order];
    }

    public function get_today_route($driver_id, $date)
    {
        // ---------------------------------
        // 1️⃣ GET PICKUP DATA
        // ---------------------------------
        $this->db
            ->select('
                bopd.order_id,
                bo.order_barcode,
                m.id as merchant_id,
                m.publication_name,
                m.company_address,
                m.location,
                m.city,
                m.state,
                m.zipcode,
                m.phone_no,
                m.latitude,
                m.longitude
            ')
            ->from('b2b_orders_pickup_details as bopd')
            ->join('b2b_orders as bo', 'bopd.order_id = bo.order_id', 'left')
            ->join('publisher as m', 'bo.publisher_id = m.id', 'left')
            ->where('bopd.pickup_status', 2)
            ->where('bopd.driver_id', $driver_id);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(bopd.created_at)', $converted_date);
        }

        $this->db->order_by('bopd.order_id', 'DESC');
        $pickup_query = $this->db->get();
        $pickup_data = $pickup_query->result_array();

        $filtered_pickup = []; // ✅ new filtered array

        foreach ($pickup_data as &$pickup) {
            $pickup['full_address'] = trim(
                $pickup['company_address'] . ', ' .
                    (!empty($pickup['location']) ? $pickup['location'] . ', ' : '') .
                    $pickup['city'] . ', ' .
                    $pickup['state'] . ' - ' .
                    $pickup['zipcode']
            );

            $pickup['latitude']  = $pickup['latitude'] !== null ? (float)$pickup['latitude'] : null;
            $pickup['longitude'] = $pickup['longitude'] !== null ? (float)$pickup['longitude'] : null;

            // ✅ Only include if both lat & long exist
            if (!empty($pickup['latitude']) && !empty($pickup['longitude'])) {
                $filtered_pickup[] = $pickup;
            }
        }

        $pickup_data = $filtered_pickup; // ✅ replace with filtered array

        // ---------------------------------
        // 2️⃣ GET DELIVERY DATA
        // ---------------------------------
        $this->db->select('order_id, is_parent_level, webshop_order_id')
            ->from('b2b_orders_delivery_details as b2bodd')
            ->where('driver_id', $driver_id)
            ->where('delivery_type', '2')
            ->where_in('delivery_status', [1, 3]);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(delivery_date)', $converted_date);
        }

        $delivery_query = $this->db->get();
        $deliveries = $delivery_query->result_array();

        $delivery_data = [];

        if (!empty($deliveries)) {
            foreach ($deliveries as $delivery) {
                $is_parent_level = $delivery['is_parent_level'];

                if ($is_parent_level == 1) {
                    $this->db->select('
                        DISTINCT b2bodd.webshop_order_id AS order_id,
                        b.order_barcode,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode,
                        ca.latitude,
                        ca.longitude
                    ', false)
                        ->from('b2b_orders_delivery_details as b2bodd')
                        ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                        ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                        ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
                        ->join('customers_address as ca', 'soa.customer_address_id = ca.id', 'left')
                        ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);
                } else {
                    $this->db->select('
                        DISTINCT b2bodd.order_id AS order_id,
                        b.order_barcode,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode,
                        ca.latitude,
                        ca.longitude
                    ', false)
                        ->from('b2b_orders_delivery_details as b2bodd')
                        ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                        ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                        ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
                        ->join('customers_address as ca', 'soa.customer_address_id = ca.id', 'left')
                        ->where('b2bodd.order_id', $delivery['order_id']);
                }

                $details = $this->db->get()->row_array();

                if (!empty($details)) {
                    $details['full_address'] = trim(
                        $details['address_line1'] . ' ' .
                            $details['address_line2'] . ', ' .
                            $details['city'] . ', ' .
                            $details['state'] . ', ' .
                            $details['country'] . ' - ' .
                            $details['pincode']
                    );

                    $details['latitude']  = $details['latitude'] !== null ? round((float)$details['latitude'], 7) : null;
                    $details['longitude'] = $details['longitude'] !== null ? round((float)$details['longitude'], 7) : null;

                    // ✅ Only include if both lat & long exist
                    if (!empty($details['latitude']) && !empty($details['longitude'])) {
                        $delivery_data[] = $details;
                    }
                }
            }
        }

        // ---------------------------------
        // 3️⃣ RETURN BOTH ARRAYS
        // ---------------------------------
        return [
            'pickup'   => $pickup_data,
            'delivery' => $delivery_data
        ];
    }





    public function get_customer_orders($customer_id, $limit = 50, $offset = 0)
    {
        $this->db
            ->select('`order`.`order_id`, `order`.`increment_id`, `order`.`created_at`, `order`.`grand_total`, `order`.`status`')
            ->from('sales_order as `order`')
            ->join('invoicing as `inv`', '`order`.`invoice_id` = `inv`.`id`', 'left')
            ->where('`order`.`customer_id`', $customer_id)
            ->where('`order`.`status !=', 7)
            ->order_by('`order`.`created_at`', 'DESC')
            ->limit($limit, $offset);

        return $this->db->get()->result();
    }

    public function get_order_products($order_id)
    {
        $this->db->select('item_id, product_id, product_name as name, qty_ordered as qty');
        $this->db->from('sales_order_items');
        $this->db->where('order_id', $order_id);

        $query = $this->db->get();
        // echo $this->db->last_Query();die();
        return $query->result();
    }

    public function get_help_desk_data($customer_id)
    {
        $this->db->select('*');
        $this->db->from('help_desk');
        $this->db->where('customer_id', $customer_id);

        $query = $this->db->get();
        // echo $this->db->last_Query();die();
        return $query->result();
    }


    public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '')
    {

        // $GlobalVar = $this->getGlobalVariableByIdentifier('fbc-admin-email');
        // if (isset($GlobalVar) && $GlobalVar->value != '') {
        $from_email = 'yellowmarketmu@gmail.com';
        // }
        $emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
        $subject = (isset($email_subject) && $email_subject != '') ? $email_subject : $emailTemplate->subject;
        $title = $emailTemplate->title;
        $emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);
        // $data['title'] = $title;
        $data['subject'] = $subject;
        $data['content'] = $emailBody;
        $content = $this->load->view('email_template/email_content', $data, TRUE);
        if ($this->sendHTMLMailSMTP($EmailTo, $subject, $content, $from_email, $attachment = "")) {
            return true;
        } else {

            return false;
        }
    }
    public function sendHTMLMailSMTP($to, $subject, $content, $from_email = '', $attachment = "", $webshop_smtp_host = "", $webshop_smtp_port = "", $webshop_smtp_username = "", $webshop_smtp_password = "", $webshop_smtp_secure = "")

    {

        $email = 'ranjana.patel@bcod.co.in';

        $this->load->library('email');

        if ($webshop_smtp_host != '' && $webshop_smtp_port != '' && $webshop_smtp_username != '' && $webshop_smtp_password != '' && $webshop_smtp_secure != '') {

            $config = array(

                'protocol'  => 'smtp',

                'smtp_host' => $webshop_smtp_host,

                'smtp_port' => $webshop_smtp_port,

                'smtp_user' => $webshop_smtp_username,

                'smtp_pass' => $webshop_smtp_password,

                'mailtype'  => 'html',

                'charset'   => 'utf-8',

                'smtp_crypto' => $webshop_smtp_secure,

            );

            $this->email->initialize($config);
        }

        $this->email->set_newline("\r\n");

        $this->email->from($from_email); // change it to yours

        $this->email->to($to); // change it to yours

        $this->email->subject($subject);

        $this->email->message($content);

        $this->email->set_mailtype("html");

        if ($this->email->send()) {

            return true;
        } else {

            return false;
        }
    }
}
