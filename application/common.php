<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Request;
use think\Validate;

Request::hook('user', 'getUserInfo'); // 方法注入

if (!function_exists('getUserInfo')) {
    function getUserInfo(Request $request, $open_id = null)
    {
        return [
            'name'   => 'Jason.Z',
            'gender' => '男',
        ];
    }
}

if (!function_exists('jsonReturn')) {
    /**
     * TODO 返回数据格式 - Json
     * -----------------------------------------------------------
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param int $status
     * @return \think\response\Json
     */
    function jsonReturn($code = 0, $msg = '', $data = [], $status = 200)
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], 200);
    }
}

if (!function_exists('arrayReturn')) {
    /**
     * TODO 返回数据格式 - Array
     * -----------------------------------------------------------
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param int $status
     * @return array
     */
    function arrayReturn($code = 0, $msg = '', $data = [])
    {
        return [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];
    }
}

if (!function_exists('isParamValid')) {
    /**
     * TODO 参数校验，使用系统提供的Validate类
     * -----------------------------------------------------------
     * @param array $rule    校验规则
     * @param array $param   校验数据
     * @return bool
     */
    function isParamValid($rule = [], $param = [])
    {
        $status = true;
        $msg    = '';

        $validate = new Validate($rule);
        if (!$validate->check($param)) {
            $status = false;
            $msg    = $validate->getError();
        }

        $res = [
            'code' => $status,
            'msg'  => $msg,
        ];
        return $res;
    }
}

if (!function_exists('getUniqid')) {
    /**
     * TODO 获取唯一ID值
     * -----------------------------------------------------------
     * @param null $type
     * @return string
     */
    function getUniqid($type = null)
    {
        return md5($type . uniqid(rand(), true));
    }
}

if (!function_exists('getMaxAndMinVal')) {
    /**
     * TODO 获取 list 数组的最大 id 和最小 id
     * -----------------------------------------------------------
     * @param array $array (list)
     * @return array
     */
    function getMaxAndMinVal($array = [])
    {
        $max_cursor = 0;
        $min_cursor = 0;

        if (!empty($array)) {
            foreach ($array as $key => $val) {
                $tmp_cursor = $val['id'];

                if ($key == 0) {
                    $max_cursor = $tmp_cursor;
                    $min_cursor = $tmp_cursor;
                }

                if ($tmp_cursor > $max_cursor) {
                    $max_cursor = $tmp_cursor;
                }

                if ($tmp_cursor < $min_cursor) {
                    $min_cursor = $tmp_cursor;
                }
            }
        }

        return [
            'min_cursor' => $min_cursor,
            'max_cursor' => $max_cursor,
        ];
    }
}

/**
 * curl请求方式
 * @param $url
 * @param array $param
 * @param string $method
 * @return mixed
 */
function CurlRequest($url, $param = [], $method = 'get')
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");

    if ($method == 'post') {
        curl_setopt($ch, CURLOPT_HEADER, 0); // http header
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        curl_setopt($ch, CURLOPT_POST, 1); // post 提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

    } else if ($method == 'get') {
        curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $output = curl_exec($ch);
    curl_close($ch);
    //var_dump($output);
    return $output;
}

//组合一维数组
function unlimitedForLevel($cate, $html = "--- ", $pid = 0, $level = 0)
{
    $arr = array();
    foreach ($cate as $k => $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level + 1;
            $v['html']  = str_repeat($html, $level);
            $arr[]      = $v;
            $arr        = array_merge($arr, unlimitedForLevel($cate, $html, $v['id'], $level + 1));
        }
    }
    return $arr;
}
//组合多维数组
function unlimitedForLayer($cate, $name = 'child', $pid = 0)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $arr[]    = $v;
        }
    }
    return $arr;
}
//传递一个子分类ID返回所有的父级分类
function getParents($cate, $id)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
            $arr   = array_merge(getParents($cate, $v['pid']), $arr);
        }
    }
    return $arr;
}
//传递一个父级分类ID返回所有子分类ID
function getChildsId($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $arr[] = $v['id'];
            $arr   = array_merge($arr, getChildsId($cate, $v['id']));
        }
    }
    return $arr;
}
//传递一个父级分类ID返回所有子分类
function getChilds($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $arr[] = $v;
            $arr   = array_merge($arr, getChilds($cate, $v['id']));
        }
    }
    return $arr;
}

//图片base64加密
function base64EncodeImage($image_file)
{
    if ($image_file != 0) {
        //downfile($image_file);
        $base64_image = '';
        $image_info   = getimagesize($image_file);
        $image_data   = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}
//获取远程图片并且保存到本地,得到本地路径
function getImgAndSaveToLocal($file)
{

}

function is_serialized($data)
{
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }

    if (!preg_match('/^([adObis]):/', $data, $badions)) {
        return false;
    }

    switch ($badions[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                return true;
            }

            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                return true;
            }

            break;
    }
    return false;
}
