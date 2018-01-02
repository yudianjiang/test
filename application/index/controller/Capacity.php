<?php
// +----------------------------------------------------------------------
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 报销管理类
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
/**
 * Created by PhpStorm.
 * User: 秋刀鱼
 * Date: 2017/11/27
 * Time: 15:08
 */
namespace app\index\controller;


use think\console\output\Question;
use think\Controller;
use think\Db;
use app\extend\language\language;
use app\extend\common\common;
use think\Session;
use think\Request;
use think\Cache;
use think\Paginator;

Class Capacity extends Controller
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

    public function index(){
        //查询未办理用户

        $rid = session('rid');
        $sid = session('sid');
        $class = session('class');
        //科研人员
        if($rid == 4){
            $capacity = db('user')->where('id',$sid)->find();
        }else if($rid == 3){//团队负责人
            $capacity = db('user')->where('id',$sid)->find();
        }elseif($rid == 5) {
            $capacity = db('user')->alias('u')->join('ticket t','u.id = t.uid','right')->where('class', $class)->group('name')->select();
            $capacity['rid'] = 5;
        }elseif($rid == 6) {
            $capacity = db('user')->alias('u')->join('ticket t','u.id = t.uid','right')->group('name')->select();
            $capacity['rid'] = 6;
        }
        $this->assign('capacity',$capacity);


        //查询金额是否有超过规定
        $ticket = db('ticket')->where(['tappId'=>0,'type'=>1])->order('id asc')->select();
        $arr = array();
        $limitPrice = cache::get('limitPrice');
        $i =1;
        foreach($ticket as $v){
            //临时字段 假排序
            $ticketNo = $v['ticketNo'];
            $no = substr($ticketNo,0,1);
            if($no == 'c'){
                $a = $i++;
                $ticketNo = 'c' . sprintf("%03d", $a);
                $v['order'] = $ticketNo;
            }
            $user = db('user')->where('id',$v['uid'])->find();
            if($v['price'] > $limitPrice){
                $v['uname'] = $user['name'];
                $arr[] = $v;
            }
        }

        //连号
        $ticket = db('ticket')->where(['type'=>1])->select();
//        $ticket = db('ticket')->where(['uid'=>$sid,'type'=>1])->select();
        $ticketArr = array();
        foreach($ticket as $v){
            $taxi = db('ticket_taxi')->where('pid',$v['id'])->find();
            $ticketArr[] = $taxi['ticketNumber'];
        }

        $repeat_arr = array();
        $len = count ( $ticketArr );
        for($i = 0; $i < $len; $i ++) {
            for($j = $i + 1; $j < $len; $j ++) {
                if ($ticketArr [$i]+1  == $ticketArr [$j]) {
                    $repeat_arr []['name'] = $ticketArr [$i].','.$ticketArr[$j];
                    break;
                }
            }
        }



        $this->assign('arr',$arr);
        $this->assign('endarr',$repeat_arr);
        $this->assign('limitPrice',$limitPrice);


        return view('index');
    }
}