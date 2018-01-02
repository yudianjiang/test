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
use think\Cache;
use app\extend\common\common;

class Applyset extends Controller
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
        $limitPrice = cache::get('limitPrice');
        $this->assign('limitPrice',$limitPrice);
        return view('index');
    }

    //提交限制金额
    public function limitP(){
        $price = input('post.price');
        Cache::set('limitPrice',$price);
        Cache::get('limitPrice');
        if($a = $price){
            return 1;
        }else{
            return 2;
        }

    }



}