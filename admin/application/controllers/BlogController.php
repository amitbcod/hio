<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BlogController extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->model('BlogModel');

        if ($this->session->userdata('LoginID') == '') {
            redirect(base_url());
        }
    }

    function blogLists()
    {
        $data['side_menu'] = 'blogs';
        $data['add_blogs'] = base_url('blogs/add-blogs');
        $this->load->view('blogs/blogs-lists', $data);
    }

    function add_blogs()
    {
        $blog_id = $this->uri->segment(3);

        if ($blog_id == '') {
            $data['pageTitle'] = 'Add Blog Details';
        } else {
            $data['pageTitle'] = 'Edit Blog Details';
            $data['get_blogs'] = $this->BlogModel->get_blogs_data($blog_id);
            $blogData = $data['get_blogs'];
            if ($blogData == '') {
                redirect('dashboard');
            }
        }
        $data['get_blogs_details'] = $this->BlogModel->get_blogs_details_data($blog_id);
        $data['get_products_details'] = $this->BlogModel->get_products_details();
        $data['side_menu'] = 'blogs';


        $this->load->view('blogs/add_blogs', $data);
    }

    function get_products_details()
    {

        $search = $this->input->post('searchTerm');

        $data = $this->BlogModel->get_products_details($search);

        echo json_encode($data);
        exit();
    }

    function add_blog_detail()
    {

        $blog_id = $this->input->post('blog_id');

        if ($blog_id == '') {

            $blog_slug = url_title($this->input->post('blog_title'));
            $url_key = strtolower($blog_slug);
            $data_blogs = array(
                'title' => $this->input->post('blog_title'),
                'description' => $this->input->post('blog_description_data'),
                'url_key' =>  $url_key,
                'status' => $this->input->post('status'),
                'created_by' => $_SESSION['LoginID'],
                'created_at' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );

            $Role_Exist = $this->BlogModel->blog_exists($this->input->post('blog_title'));

            if ($Role_Exist) {
                echo json_encode(array('status' => 403, 'msg' => 'Blog Already Exists'));
                exit();
            }

            $inserted_blog_id = $this->BlogModel->insert_blogs($data_blogs);

            $sub_blog_title = $_POST['sub_blog_title'];

            $sub_blog_description = $_POST['sub_blog_description'];

            $product_lists = $_POST['productList'];

            // $Blog_details_Exist = $this->BlogModel->blog_details_exists($sub_blog_title);
            //
            // if($Blog_details_Exist) {
            // 		echo json_encode(array('status'=>403,'msg'=>'Sub-Blog Already Exists'));exit();
            // }

            $subscription_details = array();

            //Getting subscription_det POST value in Array
            for ($i = 1; $i <= $_POST['blogCount']; $i++) {
                $subscription_details[$i] = $_POST["subscription_det$i"];
            }

            //Convert Multidimensional array to Single
            $final_subscription_details = array();
            foreach ($subscription_details as $val) {
                foreach ($val as $val2) {
                    $final_subscription_details[] = $val2;
                }
            }

            $data_blogs_details = array(
                'title' => $sub_blog_title,
                'description' => $sub_blog_description,
                'product_id' => $product_lists,
                'display_subscription_details' => $final_subscription_details,
            );

            $result = $this->BlogModel->insert_blogs_details($data_blogs_details, $inserted_blog_id);

            if ($result) {
                $redirect = base_url('blogs/blogs-lists');
                echo json_encode(array('status' => 200, 'msg' => 'Blogs Added Successfully', 'redirect' => $redirect));
                exit();
            } else {
                echo json_encode(array('status' => 400, 'msg' => 'Blogs Addition Failed'));
                exit();
            }
        } else {

            if (isset($_POST['blogCount'])) {
                $blogIDCount = $_POST['blogCount'];
            }
            $blog_slug = url_title($this->input->post('blog_title'));
            $url_key = strtolower($blog_slug);

            $data_blogs['data'] = array(
                'title' => $this->input->post('blog_title'),
                'description' => $this->input->post('blog_description_data'),
                'url_key' =>  $url_key,
                'status' => $this->input->post('status'),
                'updated_at' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );

            $data_blogs['condition'] = array('id' => $blog_id);

            $result_blogs = $this->BlogModel->update_blogs($data_blogs);

            $subscription_details = array();

            if (isset($_POST['blogCount'])) {
                //Getting subscription_det POST value in Array
                for ($i = 1; $i <= $blogIDCount; $i++) {
                    $subscription_details[$i] = $_POST["subscription_det$i"];
                }

                //Convert Multidimensional array to Single
                $final_subscription_details = array();
                foreach ($subscription_details as $val) {
                    foreach ($val as $val2) {
                        $final_subscription_details[] = $val2;
                    }
                }

                $_POST['subscription_det'] = $final_subscription_details;
            }

            //For Blogs Update and New Insert
            if (isset($_POST['blogCount'])) {
                for ($i = 0; $i < $blogIDCount; $i++) {
                    $data = array(
                        'title' => $_POST['sub_blog_title'][$i],
                        'description' => $_POST['sub_blog_description'][$i],
                        'product_id' => $_POST['productList'][$i],
                        'display_subscription_details' => $_POST['subscription_det'][$i]
                    );

                    if (isset($_POST['sub_blog_id'][$i]) && $_POST['sub_blog_id'][$i] !== '') {
                        $where_arr = ['id' => $_POST['sub_blog_id'][$i]];
                        $this->BlogModel->updateNewData('blogs_details', $where_arr, $data);
                    } else {
                        $this->db->set('blog_id', $blog_id);
                        $this->BlogModel->insertData('blogs_details', $data);
                    }
                }
            }

            //Only for Blogs Update
            if (isset($_POST['editblogCount'])) {

                for ($i = 1; $i <= $_POST['editblogCount']; $i++) {
                    $subscription_details[$i] = $_POST["subscription_det$i"];
                }

                //Convert Multidimensional array to Single
                $final_subscription_details = array();
                foreach ($subscription_details as $val) {
                    foreach ($val as $val2) {
                        $final_subscription_details[] = $val2;
                    }
                }

                $_POST['subscription_det'] = $final_subscription_details;

                for ($i = 0; $i < $_POST['editblogCount']; $i++) {
                    $data = array(
                        'title' => $_POST['sub_blog_title'][$i],
                        'description' => $_POST['sub_blog_description'][$i],
                        'product_id' => $_POST['productList'][$i],
                        'display_subscription_details' => $_POST['subscription_det'][$i]
                    );

                    if (isset($_POST['sub_blog_id'][$i]) && $_POST['sub_blog_id'][$i] !== '') {
                        $where_arr = ['id' => $_POST['sub_blog_id'][$i]];
                        $this->BlogModel->updateNewData('blogs_details', $where_arr, $data);
                    }
                }
            }

            $redirect = base_url('blogs/blogs-lists');

            echo json_encode(array('status' => 200, 'msg' => 'Blogs Updated Successfully', 'redirect' => $redirect));
            exit();
        }
    }

    function loadBlogsAjax()
    {
        $blogs_listing = $this->BlogModel->get_datatables_blogs_details();
        $data = array();

        $num = 1;

        foreach ($blogs_listing as $readData) {
            $edit_btn = '<a href="' . base_url() . 'blogs/edit_blogs/' . $readData->id . '" class="btn link-purple delete-all-btn">Edit</a>';
            $delete_btn = '<a href="javascript:void(0);" onclick="delete_blog(' . $readData->id . ')" class="btn link-purple delete-all-btn">Delete</a>';

            $row  = array();
            $row[] = $num;
            $row[] = $readData->title;
            $row[] = $readData->description;
            $row[] = $readData->url_key;
            $row[] = ($readData->status == 1) ? "Enabled" : " Disabled";
            $row[] = date('Y-m-d H:i:s', $readData->created_at);
            $row[] = $edit_btn . ' ' . $delete_btn;

            $num++;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->BlogModel->countblogrecord(),
            "recordsFiltered" => $this->BlogModel->countfilterblogrecord(),
            "data" => $data,
        );

        echo json_encode($output);
        exit;
    }

    function delete_blog()
    {

        $blog_id = $this->input->post('blog_id');

        $result_blog = $this->BlogModel->delete_blogs_record($blog_id);

        $result_blog_details = $this->BlogModel->delete_blogs_details_record($blog_id);

        if ($result_blog && $result_blog_details) {
            echo json_encode(array('status' => 200, 'msg' => 'Record Deleted'));
            exit();
        } else {
            echo json_encode(array('status' => 404, 'msg' => 'Record not Deleted'));
            exit();
        }
    }

    public function deleteBlog()
    {
        $blog_id = $this->input->post('blockID');

        if ($blog_id) {
            $rowAffected = $this->BlogModel->delete_blog_detail_record($blog_id);
            if ($rowAffected) {
                echo json_encode(array('flag' => 1, 'msg' => "Success"));
                exit();
            } else {
                echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
            exit;
        }
    }

    public function update_meta()
    {
        $this->load->view('meta_update.php');
    }
}
