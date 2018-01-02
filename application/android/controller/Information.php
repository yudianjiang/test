<?php
// | 智能财务云平台i-FFSC  安卓端
// +----------------------------------------------------------------------
// | 安卓app接口  票据管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\android\controller;

use think\Controller;
use think\Db;
use app\extend\common\common;
use think\Cache;
use think\cache\driver\Redis;

Class Information
{


    public function addLeave(){
        $uid = input('post.uid');
        $token = input('post.token');
        $sendId = input('post.sendId');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }else if(empty($token)){
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }
        $value = input('post.content');
        $date = date('Y-m-d H:i:s',time());
        $redis = new \Redis;
        $redis->connect('127.0.0.1', 6379, 3600);
        $key = $uid.'_leaveSpeak_'.$sendId;
//        $data = $date.','.$value;
        $data = array('date'=>$date,'content'=>$value,'send'=>$uid,'get'=>$sendId);
        $content = serialize($data);
        unset($data);
        $push = $redis->rpush($key,$content);
        if($push){
            return json(['data' => $push, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $push, 'code' => 0, 'message' => '失败']);
        }

    }

    //查询留言记录
    public function selectLeave(){
        $uid = input('post.uid');
        $token = input('post.token');
        $sendId = input('post.sendId');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }else if(empty($token)){
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }


        $redis = new \Redis;
        $redis->connect('127.0.0.1', 6379, 3600);
        $key = $sendId.'_leaveSpeak_'.$uid;


        $count = $redis->llen($key);
        $get = $redis->lrange($key,0,$count);
        $content = array();
        foreach($get as $v){
            $content[] = unserialize($v);
        }

        if($content){
            return json(['data' => $content, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $content, 'code' => 0, 'message' => '失败']);
        }
    }





}