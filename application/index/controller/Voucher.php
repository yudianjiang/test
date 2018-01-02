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
use app\extend\common\common;
use think\Session;
use think\Request;

Class Voucher extends Controller
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
        $sid = session('sid');

        $user  = db('user')->where('id',$sid)->find();
        $bxd = db('t_bx_d_bxd')->where('CJR',$user['num'])->order('id desc')->find();
        if (!empty($bxd)) {
                $voucher = db('voucher')->where('bid', $bxd['id'])->select();
                if(!empty($voucher)){
                    foreach ($voucher as $k => $vv) {
                        if ($vv['borrowPrice'] == 0) {
                            $voucher[$k]['borrowPrice'] = '';
                        }
                        if ($vv['loanPrice'] == 0) {
                            $voucher[$k]['loanPrice'] = '';
                        }


                    }
                    $this->assign('voucher', $voucher);
                    $this->assign('lists', $voucher[0]);

                    $this->assign('user',$user);
                }else{
                    return('暂无凭证');
                }
                   

        }else{
             return('暂无凭证');
        }

        



        return view('index');
    }

}