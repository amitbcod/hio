<?php
Class DbCMSFeature{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	public function get_cms_page($identifier,$lang_code='')
	{

  		if($lang_code !='')
  		{
  			$get_cms_page =  "SELECT cms_pages.*, mlc.title as lang_cms_title,mlc.content as lang_cms_content,mlc.meta_title as lang_cms_meta_title,mlc.meta_keywords as lang_cms_meta_keywords, mlc.meta_description as lang_cms_meta_description,mlc.lang_code as lang_code FROM cms_pages LEFT JOIN multi_lang_cms_pages as mlc ON (cms_pages.id=mlc.page_id and mlc.lang_code='$lang_code') where  `identifier` = '$identifier' ";   			
  		}
  		else
  		{
  			 $get_cms_page =  "SELECT * FROM cms_pages where  `identifier` = '$identifier'"; 
  		} 
		 $query  = $this->dbl->dbl_conn->rawQueryOne($get_cms_page);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
		
		
	}
	

	
}
