<?php
// | 智能财务云平台i-FFSC  安卓端
// +----------------------------------------------------------------------
// | 安卓app接口  票据管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\android\controller;

use think\Controller;
use think\Db;
use think\File;
use app\extend\common\common;

Class ticket
{


    //票据首页
    public function index(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        //票据
        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
            ['ticket_train b','t.id = b.pid','left'],
            ['ticket_plane c','t.id = c.pid','left'],
            ['ticket_e d','t.id = c.pid','left'],
            ['ticket_vat e','t.id = c.pid','left'],
            ['ticket_other f','t.id = c.pid','left'],
        ];

       $ticket = db('ticket')->alias('t')->join($join)->group('ticketNo')->where('tappId',0)->select();
       if($ticket){
           return json(['data' => $ticket,'code'=>1,'message'=>'成功']);
       }else{
           return json(['data' => 'error','code'=>0,'message'=>'失败']);
       }

    }

    //拉去某类票据列表
    public function onlyTicket(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $uid = input('post.uid');
        $type = input('post.type');
        $type = explode(',',$type);
        $count = count($type);
        $ticket = array();
        for($i=0;$i<$count;$i++){
            $ticket[] = db('ticket')->where('type',$type[$i])->where(['uid'=>$uid,'tappId'=>0])->select();
        }
//        foreach($ticket as $v){
//            dump($v);
//        }
//        dump($arr);die;
//        $ticket = db('ticket')->where('type',['>=',$type[0]],['<=',$type[$end]])->where(['uid'=>$uid,'tappId'=>0])->select();

        if($ticket){
            return json(['data' => $ticket,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data' => 'error','code'=>0,'message'=>'失败']);
        }
    }

    //批量删除
    public function tAllDel(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
//        $id = input('post');
        $id = array('48','49');
        $success = 0;
        $err = 0;
        $id_num = count($id);
        for($i=0;$i<$id_num;$i++){
            $ticket = db('ticket')->where('id',$id[$i])->find();
            //            判断类型
            switch ($ticket['type'])
            {
                case 0:
                    $delChild = db('ticket_taxi')->where('pid',$ticket['id'])->delete();
                    break;
                case 1:
                    $delChild = db('ticket_train')->where('pid',$ticket['id'])->delete();
                    break;
                case 2:
                    $delChild = db('ticket_plane')->where('pid',$ticket['id'])->delete();
                    break;
                case 3:
                    $delChild = db('ticket_vat')->where('pid',$ticket['id'])->delete();
                    continue;
                case 4:
                    $delChild = db('ticket_e')->where('pid',$ticket['id'])->delete();
                    continue;
                default:
                    $delChild = db('ticket_other')->where('pid',$ticket['id'])->delete();

            }


            if($delChild){
                $success += 1;
                continue;
            }else{
                $err += 1;
            }

//

        }


        if($id_num==$success){
            $delChild = db('ticket')->where('id','in',$id)->delete();
            if($delChild){
                return json(['data' => 'success','code'=>1,'message'=>"成功"]);
            }else{
                return json(['data' => 'error','code'=>0,'message'=>"失败"]);
            }
        }else{
            return json(['data' => 'error','code'=>2,'message'=>"成功 $success 个,失败 $err 个"]);
        }




    }


    //出租车票查询
    public function selectTaxi(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $join = [
            ['ticket_taxi a','t.id = a.pid','left'],
        ];

        $uid = input('post.uid');
        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>1,'tappId'=>0])->select();
        $i = 1;
        foreach($ticket as $k => $v) {
            $a = $i++;
            //临时字段 假排序
           $order =  common::fOrder($a);
            $ticket[$k]['order'] = $order;

            $ticket[$k]['taxiTname'] = '0';
            $ticket[$k]['taxiexplains'] = '0';

        }

        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }


    //保存出租车票
    public function saveTaxi(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $tid = input('post.tid');
        $ticketCode = input('post.ticketCode');
        $ticketNumber = input('post.ticketNumber');
        $ticketTime = input('post.ticketTime');
        $offTime = input('post.offTime');
        $price = input('post.price');

        //判断数据是否改变
        $staxi =  db('ticket_taxi')->where('pid',$tid)->find();
        $cticket = db('ticket')->where('id',$tid)->find();
        if($staxi['ticketCode'] != $ticketCode || ($staxi['ticketNumber'] != $ticketNumber) || ($staxi['ticketTime'] != $ticketTime) || ($staxi['offTime'] != $offTime) || ($cticket['price'] != $price)){
            $recogn = 2;
        }else{
            $recogn = $cticket['recogn'];
        }

        $taxi = db('ticket_taxi')
            ->where('pid',$tid)
            ->update([
                'ticketCode' => $ticketCode,
                'ticketNumber' => $ticketNumber,
                'ticketTime' => $ticketTime,
                'offTime' => $offTime,
                'cupdateTime' => time(),
            ]);
        if($taxi) {
            $ticket = db('ticket')->where('id',$tid)->update(['price'=>$price,'recogn'=>$recogn,'updateTime'=>time()]);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //删除出租车票
    public function delTaxi(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');
        $no = explode(',',$n);

        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_taxi')->where('pid',$no[$i])->find();
            $arr[] = $taxi['id'];//获取id
        }

        $del = db('ticket_taxi')->delete($arr);

        if($del){
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '删除失败']);
            }
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }


    //火车票查询
    public function selectTrain(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $join = [
            ['ticket_train a','t.id = a.pid','left'],
        ];

        $uid = input('post.uid');
        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>2,'tappId'=>0])->select();
        $i=1;
        foreach($ticket as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $ticketNo = 'h' . sprintf("%04d", $a);
            $ticket[$k]['order'] = $ticketNo;

            $t = db('ticket')->where('id',$v['tid'])->find();

            $ticket[$k]['ticketCode'] = $v['trainNum'];
            $ticket[$k]['ticketNumber'] = $v['startLocation'];
            $ticket[$k]['ticketTime'] = $v['endLocation'];
            $ticket[$k]['offTime'] = $v['traintime'];
            $ticket[$k]['price'] = $t['price'];
            $ticket[$k]['taxiTname'] = $v['level'];
            $ticket[$k]['taxiexplains'] = $v['trainUname'];

        }

        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //保存火车票
    public function saveTrain(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $tid = input('post.tid');
        $trainNum = input('post.ticketCode');
        $startLocation = input('post.ticketNumber');
        $endLocation = input('post.ticketTime');
        $traintime = input('post.offTime');
        $level = input('post.taxiTname');
        $trainUname = input('post.taxiexplains');
        $price = input('post.price');
//        $post = array('id'=>17,'trainNum'=>'2','startLocation'=>'2','endLocation'=>'2','traintime'=>'2','trainsum'=>'2','level'=>'2','trainUname'=>'2');
        //判断数据是否改变
        $strain =  db('ticket_train')->where('pid',$tid)->find();
        $cticket = db('ticket')->where('id',$tid)->find();
        if($strain['trainNum'] != $trainNum || ($strain['startLocation'] != $startLocation) || ($strain['endLocation'] != $endLocation) || ($strain['traintime'] != $traintime) || ($strain['level'] != $level) || ($strain['trainUname'] != $trainUname) || ($cticket['price'] != $price)){
             $recogn = 2;
        }else{
            $recogn = $cticket['recogn'];
        }
        $train = db('ticket_train')
            ->where('pid',$tid)
            ->update([
                'trainNum' => $trainNum,
                'trainUname' => $trainUname,
                'startLocation' => $startLocation,
                'endLocation' => $endLocation,
                'traintime' => $traintime,
                'level' => $level,
                'hupdateTime' => time(),
            ]);
        if($train) {
            $ticket = db('ticket')->where('id',$tid)->update(['price'=>$price,'recogn'=>$recogn,'updateTime'=>time()]);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //删除火车票
    public function delTrain(){
//        return json(['data' => '火车', 'code' => 0, 'message' => '成功']);
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');

        $no = explode(',',$n);

        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_train')->where('pid',$no[$i])->find();
            $arr[] = $taxi['id'];//获取id
        }

        $del = db('ticket_train')->delete($arr);

        if($del){
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '删除失败']);
            }
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }


    //飞机票查询
    public function selectPlane(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $uid = input('post.uid');

        $join = [
            ['ticket_plane a','t.id = a.pid','left'],
        ];

        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>3,'tappId'=>0])->group('pid')->select();
        $i = 1;
        foreach($ticket as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $ticketNo = 'f' . sprintf("%04d", $a);
            $ticket[$k]['order'] = $ticketNo;

        }
        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //飞机票详情查询
    public function allPlane(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $tid = input('post.tid');
        $ticket = db('ticket')->where('id',$tid)->find();
        $plane = db('ticket_plane')->where('pid',$tid)->select();
        $arr = array();
        foreach($plane as $k => $v){
            $v['price'] = $ticket['price'];
            $v['image'] = $ticket['image'];
            $arr[]= $v;
        }
        if($plane) {

            return json(['data' => $arr, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $arr, 'code' => 0, 'message' => '失败']);
        }
    }

    //飞机票保存
    public function savePlane(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }


        $tid = input('post.tid');
        $planeNumber = input('post.ticketCode');
        $planeSlocation = input('post.ticketNumber');
        $planeElocation = input('post.ticketTime');
        $planedate = input('post.offTime');
        $planelevel = input('post.taxiTname');
        $planeUname = input('post.taxiexplains');
        $price = input('post.price');
