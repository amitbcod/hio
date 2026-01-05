<?php

Class DbTestimonialsFeature{

    private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

    function getTestimonialsCount($page='',$page_size='') {

        if(!empty($page) || !empty($page_size))
		{
			$limit=" LIMIT $page , $page_size";
		}else{
			$limit=" ";
		}

		$query = "SELECT * FROM testimonials $limit where `status` = 1";
		
		$testimonials_list = $this->dbl->dbl_conn->rawQuery($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return count($testimonials_list);
			}else{
				return false;
			}
		}else{
			return false;
		}
    }

	function getTestimonialsListing($page=0,$page_size=0) {

        if(!empty($page) || !empty($page_size))
		{
			$limit=" LIMIT $page , $page_size";
		}else{
			$limit=" ";
		}

		$query = "SELECT * FROM testimonials $limit where `status` = 1";
		
		$testimonials_list = $this->dbl->dbl_conn->rawQuery($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $testimonials_list;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }

}

?>