<?php
class ReturnOrderModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID') ?? $this->input->post('shop_id');

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);
			if($this->seller_db->conn_id) {
				//do something
			} else {
				redirect(base_url());
			}
		}else{
			redirect(base_url());
		}
	}

	public function updateData($tableName,$condition,$updateData)
    {
		$this->seller_db->where($condition);

		  $this->seller_db->update($tableName,$updateData);

		  //echo $this->seller_db->last_query();exit;
		  if($this->seller_db->affected_rows() > 0)
		  {

			return true;
		  }
		  else
		  {
			return false;
		  }

		  $this->seller_db->reset_query();
    }



		///getDataByID
	public function getMultiDataById($tableName,$condition,$select,$order_by_column='',$order_by_type='')
	{
		if(!empty($select))
		{
			$this->seller_db->select($select);
		}
		$this->seller_db->where($condition);

		if(isset($order_by_column) &&  $order_by_column != ''){
			$this->seller_db->order_by($order_by_column,$order_by_type);
		}

		$query = $this->seller_db->get($tableName);
		return $query->result();
	}

      //getSingleDataByID
  public function getSingleDataByID($tableName,$condition,$select)
  {
    if(!empty($select))
    {
      $this->seller_db->select($select);
    }
    $this->seller_db->where($condition);
    $query = $this->seller_db->get($tableName);
    return $query->row();
  }



  //insertData
  public function insertData($table,$data)
  {
	 $this->seller_db->reset_query();

    $this->seller_db->insert($table,$data);
    if($this->seller_db->affected_rows() > 0)
    {
		$last_insert_id=$this->seller_db->insert_id();
      return $last_insert_id;
    }
    else
    {
      return false;
    }
  }


  function get_datatables_expected_return_orders(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_expected_return_orders($term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

    public function _get_datatables_query_expected_return_orders($term='') {

		$main_db_name=$this->db->database;


	   	$column = array('o.increment_id','o.created_at', 'customer_name','', '','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('o.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type');
		$this->seller_db->from('sales_order as o');
		$this->seller_db->join('sales_order_payment as wp','o.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order_return as sor','o.order_id = sor.order_id','LEFT');

		$this->seller_db->where('o.parent_id','0');
		$this->seller_db->where('o.main_parent_id','0');
		$this->seller_db->where('(o.status IN (6))');
		$this->seller_db->where('(o.tracking_complete_date <> "" )');

		$this->seller_db->where('sor.order_id IS NULL');

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'

			 )");

		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('o.order_id', 'desc');
		}

    }


   public function count_all_expected_return_orders() {
	   $term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';


	   $main_db_name=$this->db->database;

	   	$column = array('o.increment_id','o.created_at', 'customer_name','', '','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('o.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type');
		$this->seller_db->from('sales_order as o');
		$this->seller_db->join('sales_order_payment as wp','o.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order_return as sor','o.order_id = sor.order_id','LEFT');

		$this->seller_db->where('o.parent_id','0');
		$this->seller_db->where('o.main_parent_id','0');
		$this->seller_db->where('(o.status IN (6))');
		$this->seller_db->where('(o.tracking_complete_date <> "" )');

		$this->seller_db->where('sor.order_id IS NULL');

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'

			 )");

		}



		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('o.order_id', 'desc');
		}
       return $this->seller_db->count_all_results();
    }

	 function count_filtered_expected_return_orders(){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_expected_return_orders($term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	 }



	 function get_datatables_return_request_orders(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_return_request_orders($term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

    public function _get_datatables_query_return_request_orders($term='') {

		$main_db_name=$this->db->database;


	   	$column = array('sor.return_order_increment_id','o.created_at', 'customer_name','', '','','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('sor.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->seller_db->from('sales_order_return as sor');
		$this->seller_db->join('sales_order_payment as wp','sor.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order as o',' sor.order_id =o.order_id','LEFT');
		$this->seller_db->where('(sor.status  NOT IN (0,1))');

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR sor.return_order_increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'


			 )");

		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('sor.return_order_id', 'desc');
		}

    }


   public function count_all_return_request_orders() {
	   $term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';


	   $main_db_name=$this->db->database;

	   	$column = array('sor.return_order_increment_id','o.created_at', 'customer_name','', '','','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('sor.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->seller_db->from('sales_order_return as sor');
		$this->seller_db->join('sales_order_payment as wp','sor.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order as o',' sor.order_id =o.order_id','LEFT');
		$this->seller_db->where('(sor.status  NOT IN (0,1))');

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR sor.return_order_increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'


			 )");

		}



		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('sor.return_order_id', 'desc');
		}
       return $this->seller_db->count_all_results();
    }

	 function count_filtered_return_request_orders(){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_return_request_orders($term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	 }


	 function getReturnOrderItems($return_order_id)
	 {
		$this->seller_db->select("oi.*,soi.product_name,soi.product_variants,soi.product_type,soi.product_inv_type");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->join('sales_order_items as soi','oi.order_item_id = soi.item_id','LEFT');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$this->seller_db->order_by('oi.qty_return_recieved','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	 }

	 function checkOrderItemsExist($return_order_id,$barcode)
	 {
		$this->seller_db->select("oi.*");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$this->seller_db->where('oi.barcode',$barcode);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->row();
	 }

	  function incrementReturnOrderItemQtyScanned($return_order_item_id,$return_order_id)
	 {
		$sql="UPDATE sales_order_return_items   SET qty_return_recieved = qty_return_recieved + 1   WHERE return_order_item_id = $return_order_item_id  AND return_order_id = $return_order_id";
		$this->seller_db->query($sql);
		$this->seller_db->reset_query();
	 }

	  function getQtyFullyScannedOrderItems($return_order_id)
	 {
		$this->seller_db->select("oi.*");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$this->seller_db->where('(oi.qty_return=oi.qty_return_recieved)');
		$this->seller_db->order_by('oi.qty_return_recieved','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	 }

	 function UpdateOrderAfterQtyApproved($return_order_id){


		$Row=$this->getReturnOdrTotalApprovedAmount($return_order_id);
		$RowDisc=$this->getReturnOdrDiscountApprovedAmount($return_order_id);

		$order_amount_approved=$Row->total_price_approved;

		$order_discount_approved=(isset($RowDisc->order_discount_approved) && $RowDisc->order_discount_approved>0)?$RowDisc->order_discount_approved:0.00;

		/*if($order_discount_approved>0){
			$order_grandtotal_approved=$order_amount_approved-$order_discount_approved;
		}else{*/
			$order_grandtotal_approved=$order_amount_approved;
		// }

		$time=time();


		$sql="UPDATE sales_order_return   SET order_amount_approved = $order_amount_approved,order_discount_approved=$order_discount_approved,order_grandtotal_approved=$order_grandtotal_approved, updated_at=$time   WHERE  return_order_id = $return_order_id";
		$this->seller_db->query($sql);
		$this->seller_db->reset_query();

	 }

	  function getReturnOdrTotalApprovedAmount($return_order_id){

			$this->seller_db->select("sum(total_price_approved) as total_price_approved");
			$this->seller_db->from('sales_order_return_items as oi');
			$this->seller_db->where('oi.return_order_id',$return_order_id);
			$query = $this->seller_db->get();
			//echo $this->seller_db->last_query();exit;
			return $query->row();

		}

		function getReturnOdrDiscountApprovedAmount($return_order_id){

			$this->seller_db->select("sum(discount_amount*qty_return_approved) as order_discount_approved");
			$this->seller_db->from('sales_order_return_items as oi');
			$this->seller_db->where('oi.return_order_id',$return_order_id);
			$query = $this->seller_db->get();
			//echo $this->seller_db->last_query();exit;
			return $query->row();

		}


	  function checkOrderItemsExistByItemId($return_order_id,$return_order_item_id)
	 {
		$this->seller_db->select("oi.*");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$this->seller_db->where('oi.return_order_item_id',$return_order_item_id);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->row();
	 }

	  function incrementOrderItemQtyScannedByQty($return_order_item_id,$qty)
	 {
		$sql="UPDATE sales_order_return_items   SET qty_return_recieved = qty_return_recieved + $qty   WHERE return_order_item_id = $return_order_item_id";
		$this->seller_db->query($sql);
		$this->seller_db->reset_query();
	 }


	 /*------------------Refund start----------------------------*/

	 function get_datatables_refund_request_orders($current_tab){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_refund_request_orders($current_tab,$term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

    public function _get_datatables_query_refund_request_orders($current_tab,$term='') {

		$main_db_name=$this->db->database;


	   	$column = array('sor.return_order_increment_id','o.created_at', 'customer_name','', '','','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('sor.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->seller_db->from('sales_order_return as sor');
		$this->seller_db->join('sales_order_payment as wp','sor.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order as o',' sor.order_id =o.order_id','LEFT');
		/*if($current_tab=='refund_complete'){
			$this->seller_db->where('(sor.refund_status  IN (1))');
		}else{
			$this->seller_db->where('(sor.status  IN (3,4))');
			$this->seller_db->where('(sor.refund_status NOT IN (1))');
		}*/

		if($current_tab=='refund_complete'){
			// $this->seller_db->where('(sor.refund_status  IN (1))'); //old
			$this->seller_db->where('(sor.refund_status NOT IN (0))');
		}else{
			/*$this->seller_db->where('(sor.status  IN (3,4))');
			$this->seller_db->where('(sor.refund_status NOT IN (1))');*/ //old
			$this->seller_db->where('sor.refund_status', 0);
		}

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR sor.return_order_increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'


			 )");

		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('sor.return_order_id', 'desc');
		}

    }


   public function count_all_refund_request_orders($current_tab) {
	   $term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';


	   $main_db_name=$this->db->database;

	   	$column = array('sor.return_order_increment_id','o.created_at', 'customer_name','', '','','','','');
		$this->seller_db->distinct();
		$this->seller_db->select('sor.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->seller_db->from('sales_order_return as sor');
		$this->seller_db->join('sales_order_payment as wp','sor.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order as o',' sor.order_id =o.order_id','LEFT');
		if($current_tab=='refund_complete'){
			$this->seller_db->where('(sor.refund_status NOT IN (0))');
			//$this->seller_db->where('(sor.refund_status  IN (1))'); //old
		}else{
			/*$this->seller_db->where('(sor.status  IN (3,4))');
			$this->seller_db->where('(sor.refund_status NOT IN (1))');	*/ //old
			$this->seller_db->where('sor.refund_status', 0);
		}

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR sor.return_order_increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'


			 )");

		}



		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('sor.return_order_id', 'desc');
		}
       return $this->seller_db->count_all_results();
    }

	 function count_filtered_refund_request_orders($current_tab){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_refund_request_orders($current_tab,$term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	 }


	  function getFullyApprovedOrderItems($return_order_id)
	 {
		$this->seller_db->select("oi.*");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$this->seller_db->where('(oi.qty_return=oi.qty_return_approved)');
		$this->seller_db->order_by('oi.qty_return_approved','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	 }

	 /*start escalations*/
	function get_datatables_escalations_request_orders($current_tab){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_escalations_request_orders($current_tab,$term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		// echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	public function _get_datatables_query_escalations_request_orders($current_tab,$term='') {
		$main_db_name=$this->db->database;
	   	$column = '';
	   	// $column = array('soe.return_order_increment_id','o.created_at', 'customer_name','', '','','','','');
		$this->seller_db->distinct();
		//$this->seller_db->select('soe.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->seller_db->select('soe.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at,o.main_parent_id,o.parent_id');
		$this->seller_db->from('sales_order_escalations as soe');
		// $this->seller_db->from('sales_order_return as sor');
		$this->seller_db->join('sales_order_payment as wp','soe.order_id = wp.order_id','LEFT');
		$this->seller_db->join('sales_order as o',' soe.order_id =o.order_id','LEFT');
		if($current_tab=='escalations_completed'){
			$this->seller_db->where('(soe.refund_status NOT IN (0))');
			// $this->seller_db->where('soe.refund_status !=', 1);
			// $this->seller_db->where('(soe.refund_status  IN (1))');
		}else{
			// $this->seller_db->where('(soe.status  IN (3,4))');
			// $this->seller_db->where('(soe.refund_status NOT IN (1))');
			$this->seller_db->where('soe.refund_status', 0);
		}

		if($term !=''){

		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR soe.esc_order_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'


			 )");

		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->seller_db->order_by(key($order), $order[key($order)]);
		}else{
			$this->seller_db->order_by('soe.cancel_date', 'desc');
		}

    }

    // order item
    function getEscalationsOrderItems($order_id)
	 {
		$this->seller_db->select("oi.*,pi.qty,p.prod_location");
		// $this->seller_db->select("oi.*,soi.product_name,soi.product_variants,soi.product_type,soi.product_inv_type");
		$this->seller_db->from('sales_order_items as oi');
		$this->seller_db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->seller_db->join('products as p','oi.product_id = p.id','LEFT');
		// $this->seller_db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->seller_db->where("pi.qty>0");								  //adde by al later
		$this->seller_db->where('oi.order_id',$order_id);
		$this->seller_db->order_by('oi.qty_scanned','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	 }

	/*end escalations*/

	function getRefundStripeOrderIdLastInserted($order_id)
	{
		$this->seller_db->select("*");
		$this->seller_db->from('sales_order_return_stripe');
		$this->seller_db->where('order_id',$order_id);
		$this->seller_db->order_by('id', 'desc');
		$this->seller_db->limit(1);
		$query = $this->seller_db->get();
		return $query->row();
	}

	function getReturnOdrItemApprovedCount($return_order_id){

			$this->seller_db->select("*");
			$this->seller_db->from('sales_order_return_items as oi');
			$this->seller_db->where('oi.return_order_id',$return_order_id);
			$this->seller_db->where('oi.qty_return_approved >',0);
			$query = $this->seller_db->get();
			return $query->num_rows();

	}

	public function paypal_refund_detail(){
		$this->seller_db->select("*");
		$this->seller_db->from('paypal_refund_details');
		$query = $this->seller_db->get();
		return $query->result();
	}

	function getReturnOdrTotalApprovedQTY($return_order_id){

		$this->seller_db->select("sum(oi.qty_return_approved) as qty_return_approved");
		$this->seller_db->from('sales_order_return_items as oi');
		$this->seller_db->where('oi.return_order_id',$return_order_id);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->row();

	}

}
