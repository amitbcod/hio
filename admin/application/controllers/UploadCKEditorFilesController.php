<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UploadCKEditorFilesController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}

		$this->load->library('Image_upload');
	}

	public function upload(){
		if(empty($_FILES['upload']['name'])) {
			return false;
		}

		$file_name = rand() . '/' . $_FILES['upload']['name'];
		
		print_r($file_name);exit;
		if (!$this->image_upload->upload_image($_FILES['upload'], 'cmsPagesImage', $file_name)) {
			echo json_encode(array(
				'flag' => 0,
				'msg' => "error occured while uploading image ! ",
			));
			exit;
		}

		$url = get_s3_url('cmsPagesImage/' . $file_name);

		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$_GET['CKEditorFuncNum']}, '$url', '');</script>";

	}
}
