<?php
namespace app\index\controller;

use app\jobs\DingDingPushJob;
use app\ztrait\controller\TraitUser;

class Index
{
    use TraitUser;

    public function index(){
        // queue(DingDingPushJob::class, ['队列执行',2,3]);
        return 'success';
    }
}
