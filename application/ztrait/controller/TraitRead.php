<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/23 0023
 * Time: 下午 2:07
 */

namespace app\ztrait\controller;


use app\common\model\MapRead;

trait TraitRead {

    // 添加点赞数据
    function traitAddRead($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32',
            'target_type' => 'require',
            'target_id' => 'require|number',
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = [
            'open_id' => $param['open_id'],
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id'],
        ];
        $modelRead = new MapRead();

        // 判断是否有数据，没有则添加数据
        if($modelRead->where($where)->find()){
            // 更新数据
            $modelRead->where($where)->setInc('total');
            $modelRead->where($where)->setField('update_time', time());
        }else{
            // 添加数据
            $modelRead->data(array_merge($where, [
                'timestamp' => time(),
                'update_time' => time(),
                'total' => 1
            ]));
            $modelRead->allowField(true)->save();
        }

        return arrayReturn(0, 'success');
    }

    // 获取阅读人数
    function traitReadTotal($param = []){
        $rule = isParamValid([
            'target_type' => 'require',
            'target_id' => 'require|number'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg'], ['total'=>0]);
        }

        $where = [
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id'],
            'del_status' => 0
        ];
        $modelRead = new MapRead();
        $res = $modelRead->where($where)->count();

        return arrayReturn(0, 'success', ['total'=>$res]);
    }

}