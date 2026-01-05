<?php
/**
 * Handling database connection using PHP - MysqliDb Library
 *
 * @author Alanka Bcod (alanka@bcod.co.in)
 */
class DbLibrary {

    // private $conn;
    public $dbl_conn;
    // function __construct($db_name = "") {
    function __construct() {
        include_once('MysqliDb.php');
        // Connecting to mysql database
        $this->dbl_conn = new MysqliDb(DB_HOST, DB_USERNAME, DB_PASSWORD,DB_NAME);
        return $this->dbl_conn;
    }
  }

?>
