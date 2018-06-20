<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 下午 3:32
 */

namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle{

    public function render(Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }

        if (!isset($statusCode)) {
            $statusCode = 500;
        }

        $debug = function ($info){
            $res = '请切换到debug模式';
            if(config('app_debug')){
                $res = $info;
            }
            return $res;
        };

        return jsonReturn(-1, '系统异常!', [
            'request_time' => $_SERVER['REQUEST_TIME'],
            'response_status'=>$statusCode,
            'response_msg' => $debug($e->getMessage()),
            'response_line' => $debug($e->getLine()),
            'response_file' => $debug($e->getFile())
        ], $statusCode);
    }

}