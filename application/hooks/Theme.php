<?php

class Theme
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        if (!isset($this->CI->session)) {  //Check if session lib is loaded or not
              $this->CI->load->library('session');  //If not loaded, then load it here
        }

        if (file_exists(APPPATH.'config/constants.php')){
            require_once(APPPATH.'config/constants.php');
        }
    }

    public function setCurrentTheme()
    {

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;//exit;
        $theme_set_flag = $this->CI->session->userdata('theme_set_flag');
    
        if ($theme_set_flag != 1 ) {
            
            $shop_themeDetails = GlobalRepository::get_theme($shopcode,$shop_id);
            if(isset($shop_themeDetails->themeDetail)){
               
                if($shop_themeDetails->themeDetail->theme_code =='theme1' ){
                    $this->CI->session->set_userdata('theme_id', 1);
                    $this->CI->session->set_userdata('theme_name', '');
                }else{
                    $this->CI->session->set_userdata('theme_id', $shop_themeDetails->themeDetail->id);
                    $this->CI->session->set_userdata('theme_name', $shop_themeDetails->themeDetail->theme_code);
                }

                $this->CI->session->set_userdata('theme_set_flag', 1);
            }else{
                $this->CI->session->set_userdata('theme_id', '');
                $this->CI->session->set_userdata('theme_name', '');
                $this->CI->session->set_userdata('theme_set_flag', 1);
            }

            defined('SKIN_THEMENAME') || define('SKIN_THEMENAME', $this->CI->session->userdata('theme_name').'/');
            defined('THEMENAME') || define('THEMENAME', $this->CI->session->userdata('theme_name'));
            defined('TEMP_SKIN_IMG_THEME') || define('TEMP_SKIN_IMG_THEME',  BASE_URL.'public/images/'.$this->CI->session->userdata('theme_name'));
            defined('BANNER_DEFAULT_IMG') ||  define('BANNER_DEFAULT_IMG', TEMP_SKIN_IMG.'/'.THEMENAME.'/default_banner_image.png');
        }else{

            defined('SKIN_THEMENAME') || define('SKIN_THEMENAME', $this->CI->session->userdata('theme_name').'/');
            defined('THEMENAME') || define('THEMENAME', $this->CI->session->userdata('theme_name'));    
            defined('TEMP_SKIN_IMG_THEME') || define('TEMP_SKIN_IMG_THEME',  BASE_URL.'public/images/'.$this->CI->session->userdata('theme_name'));
           defined('BANNER_DEFAULT_IMG') ||  define('BANNER_DEFAULT_IMG', TEMP_SKIN_IMG.'/'.$this->CI->session->userdata('theme_name').'/default_banner_image.png');

        }
    }
}
