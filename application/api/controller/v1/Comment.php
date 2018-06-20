<?php

namespace app\api\controller\v1;


use app\common\controller\Base;
use app\ztrait\controller\TraitComment;
use app\ztrait\controller\TraitLike;
use app\ztrait\controller\TraitUser;

class Comment extends Base {

    use TraitComment;
    use TraitUser;
    use TraitLike;


    public function createComment(){
        $param = $this->param;
        return json($this->traitCreateComment($param), 200);
    }


    public function listComment(){
        $param = $this->param;
        $res_comment = $this->traitListComment($param);

        if($res_comment['code'] == 0){
            $list_comment = $res_comment['data']['list'];
            foreach ($list_comment as $key=>$val){
                $comment_id = $val['id'];
                $open_id = $val['open_id'];

                $list_comment[$key]['user'] = $this->traitGetUser(['open_id'=>$open_id])['data'];

                $like_num = $this->traitLikeTotal(['target_type'=>'comment', 'target_id'=>$comment_id])['data'];
                $list_comment[$key]['like_num'] = isset($like_num['total']) ? $like_num['total'] : 0;

                $like_status = [];
                if(isset($param['visit_open_id'])){
                    $like_status = $this->traitLikeStatus(['target_type'=>'comment', 'target_id'=>$comment_id, 'open_id'=>$param['visit_open_id']])['data'];
                }
                $list_comment[$key]['like_status'] = isset($like_status['status']) ? $like_status['status'] : 0;
            }
        }

        return json(arrayReturn(0,'success',[
            'list' => $list_comment,
            'total' => isset($res_comment['data']['total']) ? $res_comment['data']['total'] : 0
        ]), 200);
    }


    public function commentTotal(){
        $param = $this->param;
        return json($this->traitCommentTotal($param), 200);
    }


}
