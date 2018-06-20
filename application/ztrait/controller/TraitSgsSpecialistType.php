<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20 0020
 * Time: 上午 9:45
 */

namespace app\ztrait\controller;


use app\common\model\SgsSpecialistType;

trait TraitSgsSpecialistType {

    // 创建专家分类
    function traitCreateSpecialistType($param = []){
        $rule = isParamValid([
            'title' => 'require',
            'open_id' => 'require|length:32'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $title = $param['title'];
        $open_id = $param['open_id'];

        $modelSgsSpecialistType = new SgsSpecialistType();
        $modelSgsSpecialistType->data([
            'title' => $title,
            'open_id' => $open_id,
            'timestamp' => time()
        ]);
        $res = $modelSgsSpecialistType->allowField(true)->save();
        if($res){
            return arrayReturn(0, 'success',['pk_id' => $modelSgsSpecialistType->id]);
        }else{
            return arrayReturn(-1, '添加数据失败');
        }
    }

    // 专家分类列表
    function traitListSpecialistType($param = []){
        $rule = isParamValid([], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $modelSgsSpecialistType = new SgsSpecialistType();
        $list = $modelSgsSpecialistType->where(['del_status'=>0])->field(['id','title','parent_id'])->select();

        return arrayReturn(0, 'success', ['list'=>$list]);
    }

    // 删除一个专家分类
    function traitDelSpecialistType($param = []){
        $rule = isParamValid([
            'id' => 'require|number'
        ], $param);

        $id = $param['id'];
        $modelSgsSpecialistType = new SgsSpecialistType();
        $res = $modelSgsSpecialistType->where(['id'=>$id])->setField('del_status', 1);
        if($res){
            return arrayReturn(0, 'success');
        }else{
            return arrayReturn(-1, '删除数据失败');
        }
    }

}