<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20 0020
 * Time: 上午 9:45
 */

namespace app\ztrait\controller;


use app\common\model\SgsSpecialist;

trait TraitSgsSpecialist {

    // 创建专家信息
    function traitCreateSpecialist($param = []){
        $rule = isParamValid([
            'type_id' => 'require|number',
            'open_id' => 'require|alphaNum|length:32',
            'name' => 'require|chsAlphaNum',
            'photo' => 'url',
            'gender' => 'require|number',
            // 'intro' => 'chsAlphaNum',    // 介绍（属于哪个单位等）
            'post' => 'chsAlphaNum',     // 职位 （教授，研究员等）
            'goodjob' => 'chsAlphaNum'   // 擅长领域
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $modelSgsSpecialist = new SgsSpecialist();

        if($modelSgsSpecialist->where(['open_id'=>$param['open_id'], 'del_status'=>0])->find()){
            return arrayReturn(-1, '该专家已经存在!');
        }

        $modelSgsSpecialist->data($param);
        $res = $modelSgsSpecialist->allowField(true)->save();
        if($res){
            return arrayReturn(0, 'success', ['pk_id'=>$modelSgsSpecialist->id]);
        }else{
            return arrayReturn(-1, '添加数据失败');
        }
    }


    // 获取专家列表
    function traitListSpecialist($param = []){
        $rule = isParamValid([
            'type_id' => 'number'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = ['del_status'=>0];
        if(!empty($param['type_id'])){
            $where['type_id'] = $param['type_id'];
        }

        $modelSgsSpecialist = new SgsSpecialist();
        $list = $modelSgsSpecialist->where($where)->select();

        return arrayReturn(0, 'success', ['list'=>$list]);
    }


    // 获取单个专家信息
    function traitGetSpecialist($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $open_id = $param['open_id'];
        $modelSgsSpecialist = new SgsSpecialist();
        $res = $modelSgsSpecialist->where(['open_id'=>$open_id, 'del_status'=>0])->find();

        return arrayReturn(0, 'success', $res);
    }


    // 删除单个专家信息
    function traitDelSpecialist($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $open_id = $param['open_id'];
        $modelSgsSpecialist = new SgsSpecialist();
        $res = $modelSgsSpecialist->where(['open_id'=>$open_id, 'del_status'=>0])->setField('del_status', 1);

        if($res){
            return arrayReturn(0, 'success', $res);
        }else{
            return arrayReturn(-1, '删除失败，请重试~');
        }
    }


    // 获取专家数量
    function traitSpecialistTotal($param = []){
        $rule = isParamValid([
            'type_id' => 'number'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = ['del_status'=>0];
        if(isset($param['type_id'])){
            $where['type_id'] = $param['type_id'];
        }

        $modelSpecialist = new SgsSpecialist();
        $res = $modelSpecialist->where($where)->count('id');

        return arrayReturn(0, 'success', $res);
    }
}