<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductReviewController extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		
		$this->load->model('UserModel');
		$this->load->model('ProductReviewModel');
		$this->load->model('CommonModel');
		$this->load->library('encryption');

	}

	public function change_reviews_status(){
		if (isset($_POST) && !empty($_POST)) {
			$change_reviews_status = $this->ProductReviewModel->change_reviews_status($_POST['status'], $_POST['id']);
			if ($change_reviews_status) {
					echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully","status" => '1'));
					exit();
				} else {
					echo json_encode(array("flag"=> 1,"msg" => "Updated faield","status" => '2'));
					exit();
				}
			}
		else {
			echo json_encode(array("flag"=> 0,"msg" => "Please Post Data"));
			exit();
		}
	}

	public function reviewlist()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
			if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/product_reviews',$this->session->userdata('userPermission'))){ 
				redirect('dashboard');
			}
		}

		if(isset($_SESSION['LoginID'])){

			$current_tab=$this->uri->segment(2);

			$data['PageTitle']='Webshop - Reviews';
			$data['side_menu']='webshop';
			$data['current_tab']=(isset($current_tab) && $current_tab!='')?$current_tab:'';

			$this->load->view('product_review/product_review_main',$data);
		}else{
			redirect(base_url('customer/login'));
		}
	}

	public function ajaxreviewlist()
	{

		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length']; // Rows display per page
		$columnIndex = $_POST['order'][0]['column']; // Column index
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc

		$search_param =array();
		if(!empty($_POST['search'])) {
			$search_param['keyword'] = $_POST['search']['value'];
		}
		$ProductReviewList = $this->ProductReviewModel->getProductReviewList($row,$rowperpage,$search_param);

		//print_r($ProductReviewList);exit;
		
		$totalRecordwithFilter = $this->ProductReviewModel->getProductReviewCount($search_param);
		
		$data = array();
		if(isset($ProductReviewList) && !empty($ProductReviewList)){
			foreach($ProductReviewList as $row){
			$product_url = base_url()."seller/product/edit/".$row['product_id'];
			$created = (isset($row['created_at']) && $row['created_at'] !='') ? date("d/m/Y" ,$row['created_at']):'';

			if(empty($this->session->userdata('userPermission')) || in_array('webshop/product_reviews/write',$this->session->userdata('userPermission'))){
				$delete_text = '<a onclick="DeleteReview('.$row['id'].')"><i class="fa fa-trash-alt"></i></a>';
			} else{
				$delete_text =  '-';
			}
			$action='';
			if($row['status'] == '1'){
				$action ='<button type="button" id="employee_status_update" name="employee_status_update" class="btn btn-success" onclick="change_reviews_status(0,'.$row['id'].')">Show</button>';
			}else{
				$action ='<button type="button" id="employee_status_update" name="employee_status_update" class="btn btn-danger" onclick="change_reviews_status(1,'.$row['id'].')">Hide</button>';
			}	

			$data[] = array(
				"id"=>'<input type="checkbox"  name="chk_sp[]" value="'.$row['id'].'" >',
				"product_url"=>'<a href="'.$product_url.'">'.$row['name'].'</a>',
				"customer_name"=>'<a href="'.base_url().'customer-details/'.$row['customer_id'].'">'.$row['first_name']. ' '.$row['last_name'].'</a>',
				"email_id"=>$row['email_id'],
				"rating"=>$row['rating'],
				"review"=>$row['review'],
				"created_at"=>$created,
				"view_url"=>'<a class="link-purple" onclick="ViewReviewById('.$row['id'].')">View</a>',
				"action"=>$action,
				"delete"=>$delete_text,
			);
		}
		}

		## Response
		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecordwithFilter,
			"iTotalDisplayRecords" => $totalRecordwithFilter,
			"aaData" => $data
		);

		echo json_encode($response);exit;

	}

	public function viewReviewById(){
		$review_id = $this->input->post('review_id');
		if(empty($review_id)) {
			echo json_encode(array('flag'=>0, 'msg'=>"Invalid Review ID."));
			exit;
		}else{
			$data['ProductReview'] = $this->ProductReviewModel->getProductReviewById($review_id);
			print_r($data['ProductReview']);die();
			$View = $this->load->view('product_review/popup-data', $data, true);
			echo json_encode(array('flag'=>1, 'data' => $View));
			exit;
		}

	}

	public function DeleteReview(){

		if(isset($_SESSION['LoginID'])){

			$review_id = $this->input->post('review_id');

			if(empty($review_id)) {
				$redirect = base_url('product-reviews');
				echo json_encode(array('flag'=>2, 'msg'=>"Invalid Review ID.", 'redirect' => $redirect));
				exit;
			}else{

				$this->db->where('id', $review_id);
				$this->db->delete('products_reviews');

				$redirect = base_url('product-reviews');
				$message = 'Review deleted successfully';
				echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
				exit;
			}

		}else{
			$redirect = base_url('customer/login');
			$message = 'Review deleted successfully';
			echo json_encode(array('flag' => 0, 'msg' => $message, 'redirect' => $redirect));
			exit;
		}

	}

	public function getProductReviewList(){
		if(isset($_POST)){

		$search_param =array();
			if(!empty($_POST['search'])) {
				$search_param['keyword'] = $_POST['search'];
			}
			$data['ProductReviewList'] = $ProductReviewList = $this->ProductReviewModel->getProductReviewList($search_param);

		$this->load->view('product_review/product_review_list',$data);
		}
	}

	public function delete_all_product_reviews()
	{
		$pr_arr= (isset($_POST['chk_sp']) && $_POST['chk_sp'] !='') ? $_POST['chk_sp'] : '';
		if(isset($pr_arr) && $pr_arr!='')
		{
			foreach ($pr_arr as  $value)
			{
				$where_arr=array('id'=>$value);
				$rowAffected = $this->ProductReviewModel->deleteData('products_reviews',$where_arr);
			}
			if($rowAffected > 0 )
				{
					$redirect = base_url('product-reviews');
					echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Rows",'redirect'=>$redirect));
					exit();
				}else{
					echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
					exit;
				}
		}else
		{
			echo json_encode(array('flag' => 0, 'msg' => "Please Select Product Review to delete."));
			exit;
		}

	}


}
