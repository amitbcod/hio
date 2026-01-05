<?php
/**
 * @property CI_Controller $ci
 */
class Login
{
    private $ci;
    private $zumba_api_flag;
    private $display_page;
   
    public function __construct($display_page=''){
        $this->ci =& get_instance();
        $this->display_page = $display_page;
    }

    public function render(){

        // $shop_Data = GlobalRepository::get_fbc_users_shop();
        // $this->zumba_api_flag = $shop_Data->result->zumba_api_flag;

        $this->ci->template->load('components/login', ['zumba_api_flag'=>$this->zumba_api_flag,'display_page'=>$this->display_page]);
    }

}
