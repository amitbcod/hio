<?php



/**

 * @property CI_Controller $ci

 */

class FeaturedProducts

{

    private $ci;

    private $featured_product;



    public function __construct($limit=""){

        $this->ci =& get_instance();



        $limit_var = $limit ?? 25 ;







        $this->featured_product = ProductRepository::get_featured_products([

            'identifier' => 'recent_popular',

            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,

            'limit'=> $limit_var,

            'vat_percent_session' => $this->ci->session->userdata('vat_percent') ?? '',

            'lang_code' => $this->ci->session->userdata('lcode') ?? '',

            'customer_login_id'=>$this->ci->session->userdata('LoginID') ?? ''

        ]);

    }



    public function render(){

        

        if(empty($this->featured_product)) {

           return;

        }



        $this->ci->template->load('components/featured_products', ['featured_product'=>$this->featured_product]);

    }

    public function magazineswithgifts($category_id){

        echo $category_id; exit;

    }



        



}