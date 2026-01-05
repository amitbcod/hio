<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ImageresizeController extends CI_Controller {

    function __construct()
	{
		parent::__construct();
		// if($this->session->userdata('LoginID')==''){
		// 	redirect(base_url());
		// }

	   	$this->load->model('CategoryModel');
		$this->load->model('EavAttributesModel');
		$this->load->model('SellerProductModel');
		// $this->load->model('B2BImportModel');
		// $this->load->model('InboundModel');
		//$this->load->model('Multi_Languages_Model');
	 	$this->load->library('image_lib');
		// $this->load->library('pagination');
		//$this->load->library('Image_upload');
	}



    function imageresize()
    {

        $directory = $_SERVER['DOCUMENT_ROOT'].'/uploads/products/original/';
        $ThumbDirectory = $_SERVER['DOCUMENT_ROOT'].'/uploads/products/thumb/';
        $MediumDirectory = $_SERVER['DOCUMENT_ROOT'].'/uploads/products/medium/' ;
        $LargeDirectory = $_SERVER['DOCUMENT_ROOT'].'/uploads/products/large/';

        $files = scandir($directory,1);
        $Thumbfiles = scandir($ThumbDirectory,1);
        $Mediumfiles = scandir($MediumDirectory,1); 
        $Largefiles = scandir($LargeDirectory,1);

            foreach($files as $file)
            {
                if(is_file($directory.$file))
                {
                    if(!file_exists($ThumbDirectory.$file))
                    {
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $basename = bin2hex(random_bytes(8));
                        if($extension)
                        {
                             $this->makeThumbnail($file,$directory,$ThumbDirectory,$width='300',$height='300');
                        }
                        
                    }
                    if(!file_exists($MediumDirectory.$file))
                    {
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $basename = bin2hex(random_bytes(8));
                        if($extension){
                            $this->makeThumbnail($file,$directory,$MediumDirectory,$width='500',$height='500');
                        }
                            
                    }
                    if(!file_exists($LargeDirectory.$file))
                    {
                        // echo $file."</br>";
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $basename = bin2hex(random_bytes(8));
                        if($extension)
                        {
                            $this->makeThumbnail($file,$directory,$LargeDirectory,$width='800',$height='800');
                        }
                        
                    }
                    
                }
            }
    }

function makeThumbnail($file_name,$original_path,$thumb_path,$width='',$height='')
	{

		 $config_thumb = array(
			'allowed_types'     => 'jpg|jpeg|gif|png', //only accept these file types
			'max_size'          => 5*1024, //5MB max
			//'encrypt_name'		=> TRUE,
			'upload_path'       => $original_path //upload directory
		  );

			$width=(isset($width))?$width:'100';
			$height=(isset($height))?$height:'';
			$this->load->library('upload', $config_thumb);

			//your desired config for the resize() function
			$config_thumb = array(
			'source_image'      => $original_path.$file_name, //path to the uploaded image
			'new_image'         => $thumb_path.$file_name, //path to
			'maintain_ratio'    => true,
			'width'             => $width,
			'height'            => $height
			);

			// echo '<pre>';print_r($config_thumb);exit;

			//this is the magic line that enables you generate multiple thumbnails
			//you have to call the initialize() function each time you call the resize()
			//otherwise it will not work and only generate one thumbnail
			$this->image_lib->initialize($config_thumb);
			$this->image_lib->resize();
			return true;
	}
}