<?php
// +----------------------------------------------------------------------
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 用户登录状态鉴定类
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Config;
use think\Session;


class Register extends Controller
{

    public function index()
    {

        $uname = $_POST['uname'];
        $pwd = md5($_POST['upwd']);
        $user = db('user')->select();
        //账号
        $num = db('user')->where('num',$uname)->find();
        if(!empty($num)){
            if($num['password'] == $pwd){
                Session::set('num',$uname);
                Session::set('sid',$num['id']);
                Session::set('rid',$num['rid']);
                Session::set('department',$num['department']);
                Session::set('class',$num['class']);
                Session::set('uname',$num['name']);
                $this->redirect('ticket/index');

            }
            $this->error('密码错误,请重新输入!', Url('index/index'));

        }
        //手机号
        $tel = db('user')->where('tel',$uname)->find();
        if(!empty($tel)){
            if($tel['password'] == $pwd){
                Session::set('tel',$uname);
                Session::set('sid',$num['id']);
                return view('apply/index');
            }
            $this->error('密码错误,请重新输入!', Url('index/index'));

        }
        //邮箱
        $email = db('user')->where('email',$uname)->find();
        if(!empty($email)){
            if($email['password'] == $pwd){
                Session::set('email',$uname);
                Session::set('sid',$num['id']);
                return view('apply/index');
            }
            $this->error('密码错误,请重新输入!', Url('index/index'));

        }




    }

}