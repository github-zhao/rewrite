<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17 0017
 * Time: 上午 9:53
 */

namespace app\ztrait\controller;
use app\common\model\QasQuestion;
use app\common\model\SgsSpecialistType;
use Think\Exception;

/**
 * TODO 问答系统 - 提问信息相关操作
 * Trait TraitQasQuestion
 * @package app\ztrait\controller
 */
trait TraitQasQuestion {

    /**
     * TODO 创建问题
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     */
    function traitCreateQuestion($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32',
            'crop_type' => 'require|number',
            'crop_name' => 'require',
            'issue_baseinfo' => 'require',
            'issue_text' => 'require',
        ],$param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $open_id = $param['open_id'];
        $crop_type = $param['crop_type'];   // 作物类型/产业类型
        $crop_name = $param['crop_name'];   // 作物类型/产业类型
        $issue_baseinfo = $param['issue_baseinfo'];     // 基础信息，格式化数据 [{"Q":"种植作物","A":"茶叶"},{"Q":"病发程度","A":"局部发病"}]
        $issue_text = $param['issue_text'];             // 问题描述
        $issue_image = isset($param['issue_image']) ? $param['issue_image'] : '[]';
        $issue_audio = isset($param['issue_audio']) ? $param['issue_audio'] : '[]';

        // 判断作物类型是否存在
        $modelSpecialistType = new SgsSpecialistType();
        if(!$modelSpecialistType->where(['id'=>$crop_type, 'title'=>$crop_name, 'del_status'=>0])->find()){
            return arrayReturn(-1, '作物类型/产业类型不存在!');
        }

        $modelQasQuestion = new QasQuestion();  // 实例化模型
        $modelQasQuestion->data([
            'open_id' => $open_id,
            'issue_baseinfo' => $issue_baseinfo,
            'issue_text' => $issue_text,
            'issue_image' => $issue_image,
            'issue_audio' => $issue_audio,
            'crop_type' => $crop_type,
            'crop_name' => $crop_name,
            'timestamp' => time()
        ]);
        $res = $modelQasQuestion->allowField(true)->save();
        if($res){
            return arrayReturn(0, 'success',['pk_id' => $modelQasQuestion->id]);
        }else{
            return arrayReturn(-1, '添加数据失败');
        }
    }


    /**
     * TODO 问题列表
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function traitListQuestion($param = []){
        //var_dump($param);
        $rule = isParamValid([
            'max_cursor' => 'number',  // 最大游标（主键id），获取比他大的数据
            'min_cursor' => 'number',  // 最小游标（主键id），获取比他小的数据
            'data_num' => 'number',    // 每次获取的数据量
            'is_hot' => 'number',      // 1表示热门
            'keyword' => 'chsAlphaNum',           // 搜索关键词,
            'crop_type' => 'number',     // 产业类型/作物类型ID
            'open_id' => 'length:32|alphaNum'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1,'参数错误：'.$rule['msg']);
        }

        $data_num = (isset($param['data_num']) && $param['data_num'] < 10) ? $param['data_num'] : 10;

        $where = [ 'del_status'=>0 ];
        if(isset($param['max_cursor'])){
            $where['id'] = ['>', $param['max_cursor']];
        }
        if(isset($param['min_cursor'])){
            $where['id'] = ['<', $param['min_cursor']];
        }
        if(isset($param['is_hot'])){
            $where['read_num'] = ['>', 199];
        }
        if(isset($param['keyword'])){
            $where['issue_text'] = ['like', "%{$param['keyword']}%"];
        }
        if(isset($param['crop_type'])){
            $where['crop_type'] = $param['crop_type'];
        }
        if(isset($param['open_id'])){
            $where['open_id'] = $param['open_id'];
        }

        $modelQasQuestion = new QasQuestion();
        $list = $modelQasQuestion->where($where)->order('id desc')
            ->limit($data_num)
            ->field(['id','issue_baseinfo','issue_image','issue_text','open_id','read_num','timestamp','crop_type','crop_name'])
            ->select();
        foreach ($list as $key=>$val){
            $list[$key]['issue_baseinfo'] = json_decode($val['issue_baseinfo'], true);
            $list[$key]['issue_image'] = json_decode($val['issue_image'], true);
            $list[$key]['timeformat'] = date('Y-m-d H:i:s', $val['timestamp']);
        }

        return arrayReturn(0,'success',['list'=>$list]);
    }


    /**
     * TODO 问题详细信息
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function traitGetQuestion($param = []){
        $rule = isParamValid([
            'q_id' => 'require|number',    // 问题id
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1,'参数错误：'.$rule['msg']);
        }

        $q_id = $param['q_id'];
        $modelQasQuestion = new QasQuestion();
        $res = $modelQasQuestion->where(['id'=>$q_id, 'del_status'=>0])->find();
        if(empty($res)){
            $res = [];
        }else{
            $res['issue_baseinfo'] = json_decode($res['issue_baseinfo'], true);
            $res['issue_image'] = json_decode($res['issue_image'], true);
            $res['issue_audio'] = json_decode($res['issue_audio'], true);
        }

        return arrayReturn(0,'success', $res);
    }


    /**
     * TODO 获取问题总数
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     */
    function traitQuestionTotal($param = []){
        $rule = isParamValid([
            'is_hot' => 'number',    // 1表示热门
            //'keyword' => 'chsAlphaNum'           // 搜索关键词
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1,'参数错误：'.$rule['msg']);
        }

        $where = ['del_status'=>0];
        if(isset($param['is_hot']) && $param['is_hot'] == 1){
            $where['read_num'] = ['>', 200];
        }
        if(isset($param['keyword'])){
            $where['issue_text'] = ['like', "%{$param['keyword']}%"];
        }

        $modelQuestion = new QasQuestion();
        $res = $modelQuestion->where($where)->count('id');
        //var_dump($modelQuestion->getLastSql());

        return arrayReturn(0, 'success', $res);
    }

}