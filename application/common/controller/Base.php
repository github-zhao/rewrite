<?php

namespace app\common\controller;

use think\Request;

class Base {

    protected $request;
    protected $param;

    public function __construct(Request $request){
        header('Access-Control-Allow-Origin:*');
        $this->request = Request::instance();
        $this->param = $this->request->param();
    }

}
