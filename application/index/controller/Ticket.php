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

use think\console\output\Question;
use think\Controller;
use think\Db;
use app\extend\language\language;
use app\extend\common\common;
use think\Session;
use think\Request;
use think\Paginator;
use think\model;

class Ticket extends Controller
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

        $rid = session('rid');
        $sid = session('sid');
        $class = session('class');
        //科研人员
        if($rid == 4){
            $tmanage = db('user')->where('id',$sid)->find();
            $user = db('user')->where('num',$tmanage['num'])->find();
        }else if($rid == 3){//团队负责人
            $tmanage = db('user')->alias('u')->join('ticket t','u.id = t.uid','right')->where('class', $class)->group('name')->select();
            $user = db('user')->where('num',$tmanage[0]['num'])->find();
            $tmanage['rid'] = 3;
        }elseif($rid == 5) {
            $tmanage = db('user')->alias('u')->join('ticket t','u.id = t.uid','right')->where('class', $class)->group('name')->select();
            $user = db('user')->where('num',$tmanage[0]['num'])->find();
            $tmanage['rid'] = 5;
        }elseif($rid == 6) {
            $tmanage = db('user')->alias('u')->join('ticket t','u.id = t.uid','right')->group('name')->select();
            $tmanage['rid'] = 6;
            $user = db('user')->where('num',$tmanage[0]['num'])->find();
        }
        $this->assign('tmanage',$tmanage);

        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
        ];


        $ticket = db('ticket')->alias('t')->join($join)->where('uid',$user['id'])->order('tid asc')->select();
        //假排序

        $i =1;
        $h =1;
        $f =1;
        foreach($ticket as $k =>$v){
            //临时字段 假排序
            $ticketNo = $v['ticketNo'];
            $no = substr($ticketNo,0,1);
            if($no == 'c'){
                $a = $i++;
                $ticketNo = 'c' . sprintf("%04d", $a);
                $ticket[$k]['order'] = $ticketNo;
                $ticket[$k]['nextNo'] = $ticketNo;
            }else if($no == 'h'){
                $a = $h++;
                $ticketNo = 'h' . sprintf("%04d", $a);
                $ticket[$k]['order'] = $ticketNo;
                $ticket[$k]['nextNo'] = $ticketNo;
            }else if($no == 'f'){
                $a = $f++;
                $ticketNo = 'f' . sprintf("%04d", $a);
                $ticket[$k]['order'] = $ticketNo;
                $ticket[$k]['nextNo'] = $ticketNo;
            }

        }
        $this->assign('ticket',$ticket);
        return view('index');
    }


    public function doName(){

        $id = Request::instance()->post('id');
        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
        ];

        //权限
        $rid = session('rid');
        $sid = session('sid');
        $class = session('class');
        if($rid == 4){
            $user = db('user')->where(['num' => $id,'id'=>$sid])->find();
        }else if($rid ==3 || $rid ==5){
            $user = db('user')->where(['num' => $id,'class'=>$class])->find();
        }else if($rid ==6){
            $user = db('user')->where('num', $id)->find();
        }

        $ticket = db('ticket')->alias('t')->join($join)->where('uid',$user['id'])->select();