//        $post = array('id'=>17,'trainNum'=>'2','startLocation'=>'2','endLocation'=>'2','traintime'=>'2','trainsum'=>'2','level'=>'2','trainUname'=>'2');
        //判断数据是否改变
        $sPlane =  db('ticket_plane')->where('pid',$tid)->find();
        $cticket = db('ticket')->where('id',$tid)->find();
        if($sPlane['planeNumber'] != $planeNumber || ($sPlane['planeSlocation'] != $planeSlocation) || ($sPlane['planeElocation'] != $planeElocation) || ($sPlane['planedate'] != $planedate)|| ($sPlane['planelevel'] != $planelevel)|| ($sPlane['planeUname'] != $planeUname) || ($cticket['price'] != $price)){
            $recogn = 2;
        }else{
            $recogn = $sPlane['recogn'];
        }

        $plane = db('ticket_plane')
            ->where('pid',$tid)
            ->update([
                'planeNumber' => $planeNumber,
                'planeSlocation' => $planeSlocation,
                'planeElocation' => $planeElocation,
                'planedate' => $planedate,
                'planelevel' => $planelevel,
                'planeUname' => $planeUname,
            ]);
        if($plane) {
            $ticket = db('ticket')->where('id',$tid)->update(['recogn'=>$recogn,'price'=>$price]);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //飞机票删除
    public function delPlane(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');
        $no = explode(',',$n);
        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_plane')->where('pid',$no[$i])->select();
            foreach($taxi as $v){
                $arr[] = $v['id'];//获取id
            }
        }
        $del = db('ticket_plane')->delete($arr);
//        echo DB::getLastSql();
        if($del){
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '删除失败']);
            }
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //增值票查询
    public function selectVat(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $join = [
            ['ticket_vat a','t.id = a.pid','left'],
        ];

        $uid = input('post.uid');
        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>4,'tappId'=>0])->select();
        $i =1;

        foreach($ticket as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $ticketNo = 'z' . sprintf("%04d", $a);
            $ticket[$k]['order'] = $ticketNo;

            $t = db('ticket')->where('id',$v['tid'])->find();
            $ticket[$k]['ticketCode'] = $v['vatCode'];
            $ticket[$k]['ticketNumber'] = $v['vatNumber'];
            $ticket[$k]['ticketTime'] = $v['vatTime'];
            $ticket[$k]['offTime'] = $v['vbuyNo'];
            $ticket[$k]['price'] = $t['price'];
            $ticket[$k]['taxiTname'] = $v['vsellNo'];
            $ticket[$k]['taxiexplains'] = '0';
        }

        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //增值票保存
    public function saveVat(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }


        $tid = input('post.tid');
        $vatCode = input('post.ticketCode');
        $vatNumber = input('post.ticketNumber');
        $vatTime = input('post.ticketTime');
        $vbuyNo = input('post.offTime');
        $vsellNo = input('post.taxiTname');
        $price = input('post.price');
