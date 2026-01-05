<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TestimonialController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        require_once(APPPATH.'repositories/TestimonialRepository.php');
    }

    function testtimonialList() {
        $LoginID = $this->session->userdata('LoginID');

        $data['testimonialLists'] = $testimonialLists =  TestimonialRepository::testtimonialList();

        $this->load->view('testimonials/testimonial_lists', $data);

        //echo "<pre>";print_r($data['testimonialLists']);
    }

}

?>