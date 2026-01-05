<?php

class LinkUrl
{
    private $url;
    private $base_url = BASE_URL;

    public function __construct($url){
        $this->url = $url;
    }

    public static function from($input_url): LinkUrl
    {
        return new self($input_url);
    }

    private function process_url(): string
    {
        if(strpos($this->url, 'http') === 0) {
            return $this->url;
        }


        return trim($this->base_url, " \n\r\t\v\x00/") . '/' . trim($this->url, " \n\r\t\v\x00/");
    }

    public function __toString(){
        return $this->process_url();
    }
}
