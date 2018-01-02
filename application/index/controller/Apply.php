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

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\extend\language\language;
use app\extend\common\common;
use think\Session;
use think\Request;

class Apply extends Controller
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
        $imanage = db('user')->alias('u')->where('imanage',0)->select();
        $amanage = db('user')->alias('u')->where('amanage',1)->select();
        $tmanage = db('user')->alias('u')->where('tmanage',0)->select();
        $this->assign('imanage',$imanage);
        $this->assign('amanage',$amanage);
        $this->assign('tmanage',$tmanage);

        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
            ['user u','t.uid = u.id','left'],
        ];

        $tfind= db('user')->where('amanage',1)->find();
        $where['uid'] = $tfind['id'];
//        $where['status'] = 0;
        $where['timanage'] = 0;
        $where['tamanage'] = 0;
//        $where['ttmanage'] = 0;
//        $ticket = db('ticket')->alias('t')->join($join)->where($where)->group('applyNo')->paginate(10);
        $ticket = db('ticket')->alias('t')->join($join)->where($where)->group('applyNo')->select();

        foreach($ticket as $k => $v){
            $ticket[$k]['updateTime'] = substr($v['updateTime'],0,10);
        }
        $this->assign('ticket',$ticket);
        return view('index');
    }

    //左边名字
    public function doName(){

        $id = Request::instance()->post('id');
        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
            ['user u','t.uid = u.id','left'],
        ];
        $where['uid'] = $id;
        $where['timanage'] = 0;
        $where['tamanage'] = 0;
//        $where['ttmanage'] = 0;
        $ticket = db('ticket')->alias('t')->join($join)->where($where)->group('applyNo')->select();
        foreach($ticket as $k => $v){
            $ticket[$k]['updateTime'] = substr($v['updateTime'],0,10);
        }


        $str = '';
        foreach($ticket as $vv){

            if($vv['status'] == 0){
                $vv['status'] = '正常';
            }else{
                $vv['status'] = '违规';
            }
            $str.="<li class=\"click_hand stye{$vv['type']} id sty2\" checkid=\"{$vv['uid']}\" no=\"{$vv['applyNo']}\">{$vv['applyNo']}</li>";
            $str.="<li>{$vv['price']}</li>";
            $str.="<li>{$vv['updateTime']}</li>";
            $str.="<li>{$vv['status']}</li>";
            $str.="<li>{$vv['name']}</li>";
            $str.="<li class=\"click_hand ap_pass\" pno=\"{$vv['ticketNo']}\">通过</li>";
            $str.="<li class=\"click_hand ap_nopass\" npno=\"{$vv['ticketNo']}\">不通过</li>";
            $str.="<li class=\"click_hand\">说明</li>";
            $str.="<li class=\"click_hand csty3 \" ycheckid=\"{$vv['uid']}\" vappno=\"{$v['applyNo']}\">查看相关票据</li>";
        }
