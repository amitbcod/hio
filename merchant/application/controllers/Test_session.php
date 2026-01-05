<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_session extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    // Set session data
    public function set() {
        $this->session->set_userdata('username', 'JohnDoe');
        echo "Session data set. <a href='" . base_url('test_session/get') . "'>Check session</a>";
    }

    // Get session data
    public function get() {
        $username = $this->session->userdata('username');
        if ($username) {
            echo "Session username: " . $username;
        } else {
            echo "No session data found.";
        }

        echo "<br><a href='" . base_url('test_session/destroy') . "'>Destroy session</a>";
    }

    // Destroy session
    public function destroy() {
        $this->session->sess_destroy();
        echo "Session destroyed. <a href='" . base_url('test_session/get') . "'>Check session</a>";
    }
}
