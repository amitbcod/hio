<?php

class DbGlobalFeature{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	public function get_custom_variable($identifier)
	{
		$get_cms_page =  "SELECT * FROM custom_variables where  `identifier` = '$identifier'";
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


	public function get_custom_variables()
	{

        $result  = $this->dbl->dbl_conn->rawQuery(
            "SELECT identifier, name, value FROM custom_variables"
        );

        if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
            return $result;
        }

        return false;
    }

}
