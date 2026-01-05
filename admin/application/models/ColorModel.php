<?php
class ColorModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			
			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		
			$this->shop_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}

	}

	public function getData($id =false){

		if(isset($id) && !empty($id))
		{
			$this->shop_db->where('id',$id);
			$result = $this->shop_db->get('base_colors');
			return $result->row();

			if($result->num_rows() > 0)
			{
				return true;
			}
			else
			{
				return false;
			}

		}
		else{

			$query = $this->shop_db->get('base_colors');

			return $query->result();
		}


	}

	public function getVariantData($id)
	{
		if(isset($id) && !empty($id))
		{
			$this->shop_db->where('base_color_id',$id);
			$result = $this->shop_db->get('base_colors_variants');
			return $result->result();

		}else{

			$query = $this->shop_db->get('base_colors_variants');

			return $query->result();
		}

	}


	public function insert_data($tablename,$insert_array)
	{

		$result = $this->shop_db->insert($tablename,$insert_array);
		$inserted_id = $this->shop_db->insert_id();

		if($result)
		{
			
			return $inserted_id;
		}
		
	}

	public function delete_data($id){	

		$this->shop_db->where('base_color_id', $id);
		$this->shop_db->delete('base_colors_variants');

		$this->shop_db->where('id', $id);
		$this->shop_db->delete('base_colors');

	}

	public function update_data($update_array,$id){

		$this->shop_db->where('id', $id);
		$result = $this->shop_db->update('base_colors',$update_array);

		if($result)
		{
			return true; 
		}

	}

	public function variant_delete($id){

		$this->shop_db->where('base_color_id', $id);
		$this->shop_db->delete('base_colors_variants');

	}

	public function getVariantByShop($id){


		$fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID
		$shop_id		=	$this->session->userdata('ShopID');
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		

		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		
			$this->seller_db = $this->load->database($config_app,TRUE);
			if($this->seller_db->conn_id) {
				
			} else {
				redirect(base_url());
			}
		}
		else{
			redirect(base_url());
		}


		$main_db_name=$this->shop_db->database;

		$this->db->select('*'); 
			$this->db->from('eav_attributes_options a');
			$this->db->join($main_db_name.'.base_colors_variants' , 'a.id = base_colors_variants.variant_option_id');
			$this->db->where('a.id', $id); 


		 $query = $this->db->get();         
		 return $query->row(); 
		
	

	}

	public function getVariantDataName($id)
	{
		if(isset($id) && !empty($id))
		{
			$this->shop_db->select('*'); 
			$this->shop_db->from('base_colors_variants');
			$this->shop_db->where('base_color_id',$id);
			$query = $this->shop_db->get();
			return $query->result();
		}

	}


	public function getAllBasecolorsByVariantOptionID($variantoptionid){
		$basecolor_name='-';

		$this->shop_db->select('GROUP_CONCAT(DISTINCT t1.color_name) as basecolor_name');
		$this->shop_db->from('base_colors t1, base_colors_variants t2');
		$this->shop_db->where('t2.variant_option_id', $variantoptionid);
		$this->shop_db->where('t1.id = t2.base_color_id');

		$query = $this->shop_db->get();
		$Row = $query->row();
		
		if(isset($Row) && $Row->basecolor_name!=''){
			$basecolor_name=$Row->basecolor_name;
		}
		
		return $basecolor_name;
	}


	
}
