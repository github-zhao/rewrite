<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/22 0022
 * Time: 下午 8:42
 */

namespace app\api\controller\v1;


use app\common\controller\Base;
use app\ztrait\controller\TraitLike;


class Like extends Base {

    use TraitLike;

    public function addLike(){
        $param = $this->param;
        $res = $this->traitAddLike($param);
        return json($res, 200);
    }


    public function likeTotal(){
        $param = $this->param;
        $res = $this->traitLikeTotal($param);
        return json($res, 200);
    }


    public function likeStatus(){
        $param = $this->param;
        $res = $this->traitLikeStatus($param);
        return json($res, 200);
    }
}