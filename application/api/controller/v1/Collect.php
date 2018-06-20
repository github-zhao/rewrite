<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/22 0022
 * Time: 下午 8:52
 */

namespace app\api\controller\v1;

use app\common\controller\Base;
use app\common\model\MapCollect;
use app\common\model\MapComment;
use app\common\model\QasQuestion;
use app\ztrait\controller\TraitUser;

/**
 * TODO 收藏操作类
 * Class Collect
 * @package app\api\controller\v1
 */
class Collect extends Base
{

    use TraitUser;

    // 添加收藏数据
    public function addCollect()
    {
        $param = $this->param;
        $rule  = isParamValid([
            'open_id'     => 'require|length:32',
            'target_type' => 'require',
            'target_id'   => 'require',
        ], $param);
        if (!$rule['code']) {
            return jsonReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = [
            'open_id'     => $param['open_id'],
            'target_type' => $param['target_type'],
            'target_id'   => $param['target_id'],
        ];
        $modelCollect = new MapCollect();

        if (!$modelCollect->where($where)->find()) {
            $modelCollect->data(array_merge($where, [
                'total'     => 1,
                'timestamp' => time(),
            ]));
            $res = $modelCollect->allowField(true)->save();

            if (!$res) {
                return jsonReturn(-1, '收藏失败');
            }
        }

        return jsonReturn(0, 'success');
    }

    // 获取收藏总数量
    public function collectTotal()
    {
        $param = $this->param;
        $rule  = isParamValid([
            'target_type' => 'require',
            'target_id'   => 'require',
            'open_id'     => 'length:32',
        ], $param);
        if (!$rule['code']) {
            return jsonReturn(-1, '参数错误：' . $rule['msg']);
        }

        $where = [
            'del_status'  => 0,
            'target_type' => $param['target_type'],
            'target_id'   => $param['target_id'],
        ];
        if (!empty($param['open_id'])) {
            $where['open_id'] = $param['open_id'];
        }

        $modelCollect = new MapCollect();
        $res          = $modelCollect->where($where)->count('id');

        return jsonReturn(0, 'success', ['total' => $res]);
    }

    // 当前用户收藏状态
    public function collectStatus()
    {
        $param = $this->param;
        $rule  = isParamValid([
            'open_id'     => 'require|length:32',
            'target_type' => 'require',
            'target_id'   => 'require',
        ], $param);
        if (!$rule['code']) {
            return jsonReturn(-1, '参数错误：' . $rule['msg']);
        }

        $modelCollect = new MapCollect();
        $res          = $modelCollect->where([
            'open_id'     => $param['open_id'],
            'target_type' => $param['target_type'],
            'target_id'   => $param['target_id'],
            'total'       => 1,
        ])->find();

        $status = 0;
        if ($res) {
            $status = 1;
        }

        return jsonReturn(0, 'success', ['status' => $status]);
    }

    // 收藏列表
    public function listCollect()
    {
        $param = $this->param;
        $rule  = isParamValid([
            'open_id'    => 'require|length:32',
            'min_cursor' => 'number',
            'max_cursor' => 'number',
            'limit'      => 'number',
        ], $param);
        if (!$rule['code']) {
            return jsonReturn(-1, '参数错误：' . $rule['msg']);
        }

        $limit = isset($param['limit']) ? intval($param['limit']) : 10;
        $limit = $limit > 20 ? 20 : $limit;
        $where = ['open_id' => $param['open_id'], 'del_status' => 0];
        if (!empty($param['min_cursor'])) {
            $where['id'] = ['<', $param['min_cursor']];
        }
        if (!empty($param['max_cursor'])) {
            $where['id'] = ['>', $param['max_cursor']];
        }

        $modelCollect = new MapCollect();
        $collect_list = $modelCollect->where($where)->order('id desc')->limit($limit)->select();

        // **************************************************
        // 分类获取对应数据
        // **************************************************
        $res_question_ids = [];
        $res_comment_ids  = [];
        $res_article_ids  = [];

        foreach ($collect_list as $key => $item) {
            $open_id     = $item['open_id'];
            $target_type = $item['target_type'];
            $target_id   = $item['target_id'];
            $id          = $item['id'];
            $user        = $this->traitGetUser(['open_id' => $open_id])['data']; // 获取缓存数据
            switch ($target_type) {
                case 'question':
                    $res_question_ids['target_ids'][]    = $target_id;
                    $res_question_ids['ids'][$target_id] = $id;
                    $res_question_ids['ids']['user']     = $user;
                    break;
                case 'comment':
                    $res_comment_ids['target_ids'][]    = $target_id;
                    $res_comment_ids['ids'][$target_id] = $id;
                    //$res_comment_ids['ids']['user'] = $user;
                    break;
                case 'article':
                    $res_article_ids['target_ids'][]    = $target_id;
                    $res_article_ids['ids'][$target_id] = $id;
                    //$res_article_ids['ids']['user'] = $user;
                    break;
            }
        }

        $res          = [];
        $res_article  = [];
        $res_comment  = [];
        $res_question = [];

        if (!empty($res_article_ids)) {}

        if (!empty($res_question_ids)) {
            $modelQasQuestion = new QasQuestion();
            $res              = $modelQasQuestion->where(['id' => ['in', $res_question_ids['target_ids']], 'del_status' => 0])->select();
            foreach ($res as $key => $item) {
                $item['issue_audio']    = json_decode($item['issue_audio'], true);
                $item['issue_image']    = json_decode($item['issue_image'], true);
                $item['issue_baseinfo'] = json_decode($item['issue_baseinfo'], true);
                $item['timeformat']     = date('Y-m-d H:i:s', $item['timestamp']);
                $item['user']           = $this->traitGetUser(['open_id' => $item['open_id']])['data']; // 获取缓存数据
                $res_question[]         = [
                    'id'      => $res_question_ids['ids'][$item['id']],
                    'user'    => $res_question_ids['ids']['user'],
                    'type'    => 'question',
                    'content' => $item,
                ];
            }
        }

        if (!empty($res_comment_ids)) {
            $modelComment = new MapComment();
            $res          = $modelComment->where(['id' => ['in', $res_comment_ids['target_ids']], 'del_status' => 0])->select();
            foreach ($res as $key => $item) {
                $res_comment[] = [
                    'id'      => $res_comment_ids['ids'][$item['id']],
                    'user'    => $this->traitGetUser(['open_id' => $item['open_id']])['data'],
                    'type'    => 'comment',
                    'content' => $item,
                ];
            }
        }

        $res = array_merge($res_comment, $res_question, $res_article);
        array_multisort(array_column($res, 'id'), SORT_DESC, $res); // 二维数组排序，按照order索引

        unset($collect_list);
        unset($res_question_ids);
        unset($res_comment_ids);
        unset($res_article_ids);
        unset($res_article);
        unset($res_comment);
        unset($res_question);

        return jsonReturn(0, 'success', ['list' => $res]);
    }
}
