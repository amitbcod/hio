<?php
class MessageModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function getShopByEmail($term_requested)
    {
        $result =$this->db->query("SELECT fu.fbc_user_id,fu.shop_id,fu.owner_name,fus.org_shop_name FROM `fbc_users` fu JOIN fbc_users_shop fus ON fus.fbc_user_id = fu.fbc_user_id WHERE `parent_id` = '0' AND `status` = '1' and fu.email like '%".$term_requested."%' or fu.owner_name like '%".$term_requested."%' or  fus.org_shop_name like '%".$term_requested."%' or  fu.shop_id like '%".$term_requested."%' ");		// echo $this->db->last_query();
        return $result->result();
    }
	
	public function getMessageDetails($ShopId,$vals){
		$serach_val= '';
	  if(isset($vals)&& $vals!="")
	  {
		$valsexp = explode(' ',$vals);
		
        	foreach($valsexp as $val) 
        	{
				$serach_val = "(`md`.`message_id` LIKE '%".$vals."%' ESCAPE '!' OR `fus`.`org_shop_name` LIKE '%".$vals."%' ESCAPE '!'  OR m.subject LIKE '%".$vals."%' ESCAPE '!')";
				
			}
			
	  }
		
		//$result =$this->db->query("SELECT * FROM messages msg join fbc_users_shop fus on fus.shop_id = msg.shop_id order by msg.id desc");
		
		$result =$this->db->query("SELECT m.*, a.`first_name`, a.`last_name`, a.`username`,fb.`owner_name` , fus.`org_shop_name` , md.`fbc_user_readflag` FROM `messages` m LEFT JOIN `messages_details` md ON ( m.`id`=md.`message_id` ) LEFT JOIN `adminusers` a ON ( md.`fbc_admin_id`=a.`id` )  AND m.created_by_type = 0 LEFT JOIN `fbc_users` fb ON ( md.`fbc_user_id`=fb.`fbc_user_id` )  AND m.created_by_type = 1  LEFT JOIN `fbc_users_shop` fus ON ( md.`shop_id`=fus.`shop_id` ) where md.`id` in ( select max(`id`) from `messages_details` WHERE shop_id = ".$ShopId." group by message_id ) ".($serach_val != '' ? 'and'. $serach_val : '')." and m.status = 1 ORDER BY m.`id` DESC");
		// echo $this->db->last_query();die();
		if($result->num_rows() > 0)
    		{
				return $result->result();
			}
			else
			{
				 return 0;
			}

	}
	
	public function getMessageDetailsClosed($ShopId,$vals=false){
		//$result =$this->db->query("SELECT * FROM messages msg join fbc_users_shop fus on fus.shop_id = msg.shop_id order by msg.id desc");
		$serach_val= '';
	  if(isset($vals)&& $vals!="")
	  {
		$valsexp = explode(' ',$vals);
		
        	foreach($valsexp as $val) 
        	{
				$serach_val = "(`md`.`message_id` LIKE '%".$vals."%' ESCAPE '!' OR `fus`.`org_shop_name` LIKE '%".$vals."%' ESCAPE '!'  OR m.subject LIKE '%".$vals."%' ESCAPE '!')";
				
			}
			
	  }
		$result =$this->db->query("SELECT m.*, a.`first_name`, a.`last_name`, a.`username`, fb.`owner_name` ,fus.`org_shop_name`, md.`fbc_user_readflag` FROM `messages` m LEFT JOIN `messages_details` md ON ( m.`id`=md.`message_id` ) LEFT JOIN `adminusers` a ON ( md.`fbc_admin_id`=a.`id` )  AND m.created_by_type = 0 LEFT JOIN `fbc_users` fb ON ( md.`fbc_user_id`=fb.`fbc_user_id` )  AND m.created_by_type = 1  LEFT JOIN `fbc_users_shop` fus ON ( md.`shop_id`=fus.`shop_id` ) where md.`id` in ( select max(`id`) from `messages_details` WHERE shop_id = ".$ShopId."  group by message_id ) ".($serach_val != '' ? 'and'. $serach_val : '')." and m.status = 2 ORDER BY m.`id` DESC");
		
		return $result->result();

	}
	
	public function getMessageInfoById($id){
		$result =$this->db->query("SELECT * FROM messages where id =".$id);
		return $result->row();

	}
	
	public function getMessageListById($id){
		$result =$this->db->query("SELECT * FROM messages_details where message_id =".$id);
		return $result->result();

	}		
	public function changeUserReadFlag($id)	
	{		
		$this->db->where('id',$id);		
		$this->db->set('fbc_user_readflag',1);		
		$result = $this->db->update('messages_details');

		if($result)		
		{			
			return true;		
		}		
		else		
		{			
			return false;		
		}	
	}
	
	public function getLastReply($message_id)
	{ 		
		$this->db->select('md.*,a.first_name, a.last_name, a.username');
		$this->db->from('messages_details md');
		$this->db->where('md.message_id',$message_id);	
		$this->db->where('md.created_by_type ',0);	
		$this->db->join('adminusers a','md.`fbc_admin_id`= a.`id`');
		$this->db->order_by('md.id DESC');		
		$result = $this->db->get();
		// echo $this->db->last_query();
		if($result->num_rows() > 0 )		
		{			
			return $result->row_array(); ;	
		}		
		else	
		{			
			return false;		
		}	
	}
	public function get_unread_messages()
	{
		$this->db->select('md.*,fus.org_shop_name,m.subject');
		$this->db->where('md.fbc_user_readflag',0);
		$this->db->where('md.shop_id',$_SESSION['ShopID'],);
		 $this->db->from('messages_details md');
		$this->db->join('fbc_users_shop fus','md.`fbc_user_id`= fus.`fbc_user_id`');
		$this->db->join('messages m','md.`message_id`= m.`ticket_no`');
		$result = $this->db->get();
		// echo $this->db->last_query();
		if($result->num_rows() > 0 )		
		{			
			return $result->result_array(); ;	
		}		
		else	
		{			
			return false;		
		}	
	}
	
	public function get_admin_details()
	{
		$this->db->where('user_type',1);
		$this->db->order_by('id');
		$result = $this->db->get('adminusers');
		// echo $this->db->last_query();
		if($result->num_rows() > 0 )		
		{			
			return $result->row_array(); ;	
		}		
		else	
		{			
			return false;		
		}
		
	}

	
}
