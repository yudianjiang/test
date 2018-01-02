<?php
// +----------------------------------------------------------------------
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 信息管理类
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\extend\language\language;
use app\extend\common\common;
use think\Session;
use think\Request;

class Info extends Controller
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
       //查询未办理用户
       $imanage = db('user')->where('imanage',0)->select();
       $amanage = db('user')->where('amanage',0)->select();
       $tmanage = db('user')->where('tmanage',0)->select();
       $this->assign('imanage',$imanage);
       $this->assign('amanage',$amanage);
       $this->assign('tmanage',$tmanage);

       return view('index');
   }


}