<?php
// | 智能财务云平台i-FFSC  安卓端
// +----------------------------------------------------------------------
// | 安卓首页
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\android\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Cache;

Class Login {

    public function login(){

        $time_out = strtotime("+7 days");
        $uname = $_POST['uname'];
        $pwd = md5($_POST['upwd']);
        //账号
        $num = db('user')->where('num',$uname)->find();
        if(!empty($num)){
            $token = $num['id'].$time_out;
            $key = $num['id'].'_token';
            Cache::set($key,$token);
            $user = db('user')->where('num',$uname)->update(['token'=>$token]);
            $use = db('user')->where('num',$uname)->find();
            if($user) {
                if ($num['password'] == $pwd) {
                    $department = db('department')->where('id', $num['company'])->find();
//                    Session::set('num', $uname);
//                    Session::set('sid', $num['id']);
//                    Session::set('rid', $num['rid']);
                    $num['aclass'] = $num['class'];
                    unset($num['class']);
                    $num['department'] = $department['name'];
                    $num['token'] = $use['token'];
                    return json(['data' => $num, 'code' => 1, 'message' => '成功']);

                }else{
                    return json(['data'=>$num,'code'=>0,'message'=>'失败']);
                }
            }else{
                return json(['data'=>$num,'code'=>0,'message'=>'失败']);
            }


        }
        //手机号
        $tel = db('user')->where('tel',$uname)->find();
        if(!empty($tel)){
            $token = $tel['id'].$time_out;
            if($tel['password'] == $pwd){
                return json(['data'=>session('token'),'code'=>1,'message'=>'成功']);
            }
            return json(['data'=>'error','code'=>0,'message'=>'失败']);

        }
        //邮箱
        $email = db('user')->where('email',$uname)->find();
        if(!empty($email)){
            $token = $email['id'].$time_out;
            if($email['password'] == $pwd){
                Session::set('email',$uname);
                Session::set('sid',$num['id']);
                Session::set('token',$token);
                return json(['data'=>session('token'),'code'=>1,'message'=>'成功']);
            }
            return json(['data'=>'error','code'=>0,'message'=>'失败']);

        }

        if(empty($num) || empty($tel) || empty($email)){
            return json(['data'=>$num,'code'=>0,'message'=>'用户不存在']);
        }


    }


}
