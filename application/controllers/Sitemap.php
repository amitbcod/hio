<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Sitemap extends CI_Controller
{
    public function index()
    {
        //static pages
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'cms_pages';
        $flag = 'own';
        $where = 'status = ? AND  remove_flag = ? ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0);
        $postArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        // $response= $this->restapi->post_method($apiUrl, $postArr);
        if ($response->is_success == 'true') {
            $data['cms_pages'] = $response->tableData;
        } else {
            $data['cms_pages'] = array();
        }
        // print_r($data['cms_pages']);die();

        //main category
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'category';
        $flag = 'main';
        $where = 'status = ? AND parent_id= ? ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0);
        $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        // $response = $this->restapi->post_method($apiUrl, $postArr);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        if ($response->is_success == 'true') {
            $data['categorymenu'] = $response->tableData;
        } else {
            $data['categorymenu'] = array();
        }
        // echo"<pre>"; print_r($data['categorymenu']);die();

        //main category
        $date = strtotime(date('d-m-Y'));
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'products';
        $flag = 'own';
        $where = 'status = ? AND  remove_flag = ? AND launch_date <= ?  AND product_type IN (?,?) ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0, $date, 'simple', 'configurable');
        $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        // $response = $this->restapi->post_method($apiUrl, $postArr);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        if ($response->is_success == 'true') {
            $data['products'] = $response->tableData;
        } else {
            $data['products'] = array();
        }

        // echo"<pre>"; print_r($data['products']);die();
        header("Content-Type: text/xml;charset=iso-8859-1");
        $this->load->view('sitemap', $data);
    }
}
