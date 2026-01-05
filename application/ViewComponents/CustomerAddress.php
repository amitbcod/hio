<?php
/**
 * @property CI_Controller $ci
 */
class CustomerAddress
{
    private $ci;
    private $addressList;
    private $restricted_access;
    
    public function __construct(){
        $this->ci =& get_instance();
    }

    public function list(){

        $LoginID = $this->ci->session->userdata('LoginID');

        $table = 'customers_address';
        $flag = 'own';
        $where = 'customer_id = ? AND remove_flag = ?';
        $order_by = 'ORDER BY id DESC';
        $params = array($LoginID,0);
        $postArr = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'order_by'=>$order_by,'params'=>$params);
        $response= CommonRepository::get_table_data($postArr);

        if (!empty($response) && isset($response) && $response->is_success=='true') {
            $this->addressList = $response->tableData;
        }else{
           return;
        }

        $identifier='restricted_access';
        $ApiResponse = GlobalRepository::get_custom_variable($identifier);
        if (!empty($ApiResponse) && isset($ApiResponse) && $ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $this->restricted_access = $restricted_access;

        $this->ci->template->load('components/customer_address_list', ['addressList' => $this->addressList,'restricted_access'=> $this->restricted_access]);

    }
}