//        dump($str);
        echo  $str;
    }

    //下一页
    public function nextPage()
    {
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo', $no)->find();
        $user = db('user')->where('id',$ticket['uid'])->find();
        $next = db('ticket')
            ->where([
                'uid' => $ticket['uid'],
                'ticketNo' => ['<>', $no],
            ])
            ->find();
        //查询下条数据
        $train = db('ticket_train')->where('pid', $next['id'])->find();
        $taxi = db('ticket_taxi')->where('pid', $next['id'])->where('id','>',$next['id'])->find();
        $plane = db('ticket_plane')->where('pid', $next['id'])->where('id','>',$next['id'])->find();
        $str = '';
        if (!empty($taxi)) {

            $str .= "<div class=\"u2 a1\" style=\"display:block\">";
            $str .= "<input type=\"hidden\" id=\"cno\" name=\"ticketNo\" value=\"\"/>";
            $str .= "<ul class=\"nu\">";
            $str .= "<li class=\"click_hand appno\" anoid=\"\">不合格</li>";
            $str .= "<li class=\"click_hand appund\" unoid=\"\">说明</li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"\"  class=\"imgc\" src=\"{$next['image']}\" alt=\"\">";


            $str .= "<ul>";
            $str .= "<li class=\"nli\">发票代码&nbsp;&nbsp;:<input type=\"text\" id=\"cticketCode\" name=\"ticketCode\" value=\"{$taxi['ticketCode']}\"/></li>";
            $str .= "<li class=\"nli\">发票号码&nbsp;&nbsp;:<input type=\"text\" id=\"cticketNumber\" name=\"ticketNumber\" value=\"{$taxi['ticketNumber']}\"/></li>";
            $str .= "<li class=\"nli\">发票日期:<input type=\"text\" id=\"cticketTime\" name=\"ticketTime\" value=\"{$taxi['ticketTime']}\"/></li>";
            $str .= "<li class=\"nli\">发票时间:<input type=\"text\" id=\"conTime\" name=\"onTime\" value=\"{$taxi['onTime']}\"/></li>";
            $str .= "<li class=\"nli\">发票金额:<input type=\"text\" id=\"cprice\" name=\"price\" value=\"{$next['price']}\"/></li>";
            $str .= "<li class=\"nli\">起止地点:<input type=\"text\" id=\"clocation1\" name=\"taxilocation1\" value=\"{$taxi['taxilocation1']}\"/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"{$next['ticketNo']}\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            //<!--火车-->

            $str .= "<form method=\"post\" action=\"{:url('ticket/hSave')}\">";
            $str .= "<div class=\"hc_u2 a2\" style=\"display:none\">";
            $str .= "<input type=\"hidden\" id=\"hno\" name=\"hticketNo\" value=\"\"/>";
            $str .= "<ul class=\"ub\">";
            $str .= "<li class=\"lis\"><input type=\"submit\" value=\"保存\"></li>";
            $str .= "<li  hdelno=\"\" class=\"lis hdel\">删除</li>";
            $str .= "<li class=\"rid\"><span id=\"huname\"></span><span>-</span><span id=\"hcno\"></span></li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"{$next['ticketNo']}\" class=\"imgc2\" src=\"\static\image\huoche.png\" alt=\"\">";
//
            $str .= "<ul >";
            $str .= "<li class=\"nli\">发票代码&nbsp;&nbsp;:<input type=\"text\" id=\"htrainNum\" name=\"trainNum\" value=\"{$train['trainNum']}\"/></li>";
            $str .= "<li class=\"nli\">发票号码&nbsp;&nbsp;:<input type=\"text\" id=\"hstartLocation\" name=\"startLocation\" value=\"{$train['startLocation']}\"/></li>";
            $str .= "<li class=\"nli\">发票日期&nbsp;&nbsp;:<input type=\"text\" id=\"time\" name=\"time\" value=\"{$train['time']}\"/></li>";
            $str .= "<li class=\"nli\">发票时间&nbsp;&nbsp;:<input type=\"text\" id=\"htrainName\" name=\"trainName\" value=\"{$train['trainName']}\"/></li>";
            $str .= "<li class=\"nli\">发票金额&nbsp;&nbsp;:<input type=\"text\" id=\"hlevel\" name=\"level\" value=\"{$train['level']}\"/></li>";
            $str .= "<li class=\"nli\">起止地点&nbsp;&nbsp;:<input type=\"text\" id=\"hprice\" name=\"hprice\" value=\"{$next['price']}\"/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            $str .= "</form>";
            return $str;
        } else if (!empty($train)) {
            $str .= "<form method=\"post\" action=\"{:url('ticket/cSave')}\">";

            $str .= "<div class=\"u2 a1\" style=\"display:none\">";
            $str .= "<input type=\"hidden\" id=\"cno\" name=\"ticketNo\" value=\"\"/>";
            $str .= "<ul class=\"ub\">";
            $str .= "<li class=\"lis\"><input type=\"submit\" value=\"保存\"</li>";
            $str .= "<li  delno=\"\" class=\"lis del\">删除</li>";
            $str .= "<li class=\"rid\"><span id=\"cuname\"></span><span>-</span><span id=\"ccno\"></span></li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"\"  class=\"imgc\" src=\"\static\image\chuzuche.png\" alt=\"\">";


            $str .= "<ul>";
            $str .= "<li class=\"nli\">火车车次&nbsp;&nbsp;:<input type=\"text\" id=\"htrainNum\" name=\"trainNum\" value=\"\"/></li>";
            $str .= "<li class=\"nli\">起止地点&nbsp;&nbsp;:<input type=\"text\" id=\"hstartLocation\" name=\"startLocation\" value=\"\"/></li>";
            $str .= "<li class=\"nli\">车票日期&nbsp;&nbsp;:<input type=\"text\" id=\"time\" name=\"time\" value=\"\"/></li>";
            $str .= "<li class=\"nli\">乘客姓名&nbsp;&nbsp;:<input type=\"text\" id=\"htrainName\" name=\"trainName\" value=\"\"/></li>";
            $str .= "<li class=\"nli\">座位级别&nbsp;&nbsp;:<input type=\"text\" id=\"hlevel\" name=\"level\" value=\"\"/></li>";
            $str .= "<li class=\"nli\">车票金额&nbsp;&nbsp;:<input type=\"text\" id=\"hprice\" name=\"hprice\" value=\"\"/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            $str .= "</form>";
            //<!--火车-->


            $str .= "<div class=\"hc_u2 a2\" style=\"display:block\">";
            $str .= "<input type=\"hidden\" id=\"hno\" name=\"hticketNo\" value=\"\"/>";
            $str .= "<ul class=\"nu\">";
            $str .= "<li class=\"click_hand appno\" anoid=\"\">不合格</li>";
            $str .= "<li class=\"click_hand appund\" unoid=\"\">说明</li>";
            $str .= "</ul>";
            $str .= "<img ptno=\"{$next['ticketNo']}\" class=\"imgc2\" src=\"{$next['image']}\" alt=\"\">";
//
            $str .= "<ul >";
            $str .= "<li class=\"nli\">火车车次&nbsp;&nbsp;:<input type=\"text\" id=\"htrainNum\" name=\"trainNum\" value=\"{$train['trainNum']}\" readonly/></li>";
            $str .= "<li class=\"nli\">起止地点&nbsp;&nbsp;:<input type=\"text\" id=\"hstartLocation\" name=\"startLocation\" value=\"{$train['startLocation']}\" readonly/></li>";
            $str .= "<li class=\"nli\">车票日期&nbsp;&nbsp;:<input type=\"text\" id=\"time\" name=\"traintime\" value=\"{$train['traintime']}\" readonly/></li>";
            $str .= "<li class=\"nli\">乘客姓名&nbsp;&nbsp;:<input type=\"text\" id=\"htrainName\" name=\"trainUname\" value=\"{$train['trainUname']}\" readonly/></li>";
            $str .= "<li class=\"nli\">座位级别&nbsp;&nbsp;:<input type=\"text\" id=\"hlevel\" name=\"level\" value=\"{$train['level']}\" readonly/></li>";
            $str .= "<li class=\"nli\">车票金额&nbsp;&nbsp;:<input type=\"text\" id=\"hprice\" name=\"hprice\" value=\"{$next['price']}\" readonly/></li>";
            $str .= "</ul>";
            $str .= " <div class=\"next_page npage\" fno=\"{$next['ticketNo']}\">";
            $str .= "<b>下一页</b>";
            $str .= "</div>";
            $str .= "</div>";
            return $str;
        } else if (!empty($plane)){
            return 3;
        }else{
            return 4;
        }
    }


    //查看相关票据
    public function see(){
        $uid = Request::instance()->post('uid');
        $ano = Request::instance()->post('ano');
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
        ];
        $where['uid'] = $uid;
        $where['applyNo'] = $ano;
        $ticket = db('ticket')->alias('t')->join($join)->where($where)->paginate(10);
        $str = '';
        foreach($ticket as $v){
            if($v['type'] == 0){
                $v['ctype'] = '出租票';
            }else if($v['type'] == 1){
                $v['ctype'] = '火车票';
            }else{
                $v['ctype'] = '飞机票';
            }
            if($v['status'] == 0){
                $v['status'] = '正常';
            }else{
                $v['status'] = '违规';
            }
            $str.="<li class=\"click_hand ty{$v['type']} id\" ap_no=\"{$v['ticketNo']}\">{$v['ticketNo']}</li>";
            $str.="<li>{$v['ctype']}</li>";
            $str.="<li>{$v['price']}</li>";
            $str.="<li>{$v['updateTime']}</li>";
            $str.="<li>{$v['status']}</li>";

        }
        echo $str;

    }

    //切换详细数据样式 出租车
    public function cstyle(){
        $no = Request::instance()->post('no');

        $ticket = db('ticket')->where('ticketNo',$no)->find();
        $user = db('user')->where('id',$ticket['uid'])->find();
        $type = db('ticket_taxi')->where('pid',$ticket['id'])->find();
        $type['ticketNo'] = $ticket['ticketNo'];
        $type['price'] = $ticket['price'];
        $type['uname'] = $user['name'];
        echo json_encode($type);die;

    }







    //不合格
    public function nohg(){
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo',$no)->update(['status' => 1]);
        if($ticket){
            echo 1;
        }else{
            echo 2;
        }
    }

    //加载说明
    public function tsave(){
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo',$no)->find();
        exit(json_encode($ticket));
    }

    //保存说明
    public function savetext(){
        $text = input('post.text');
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo',$no)->update(['explain' => $text]);
        if($ticket){
            echo 1;
        }else{
            echo 2;
        }
    }




    //通过
    public function apass(){
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo',$no)->update(['applyPass' => 1]);
        if($ticket){
            return 1;
        }else{
            return 2;
        }

    }

    //未通过
    public function npass(){
        $no = input('post.no');
        $ticket = db('ticket')->where('ticketNo',$no)->update(['applyPass' => 2]);
        if($ticket){
            return 1;
        }else{
            return 2;
        }

    }

    //d2 通过
    public function dapass(){
        $no = input('post.no');
        $ticket = db('ticket')->where('applyNo',$no)->update(['applyPass' => 1]);
        if($ticket){
            return 1;
        }else{
            return 2;
        }

    }

    //d2未通过
    public function dnpass(){
        $no = input('post.no');
        $ticket = db('ticket')->where('applyNo',$no)->update(['applyPass' => 2]);
        if($ticket){
            return 1;
        }else{
            return 2;
        }

    }

    //d2说明
    public function dsave(){
        $no = input('post.no');
        $ticket = db('ticket')->where('applyNo',$no)->find();
        exit(json_encode($ticket));
    }

    //d2保存说明
    public function dsavetext(){
        $text = input('post.text');
        $no = input('post.no');
        $ticket = db('ticket')->where('applyNo',$no)->update(['apExplain' => $text]);
        if($ticket){
            echo 1;
        }else{
            echo 2;
        }
    }



}
