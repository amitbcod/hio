<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class AdminuserController extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model('AdminuserModel');

		if($this->session->userdata('LoginID') == ''){
			redirect(base_url());
		}
	}

    function email_exists() {
        $user_id = $this->input->post('user_id');

        $email = $this->input->post('email');

        $Email_Exist = $this->AdminuserModel->email_exists($email, $user_id);

        if ($Email_Exist) {
            echo 'false';
            exit();
        }
        else {
            echo 'true';
            exit();
        }
    }

    function adminLists() {
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('admin_user/read',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

        $data['side_menu']='adninuser';
        $data['add_admin_user'] = base_url('adminuser/add-admin-user');
        $this->load->view('adminusers/admin-lists', $data);
    }

    public function add_admin_users()
	{
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('admin_user/write',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

        $data['id'] = $this->uri->segment(3);

        $_SESSION['user_id'] = $this->uri->segment(3);

        $_SESSION['from_user'] = 'admin_user';

        $data['side_menu']='adninuser';

        $data['details'] = $this->AdminuserModel->display_records_by_id($data['id']);

        $data['roleType'] = $this->AdminuserModel->get_role_type();

		$this->load->view('adminusers/add_admin_users', $data);

	}

    public function loadAdminUsersAjax()
    {
        $adminuser_listing = $this->AdminuserModel->get_datatables_adminuser_details();
		$data = array();

		foreach ($adminuser_listing as $readData)
		{

            $edit_btn = '<a href="'.base_url().'adminuser/edit_user/'.$readData->id.'" class="btn link-purple delete-all-btn">Edit</a>';
            $delete_btn = '<a href="javascript:void(0);" onclick="delete_user('.$readData->id.')" class="btn link-purple delete-all-btn">Delete</a>';

			$row  = array();
			$row[] = $readData->id;
			$row[] = $readData->first_name;
			$row[] = $readData->email;
            $row[] = $readData->username;
            $row[] = ($readData->role_name == '') ? "Super Admin" : $readData->role_name;
            $row[] = ($readData->status == 0) ? "Active" : " Disable";
            $row[] = $edit_btn . ' ' . $delete_btn;



			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->AdminuserModel->countadminuserrecord(),
			"recordsFiltered" => $this->AdminuserModel->countfilteradminuserrecord(),
			"data" => $data,
		);

		echo json_encode($output);
		exit;
    }

    function add_admin_user_detail()
    {
        $user_id = $this->input->post('user_id');

        $first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');
		$email = $this->input->post('email');
        $username = $this->input->post('username');
		$usertype = $this->input->post('usertype');
		$cp_radio = $this->input->post('cp_radio');

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo json_encode(array('status'=>404,'msg'=>'Please Enter Valid Email ID'));exit;
        }

        if($user_id == '') {
            $password = md5($this->input->post('password'));

            if($first_name == "" || $last_name == "" || $email == "" || $password == "" || $username == '' || $usertype == '' || $cp_radio == '')
            {
                echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));exit;
            }

            $Email_Exist = $this->AdminuserModel->email_exists($email);

            if($Email_Exist) {
                echo json_encode(array('status'=>403,'msg'=>'Email Already Exists'));exit();
            }

            $data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'password' => $password,
                'username' => $username,
                'email' => $email,
                'role_id' => $usertype,
                'status' => $cp_radio,
                'created_by' => 1,
                'created_at' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );

            $result = $this->AdminuserModel->insert_user($data);

            if ($result) {
                $redirect = base_url('adminuser/user-lists');
                echo json_encode(array('status'=>200,'msg'=>'User Register Successfully','redirect'=> $redirect));
                exit();
            }
            else {
                echo json_encode(array('status'=>400,'msg'=>'User Registeration Failed'));
                return false;
            }
        }

        else {
            if($first_name == "" || $last_name == "" || $username == '' || $usertype == '' || $cp_radio == '')
            {
                echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));exit();
            }

            $Email_Exist = $this->AdminuserModel->email_exists($email, $user_id);

            if($Email_Exist) {
                echo json_encode(array('status'=>403,'msg'=>'Email Already Exists'));exit();
            }

            $data['data'] = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'role_id' => $usertype,
                'status' => $cp_radio,
                'updated_at' => time()
            );

            $data['condition'] = array('id'=>$user_id);

            $result = $this->AdminuserModel->update_admin_user_details($data);

            if($result)
            {
                echo json_encode(array('status'=>200,'msg'=>'Updated Succesfully', 'redirect'=> ''));
                exit();
            }
            else{
                echo json_encode(array('status'=>200,'msg'=>'Updation Failed'));exit();
            }
            $data['details'] = $this->AdminuserModel->display_records_by_id($id);
            $this->load->view('adminusers/add_admin_users', $data);
        }
    }

    public function edit_user()
    {
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('admin_user/write',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

        $user_id = $this->uri->segment(3);
        $data['details'] = $this->AdminuserModel->display_records_by_id($user_id);
        //$this->load->view('adminusers/edit_admin_user_details', $data);
        $this->load->view('adminusers/add_admin_users', $data);
    }

    function update_admin_user_detail()
    {
        $id = $this->input->post('id');
        $first_name = $this->input->post('first_name');
        //$email = $this->input->post('email');
        $cp_usertype_radio = $this->input->post('cp_usertype_radio');
        $last_name = $this->input->post('last_name');
        $username = $this->input->post('username');
        $cp_radio = $this->input->post('cp_radio');

        if($first_name == "" || $cp_usertype_radio == "" || $last_name == "" || $username == "" || $cp_radio == "")
        {
            echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));
            return false;
        }

        $data['data'] = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role_id' => $cp_usertype_radio,
            'status' => $cp_radio,
            'updated_at' => time()
        );

        $data['condition'] = array('id'=>$id);

        $result = $this->AdminuserModel->update_admin_user_details($data);

        if($result)
        {
            json_encode(array('status'=>200,'msg'=>'Updated Succesfully'));
        }
		else{
			json_encode(array('status'=>200,'msg'=>'Updated Succesfully'));
			return false;
		}
        $data['details'] = $this->AdminuserModel->display_records_by_id($id);
        $this->load->view('adminusers/edit_admin_user_details', $data);
    }

    public function delete_users() {
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('admin_user/write',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
        
		$id = $this->input->post('id');

		$result = $this->AdminuserModel->delete_admin_user_records($id);

		if($result)
        {
			echo json_encode(array('status'=>200,'msg'=>'Record Deleted'));
            exit();
        }
        else {
			echo json_encode(array('status'=>404,'msg'=>'Record not Deleted'));
            return false;
		}
	}
}

?>
