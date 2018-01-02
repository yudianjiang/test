<?php
/**
 * Date: 2017/10/28
 * Time: 14:13
 */
// +----------------------------------------------------------------------
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 报销管理类
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\extend\language\language;
use think\Session;
use think\Request;
use app\extend\common\common;

class Member extends Controller
{

    public function _initialize()
    {
        $sid = session('sid');
        //判断用户登录信息
        if(empty($sid)){
            $this->error('非法访问','index/index');
        }

        $language = new language();
        $xml = $language->lang();
        if ($xml) {
            $this->assign('xml', $xml); //模板页面显示手机号
        }
        $data = array(
            'ch' => '中文',
            'en' => 'English'
        );
        $this->assign('data',$data);
        //左边遍历调用
        $user = db('user')->where('id',$sid)->find();
        $company = db('department')->select();
        $list = common::getTree($company,$user['company']);//调用公共方法
        $this->assign('list',$list);
        //实例化request
        $request= \think\Request::instance();
        $this->assign('request',$request);

    }


    public function index()
    {
        $id = session('sid');
        $user = db('user')->where('id',$id)->find();
        $this->assign('user',$user);

        //权限
        $rank = db('rank')->select();
        $this->assign('rank',$rank);
        // 单位 部门
        $company = db('department')->select();
        $results = common::getTree($company,$user['company']);
        $cp = db('department')->where('pid',0)->select();

        $this->assign('results',$results);
        $this->assign('company',$cp);

        return view('index');
    }

    public function selectDepartment(){
        $id = empty($_POST['id'])?0:intval($_POST['id']);
        $result = db('department')->where('pid',$id)->select();
        $data = array();
        foreach($result as $k => $v) {
            $data[] = $v;
        }
//        dump($data);die;
        return json_encode($data);
    }


    public function upPassWord(){
        $pwd = input('post.pwd');
        $npwd = input('post.npwd');
        $id = session('sid');
        $user = db('user')->where('id',$id)->find();

        if(md5($pwd) != $user['password']){
            return 1;
        }

        $update = db('user')->where('id',$id)->update(['password'=>md5($npwd)]);
        if($update){
            return 2;
        }else{
            return 3;
        }


    }

    public function startPwd(){
        $data = input('post.');
        $id = session('sid');
        $start = db('user')->where('id',$id)->find();
        if($start['password'] = md5('123456')){
            return 3;
        }
        if($data == 1){
            $user = db('user')->where('id',$id)->update(['password'=>md5('123456')]);
            if($user){
                return 1;
            }else{
                return 2;
            }
        }
    }

    //个人资料
    public function selfForm(){
        $post = input('post.');
        $id = session('sid');
        $user = db('user')->where('id',$id)->update($post);
        if($user){
            return 1;
        }else{
            return 2;
        }
    }

    //添加人员
    public function personForm(){
        $post = input('post.');
        if($post['rank']){
            return '3';
        }else if($post['company']){
            return '4';
        }else if($post['department']){
            return '5';
        }else{
            return '6';
        }
//        dump($post);die;
//        $user = db('user')->insert($post);
//        if($user){
//            return 1;
//        }else{
//            return 2;
//        }
    }


}