//        return json(['data' => $price, 'code' => 1, 'message' => '成功']);
//        $post = array('id'=>63,'vatCode'=>'2','vatNumber'=>'2','vatTime'=>'2','price'=>'2');
        $vat = db('ticket_vat')
            ->where('pid',$tid)
            ->update([
                'vatCode' => $vatCode,
                'vatNumber' => $vatNumber,
                'vatTime' => $vatTime,
                'vbuyNo' => $vbuyNo,
                'vsellNo' => $vsellNo,
            ]);
        if($vat) {
            $ticket = db('ticket')->where('id',$tid)->update(['price'=>$price]);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //增值票删除
    public function deleteVat(){

        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');
        $no = explode(',',$n);

        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_vat')->where('pid',$no[$i])->find();
            $arr[] = $taxi['id'];//获取id
        }

        $vat = db('ticket_vat')->delete($arr);
        if($vat) {
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //电子票查询
    public function selectE(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $join = [
            ['ticket_e a','t.id = a.pid','left'],
        ];

        $uid = input('post.uid');
        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>5,'tappId'=>0])->select();
        $i =1;
        foreach($ticket as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $ticketNo = 'd' . sprintf("%04d", $a);
            $ticket[$k]['order'] = $ticketNo;

            $t = db('ticket')->where('id',$v['tid'])->find();
            $ticket[$k]['ticketCode'] = $v['eCode'];
            $ticket[$k]['ticketNumber'] = $v['eNumber'];
            $ticket[$k]['ticketTime'] = $v['eTime'];
            $ticket[$k]['offTime'] = $v['ebuyNo'];
            $ticket[$k]['price'] = $t['price'];
            $ticket[$k]['taxiTname'] = $v['esellNo'];
            $ticket[$k]['taxiexplains'] = '0';
        }

        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //电子票保存
    public function saveE(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }



        $tid = input('post.tid');
        $eCode = input('post.ticketCode');
        $eNumber = input('post.ticketNumber');
        $eTime = input('post.ticketTime');
        $ebuyNo = input('post.offTime');
        $esellNo = input('post.taxiTname');
        $price = input('post.price');
//        $post = array('id'=>62,'eCode'=>'2','eNumber'=>'3','eTime'=>'3','price'=>'2','ebuyNo'=>'2','esellNo'=>'2');
        $vat = db('ticket_e')
            ->where('pid',$tid)
            ->update([
                'eCode' => $eCode,
                'eNumber' => $eNumber,
                'eTime' => $eTime,
                'ebuyNo' => $ebuyNo,
                'esellNo' => $esellNo,
            ]);
        if($vat) {
            $ticket = db('ticket')->where('id',$tid)->update(['price'=>$price]);
//            $ticket = db('ticket')->where('id',$tid)->select();
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //电子票删除
    public function deleteE(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');
        $no = explode(',',$n);

        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_e')->where('pid',$no[$i])->find();
            $arr[] = $taxi['id'];//获取id
        }
        $del = db('ticket_e')->delete($arr);
        if($del) {
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '删除失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //其他票查询
    public function selectOther(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $join = [
            ['ticket_other a','t.id = a.pid','left'],
        ];

        $uid = input('post.uid');
        $ticket = db('ticket')->alias('t')->join($join)->where(['uid'=>$uid,'type'=>6,'tappId'=>0])->select();
        $t = db('ticket')->where('uid',$uid)->find();
        $i=1;
        foreach($ticket as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $ticketNo = 'o' . sprintf("%04d", $a);
            $ticket[$k]['order'] = $ticketNo;

            $t = db('ticket')->where('id',$v['tid'])->find();
            $ticket[$k]['ticketCode'] = $v['oCode'];
            $ticket[$k]['ticketNumber'] = $v['oNumber'];
            $ticket[$k]['ticketTime'] = $v['oTime'];
            $ticket[$k]['offTime'] = $v['obuyNo'];
            $ticket[$k]['price'] = $t['price'];
            $ticket[$k]['taxiTname'] = $v['osellNo'];
            $ticket[$k]['taxiexplains'] = '0';
        }
        if($ticket) {
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //其他票保存
    public function saveOther(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }


        $tid = input('post.tid');
        $oCode = input('post.ticketCode');
        $oNumber = input('post.ticketNumber');
        $oTime = input('post.ticketTime');
        $obuyNo = input('post.offTime');
        $osellNo = input('post.taxiTname');
        $price = input('post.price');
        $other = db('ticket_other')
            ->where('pid',$tid)
            ->update([
                'oCode' => $oCode,
                'oNumber' => $oNumber,
                'oTime' => $oTime,
                'obuyNo' => $obuyNo,
                'osellNo' => $osellNo,
            ]);
        if($other) {
            $ticket = db('ticket')->where('id',$tid)->update(['price'=>$price]);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '保存失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //其他票删除
    public function deleteOther(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $n = input('post.tid');
        $no = explode(',',$n);
        $arr = array();
        for($i=0;$i<count($no);$i++){
            $taxi = db('ticket_other')->where('pid',$no[$i])->find();
            $arr[] = $taxi['id'];//获取id
        }
        $del = db('ticket_other')->delete($arr);
        if($del) {
            $ticket = db('ticket')->delete($no);
            if($ticket){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '删除失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //查询附件
    public function selectAcc(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.tid');
        $acc = db('accessory')->where('tid',$id)->select();
        if($acc){
            return json(['data' => $acc, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $acc, 'code' => 0, 'message' => '失败']);
        }
    }

    //添加附件
    public function saveAcc(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.tid');
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'acc');
        $image = 'http://120.27.49.216:8088/uploads/acc/'.$info->getSaveName();
        if($info){
            $acc = db('accessory')->insert(['tid'=>$id,'accImage'=>$image]);
            if($acc){
                return json(['data' => 'success', 'code' => 1, 'message' => '添加成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '添加失败']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //添加其他附件
    public function saveOtherAcc(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.tid');
        $url = input('post.url');
        $acc = db('accessory')->insert(['tid'=>$id,'accImage'=>$url]);
        if($acc){
            return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '添加失败']);
        }
    }

    //删除附件
    public  function deleteAcc(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.id');
        $id = explode(',',$id);

        $del = db('accessory')->delete($id);
        if($del){
            return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //说明查询
    public function accExplain(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.tid');
        $ticket = db('ticket')->where('id',$id)->find();
        if($ticket){
            return json(['data' => $ticket, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $ticket, 'code' => 0, 'message' => '失败']);
        }
    }

    //说明保存
    public function saveExplain(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id = input('post.tid');
        $text = input('post.explains');
        $ticket = db('ticket')->where('id',$id)->update(['accExplain'=>$text]);
        if($ticket){
            return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //token
    public function personInfo(){
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $uid = input('post.uid');
        $user = db('user')->where('id',$uid)->find();
        $department = db('department')->where('id', $user['company'])->find();
        $class = db('department')->where('id', $user['class'])->find();
        $user['department'] = 1;
        $user['aclass'] = $class['name'];
        unset($user['class']);
        $user['department'] = $department['name'];
        if($user){
            return json(['data' => $user, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $user, 'code' => 0, 'message' => '失败']);
        }
    }



}