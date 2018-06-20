<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/23 0023
 * Time: 下午 2:04
 */

namespace app\api\controller\v1;

use app\common\controller\Base;
use app\common\model\QasQuestion;
use app\ztrait\controller\TraitRead;

/**
 * 用户阅读文章、评论、提问等的操作类，
 * 功能：添加阅读记录，获取阅读数等
 * Class Read
 * @package app\api\controller\v1
 */
class Read extends Base
{

    use TraitRead;

    public function addRead()
    {
        $param = $this->param;

        $rule = isParamValid([
            'open_id'     => 'require|length:32',
            'target_type' => 'require',
            'target_id'   => 'require|number',
        ], $param);
        if (!$rule['code']) {
            return jsonReturn(-1, '参数错误：' . $rule['msg']);
        }

        // 添加阅读记录
        $res_addreadlog = $this->traitAddRead($param);

        // 修改内容表（提问，文章，评论等）的阅读数
        $res_updatereadnum = null;
        $target_type       = $param['target_type'];
        switch ($target_type) {
            case 'question':
                $modelTable        = new QasQuestion();
                $res_updatereadnum = $modelTable->where(['id' => $param['target_id'], 'del_status' => 0])->setInc('read_num');
                break;
        }

        return jsonReturn(0, 'success', [
            'add_log'    => $res_addreadlog,
            'update_log' => $res_updatereadnum,
        ]);
    }

    public function readTotal()
    {
        return json($this->traitReadTotal($this->param), 200);
    }

}
