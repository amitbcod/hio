<?php

class ZumbaForwardRedirect
{
    private $redirects = [
      '/women' => '/category/women',
      '/men' => '/category/men',
      '/unisex' => '/category/women/unisex',
      '/shoes' => '/category/footwear-2',
      '/strong-id' => '/category/strong-id',
      '/accessories' => '/category/accessories-2',
      '/zumba-wear-kids' => '/category/zumba-wear-kids',
      '/tag/zumbini' => '/category/zumbini',
      '/tag/zumbini/' => '/category/zumbini',
    ];

    public function initialize(){
        if(SHOP_ID !== 1) {
            return;
        }

        if(isset($_GET['uri'])){
            if(array_key_exists($_GET['uri'], $this->redirects)){
                redirect($this->redirects[$_GET['uri']]);
            }
        }
        if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/returns'){
            redirect('/page/returns');
        }

        if(isset($_SERVER['REQUEST_URI']) && isset($_GET['utm_source']) && strtolower($_GET['utm_source']) === 'iterable' && strpos($_SERVER['REQUEST_URI'], 'searchresult') === false && strpos($_SERVER['REQUEST_URI'], 'category/') === false){
            redirect('/searchresult?s=newarrivals&utm_campaign='.$_GET['utm_campaign'].'&utm_medium=email&utm_source=Iterable');
        }
    }
}
