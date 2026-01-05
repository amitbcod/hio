<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class TestimonialController extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model('TestimonialModel');

		if($this->session->userdata('LoginID') == ''){
			redirect(base_url());
		}
	}

    function testimonialLists() {
        $data['add_testimonial'] = base_url('testimonials/add-testimonial');
        $data['side_menu']='testimonials';
        $this->load->view('testimonials/testimonial-lists', $data);
    }

    function add_testimonials()
	{
        $data['id'] = $this->uri->segment(3);

        if($data['id'] == '') {
            $data['pageTitle'] = 'Add Testimonial Details';
        }

        else {
            $data['pageTitle'] = 'Edit Testimonial Details';
            
            $testimonialData= $this->TestimonialModel->getSingleDataByID('testimonials',array('id'=>$data['id']),'*');
            if($testimonialData == ''){
                redirect('dashboard');
            }    
        }
        $data['details'] = $this->TestimonialModel->display_records_by_id($data['id']);
        $data['side_menu']='testimonials';

        $this->load->view('testimonials/add_testimonials', $data);
	}

    function CheckImageType() {
        $allowed =  array('jpg', 'png', 'gif');

		$filename = $_FILES['custImages']['name'];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext,$allowed) ) {
				$appne_msg.=" Please Upload Files With .jpg, .png, .gif Extension Only.";
				$arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
			}
    }

    function CheckPdfType() {
        $allowed =  array('pdf');

		$filename = $_FILES['custPdfs']['name'];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext,$allowed) ) {
				$appne_msg.=" Please Upload Files With .pdf Extension Only.";
				$arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
			}
    }

    function add_testimonial_detail() {
        //$this->CheckImageType();

        //$this->CheckPdfType();

        $testimonial_id = $this->input->post('testimonial_id');

        $client_name = $this->input->post('client_name');
        $client_description = $this->input->post('client_description');
        $client_company = $this->input->post('client_company');
        $website = $this->input->post('website');
        $video_url = $this->input->post('video_url');
        $testimonial = $this->input->post('testimonial_data');
        $status = $this->input->post('status');

        if($client_name == "" || $client_description == "" || $testimonial == '')
        {
            echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));
            exit();
        }

				$Name_Exist = $this->TestimonialModel->name_exists($client_name);

				if($Name_Exist) {
						echo json_encode(array('status'=>403,'msg'=>'Testimonial Already Exists'));exit();
				}

        if($testimonial_id == '') {
            $data = array(
                'client_name' => $client_name,
                'client_description' => $client_description,
                'client_company' => $client_company,
                'website' => $website,
                'video_url' => $video_url,
                'testimonial' => $testimonial,
                'status' => $status,
                'created_by' => $_SESSION['LoginID'],
                'created_at' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );

            if (!empty($_FILES['custImages']['name'])) {

                //$config2["upload_path"] = FCPATH . '../uploads/testimonials/images/';
                $config2["upload_path"] = SIS_SERVER_PATH.'/'.'uploads/testimonials/images/';
                $config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|gif|GIF|png|PNG';
                $config2['max_size']			= 1024 * 5;
                $config2['max_width']		= '0';
                $config2['max_height']		= '0';
                $config2['overwrite']		= FALSE;
                $config2['encrypt_name']		= TRUE;

                $this->load->library('upload', $config2);

                $this->upload->initialize($config2);

                if ($_FILES['custImages']['tmp_name'] !== "") {
                    $_FILES["gfile"]["name"] = $attachment_name=$_FILES["custImages"]["name"];
                    $_FILES["gfile"]["type"] = $_FILES["custImages"]["type"];
                    $_FILES["gfile"]["tmp_name"] = $_FILES["custImages"]["tmp_name"];
                    $_FILES["gfile"]["error"] = $_FILES["custImages"]["error"];
                    $_FILES["gfile"]["size"] = $_FILES["custImages"]["size"];

                    if($_FILES["gfile"]["size"]>5*1048576)
                    {
                        $arrResponse  = array('status' =>400 ,'message'=>"Limit exceeds above 5 MB for ".$_FILES["gfile"]["name"]);
                        echo json_encode($arrResponse);exit;
                    }

                    $this->load->library('upload', $config2);

                    if(!$this->upload->do_upload('gfile'))
                    {
                        echo $this->upload->display_errors();
                        echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));
                        exit;
                    }

                    $uploadData = $this->upload->data();

                    $data['image_path'] = 'uploads/testimonials/images/' . $uploadData['file_name'];
                }
            }

            if (!empty($_FILES['custPdfs']['name'])) {

                //$config2["upload_path"] = FCPATH . '../uploads/testimonials/pdfs/';
                $config2["upload_path"] = SIS_SERVER_PATH.'/'.'uploads/testimonials/pdfs/';
                $config2["allowed_types"] = 'pdf|PDF';
                $config2['max_size']			= 1024 * 5;
                $config2['max_width']		= '0';
                $config2['max_height']		= '0';
                $config2['overwrite']		= FALSE;
                $config2['encrypt_name']		= TRUE;

                $this->load->library('upload', $config2);

                $this->upload->initialize($config2);

                if ($_FILES['custPdfs']['tmp_name'] !== "") {
                    $_FILES["gfile"]["name"] = $attachment_name=$_FILES["custPdfs"]["name"];
                    $_FILES["gfile"]["type"] = $_FILES["custPdfs"]["type"];
                    $_FILES["gfile"]["tmp_name"] = $_FILES["custPdfs"]["tmp_name"];
                    $_FILES["gfile"]["error"] = $_FILES["custPdfs"]["error"];
                    $_FILES["gfile"]["size"] = $_FILES["custPdfs"]["size"];

                    if($_FILES["gfile"]["size"]>5*1048576)
                    {
                        $arrResponse  = array('status' =>400 ,'message'=>"Limit exceeds above 5 MB for ".$_FILES["gfile"]["name"]);
                        echo json_encode($arrResponse);exit;
                    }

                    $this->load->library('upload', $config2);

                    if(!$this->upload->do_upload('gfile'))
                    {
                        echo $this->upload->display_errors();
                        echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));
                        exit;
                    }

                    $uploadData = $this->upload->data();

                    $data['pdf_path'] = 'uploads/testimonials/pdfs/' . $uploadData['file_name'];
                }
            }

            $result = $this->TestimonialModel->insert_testimonial($data);

            if ($result) {
                $redirect = base_url('testimonials/testimonial-lists');
                echo json_encode(array('status'=>200,'msg'=>'Testimonial Added Successfully','redirect'=> $redirect));
                exit();
            }
            else {
                echo json_encode(array('status'=>400,'msg'=>'Testimonial Addition Failed'));
                exit();
            }
        }
        else {
            $data['data'] = array(
                'client_name' => $client_name,
                'client_description' => $client_description,
                'client_company' => $client_company,
                'website' => $website,
                'video_url' => $video_url,
                'testimonial' => $testimonial,
                'status' => $status,
                'updated_at' => time()
            );

            if (!empty($_FILES['custImages']['name'])) {

                //$config2["upload_path"] = FCPATH . '../uploads/testimonials/images/';
                $config2["upload_path"] = SIS_SERVER_PATH.'/'.'uploads/testimonials/images/';
                $config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|gif|GIF|png|PNG';
                $config2['max_size']			= 1024 * 5;
                $config2['max_width']		= '0';
                $config2['max_height']		= '0';
                $config2['overwrite']		= FALSE;
                $config2['encrypt_name']		= TRUE;

                $this->load->library('upload', $config2);

                $this->upload->initialize($config2);

                if ($_FILES['custImages']['tmp_name'] !== "") {
                    $_FILES["gfile"]["name"] = $attachment_name=$_FILES["custImages"]["name"];
                    $_FILES["gfile"]["type"] = $_FILES["custImages"]["type"];
                    $_FILES["gfile"]["tmp_name"] = $_FILES["custImages"]["tmp_name"];
                    $_FILES["gfile"]["error"] = $_FILES["custImages"]["error"];
                    $_FILES["gfile"]["size"] = $_FILES["custImages"]["size"];

                    if($_FILES["gfile"]["size"]>5*1048576)
                    {
                        $arrResponse  = array('status' =>400 ,'message'=>"Limit exceeds above 5 MB for ".$_FILES["gfile"]["name"]);
                        echo json_encode($arrResponse);exit;
                    }

                    $this->load->library('upload', $config2);

                    if(!$this->upload->do_upload('gfile'))
                    {
                        echo $this->upload->display_errors();
                        echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));
                        exit;
                    }

                    $uploadData = $this->upload->data();

                    $data['data']['image_path'] = 'uploads/testimonials/images/' . $uploadData['file_name'];
                }
            }

            if (!empty($_FILES['custPdfs']['name'])) {

                //$config2["upload_path"] = FCPATH . '../uploads/testimonials/pdfs/';
                $config2["upload_path"] = SIS_SERVER_PATH.'/'.'uploads/testimonials/pdfs/';
                $config2["allowed_types"] = 'pdf|PDF';
                $config2['max_size']			= 1024 * 5;
                $config2['max_width']		= '0';
                $config2['max_height']		= '0';
                $config2['overwrite']		= FALSE;
                $config2['encrypt_name']		= TRUE;

                $this->load->library('upload', $config2);

                $this->upload->initialize($config2);

                if ($_FILES['custPdfs']['tmp_name'] !== "") {
                    $_FILES["gfile"]["name"] = $attachment_name=$_FILES["custPdfs"]["name"];
                    $_FILES["gfile"]["type"] = $_FILES["custPdfs"]["type"];
                    $_FILES["gfile"]["tmp_name"] = $_FILES["custPdfs"]["tmp_name"];
                    $_FILES["gfile"]["error"] = $_FILES["custPdfs"]["error"];
                    $_FILES["gfile"]["size"] = $_FILES["custPdfs"]["size"];

                    if($_FILES["gfile"]["size"]>5*1048576)
                    {
                        $arrResponse  = array('status' =>400 ,'message'=>"Limit exceeds above 5 MB for ".$_FILES["gfile"]["name"]);
                        echo json_encode($arrResponse);exit;
                    }

                    $this->load->library('upload', $config2);

                    if(!$this->upload->do_upload('gfile'))
                    {
                        echo $this->upload->display_errors();
                        echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));
                        exit;
                    }

                    $uploadData = $this->upload->data();

                    $data['data']['pdf_path'] = 'uploads/testimonials/pdfs/' . $uploadData['file_name'];
                }
            }

            $data['condition'] = array('id'=>$testimonial_id);

            $result = $this->TestimonialModel->update_testimonial_details($data);

            if($result)
            {
                echo json_encode(array('status'=>200,'msg'=>'Updated Succesfully', 'redirect'=> ''));
                exit();
            }
            else{
                echo json_encode(array('status'=>200,'msg'=>'Updation Failed'));exit();
            }
            $data['details'] = $this->TestimonialModel->display_records_by_id($id);
            $this->load->view('testimonials/add_testimonials', $data);
        }
    }

    function loadTestimonialsAjax()
    {
        $testimonials_listing = $this->TestimonialModel->get_datatables_testimonials_details();
		$data = array();

        $num = 1;

        foreach ($testimonials_listing as $readData)
        {
            $edit_btn = '<a href="'.base_url().'testimonials/edit_testimonial/'.$readData->id.'" class="btn link-purple delete-all-btn">Edit</a>';
            $delete_btn = '<a href="javascript:void(0);" onclick="delete_testimonial('.$readData->id.')" class="btn link-purple delete-all-btn">Delete</a>';

            $row  = array();
            $row[] = $num;
            $row[] = $readData->client_name;
            $row[] = $readData->client_description;
            $row[] = $readData->client_company;
            $row[] = ($readData->status == 1) ? "Enabled" : " Disabled";
            $row[] = date('Y-m-d H:i:s', $readData->created_at);
            $row[] = $edit_btn . ' ' . $delete_btn;

            $num++;

            $data[] = $row;
        }

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->TestimonialModel->counttestimonialrecord(),
			"recordsFiltered" => $this->TestimonialModel->countfiltertestimonialrecord(),
			"data" => $data,
		);

		echo json_encode($output);
		exit;
    }

    function delete_testimonial() {

        $testimonial_id = $this->input->post('testimonial_id');

		$result = $this->TestimonialModel->delete_testimonial_record($testimonial_id);

		if($result)
        {
			echo json_encode(array('status'=>200,'msg'=>'Record Deleted'));
            exit();
        }
        else {
			echo json_encode(array('status'=>404,'msg'=>'Record not Deleted'));
            exit();
		}
	}

    // Export data in CSV format
	public function DownloadAllTestimonialCSV()
	{

        $storData = array();
        $metaData[] = array('id' => 'ID', 'client_name' => 'Client Name', 'client_description' => 'Client Description', 'client_company' => 'Client Company', 'status' => 'Status', 'created_at' => 'Created At');

		// get data
		$testimonialsData = $this->TestimonialModel->getTestimonialsDetails();

        foreach($testimonialsData as $key=>$element) {
            $storData[] = array(
                'id' => $element['id'],
                'client_name' => $element['client_name'],
                'client_description' => $element['client_description'],
                'client_company' => $element['client_company'],
                'status' => ($element['status'] == 1) ? "Enabled" : " Disabled",
                'created_at' => date('Y-m-d H:i:s', $element['created_at'])
            );
        }

		$data = array_merge($metaData,$storData);

        $filename = 'Testimonials_'.date('Ymd_His').'.csv';

        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-type: application/csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        $handle = fopen('php://output', 'w');
        foreach ($data as $data) {
            fputcsv($handle, $data);
        }
        fclose($handle);
        exit;

        echo "success";exit;
	}
}

?>
