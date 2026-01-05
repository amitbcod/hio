<?php
/**
 * @property CI_Controller $ci
 */
class Register
{
    private $ci;
    private $display_page;
   
    public function __construct($display_page=''){
        $this->ci =& get_instance();
        $this->display_page = $display_page;

        $table = 'country_master';
        $flag = 'own';
        $postArr2 = array('table_name'=>$table,'database_flag'=>$flag);
        $this->country_prifix = CommonRepository::get_table_data($postArr2); 

    }

    public function render(){

        $this->ci->template->load('components/register', ['display_page'=>$this->display_page, 'countryPrifix'=>$this->country_prifix->tableData]);
    }

}
