<?php
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 首页
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Controller;
use think\Cookie;
use think\Config;
use app\extend\language\language;
use app\extend\common\common;
use think\Request;

class Index extends Controller
{
    public function _initialize(){
        $sid = session('sid');
        Cookie::init(['prefix'=>'think_','expire'=>3600,'path'=>'/']);
        /*实例化语言包*/
        $language = new language();
        $xml = $language->lang();
        if ($xml) {
            $this->assign('xml', $xml);
        }
        $data = array(
            'ch' => '中文',
            'en' => 'English'
        );
        $this->assign('data',$data);
        $lang = Cookie::get('lang');
        $this->assign('lang',$lang);
        //左边遍历调用
        $user = db('user')->where('id',$sid)->find();
        $company = db('department')->select();
        $list = common::getTree($company,$user['company']);//调用公共方法
        $this->assign('list',$list);
    }

    public function index()
    {
            return view('index');

    }
}
