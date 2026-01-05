<?php

/**
 * @property CI_Controller $ci
 */
class TopMenu
{
    private $ci;
    private $top_menu_list;
    private $identifier;
    private $search_flag;
    private $feature_prod;

    use UsesRestAPI;
    public function __construct($identifier, $search_flag = '', $feature_prod = '')
    {

        $this->ci = &get_instance();
        $this->identifier = $identifier;
        $this->search_flag = $search_flag;
        $this->feature_prod = $feature_prod;

        $this->top_menu_list = HomeDetailsRepository::get_menus([
            'Identifier' => $this->identifier,
            'lang_code' => $this->ci->session->userdata('lcode') ?? 'en',
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1
        ]);
    }

    public function render()
    {
  
        if (empty($this->top_menu_list)) {
            return;
        }

        if ($this->identifier === 'top-menu') {
            $this->ci->template->load('components/top_menu', ['navCatData' => $this->top_menu_list->AllMenuLevels, 'menuType' => $this->top_menu_list->menu_type]);
        } else {
            $this->ci->template->load('components/category_menu', ['navCatData' => $this->top_menu_list->AllMenuLevels, 'search_flag' => $this->search_flag, 'feature_prod' => $this->feature_prod]);
        }
    }

    public function not_found_page()
    {
        
        if (empty($this->top_menu_list)) {
            return;
        }
        $this->ci->template->load('components/custom_404_page.php',['navCatData' => $this->top_menu_list->AllMenuLevels, 'search_flag' => $this->search_flag, 'feature_prod' => $this->feature_prod]);

        
    }
}
