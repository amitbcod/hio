<?php

abstract class BasePresenter
{
    private $model;
    public function __construct($model)
    {
        $this->model = $model;
    }

    public function __get($key){
        if(method_exists($this, $key)){
            return call_user_func([$this, $key]);
        }
        return $this->model->{$key};
    }

    public function __isset($key){
        return isset($this->model->{$key});
    }

    public static function from($model){
        return new static($model);
    }
}
