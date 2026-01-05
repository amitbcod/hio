<?php
class EavAttributesModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function getSingleDataByID($tableName, $condition, $select)
	{
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->row();
	}
	public function get_attributes_for_seller($attr_ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where_in('id', $attr_ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_attribute_detail($id)
	{
		$result = $this->db->get_where('eav_attributes', array('id' => $id))->row();
		return $result;
	}

	public function get_attribute_option_values($id)
	{
		$this->db->select('attr_options_name');
		$this->db->where('attr_id', $id);
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_attribute_detail_opt($id)
	{
		$this->db->select('attr_properties');
		$result = $this->db->get_where('eav_attributes', array('id' => $id))->row();
		return $result;
	}

	public function getAttributeCode($attribute_code, $attr_type = '')
	{
		$extra_where = '';
		if (isset($attr_type) && $attr_type != '') {
			$extra_where = " AND (attr_type=$attr_type) ";
		}
		$result = $this->db->query("
            SELECT * FROM `eav_attributes` WHERE
             (attr_code = '$attribute_code') $extra_where ");
		return $result->row();
	}

	public function get_default_attributes()
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where('is_default', 1);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_default_variants()
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where('is_default', 1);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_variant_by_category($variant_ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where_in('id', $variant_ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();

		return $resultArr;
	}


	public function get_attr_by_seller($seller_id)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where('is_default', 0);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function getAttrDataByIds($attr_ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where_in('id', $attr_ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function getVariantDataByIds($attr_ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where_in('id', $attr_ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		// echo $this->db->last_Query();
		return $resultArr;
	}


	public function get_variant_by_seller($seller_id)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where('is_default', 0);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function get_variant_masters()
	{
		$this->db->select('*');
		$this->db->where('attr_type', 2);
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function get_attributes_masters()
	{
		$this->db->select('*');
		$this->db->where('attr_type', 1);
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}


	public function get_category_attributes_by_seller($ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where_in('id', $ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function insert_update_attributes($attributes, $SISA_ID, $attribute_id, $attr_type)
	{

		if ($attribute_id != '') {
			$updateData = array(
				'attr_code'    		=> $_POST['attribute_code'],
				'attr_name'			=> $_POST['attribute_name'],
				'attr_description'	=> $_POST['attribute_description'],
				'attr_properties' 	=> $_POST['attribute_properties'],
				'created_by' 		=> $SISA_ID,
				'status'			=> $_POST['status'],
				'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
				'ip'				=> $_SERVER['REMOTE_ADDR'],
			);

			$this->db->where('id', $attribute_id);
			return  $this->db->update('eav_attributes', $updateData);
		} else {

			$insertData = array(
				'attr_code'    		=> $_POST['attribute_code'],
				'attr_name'			=> $_POST['attribute_name'],
				'attr_type'			=> $attr_type,
				'attr_description'	=> $_POST['attribute_description'],
				'attr_properties' 	=> $_POST['attribute_properties'],
				'created_by' 		=> $SISA_ID,
				'status'			=> $_POST['status'],
				'created_at'		=> strtotime(date('Y-m-d H:i:s')),
				'ip'				=> $_SERVER['REMOTE_ADDR'],
			);
			return $this->db->insert('eav_attributes', $insertData);
		}
	}

	public function insert_attributes_option_value($values, $attribute_id, $SISA_ID)
	{

		$attributesData = array(
			'attr_id'    		=> $attribute_id,
			'attr_options_name'	=> $values,
			'created_by' 		=> $SISA_ID,
			'status'			=> 1,
			'created_at'		=> strtotime(date('Y-m-d H:i:s')),
			'ip'				=> $_SERVER['REMOTE_ADDR'],
		);
		$this->db->insert('eav_attributes_options', $attributesData);
	}
	public function get_category_variant_by_seller($ids)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where_in('id', $ids);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_attributes_options_by_seller($attr_id)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_id', $attr_id);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->result_array();
		return $resultArr;
	}


	public function getAttrDataByAttrCode($attr_codes)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 1);
		$this->db->where_in('attr_code', $attr_codes);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getVariantDataByAttrCode($attr_codes)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', 2);
		$this->db->where_in('attr_code', $attr_codes);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function check_attr_exist_by_shop_and_attr_code($attr_type, $attr_code)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_type', $attr_type);
		$this->db->where('attr_code', $attr_code);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes');
		// echo $this->db->last_query();
		// die;
		$resultArr = $query->row();
		return $resultArr;
	}

	public function check_attributes_options_exist_by_seller($attr_id, $attr_options_name)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_id', $attr_id);
		$this->db->where('attr_options_name', $attr_options_name);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->row();
		return $resultArr;
	}

	public function check_attributes_options_exist_by_option_id($shop_id, $attr_id, $option_id)
	{
		$this->db->select('*');
		$this->db->where('status', 1);
		$this->db->where('attr_id', $attr_id);
		$this->db->where('id', $option_id);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->row();
		return $resultArr;
	}

	public function check_attributes_options_exist_by_option_id_opt($shop_id, $attr_id, $option_id)
	{
		$this->db->select('id,attr_options_name');
		$this->db->where('status', 1);
		$this->db->where('attr_id', $attr_id);
		$this->db->where('id', $option_id);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->row();
		return $resultArr;
	}

	public function check_attributes_options_exist_by_option_id_opt_multiple($shop_id, $attr_id)
	{
		$condition1 = implode(',', $attr_id);
		$this->db->select('id,attr_options_name');
		$this->db->where('status', 1);
		$this->db->where("attr_id IN (" . $condition1 . ")", null, false);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_attribute_detail_ownshop_admin($id, $shop_id)
	{
		$this->db->select('*');
		$this->db->where('id', $id);
		$query = $this->db->get('eav_attributes');
		$resultArr = $query->row();
		return $resultArr;
	}
	// new
	public function get_attribute_detail_opt_multiple($att_ids)
	{
		$this->db->select('attr_properties,id');
		$this->db->where_in('id', $att_ids);
		$query = $this->db->get('eav_attributes');
		return $query->result_array();
	}
	//end new

	public function getVariantNameCode($variant_name)
	{
		$this->db->select('*');
		$this->db->where('attr_name', $variant_name);
		$this->db->where('attr_type', 2);
		$query = $this->db->get('eav_attributes');
		return $query->num_rows();
	}

	public function getAttributeName($attribute_name)
	{
		$this->db->select('*');
		$this->db->where('attr_name', $attribute_name);
		$this->db->where('attr_type', 1);
		$query = $this->db->get('eav_attributes');
		return $query->num_rows();
	}
	public function get_subscription_attribute_option_values($id)
	{
		$this->db->select('*');
		$this->db->where('attr_id', $id);
		$this->db->order_by("created_at", "desc");
		$query = $this->db->get('eav_attributes_options');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_subscription_time_details($id)
	{
		$this->db->select('*');
		$this->db->where('eav_option_id', $id);
		$query = $this->db->get('subscription_time');
		// echo $this->db->last_Query();
		if ($query->num_rows() > 0) {
			$resultArr = $query->row_array();
			return $resultArr;
		} else {
			return false;
		}
	}

	public function check_if_sub_time_exist($id)
	{
		$this->db->select('*');
		$this->db->where('eav_option_id', $id);
		$query = $this->db->get('subscription_time');
		// echo $this->db->last_Query();
		if ($query->num_rows() > 0) {
			$resultArr = $query->row_array();
			return $resultArr;
		} else {
			return false;
		}
	}
	public function update_sub_time($update_array, $id)
	{
		$this->db->where('id', $id);
		$this->db->update('subscription_time', $update_array);
		if ($this->db->affected_rows() == '1') {
			return TRUE;
		} else {
			return false;
		}
	}

	public function insert_sub_time($insert_array)
	{
		$this->db->insert('subscription_time', $insert_array);
		if ($this->db->affected_rows() == '1') {
			return TRUE;
		} else {
			return false;
		}
	}
}
