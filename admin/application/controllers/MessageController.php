<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MessageController extends CI_Controller{

	public function __construct(){

        parent::__construct();
		$this->load->model('MessageModel');
		$this->load->model('CommonModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		if(!empty($this->session->userdata('userPermission')) && !in_array('message',$this->session->userdata('userPermission'))){ 
			redirect(base_url('dashboard'));  }

    }


	public function messageList() {

			// $data['messageList'] = $this->MessageModel->getMessageDetails($_SESSION['ShopID'],$search_term);
			$data['PageTitle']='Messages';
			$data['side_menu']='messages';
			$this->load->view('messages/message_list',$data);

	}

	public function loadmessagesajax(){

		$search_term = $this->input->post('search[value]');
		$messageList = $this->MessageModel->getMessageDetails($_SESSION['ShopID'],$search_term);
		$total_count = (is_array($messageList)) ? count($messageList) : 0;
		$data = array();
		$no = $_POST['start'];
		$selected = 'selected';
		$columns = array(
                            0 =>'Ticket Number',
                            1 =>'Webshop Name',
                            2=> 'Subject',
                            3=> 'created_at',
                            4=> 'Time',
                            5=> 'Priority',
                            6=> 'Last Replied',
                            7=> 'Details'
                        );
		if(is_array($messageList))
		{
			foreach ($messageList as $readData) {
				if($readData->fbc_user_readflag == 1)
				{
					$read = "user_read";
				}
				else{
					$read = "unread";
				}
				$no++;
				$row = array();
				$row[]="#".$readData->ticket_no;
				$row[]=$readData->org_shop_name;
				$row[]=$readData->subject;
				$row[]=(isset($readData->updated_at))?date( SIS_DATE_FM, $readData->updated_at ) ." | ".date('h:i A', $readData->updated_at):date( SIS_DATE_FM, $readData->created_at ) ." | ".date('h:i A', $readData->created_at);
				$row[]='<select id="priority_select" class="'.$read.'" onchange="change_priority_listing(this.value,'.$readData->id.')" class="priority-list" data-id="'.$readData->id.'">
				<option class="low-priority" value="0" '. (($readData->priority == 0) ? "selected" : " " ).'>★ Low</option>
				<option class="medium-priority" value="1" '. (($readData->priority == 1) ? "selected" : " " ).' >★ Medium</option>
				<option class="high-priority" value="2" '. (($readData->priority == 2) ? "selected" : " " ).'>★ High</option></select>';
				if($readData->created_by_type != 0)
				{
					$last_admin_reply = $this->MessageModel->getLastReply($readData->ticket_no);
					if(is_array($last_admin_reply))
					{
						$row[]=$last_admin_reply['first_name']." ".$last_admin_reply['last_name']."-".$last_admin_reply['username'];
					}
					else
					{
						$row[] = '--';
					}

				}
				else
				{
					$row[]=$readData->first_name." ".$readData->last_name."-".$readData->username;
				}
				$row[]='<a class="link-purple" href="'.base_url().'message/view-message/'.$readData->id.'">View</a>';

				$data[] = $row;

			}
		}
		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $total_count,
						"recordsFiltered" =>$total_count,
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);

		exit;
	}

	public function closeMessageList(){



		//	$data['messageList'] = $this->MessageModel->getMessageDetailsClosed($_SESSION['ShopID']);
			$data['PageTitle']='Closed Messages';
			$data['side_menu']='messages';
			$this->load->view('messages/message_list_closed',$data);



	}

	public function loadClosedmessagesajax(){

		$messageList = $this->MessageModel->getMessageDetailsClosed($_SESSION['ShopID']);
		$total_count =count($messageList);
		$data = array();
		$no = $_POST['start'];
		$selected = 'selected';
		foreach ($messageList as $readData) {
			if($readData->fbc_user_readflag == 1)
			{
				$read = "user_read";
			}
			else{
				$read = "unread";
			}
			$no++;
			$row = array();
			$row[]="#".$readData->ticket_no;
			$row[]=$readData->org_shop_name;
			$row[]=$readData->subject;
			$row[]=(isset($readData->updated_at))?date( SIS_DATE_FM, $readData->updated_at ) ." | ".date('h:i A', $readData->updated_at):date( SIS_DATE_FM, $readData->created_at ) ." | ".date('h:i A', $readData->created_at);
			$row[]='<select id="priority_select" class="'.$read.'">
			<option class="low-priority" value="0" '. (($readData->priority == 0) ? "selected" : " " ).'>★ Low</option>
			<option class="medium-priority" value="1" '. (($readData->priority == 1) ? "selected" : " " ).' >★ Medium</option>
			<option class="high-priority" value="2" '. (($readData->priority == 2) ? "selected" : " " ).'>★ High</option></select>';
			if($readData->created_by_type != 0)
			{
				$last_admin_reply = $this->MessageModel->getLastReply($readData->ticket_no);
				if(is_array($last_admin_reply))
				{
					$row[]=$last_admin_reply['first_name']." ".$last_admin_reply['last_name']."-".$last_admin_reply['username'];
				}
				else
				{
					$row[] = '--';
				}

			}
			else
			{
				$row[]=$readData->first_name." ".$readData->last_name."-".$readData->username;
			}
			$row[]='<a class="link-purple" href="'.base_url().'message/view-message/'.$readData->id.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $total_count,
						"recordsFiltered" =>$total_count,
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);

		exit;
	}



	public function messageDetail()
	{

			$id = $this->uri->segment(3);
			$data['mainMessageDetails'] = $this->MessageModel->getMessageInfoById($id);
			$data['AllMessageList'] = $this->MessageModel->getMessageListById($id);
			$data['PageTitle']='Messages Details';
			$data['side_menu']='messages';
			foreach($data['AllMessageList'] as $key=>$val)
			{
				if($val->fbc_user_readflag  == 0)
				{
					echo $data['AllMessageList'][$key]->main_thread;
					$change_adminReadFlag = $this->MessageModel->changeUserReadFlag($data['AllMessageList'][$key]->id);

				}
			}
			$this->load->view('messages/message_details',$data);



	}



	public function addMessage(){
			$data['message_to'] =  $this->MessageModel->get_admin_details();
			$data['PageTitle']='Messages Add';
			$data['side_menu']='messages';
			$this->load->view('messages/message_add',$data);
	}



	public function getShopUserList(){

		$term_requested = $_GET['term'];
		$ShopByEmailData = $this->MessageModel->getShopByEmail($term_requested);

		echo json_encode($ShopByEmailData); exit();

	}


	public function submitMessage(){



		$attachments_text = '';
		$shop_id = $_SESSION['ShopID'];
		// $SISA_ID=$this->session->userdata('SISA_ID');


		if(empty($_POST['message_to']) /*|| empty($_POST['message_to_userId'])*/ || empty($_POST['message_subject'] ) || empty($_POST['message_description'] )){

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;

		}else{

			$countfiles = count($_FILES['messgagefiles']['name']);
			$incremt_id = 1;
			for($i=0;$i<$countfiles;$i++){

				if(!empty($_FILES['messgagefiles']['name'][$i]))

				{
					// Define new $_FILES array - $_FILES['file']
					$_FILES['file']['name'] = $_FILES['messgagefiles']['name'][$i];
					$_FILES['file']['type'] = $_FILES['messgagefiles']['type'][$i];
					$_FILES['file']['tmp_name'] = $_FILES['messgagefiles']['tmp_name'][$i];
					$_FILES['file']['error'] = $_FILES['messgagefiles']['error'][$i];
					$_FILES['file']['size'] = $_FILES['messgagefiles']['size'][$i];

					// Set preference
					$ext = pathinfo($_FILES['messgagefiles']['name'][$i], PATHINFO_EXTENSION);
					$new_name = 'msg-'.$shop_id.'-'.time().'-'.$incremt_id.'.'.$ext;

					$this->load->library('s3_filesystem', ['bucket' => 'fbc-admin']);
					$this->s3_filesystem->putFile($_FILES['file']['tmp_name'], '/messaging/' . $new_name);

					$data['filenames'][] = $_FILES['file']['name'];
				}
				$incremt_id++;
			}

			if(!empty($data['filenames'])){
				$attachments_text = implode(",",$data['filenames']);
			}

			$this->db->select_max('id');
			$result= $this->db->get('messages')->row_array();
			$ticket_no =  $result['id']+1;

			$insertData=array(
				'ticket_no'         => $ticket_no,
				'subject'    		=> $_POST['message_subject'],
				'priority'			=> 0,
				'shop_id'			=> $shop_id,
				'fbc_user_id'		=> $_SESSION['LoginID'],
				// 'fbc_admin_id' 		=> $SISA_ID,
				'created_by_type'	=> 1,
				// 'closed_by' 		=> $SISA_ID,
				// 'closed_by_type' 	=> 0,
				'status'			=> 1,
				'created_at'		=> strtotime(date('Y-m-d H:i:s')),
				'ip'				=> $_SERVER['REMOTE_ADDR'],
			);

			$this->db->insert('messages', $insertData);
			$insert_id = $this->db->insert_id();


			$insertData_other=array(
				'message_id' 		=> $insert_id,
				'message' 			=> $_POST['message_description'],
				'main_thread'    	=> 1,
				'attachments'		=> $attachments_text,
				'shop_id'			=> $shop_id,
				'fbc_user_id'		=> $_SESSION['LoginID'],
				// 'fbc_admin_id' 		=> $SISA_ID,
				'created_at'		=> strtotime(date('Y-m-d H:i:s')),
				'created_by_type'	=> 1,
				'fbc_user_readflag' => 0,
				'fbc_admin_readflag'=> 0,
				'ip'				=> $_SERVER['REMOTE_ADDR'],
			);

			$this->db->insert('messages_details', $insertData_other);

			echo json_encode(array('flag' => 1,'msg' => "Update Successfully!!")); exit();

		}

	}


	public function replyMessage(){


		$attachments_text = '';
		$shop_id = $_POST['shop_id'];
		$fbc_user_id = $_POST['fbc_user_id'];
		$message_id =  $_POST['message_id'];
		// $SISA_ID=$this->session->userdata('SISA_ID');


		if(empty($shop_id) || empty($fbc_user_id) || empty($message_id) || empty($_POST['message_description'] )){

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;

		}else{

			$countfiles = count($_FILES['messgagefiles']['name']);
			$incremt_id = 1;
			for($i=0;$i<$countfiles;$i++){

				if(!empty($_FILES['messgagefiles']['name'][$i]))

				{
					// Define new $_FILES array - $_FILES['file']
					$_FILES['file']['name'] = $_FILES['messgagefiles']['name'][$i];
					$_FILES['file']['type'] = $_FILES['messgagefiles']['type'][$i];
					$_FILES['file']['tmp_name'] = $_FILES['messgagefiles']['tmp_name'][$i];
					$_FILES['file']['error'] = $_FILES['messgagefiles']['error'][$i];
					$_FILES['file']['size'] = $_FILES['messgagefiles']['size'][$i];

					$ext = pathinfo($_FILES['messgagefiles']['name'][$i], PATHINFO_EXTENSION);
					$new_name = 'msg-'.$shop_id.'-'.time().'-'.$incremt_id.'.'.$ext;

					$this->load->library('s3_filesystem', ['bucket' => 'fbc-admin']);
					$this->s3_filesystem->putFile($_FILES['file']['tmp_name'], '/messaging/' . $new_name);
					// Initialize array
					$data['filenames'][] = $_FILES['file']['name'];
				}
				$incremt_id++;
			}



			if(!empty($data['filenames'])){
				$attachments_text = implode(",",$data['filenames']);
			}


			$updateData=array(
				'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
			);



			$this->db->where(array('id' => $message_id));
			$afftedRow = $this->db->update('messages', $updateData);

			if($afftedRow){

				$insertData=array(
					'message_id' 		=> $message_id,
					'message' 			=> $_POST['message_description'],
					'main_thread'    	=> 0,
					'attachments'		=> $attachments_text,
					'shop_id'			=> $shop_id,
					'fbc_user_id'		=> $fbc_user_id,
					// 'fbc_admin_id' 		=> $SISA_ID,
					'created_at'		=> strtotime(date('Y-m-d H:i:s')),
					'created_by_type'	=> 1,
					'fbc_user_readflag' => 0,
					'fbc_admin_readflag'=> 0,
					'ip'				=> $_SERVER['REMOTE_ADDR'],
				);

				$this->db->insert('messages_details', $insertData);

				echo json_encode(array('flag' => 1,'msg' => "Update Successfully!!")); exit();
			}

		}

	}



	function changePriorityMessage(){

		if($_POST['msg_id'] != "" && $_POST['priority_val'] != "" ){

			$updateData=array(
				'priority'    		=> $_POST['priority_val'],
				'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
			);

			$this->db->where(array('id' => $_POST['msg_id']));
			$afftedRow = $this->db->update('messages', $updateData);


			if($afftedRow){
				echo json_encode(array('flag' => 1, 'msg' => "Updated Successfully"));
				exit();

			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));
				exit;

			}

		}else{

			echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));
			exit;

		}
	}

	function closeMessageTopic(){

		if($_POST['id'] != ""){

			$updateData=array(
					'closed_by'    		=> $_SESSION['LoginID'],
					'closed_by_type'	=> 1,
					'status' 			=> 2,
					'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
				);

			$this->db->where(array('id' => $_POST['id']));
			$afftedRow = $this->db->update('messages', $updateData);

			if($afftedRow){
				echo json_encode(array('flag' => 1, 'msg' => "Topic Closed."));
				exit();

			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));
				exit;

			}

		}else{

			echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));
			exit;

		}

	}



}
