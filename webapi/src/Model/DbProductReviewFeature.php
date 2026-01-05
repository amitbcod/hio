<?php

Class DbProductReviewFeature{
	private $dbl;
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	public function addProductReview($LoginToken,$LoginID,$product_id,$rating,$reviews)
	{
		$IP = $_SERVER['REMOTE_ADDR'];
		$param = array($product_id,$LoginID,$rating,$reviews,1,time(),$IP);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO products_reviews (product_id,customer_id,rating,review,status,created_at,ip) VALUES(?, ?, ?, ?, ?, ?, ?)", $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{

				return false;
			}
		} else {
			return false;
		}
	}



	public function getProductRatings($product_id)

	{

  		$param = array($product_id,1);

		$reviewData = $this->dbl->dbl_conn->rawQueryOne("SELECT SUM(p_rv.rating) as ratings, COUNT(p_rv.rating) as total_rating_count FROM products_reviews as p_rv WHERE p_rv.product_id=? AND p_rv.status=?", $param);



		if ($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){

				if (!empty($reviewData['ratings']) ){

					return $reviewData;

				}else{

					return false;

				}

			}else{

				return false;

			}

		} else {

			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();

			return false;

		}

	}



	public function productidsByReviewCode($product_id)
	{
		$date = strtotime(date('d-m-Y'));
  		$param = array($product_id,1);
  		$review_code = $this->dbl->dbl_conn->rawQueryOne("SELECT prod.product_reviews_code FROM products as prod WHERE prod.id=? AND prod.status=?", $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				if (!empty($review_code['product_reviews_code']) ){
					$params = array($review_code['product_reviews_code'],$date);
					$product_ids = $this->dbl->dbl_conn->rawQuery("SELECT product.id FROM products as product WHERE product.product_reviews_code=? AND product.launch_date <=? ", $params);
					if ($this->dbl->dbl_conn->getLastErrno() === 0){
						if ($this->dbl->dbl_conn->count > 0){
							return $product_ids;
						}else{
							return false;
						}
					}else{
						return false;
					}
				}else{
					return array();
				}
			}else{
				return false;

			}
		} else {
			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}

	public function getProductReviews(array $product_ids, $limit, $review_id='')

	{

		$where_id = '';

		if($review_id != ''){

			$where_id = " AND p_rv.id < $review_id";

		}

		$limit_var = '';

		if($limit != ''){

			$limit_var = " LIMIT $limit";

		}



        $prod_ids = "'".implode("','", $product_ids)."'";



		$review_list = $this->dbl->dbl_conn->rawQuery("SELECT p_rv.*, cust.first_name, cust.last_name FROM products_reviews as p_rv INNER JOIN customers as cust ON cust.id = p_rv.customer_id WHERE p_rv.product_id IN ($prod_ids) AND p_rv.status='1' $where_id ORDER BY p_rv.created_at DESC $limit_var");



		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {

            return $review_list;

        }

        return false;

	}



    public function getProductReviewsCount(array $product_ids)

    {

        $prod_ids = "'".implode("','", $product_ids)."'";



        $count = $this->dbl->dbl_conn->rawQueryOne("SELECT COUNT(*) as reviewcount FROM products_reviews as p_rv INNER JOIN customers as cust ON cust.id = p_rv.customer_id WHERE p_rv.product_id IN ($prod_ids) AND p_rv.status='1' ORDER BY p_rv.created_at DESC");



        if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {

            return $count['reviewcount'];

        }

        return false;

    }





    public function getProductsBlock($identifier)
	{
		$param = array($identifier);
		$blocks = $this->dbl->dbl_conn->rawQueryOne("SELECT p_bm.block_identifier,p_bm.block_name, p_bd.* FROM products_block_master as p_bm INNER JOIN products_block_details as p_bd ON p_bd.pb_master_id = p_bm.id WHERE p_bm.block_identifier = ?", $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){

				return $blocks;

			}else{

				return false;

			}

		} else {

			return false;

		}

	}



}

