<?php

class ResetCacheController extends CI_Controller
{
    public function index(){
        if($this->input->get('token') === '9867CE92CE363D47D8C56A4332183') {
            $this->cache->clean();
        }
    }
}
