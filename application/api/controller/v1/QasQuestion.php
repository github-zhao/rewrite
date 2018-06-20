<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17 0017
 * Time: 上午 11:00
 */

namespace app\api\controller\v1;


use app\common\controller\Base;
use app\ztrait\controller\TraitComment;
use app\ztrait\controller\TraitQasQuestion;
use app\ztrait\controller\TraitSgsSpecialistType;
use app\ztrait\controller\TraitUser;

class QasQuestion extends Base {

    use TraitQasQuestion;
    use TraitComment;
    use TraitUser;
    use TraitSgsSpecialistType;

    public function createQuestion(){
        //测试数据
        /*$test = [
            'open_id' => md5(time()),
            'issue_baseinfo' => '[{"Q":"种植作物","A":"杨梅"},{"Q":"病发程度","A":"大面积发病"},{"Q":"天气情况","A":"台风刚过，田里积水严重"},{"Q":"用药用肥","A":"使用了除虫药全园喷洒"}]',
            'issue_text' => '杨梅发霉了应该怎么处理',
            //'issue_image' => $issue_image,
            //'issue_audio' => $issue_audio,
            'timestamp' => time()
        ];*/
        $param = $this->param;
        return json($this->traitCreateQuestion($param), 200);
    }


    public function listQuestion(){
        $param = $this->param;
        $res_question = $this->traitListQuestion($param);

        if($res_question['code'] == 0){
            $list_question = $res_question['data']['list'];
            foreach ($list_question as $key=>$val){
                $list_question[$key]['user'] = $this->traitGetUser(['open_id'=>$val['open_id']])['data'];
                //$list_question[$key]['comment_num'] = 0;
                $list_question[$key]['comment_num'] = $this->traitCommentTotal([
                    'target_type' => 'question',
                    'target_id' => $val['id']
                ])['data'];
            }

            return json(arrayReturn(0,'success',['list'=>$list_question]), 200);
        }else{
            return json($res_question, 200);
        }
    }


    /**
     * TODO 获取问题详情
     * -----------------------------------------------------------
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getQuestion(){
        $param = $this->param;
        $res = $this->traitGetQuestion($param);

        if($res['code'] == 0){
            $res['data']['user'] = $this->traitGetUser(['open_id'=>$res['data']['open_id']])['data'];
            $res['data']['timeformat'] = date('Y-m-d H:i:s', $res['data']['timestamp']);
            $res['data']['comment_num'] = $this->traitCommentTotal([
                'target_type' => 'question',
                'target_id' => $res['data']['id']
            ])['data'];
        }

        return json($res, 200);
    }


    /**
     * TODO 获取问题总数
     * -----------------------------------------------------------
     * @return \think\response\Json
     */
    public function questionTotal(){
        $param = $this->param;
        return json($this->traitQuestionTotal($param), 200);
    }


    /**
     * TODO 提问基础信息模板
     * -----------------------------------------------------------
     * @return \think\response\Json
     */
    public function templateBaseInfo(){
        $crop_type = $this->traitListSpecialistType([]);
        if($crop_type['code'] != 0){
            return json($crop_type, 200);
        }

        $tpl = [
            [
                'title' => '产业类型',
                'placeholder' => '请选择种植的作物类型',
                'options' => $crop_type['data']['list'],
                /*'options' => [
                    '茶叶','水果','蔬菜','畜牧','食用菌','中药材','油茶','渔业','竹笋','质量检测','品牌运营','农资','法律政策'
                ],*/
                'type' => 'select'
            ],
            [
                'title' => '病发程度',
                'placeholder' => '请选择植物病发程度',
                'options' => [
                    '零星发病',
                    '局部发病',
                    '大面积发病'
                ],
                'type' => 'select'
            ],
            [
                'title' => '天气状况',
                'placeholder' => '填写您种植区域的天气与温度',
                'options' => [],
                'type' => 'input'
            ],
            [
                'title' => '用药施肥',
                'placeholder' => '填写您用药施肥的情况',
                'options' => [],
                'type' => 'input'
            ],
        ];
        return jsonReturn(0, 'success', $tpl);
    }

}