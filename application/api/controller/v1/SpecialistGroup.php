<?php

namespace app\api\controller\v1;

use app\common\controller\Base;
use app\ztrait\controller\TraitSgsSpecialist;
use app\ztrait\controller\TraitSgsSpecialistType;

/**
 * TODO 专家库
 * Class SpecialistGroup
 * @package app\api\controller\v1
 */
class SpecialistGroup extends Base {

    use TraitSgsSpecialist;
    use TraitSgsSpecialistType;


    // 获取专家数量
    public function specialistTotal(){
        return json($this->traitSpecialistTotal($this->param), 200);
    }


    // 创建专家信息
    public function createSpecialist(){
        $param = $this->param;
        return json($this->traitCreateSpecialist($param), 200);
    }


    // 获取专家列表
    public function listSpecialist(){
        $param = $this->param;
        return json($this->traitListSpecialist($param), 200);
    }


    // 删除一个专家数据
    public function delSpecialist(){
        $param = $this->param;
        return json($this->traitDelSpecialist($param), 200);
    }


    // 获取专家类型列表
    public function listSpecialistType(){
        $param = $this->param;
        return json($this->traitListSpecialistType($param), 200);
    }


}
