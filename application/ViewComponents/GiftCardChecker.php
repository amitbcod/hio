<?php

/**
 * @property CI_Controller $ci
 */
class GiftCardChecker
{
    private $ci;

    public function __construct(){
        $this->ci =& get_instance();
    }

    public function render(){
        $this->ci->template->load('components/gift-card-checker');
    }

}
