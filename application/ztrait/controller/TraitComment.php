<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18 0018
 * Time: 下午 3:35
 */

namespace app\ztrait\controller;


use app\common\model\MapComment;
use app\common\model\QasQuestion;

trait TraitComment {

    /**
     * TODO 发布评论信息
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     */
    function traitCreateComment($param = []){
        $rule = isParamValid([
            'open_id' => 'require|length:32',
            'parent_id' => 'number',
            'target_type' => 'require',
            'target_id' => 'require',
            'comment_text' => 'require',
            // 'comment_image' => '',
            // 'comment_video' => '',
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1, '参数错误：' . $rule['msg']);
        }

        $open_id = $param['open_id'];
        $parent_id = isset($param['parent_id']) ? $param['parent_id'] : 0;
        $target_type = $param['target_type'];
        $target_id = $param['target_id'];
        $comment_text = $param['comment_text'];
        $comment_image = isset($param['comment_image']) ? $param['comment_image'] : '[]';
        $comment_audio = isset($param['comment_audio']) ? $param['comment_audio'] : '[]';

        $modelMapComment = new MapComment();
        $modelMapComment->data([
            'open_id' => $open_id,
            'parent_id' => $parent_id,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'comment_text' => $comment_text,
            'comment_image' => $comment_image,
            'comment_audio' => $comment_audio,
            'timestamp' => time()
        ]);
        $res = $modelMapComment->allowField(true)->save();
        if($res){
            return arrayReturn(0, 'success',['pk_id' => $modelMapComment->id]);
        }else{
            return arrayReturn(-1, '添加数据失败');
        }
    }


    /**
     * TODO 获取多个评论信息
     * -----------------------------------------------------------
     * @param array $param
     * @return array
     */
    function traitListComment($param = []){
        $rule = isParamValid([
            'parent_id' => 'number',
            'target_type' => 'alpha',
            'target_id' => 'number',
            'max_cursor' => 'number',
            'min_cursor' => 'number',
            'open_id' => 'length:32|alphaNum'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1,'参数错误：' . $rule['msg']);
        }

        if((empty($param['target_type']) || empty($param['target_id'])) && empty($param['open_id'])){
            return arrayReturn(-1, '参数错误：target参数和用户id必须有一项不能为空!');
        }

        $where = [
            'del_status' => 0,
            'parent_id' => isset($param['parent_id']) ? $param['parent_id'] : 0,
        ];
        if(isset($param['open_id'])){
            $where['open_id'] = $param['open_id'];
        }
        if(isset($param['target_type'])){
            $where['target_type'] = $param['target_type'];
        }
        if(isset($param['target_id'])){
            $where['target_id'] = $param['target_id'];
        }
        if(isset($param['max_cursor'])){
            $where['id'] = ['>',$param['max_cursor']];
        }
        if(isset($param['min_cursor'])){
            $where['id'] = ['<',$param['min_cursor']];
        }

        /************************************************************/
        // 评论列表
        /************************************************************/
        $modelQuestion = new QasQuestion();

        $modelMapComment = new MapComment();
        $comment_list = $modelMapComment->where($where)->limit(10)->order('id desc')->select();
        foreach ($comment_list as $key=>$val){
            $comment_list[$key]['timeformat'] = date('Y-m-d H:i:s', $val['timestamp']);
            $comment_list[$key]['comment_image'] = json_decode($val['comment_image'], true);
            $comment_list[$key]['comment_audio'] = json_decode($val['comment_audio'], true);

            $target_id = $val['target_id'];
            $target_type = $val['target_type'];
            if(!empty($param['open_id'])){
                if($target_type == 'question'){
                    $parent_data = $modelQuestion->where(['id'=>$target_id])->find();
                    $parent_data['timeformat'] = date('Y-m-d H:i:s', $parent_data['timestamp']);
                    $parent_data['issue_audio'] = json_decode($parent_data['issue_audio'], true);
                    $parent_data['issue_image'] = json_decode($parent_data['issue_image'], true);
                    $parent_data['issue_baseinfo'] = json_decode($parent_data['issue_baseinfo'], true);
                    $parent_data['user'] = $this->traitGetUser(['open_id'=>$parent_data['open_id']])['data'];
                    $comment_list[$key]['parent_data'] = $parent_data;
                }
            }
        }


        /*************************************************************/
        // 评论总数
        /*************************************************************/
        $comment_total = $this->traitCommentTotal($where);
        $comment_total = $comment_total['code'] == 0 ? $comment_total['data'] : 0;


        return arrayReturn(0, 'success', [
            'list' => $comment_list,
            'total' => $comment_total
        ]);
    }


    /**
     * TODO 获取单个评论信息
     * -----------------------------------------------------------
     * @param array $param
     */
    function traitGetComment($param = []){}


    /**
     * TODO 计算评论数量
     * -----------------------------------------------------------
     * @param array $param
     * @return array|int|string
     */
    function traitCommentTotal($param = []){
        $rule = isParamValid([
            'parent_id' => 'number',
            'target_type' => 'require',
            'target_id' => 'require|number'
        ], $param);
        if(!$rule['code']){
            return arrayReturn(-1,'参数错误：' . $rule['msg']);
        }

        $modelMapComment = new MapComment();
        $where = [
            'parent_id' => isset($param['parent_id']) ? $param['parent_id'] : 0,
            'target_type' => $param['target_type'],
            'target_id' => $param['target_id']
        ];
        $total = $modelMapComment->where($where)->count('id');

        return arrayReturn(0,'success', $total);
    }

}