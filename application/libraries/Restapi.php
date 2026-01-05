<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Restapi
{
    public $API ="";

    public function __construct()
    {
        $this->API=API_URL;
    }

    public function get_method($url)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->API.$url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        $result = json_decode($buffer);
        return $result;
    }

    public function post_method($url, $array)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->API.$url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($array)));
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        $result = json_decode($buffer);
        return $result;
    }


    //create by al
    public function post_method_with_json($url, $array)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->API.$url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, json_encode($array));
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        $result = json_decode($buffer);
        return $result;
    }

    public function delete($id)
    {
        $array = array("item_id" => $id);
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->API);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($array));
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        $buffer = curl_exec($curl_handle);
        return $buffer;
    }
}