//        dump($ticket);die;
        $str = '';
        //假排序
        $i =1;
        $h =1;
        foreach($ticket as $k => $v){

            if($v['type'] == 1){
                $v['ctype'] = '出租票';
            }else if($v['type'] == 2){
                $v['ctype'] = '火车票';
            }else{
                $v['ctype'] = '飞机票';
            }
            if($v['status'] == 0){
                $v['status'] = '正常';
            }else{
                $v['status'] = '违规';
            }


            //临时字段 假排序
            $ticketNo = $v['ticketNo'];
            $no = substr($ticketNo,0,1);
            if($no == 'c'){
                $a = $i++;
                $ticketNo = 'c' . sprintf("%04d", $a);
            }else if($no == 'h'){
                $a = $h++;
                $ticketNo = 'h' . sprintf("%04d", $a);
            }
            $date = date('Y-m-d H:i:s',$v['addTime']);
            $str.="<li class=\"stye{$v['type']} id\" no=\"{$v['ticketNo']}\">{$ticketNo}</li>";
            $str.="<li>{$v['ctype']}</li>";
            $str.="<li>{$v['price']}</li>";
            $str.="<li>{$date}</li>";
            $str.="<li>{$v['status']}</li>";

        }


        echo  $str;
    }


    //切换详细数据样式 出租车
    public function cstyle(){
        $no = Request::instance()->post('no');
        $orderNo = Request::instance()->post('orderNo');

        $ticket = db('ticket')->where('ticketNo',$no)->find();
        $user = db('user')->where('id',$ticket['uid'])->find();
        $type = db('ticket_taxi')->where('pid',$ticket['id'])->find();
        $type['ticketNo'] = $ticket['ticketNo'];
        $type['price'] = $ticket['price'];
        $type['uname'] = $user['name'];
        $type['image'] = $ticket['image'];
        $type['order'] = $orderNo;
        echo json_encode($type);die;

    }

    //保存 出租票
    public function cSave(){
        $post = Request::instance()->post();
        $ticketNo = $post["ticketNo"];

        $price = $post["price"];
        $data['offTime']=$post["offTime"];
        $data['ticketTime']=$post["ticketTime"];
        $data['ticketNumber']=$post["ticketNumber"];
        $data['ticketCode']=$post["ticketCode"];
        $data['cupdateTime']=time();
        $savep = db('ticket')->where('ticketNo',$ticketNo)->update(['price' => $price,'updateTime'=>time()]);
        $ticket = db('ticket')->where('ticketNo',$ticketNo)->find();
//        $user = db('user')->where('id',$ticket['uid'])->update(['imanage','1']);
        $taxi = db('ticket_taxi')
            ->where('pid',$ticket['id'])
            ->update($data);

        if($taxi || $savep){
            return 1;
        }else{
            return 2;
        }

    }

    //删除出租票
    public function delc(){
        $post = Request::instance()->post('delno');

        $ticket = db('ticket')->where('ticketNo',$post)->find();
        $deltaxi = db('ticket_taxi')->where('pid',$ticket['id'])->delete();

        if($deltaxi){
            $del = db('ticket')->where('ticketNo',$post)->delete();
            if($del) {
                echo 1;
            }else{
                echo 2;
            }
        }else{
            echo 2;
        }
    }


    //切换详细数据样式 火车
    public function hstyle(){
        $no = Request::instance()->post('no');
        $orderNo = Request::instance()->post('orderNo');

        $ticket = db('ticket')->where('ticketNo',$no)->find();
        $user = db('user')->where('id',$ticket['uid'])->find();
        $type = db('ticket_train')->where('pid',$ticket['id'])->find();
        $type['ticketNo'] = $ticket['ticketNo'];
        $type['price'] = $ticket['price'];
        $type['uname'] = $user['name'];
        $type['image'] = $ticket['image'];
        $type['order'] = $orderNo;
        echo json_encode($type);die;

    }

    //保存 火车票
    public function hSave(){
        $post = Request::instance()->post();
        $ticketNo = $post["hticketNo"];
        $price = $post["hprice"];
        $data['trainNum']=$post["trainNum"];
        $data['startLocation']=$post["startLocation"];
        $data['traintime']=$post["traintime"];
        $data['trainUname']=$post["trainUname"];
        $data['level']=$post["level"];
        $data['hupdateTime']=time();
        $savep = db('ticket')->where('ticketNo',$ticketNo)->update(['price' => $price,'updateTime'=>time()]);
        $ticket = db('ticket')->where('ticketNo',$ticketNo)->find();
        $train = db('ticket_train')
            ->where('pid',$ticket['id'])
            ->update($data);
        if($train || $savep){
            return 1;
        }else{
            return 2;
        }

    }

    //删除火车票
    public function delh(){
        $post = Request::instance()->post('delno');

        $ticket = db('ticket')->where('ticketNo',$post)->find();
        $deltrain = db('ticket_train')->where('pid',$ticket['id'])->delete();

        if($deltrain ){
            $del = db('ticket')->where('ticketNo',$post)->delete();
            if($del) {
                echo 1;
            }else{
                echo 2;
            }
        }else{
            echo 2;
        }
    }

    //切换详细数据样式 飞机
    public function fstyle(){
        $no = Request::instance()->post('no');

        $ticket = db('ticket')->where('ticketNo',$no)->find();
        $user = db('user')->where('id',$ticket['uid'])->find();
        $type = db('ticket_plane')->where('pid',$ticket['id'])->find();
        $type['ticketNo'] = $ticket['ticketNo'];
        $type['price'] = $ticket['price'];
        $type['uname'] = $user['name'];
        $type['image'] = $ticket['image'];
        return json_encode($type);

    }

    //保存 飞机票
    public function fSave(){
        $post = Request::instance()->post();
        $ticketNo = $post["fticketNo"];
        $price = $post["price"];
        $data['planeNumber']=$post["planeNumber"];
        $data['planeSlocation']=$post["planeSlocation"];
        $data['planedate']=$post["planedate"];
        $data['planeUname']=$post["planeUname"];
        $data['planelevel']=$post["planelevel"];
        $data['fupdateTime']=time();
        $savep = db('ticket')->where('ticketNo',$ticketNo)->update(['price' => $price,'updateTime'=>time()]);
        $ticket = db('ticket')->where('ticketNo',$ticketNo)->find();
        $plane = db('ticket_plane')
            ->where('pid',$ticket['id'])
            ->update($data);
        if($plane || $savep){
            return 1;
        }else{
            return 2;
        }

    }


    //删除飞机票
    public function delf(){
        $post = input('post.delno');

        $ticket = db('ticket')->where('ticketNo',$post)->find();

        $delplane = db('ticket_plane')->where('pid',$ticket['id'])->delete();
        if($delplane ){
            $del = db('ticket')->where('ticketNo',$post)->delete();
            if($del) {
                echo 1;
            }else{
                echo 2;
            }
        }else{
            echo 2;
        }
    }



    //下一页
    public function nextPage()
    {
        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
        ];
        $list = db('ticket')->alias('t')->join($join)->where('uid',session('sid'))->order('tid asc')->select();

        //假排序

        $i =1;
        $h =1;
        $f =1;
        foreach($list as $k =>$v){
            //临时字段 假排序
            $ticketNo = $v['ticketNo'];
            $no = substr($ticketNo,0,1);
            if($no == 'c'){
                $a = $i++;
                $ticketNo = 'c' . sprintf("%04d", $a);
                $list[$k]['order'] = $ticketNo;
                $list[$k]['nextNo'] = $ticketNo;
            }else if($no == 'h'){
                $a = $h++;
                $ticketNo = 'h' . sprintf("%04d", $a);
                $list[$k]['order'] = $ticketNo;
                $list[$k]['nextNo'] = $ticketNo;
            }else if($no == 'f'){
                $a = $f++;
                $ticketNo = 'f' . sprintf("%04d", $a);
                $list[$k]['order'] = $ticketNo;
                $list[$k]['nextNo'] = $ticketNo;
            }

        }

        $no = input('post.fno');
        $ticket = db('ticket')->where('ticketNo', $no)->find();
        $user = db('user')->where('id', $ticket['uid'])->find();
        $next = db('ticket')
            ->where([
                'uid' => $ticket['uid'],
                'id' => ['>', $ticket['id']],
                // 'ttmanage' => 0,
            ])
            ->find();
        foreach($list as $v){
            if($v['tid'] == $next['tid']){
                $next['order'] = $v['order'];//下一页假排序
            }
        }

        //查询下条数据
        $taxi = db('ticket_taxi')->where('pid',$next['id'])->find();
        $train = db('ticket_train')->where('pid', $next['id'])->find();
        $plane = db('ticket_plane')->where('pid', $next['id'])->find();
        $str = '';
        if (!empty($taxi)) {
            $str .= "<form id=\"taxiForm\" onsubmit=\"return false\" action=\"##\" method=\"post\" >";

            $str .= "<div class=\"u2 a1\" style=\"display:block\">";
            $str .= "<input type=\"hidden\" id=\"cno\" name=\"ticketNo\" value=\"{$next['ticketNo']}\"/>";
            $str .= "<ul class=\"ub\">";
            $str .= "<li class=\"lis\"><button class=\"czbtn\" type=\"submit\" value=\"\">保存</button></li>";
            $str .= "<li  delno=\"\" class=\"lis del\">删除</li>";
            $str .= "<li class=\"rid\"><span id=\"cuname\">{$user['name']}</span><span>-</span><span id=\"ccno\">{$next['order']}</span></li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"\"  class=\"imgc\" src=\"{$next['image']}\" alt=\"\">";


            $str .= "<ul>";
            $str .= "<li class=\"nli\">发票代码&nbsp;&nbsp;:<input type=\"text\" id=\"cticketCode\" name=\"ticketCode\" value=\"{$taxi['ticketCode']}\"/></li>";
            $str .= "<li class=\"nli\">发票号码&nbsp;&nbsp;:<input type=\"text\" id=\"cticketNumber\" name=\"ticketNumber\" value=\"{$taxi['ticketNumber']}\"/></li>";
            $str .= "<li class=\"nli\">发票日期:<input type=\"text\" id=\"cticketTime\" name=\"ticketTime\" value=\"{$taxi['ticketTime']}\"/></li>";
            $str .= "<li class=\"nli\">发票时间:<input type=\"text\" id=\"coffTime\" name=\"offTime\" value=\"{$taxi['offTime']}\"/></li>";
            $str .= "<li class=\"nli\">发票金额:<input type=\"text\" id=\"cprice\" name=\"price\" value=\"{$next['price']}\"/></li>";
//
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"{$next['ticketNo']}\" ono=\"\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            $str .= "</form>";
            return $str;
        } else if (!empty($train)) {
            //<!--火车-->

            $str .= "<form id=\"trainForm\" method=\"post\" onsubmit=\"return false\" action=\"##\" method=\"post\">";
            $str .= "<div class=\"hc_u2 a2\" style=\"display:block\">";
            $str .= "<input type=\"hidden\" id=\"hno\" name=\"hticketNo\" value=\"{$next['ticketNo']}\"/>";
            $str .= "<ul class=\"ub\">";
            $str .= "<li class=\"lis\"><button class=\"hcbtn\" type=\"submit\" value=\"\">保存</button></li>";
            $str .= "<li  hdelno=\"\" class=\"lis hdel\">删除</li>";
            $str .= "<li class=\"rid\"><span id=\"huname\"></span>{$user['name']}<span>-</span><span id=\"hcno\">{$next['order']}</span></li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"{$next['ticketNo']}\" class=\"imgc2\" src=\"{$next['image']}\" alt=\"\">";
//
            $str .= "<ul >";
            $str .= "<li class=\"nli\">火车车次&nbsp;&nbsp;:<input type=\"text\" id=\"htrainNum\" name=\"trainNum\" value=\"{$train['trainNum']}\"/></li>";
            $str .= "<li class=\"nli\">起始地点&nbsp;&nbsp;:<input type=\"text\" id=\"hstartLocation\" name=\"startLocation\" value=\"{$train['startLocation']}\"/></li>";
            $str .= "<li class=\"nli\">车票日期&nbsp;&nbsp;:<input type=\"text\" id=\"time\" name=\"time\" value=\"{$train['traintime']}\"/></li>";
            $str .= "<li class=\"nli\">乘客姓名&nbsp;&nbsp;:<input type=\"text\" id=\"htrainName\" name=\"trainName\" value=\"{$train['trainUname']}\"/></li>";
            $str .= "<li class=\"nli\">座位级别&nbsp;&nbsp;:<input type=\"text\" id=\"hlevel\" name=\"level\" value=\"{$train['level']}\"/></li>";
            $str .= "<li class=\"nli\">车票金额&nbsp;&nbsp;:<input type=\"text\" id=\"hprice\" name=\"hprice\" value=\"{$next['price']}\"/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"{$next['ticketNo']}\" ono=\"\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            $str .= "</form>";
            return $str;
        } else if (!empty($plane)){
            //<!--飞机-->

            $str .= "<form id=\"planeForm\" method=\"post\" onsubmit=\"return false\" action=\"##\" method=\"post\">";
            $str .= "<div class=\"fj_u2 a3\" style=\"display:block\">";
            $str .= "<input type=\"hidden\" id=\"fno\" name=\"fticketNo\" value=\"{$next['ticketNo']}\"/>";
            $str .= "<ul class=\"ub\">";
            $str .= "<li class=\"lis\"><button class=\"fjbtn\" type=\"submit\" value=\"\">保存</button></li>";
            $str .= "<li  fdelno=\"\" class=\"lis fdel\">删除</li>";
            $str .= "<li class=\"rid\"><span id=\"funame\"></span>{$user['name']}<span>-</span><span id=\"fcno\">{$next['order']}</span></li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"{$next['ticketNo']}\" class=\"imgc3\" src=\"{$next['image']}\" alt=\"\">";
//
            $str .= "<ul >";
            $str .= "<li class=\"nli\">航班&nbsp;&nbsp;:<input type=\"text\" id=\"forderNum\" name=\"planeNumber\" value=\"{$train['planeNumber']}\"/></li>";
            $str .= "<li class=\"nli\">起始地点&nbsp;&nbsp;:<input type=\"text\" id=\"fstartLocation\" name=\"planeSlocation\" value=\"{$train['planeSlocation']}\"/></li>";
            $str .= "<li class=\"nli\">日期&nbsp;&nbsp;:<input type=\"text\" id=\"fdate\" name=\"planedate\" value=\"{$train['planedate']}\"/></li>";
            $str .= "<li class=\"nli\">机票级别 &nbsp;&nbsp;:<input type=\"text\" id=\"flevel\" name=\"planelevel\" value=\"{$train['planelevel']}\"/></li>";
            $str .= "<li class=\"nli\">乘客姓名&nbsp;&nbsp;:<input type=\"text\" id=\"fname\" name=\"planeUname\" value=\"{$train['planeUname']}\"/></li>";
            $str .= "<li class=\"nli\">机票金额&nbsp;&nbsp;:<input type=\"text\" id=\"fprice\" name=\"price\" value=\"{$train['price']}\"/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"{$next['ticketNo']}\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            $str .= "</form>";
            return $str;
        }else{
            return 4;
        }


    }

}