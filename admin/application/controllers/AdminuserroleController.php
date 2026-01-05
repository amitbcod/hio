<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminuserroleController extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        ini_set('display_errors', 1);

        $this->load->model('AdminuserroleModel');
        if ($this->session->userdata('LoginID') == '') {
            redirect(base_url());
        }
    }

    function editadminuserRole()
    {
        if ($_SESSION['UserRole'] !== 'Super Admin') {
            if (!empty($this->session->userdata('userPermission')) && !in_array('admin_user_role/read', $this->session->userdata('userPermission'))) {
                redirect('dashboard');
            }
        }

        $user_id = $_SESSION['LoginID'];
        $mode = 'user';
        $data['userdetails'] = $this->AdminuserroleModel->get_role_master($user_id);
        $data['add_admin_user_role'] = base_url('adminuserrole/add-admin-user-role');
        $data['side_menu'] = 'adminuserrole';
        $data['pageTitle'] = 'Admin User Role List';
        $this->load->view('adminuserrole/admin_user_role_list', $data);
    }

    function create_role_resource()
    {
        // echo "<pre>";
        // print_r($_POST);
        // die;
        if ($this->input->post('role_name') != "" && $this->input->post('resource_access') != "") {
            $roleId = $_POST['roleId'];
            $data['role_name'] = $this->input->post('role_name');
            $data['resource_access'] = $this->input->post('resource_access');
            $resource_checkbox['employee_resource'] = $this->input->post('myArray');

            $Role_Exist = $this->AdminuserroleModel->role_exists($this->input->post('role_name'));

            if ($Role_Exist) {
                echo json_encode(array('status' => 403, 'msg' => 'Admin User Role Already Exists'));
                exit();
            }

            if ($roleId > 0) {
                if ($_POST['resource_access'] == 0) {
                    $this->AdminuserroleModel->deleteRole($roleId);
                    $this->AdminuserroleModel->updateRole($roleId, $data);
                    $redirect = base_url('adminuserrole/edit-user-role');

                    echo json_encode(array('flag' => 1, 'msg' => "Role Updated.", 'redirect' => $redirect));
                    exit;
                } else {
                    $this->AdminuserroleModel->updateRole($roleId, $data);
                    $this->AdminuserroleModel->deleteRole($roleId);
                    $result = $this->AdminuserroleModel->insertRoleResource($roleId, $resource_checkbox['employee_resource']);
                    $redirect = base_url('adminuserrole/edit-user-role');
                    echo json_encode(array('flag' => 1, 'msg' => "Role Updated.", 'redirect' => $redirect));
                    exit;
                }
            } else {
                $data['created_at'] = time();
                $data['created_by'] = $_SESSION['LoginID'];
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
                $response = $this->AdminuserroleModel->insertRole($data);
                $result = $this->AdminuserroleModel->insertRoleResource($response, $resource_checkbox['employee_resource']);
                $redirect = base_url('adminuserrole/edit-user-role');
                echo json_encode(array('flag' => 1, 'msg' => "New Role Created.", 'redirect' => $redirect));
                exit;
            }
        } else {

            $redirect = base_url('adminuserrole/add-admin-user-role');
            echo json_encode(array('flag' => 0, 'msg' => "Please fill all requrired fields"));
            exit();
        }
    }

    function add_admin_user_role()
    {
        if ($_SESSION['UserRole'] !== 'Super Admin') {
            if (!empty($this->session->userdata('userPermission')) && !in_array('admin_user_role/write', $this->session->userdata('userPermission'))) {
                redirect('dashboard');
            }
        }

        $roleId = $this->uri->segment(3);
        $data['PageTitle'] = 'Edit Admin User Role';
        $data['userId'] = $_SESSION['LoginID'];
        $data['side_menu'] = 'adminuserrole';
        $data['roleId'] = $roleId;
        $data['parentData'] = $this->AdminuserroleModel->get_parent_data();
        $data['singleRole'] = $this->AdminuserroleModel->getSingleRoleNameByID($roleId);
        // echo "<pre>";
        // print_r($data['singleRole']);
        // die;
        $this->load->view('adminuserrole/add_admin_user_role', $data);
    }

    public function delete_role()
    {
        if ($_SESSION['UserRole'] !== 'Super Admin') {
            if (!empty($this->session->userdata('userPermission')) && !in_array('admin_user_role/write', $this->session->userdata('userPermission'))) {
                redirect('dashboard');
            }
        }

        $user_id = $this->input->post('user_id');
        $result = $this->AdminuserroleModel->delete_role($user_id);
        if ($result == true) {
            echo json_encode(array('flag' => 1, 'msg' => "Role Deleted Successfully."));
            exit;
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Role UnDeleted Successfully."));
            exit;
        }
    }
}
