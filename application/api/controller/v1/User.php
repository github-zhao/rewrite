<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17 0017
 * Time: 下午 1:59
 */

namespace app\api\controller\v1;


use app\common\controller\Base;
use app\ztrait\controller\TraitUser;

class User extends Base {

    use TraitUser;

    // 获取一个用户数据
    public function getUser(){
        $param = $this->param;
        return json($this->traitGetUser($param),200);
    }



    public function getUsers(){
        $param = $this->param;
        return json($this->traitGetUsers($param),200);
    }

}