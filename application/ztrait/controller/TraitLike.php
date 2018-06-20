<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19 0019
 * Time: 下午 2:43
 */

namespace app\ztrait\controller;


use app\common\model\MapLike;

trait TraitLike {

    /**
     * TODO 添加点赞记录
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function traitAddLike($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32',
            'target_type' => 'require',
            'target_id' => 'require'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = [
            'open_id' => $param['open_id'],
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id'],
        ];
        $modelLike = new MapLike();

        if(!$modelLike->where($where)->find()){
            $modelLike->data(array_merge($where, [
                'total' => 1,
                'timestamp' => time()
            ]));
            $res = $modelLike->allowField(true)->save();

            if(!$res){
                return arrayReturn(-1, '点赞失败');
            }
        }

        return arrayReturn(0, 'success');
    }


    /**
     * TODO 获取点赞数量
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     */
    function traitLikeTotal($param = []){
        $rule = isParamValid([
            'target_type' => 'require',
            'target_id' => 'require'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $modelLike = new MapLike();
        $res = $modelLike->where([
            'del_status' => 0,
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id']
        ])->count('id');

        return arrayReturn(0, 'success', ['total'=>$res]);
    }


    /**
     * TODO 当前用户点赞状态
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function traitLikeStatus($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32',
            'target_type' => 'require',
            'target_id' => 'require'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $modelLike = new MapLike();
        $res = $modelLike->where([
            'open_id' => $param['open_id'],
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id'],
            'total' => 1
        ])->find();

        $status = 0;
        if($res){
            $status = 1;
        }

        return arrayReturn(0, 'success', ['status'=>$status]);
    }

}