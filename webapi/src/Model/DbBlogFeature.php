<?php
class DbBlogFeature
{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	public function getPrevNextBlogDetails($blog_id)
	{
		$query = "(SELECT blo.id, blo.url_key FROM blogs as blo WHERE blo.id < " . $blog_id . " AND blo.status='1'  ORDER BY blo.id DESC LIMIT 1) UNION (SELECT blo.id, blo.url_key FROM blogs as blo WHERE blo.id > " . $blog_id . " AND blo.status='1'  ORDER BY blo.id DESC LIMIT 1)";

		$blog_detail = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $blog_detail;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function configurableProductForMultipleProducts($product_ids)
	{
		$product_ids_string = implode(',', $product_ids);
		$query = "SELECT products.* FROM products WHERE products.id IN ($product_ids_string) ";
		$config_products = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $config_products;
			}
		}

		return [];
	}

	public function blogChildDetails($blog_id)
	{
		$param = array($blog_id);
		$query = "SELECT blogs_details.* FROM blogs_details where blogs_details.blog_id=? ";
		$blogChildDetails = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $blogChildDetails;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function blogDetails($url_key)
	{
		$param = array($url_key, 1);

		$query = "SELECT blogs.* FROM blogs where blogs.url_key=? AND blogs.status=?";
		$blogDetails = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $blogDetails;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getBlogListing($offset = 0, $page_size = 3)
	{
		$query = "SELECT blogs.* FROM blogs WHERE status='1' LIMIT {$offset}, {$page_size}";
		$blog_data = $this->dbl->dbl_conn->rawQuery($query);

		$countQuery = "SELECT COUNT(*) as total FROM blogs WHERE status='1'";
		$totalResult = $this->dbl->dbl_conn->rawQueryOne($countQuery);
		$totalCount = $totalResult['total'] ?? 0;

		if (!empty($blog_data)) {
			return [
				'statusCode' => '200',
				'is_success' => 'true',
				'message' => 'Blog available',
				'blogData' => $blog_data,
				'BlogDataCount' => $totalCount
			];
		} else {
			return [
				'statusCode' => '500',
				'is_success' => 'false',
				'message' => 'Blog not available'
			];
		}
	}



}
