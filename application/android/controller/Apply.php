<?php
// | 智能财务云平台i-FFSC  安卓端
// +----------------------------------------------------------------------
// | 安卓首页
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\android\controller;

use think\Controller;
use think\Db;
use think\Cache;
use app\extend\common\common;

Class apply
{


    //首页报销接口
    public function index()
    {
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $num = input('post.num');
        $user = db('user')->where('num',$num)->find();
        $item = db('item')->where('uid',$user['id'])->select();
        $arrId = array();
        $arr = array();
        //有课题号的
        if(!empty($item)){

            //判断是否提交的本人课题号
            foreach($item as $vo){
                $arrId[] = $vo['id'];
                if ($user['rid'] == 4) {
                    $apply = db('t_bx_d_bxd')->where('DFKT', 'in', $arrId)->whereOr('CJR', $num)->select();
                    foreach($apply as $k => $v){
                        //不查询拒签
                        $flow = db('apply_flow')->where('appid',$v['id'])->find();
//                            //不查询本人课题号
                        $v['CJR'] = $vo['id'];
                        if($v['CJR'] == $v['DFKT'] || !empty($flow)){
                            unset($k);
                            $arr[] = $v;
                        }

                    }

                }else if ($user['rid'] == 3){
                    $arr = db('t_bx_d_bxd')->where('DFKT', 'in', $arrId)->whereOr('CJR', $num)->select();

//                    foreach($apply as $k => $v){
//                        //不查询拒签
//                        // if(!empty($v['signBecause'])){
//                        //        unset($k);
//                        //    }
//                        //不查询本人课题号
//                        $v['CJR'] = $vo['id'];
//                        if($v['CJR'] == $v['DFKT']){
//                            unset($k);
//                            $arr[] = $v;
//                        }
//
//                    }
//
//                    //查询课题组所有成员
//                    $users = db('user')->where('class',$user['class'])->select();
//                    $lastArr= array();
//                    foreach($users as $vu){
//                        $items = db('item')->where('uid',$vu['id'])->select();
//                        foreach($items as $key => $vi){
//                            $vi['uname'] = $vu['name'];
//                            $vi['rid'] = $vu['rid'];
//                            $vi['num'] = $vu['num'];
//                            $lastArr[] = $vi;
//                        }
//                    }
//                    foreach($lastArr as $vl){
//                        if($vl['rid'] ==4){
//
//                            $apply2 = db('t_bx_d_bxd')->where('DFKT', $vl['id'])->where('CJR',$vl['num'])->select();
//                        }
//                    }
//                    if(!empty($apply2)){
//                        foreach($arr as $k=>$r){
//
//                            $arr[] = array_merge($r,$apply2[$k]);
//
//                        }
//                    }
                }else if ($user['rid'] == 2){
                    $users = db('user')->where(['department'=>$user['department'],'rid'=>3])->select();
                    $numArr = array();
                    foreach($users as $v){
                        $numArr[] = $v['num'];
                    }
                    $arr = db('t_bx_d_bxd')->where('DFKT', 'in', $arrId)->where('CJR', 'in',$numArr)->select();
                }

            }

        }else{
            $arr = db('t_bx_d_bxd')->where('CJR',$num)->select();
        }
        //财务查看报销单
        if($user['rid'] == 6){
            $arr = db('t_bx_d_bxd')->where('voucherType','<>','')->select();
        }

        $i = 1;
        foreach($arr as $k => $v){
            //临时字段 假排序
            $a = $i++;
            $applyNo = 'b'.sprintf("%04d", $a);
            $arr[$k]['order'] = $applyNo;

            $item = db('item')->where('id',$v['DFKT'])->find();
            $arr[$k]['kt_uid'] = $item['uid'];

            $appflow = db('apply_flow')->where('appid',$v['id'])->find();
            if($appflow['threeSign'] == 1){
                $arr[$k]['flow'] = '已拒签';
            }else if($appflow['twoSign'] == 1){
                $arr[$k]['flow'] = '部门负责人拒签';
            }else if($appflow['oneSign'] == 1){
                $arr[$k]['flow'] = '课题负责人拒签';
            }else if($appflow['sign'] == 1){
                $arr[$k]['flow'] = '待签字';
            }else if($appflow['threeApprove'] == 1){
                $arr[$k]['flow'] = '已付款';
            }else if($appflow['twoApprove'] == 1) {
                $arr[$k]['flow'] = '待付款';
            }else if($appflow['oneApprove'] == 1) {
                $arr[$k]['flow'] = '待审批';
            }else{
                $arr[$k]['flow'] = '待提交';
            }

        }
        if($arr){
            // 指定json数据输出
            return json(['data'=>$arr,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>$arr,'code'=>0,'message'=>'失败']);
        }



    }



    //按名字查询
    public function sName()
    {
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $uid = input('post.uid');
        $data = db('apply')->where('uid',$uid)->select();
        if($data){
            // 指定json数据输出
            return json(['data'=>$data,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'成功']);
        }


    }

    //列表接口
    public function getTree(){

        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $cid = input('post.company');
        $company = db('department')->select();
        $list = common::getTree($company,$cid);//调用公共方法
        return json(['data'=>$list,'code'=>1,'message'=>'成功']);
    }

    //删除根据票据单号
    public function delApp()
    {
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $applyId = input('post');
        $applyId = explode(',',$applyId);
        $data = db('t_bx_d_bxd')->delete($applyId);
        if($data) {
            return json(['data' => $data, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $data, 'code' => 0, 'message' => '失败']);
        }
    }


    //生成报销单
    public function makeTaxiApply(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $num = input('post.num');
        $id = input('post.id');
        $uid = input('post.uid');
        $tid = explode(',',$id);
        //交通费
        $traffic = db('t_bx_d_jtfbxdj')->order('id desc')->limit(1)->find();
        $newid = $traffic['id']+1;
        //报销单
        $bxd = db('t_bx_d_bxd')->order('id desc')->limit(1)->find();

        $array = array();
        for($i=0;$i<count($tid);$i++){
            //判断是否已经生成报销单
            $ticketPid = db('ticket')->where('id',$tid[$i])->find();
            if($ticketPid['tappId'] != 0){
                $tt = 1;
            }else{
                $tt = 0;
            }

            // 单张金额超过200
            $limitPrice = cache::get('limitPrice');
            if($ticketPid['price'] > $limitPrice){
                return json(['data'=>'error','code'=>2,'message'=>'单张票据金额不能超过'.$limitPrice.'元']);
            }

        }

        //连号
//        $ticket = db('ticket')->where(['uid'=>$uid,'type'=>1])->where('tappId','<>','0')->select();
//        $ticketArr = array();
        foreach($tid as $v){
            $taxi = db('ticket_taxi')->where('pid',$v)->find();
            $ticketArr[] = $taxi['ticketNumber'];
        }
        $repeat_arr = array();
        $len = count ( $ticketArr );
        for($i = 0; $i < $len; $i ++) {
            for($j = $i + 1; $j < $len; $j ++) {
                if ($ticketArr [$i]+1  == $ticketArr [$j] || $ticketArr [$i]-1  == $ticketArr [$j]) {

                    return json(['data'=>'error','code'=>4,'message'=>'不能有连号']);
                }
            }
        }

        //判断是否有拒识状态
        $recogn = db('ticket')->where('id','in',$tid)->select();
        $recognArr = array();
        foreach($recogn as $v){
            $recognArr[] = $v['recogn'];
        }
        if(in_array('0',$recognArr)){
            return json(['data'=>'error','code'=>4,'message'=>'不能有据识票据']);
        }

        $array1 = array();
        $array2 = array();
        for($i=0;$i<count($tid);$i++) {
            if ($tt == 1) {
                return json(['data' => 'error', 'code' => 0, 'message' => '不能重复添加']);
            } else {
                $tprice = db('ticket')->where('id', $tid[$i])->find(); //票据金额
                //判断是否有人工识别两种状态
                if(in_array('1',$recognArr) && (in_array('2',$recognArr))){
                    if($tprice['recogn'] == 1){
                        $array1[] = $tprice['price'];
                    }else{
                        $array2[] = $tprice['price'];
                    }
                }else{
                    $array[] = $tprice['price'];

                }

            }
        }
        //计算所有票据总额
        $appPrice = array_sum($array);
        if(empty($array1)) {
            $appPrice1 = array_sum($array1);
            $appPrice2 = array_sum($array2);
            $count1 = count($array1);
            $count2 = count($array2);
        }


        //添加交通表
        $sub = substr($bxd['BXDBH'],1,4);
        $end = ltrim ($sub, '0');



            //判断是否有人工识别两种状态
            if(in_array('1',$recognArr) && (in_array('2',$recognArr))) {
                $user = db('user')->where('num', $num)->find();
                $end = $end + 1;
                $end2 = $end + 1;

                $applyNo = 'b' . sprintf("%04d", $end);
                $applyNo2 = 'b' . sprintf("%04d", $end2);


                $time = date('Y-m-d H:i:s', time());
                $data = ['BXDBH' => $applyNo, 'ROLES_ID' => $user['id'], 'CWHDJE' => $appPrice1, 'DJZS' => $count1, 'CJR' => $num, 'CJRQ' => $time, 'BXKM' => '差旅费', 'type' => 0,'bxdType' =>0,'uid'=>$uid];
                $data2 = ['BXDBH' => $applyNo2, 'ROLES_ID' => $user['id'], 'CWHDJE' => $appPrice2, 'DJZS' => $count2, 'CJR' => $num, 'CJRQ' => $time, 'BXKM' => '差旅费', 'type' => 1,'bxdType' =>0,'uid'=>$uid];
                $bxd = db('t_bx_d_bxd')->insert($data);
                $bxd2 = db('t_bx_d_bxd')->insert($data2);
                if ($bxd && $bxd2) {
                    //添加报销单审批表
                    $bxdid = db('t_bx_d_bxd')->limit('1')->order('id desc')->find();
                    $apply_flow = db('apply_flow')->insert(['appid' => $bxdid['id']-1]);
                    $apply_flow2 = db('apply_flow')->insert(['appid' => $bxdid['id']]);
                    if($apply_flow && $apply_flow2) {
                        //添加交通费
                        $data = ['DJBH' => 1, 'BXDBH' => $applyNo, 'CJRQ' => $time, 'CJR' => $num, 'bxdId' => $bxdid['id']-1];
                        $data2 = ['DJBH' => 1, 'BXDBH' => $applyNo2, 'CJRQ' => $time, 'CJR' => $num, 'bxdId' => $bxdid['id']];
                        $apply = db('t_bx_d_jtfbxdj')->insert($data);
                        $apply2 = db('t_bx_d_jtfbxdj')->insert($data2);
                        if($apply && $apply2){
                            $jt = db('t_bx_d_jtfbxdj')->order('id desc')->find();
                            for($i=0;$i<count($tid);$i++) {
                                $tkt = db('ticket')->where('id',$tid[$i])->find();
                                if($tkt['recogn'] == 1){
                                    $ticket = db('ticket')->where('id', $tid[$i])->update(['tappId' => $jt['id']-1]);
                                }else{
                                    $ticket2 = db('ticket')->where('id', $tid[$i])->update(['tappId' => $jt['id']]);
                                }

                            }

                            if($ticket && $ticket2){
                                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                            }else{
                                return json(['data'=>'error','code'=>0,'message'=>'失败end2']);
                            }

                        }else{
                            return json(['data'=>'error','code'=>0,'message'=>'失败end']);
                        }

                    }else {
                        return json(['data'=>'error','code'=>0,'message'=>'失败2']);
                    }
                    return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                } else {
                    return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
                }




            }else{
                //添加报销审批表

                $user = db('user')->where('num',$num)->find();
                $end = $end+1;

                $applyNo = 'b'.sprintf("%04d", $end);
                $applyid = db('t_bx_d_bxd')->order('id desc')->find();
                if(empty($applyid)){
                    $applyid['id'] =1;
                }
                    $time = date('Y-m-d H:i:s',time());
                    $data = ['BXDBH' => $applyNo,'ROLES_ID' => $user['id'],'CWHDJE' => $appPrice,'DJZS'=>count($tid),'CJR'=>$num,'CJRQ'=>$time,'BXKM'=>'差旅费','type'=>0,'bxdType' =>0,'uid'=>$uid];
                    $bxd = db('t_bx_d_bxd')->insert($data);

                    if($bxd){
                        $bxd = db('t_bx_d_bxd')->order('id desc')->find();
                        $apply_flow = db('apply_flow')->insert(['appid' => $bxd['id']]);
                        if($apply_flow) {
                            $data = ['DJBH'=>1,'BXDBH' => $applyNo,'CJRQ' => $time,'CJR'=>$num,'bxdId'=>$bxd['id']];
                            $apply = db('t_bx_d_jtfbxdj')->insert($data);
                            if($apply){
                                $jt = db('t_bx_d_jtfbxdj')->order('id desc')->find();
                                for($i=0;$i<count($tid);$i++) {
                                    $ticket = db('ticket')->where('id', $tid[$i])->update(['tappId' => $jt['id']]);
                                }

                                if($ticket){
                                    return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                                }else{
                                    return json(['data'=>'error','code'=>0,'message'=>'失败end2']);
                                }
                            }else{
                                return json(['data'=>'error','code'=>0,'message'=>'失败3']);
                            }

                        }else {
                            return json(['data'=>'error','code'=>0,'message'=>'失败2']);
                        }
                    }else{
                        return json(['data'=>'error','code'=>0,'message'=>'失败']);
                    }

            }


    }

    //添加报销单后  返回数据
    public function tAppData(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $num = input('post.num');
        $id = input('post.id');
        $type = input('post.bxdType');
        $user = db('user')->where('num',$num)->find();

        //临时字段
        $department = db('department')->where('id',$user['department'])->find();
        //判断差旅还是交通
        if($type == 0){
            $apply = db('t_bx_d_bxd')->where('id',$id)->find();
        }else if($type == 1){
            $apply = db('t_bx_d_bxd')->alias('b')->join('t_bx_d_clfbxdj c','b.id = c.bxdId','left')->where('b.id',$id)->find();
        }

        $item = db('item')->where('id',$apply['DFKT'])->find();
        $apply['department'] = $department['name'];
        $apply['name'] = $user['name'];
        $apply['email'] = $user['email'];
        $apply['bankcardNo'] = $user['bankcardNo'];
        $apply['item'] = $item;
        $apply['itemNo'] = $item['itemNo'];
        if($apply){
            return json(['data'=>$apply,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>$apply,'code'=>0,'message'=>'失败']);
        }
    }

    //保存报销单
    public function saveApply(){
        //token验证
        $token = input('post');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $appReason = input('post.SY');
        $aitemNo = input('post.DFKT');
        $subject = input('post.BXKM');
        $remark = input('post.BZ');
        $settlement = input('post.JSFS');
        $openBank = input('post.SKDWKHH');
        $bankName = input('post.FKRNAME');
        $bankNo = input('post.SKDWZH');
        $bankprice = input('post.CWHDJE');
        $companyNo = input('post.SKDWQC');
        $id = input('post.id');
        //报销人签字
        $appPerson = request()->file('appPerson');
        if(!empty($appPerson)) {
            $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
            $image = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
        }else{
            $image = '';
        }
        $data = ['SY' => $appReason,'DFKT' => $aitemNo,'BXKM' => $subject,'BZ' => $remark,'JSFS' => $settlement,'SKDWKHH' => $openBank,'FKRNAME' => $bankName,'SKDWZH' => $bankNo,'CWHDJE' => $bankprice,'SKDWQC' => $companyNo,'appPerson'=>$image];
//
        $apply = db('t_bx_d_bxd')->where('id',$id)->update($data);
        if($apply){
            return json(['data'=>'success','code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败']);
        }


    }

    //提交报销单
    public function submitAp(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $appReason = input('post.SY');
        $aitemNo = input('post.DFKT');
        $subject = input('post.BXKM');
        $remark = input('post.BZ');
        $settlement = input('post.JSFS');
        $openBank = input('post.SKDWKHH');
        $bankName = input('post.FKRNAME');
        $bankNo = input('post.SKDWZH');
        $bankprice = input('post.CWHDJE');
        $companyNo = input('post.SKDWQC');
        $id = input('post.id');
        $num = input('post.num');
        $time = date('Y-m-d H:i:s',time());

        $item = db('item')->where('id',$aitemNo)->find();
        //报销人签字
        $appPerson = request()->file('appPerson');
        if(!empty($appPerson)) {
            $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
            $image = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
        }else{
            $image = '';
        }


        if(empty($aitemNo)){
            return json(['data' => 'error', 'code' => 2, 'message' => '课题号不能为空']);
        }
        $post = ['SY' => $appReason,'DFKT' => $aitemNo,'BXKM' => $subject,'BZ' => $remark,'JSFS' => $settlement,'SKDWKHH' => $openBank,'FKRNAME' => $bankName,'SKDWZH' => $bankNo,'CWHDJE' => $bankprice,'SKDWQC' => $companyNo,'XGRQ'=>$time,'XGR'=>$num,'appPerson'=>$image,'approverId'=>$item['uid']];

        //判断是否已经有流水单号
        $apply = db('t_bx_d_bxd')->where('id',$id)->find();
        if($apply['bxdOnlyNo'] !=0) {
            $appNum = 'Bx' . time() . mt_rand(1000, 9999);//流水单号
            $post['bxdOnlyNo'] = $appNum;
        }


        $flow = db('apply_flow')->where('appid',$id)->find();
        if($flow['oneApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneApprove'=>1]);
        }else if($flow['twoApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoApprove'=>1]);
            //审批人签字
            $bxd = db('t_bx_d_bxd')->where('id',$id)->find();
            if($bxd['SPRNAME'] == '') {
                $SPRNAME = '';
                $appPerson = request()->file('appPerson');
                if(!empty($appPerson)) {
                    $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
                    $SPRNAME = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
                }
            }else{
                $SPRNAME = $bxd['appPerson'];
            }

            $post['SPRNAME'] = $SPRNAME;

        }else if($flow['threeApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['threeApprove'=>1]);
        }else if($flow['sign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['sign'=>1]);
        }else if($flow['oneSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneSign'=>1]);
        }else if($flow['twoSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoSign'=>1]);
        }else if($flow['threeSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['threeSign'=>1]);
        }
        if($update){
            $apply = db('t_bx_d_bxd')->where('id',$id)->update($post);
            if($apply) {
                $data = ['XGR'=>$num,'XGRQ'=>$time];
                $jxf = db('t_bx_d_jtfbxdj')->where('bxdId',$id)->update($data);
                if($jxf){
                    return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                }else{
                    return json(['data'=>'error','code'=>0,'message'=>'失败end']);
                }

            }else{
                return json(['data'=>'error','code'=>0,'message'=>'提交失败']);
            }
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败']);
        }


    }

    //删除报销单
    public function delAp(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id  = input('post.id');
        $delFlow = db('apply_flow')->where('appid',$id)->delete();
        if($delFlow){
            $travle = db('t_bx_d_jtfbxdj')->where('bxdId',$id)->find();
            $ticket = db('ticket')->where('tappId',$travle['id'])->update(['tappId'=>0]);
            if($ticket){
                $deljt = db('t_bx_d_jtfbxdj')->where('bxdId',$id)->delete();
                if($deljt){
                    $bxd = db('t_bx_d_bxd')->delete($id);
                    if($bxd){
                        return json(['data'=>'success','code'=>1,'message'=>'成功']);
                    }else{
                        return json(['data'=>'error','code'=>0,'message'=>'失败4']);
                    }

                }else{
                    return json(['data'=>'error','code'=>0,'message'=>'失败3']);
                }
            }else{
                return json(['data'=>'error','code'=>0,'message'=>'失败2']);
            }
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败1']);
        }
    }



    //查询报销单里面的票据
    public function tsApply(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        //报销单id
        $id = input('post.id');
        $bxdType = input('post.bxdType');
        $arr = array();
        $i = 1;
        if($bxdType == 0){
            $traffic = db('t_bx_d_jtfbxdj')->where('bxdId',$id)->find();
            $ticket = db('ticket')->where('tappId',$traffic['id'])->select();

            foreach($ticket as $v){

                $taxi = db('ticket_taxi')->where('pid',$v['id'])->find();
                $v['ticketCode'] = $taxi['ticketCode'];
                $v['ticketNumber'] = $taxi['ticketNumber'];
                $v['ticketTime'] = $taxi['ticketTime'];
                $v['offTime'] = $taxi['offTime'];
                $v['taxiTname'] = $taxi['taxiTname'];
                $v['ticketType'] = 1;
                $v['date'] = date('Y-m-d',$v['addTime']);

                //临时字段 假排序
                $a = $i++;
                $applyNo = 'c' . sprintf("%04d", $a);
                $v['order'] = $applyNo;
                $arr[] = $v;
            }

        }else if($bxdType == 1){
            $travel = db('t_bx_d_clfbxdj')->where('bxdId',$id)->find();
            $ticket = db('ticket')->where('tappId',$travel['id'])->select();
            foreach($ticket as $v){
                if($ticketType = 2){
                    $train = db('ticket_train')->where('pid',$v['id'])->find();
                    $v['trainNum'] = $train['trainNum'];
                    $v['trainUname'] = $train['trainUname'];
                    $v['startLocation'] = $train['startLocation'];
                    $v['endLocation'] = $train['endLocation'];
                    $v['traintime'] = $train['traintime'];
                    $v['level'] = $train['level'];
                    $v['ticketType'] = 2;
                    $v['date'] = date('Y-m-d',$v['addTime']);

                    //临时字段 假排序
                    $a = $i++;
                    $applyNo = 'c' . sprintf("%04d", $a);
                    $v['order'] = $applyNo;
                    $arr[] = $v;
                }else{
                    $plane = db('ticket_plane')->where('pid',$v['id'])->find();
                    $v['planeNumber'] = $plane['planeNumber'];
                    $v['planeUname'] = $plane['planeUname'];
                    $v['planeSlocation'] = $plane['planeSlocation'];
                    $v['planeElocation'] = $plane['planeElocation'];
                    $v['planedate'] = $plane['planedate'];
                    $v['planelevel'] = $plane['planelevel'];
                    $v['purchase'] = $plane['purchase'];
                    $v['printing'] = $plane['printing'];
                    $v['passengerNo'] = $plane['passengerNo'];
                    $v['planeInsurance'] = $plane['planeInsurance'];
                    $v['planeTime'] = $plane['planeTime'];
                    $v['ticketType'] = 3;
                    $v['date'] = date('Y-m-d',$v['addTime']);

                    //临时字段 假排序
                    $a = $i++;
                    $applyNo = 'c' . sprintf("%04d", $a);
                    $v['order'] = $applyNo;
                    $arr[] = $v;
                }

            }
        }


        if($arr){
            return json(['data'=>$arr,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>$arr,'code'=>0,'message'=>'失败']);
        }




    }



    //课题号查询
    public function selectItem()
    {
        //token验证
        $token = input('post.token');
        if (!empty($token)) {
            $user = db('user')->where('token', $token)->find();
            if (!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $uid = input('post.uid');
        $item = db('item')->where('uid', $uid)->select();
        if ($item) {
            return json(['data' => $item, 'code' => 1, 'message' => '成功']);
        } else {
            return json(['data' => $item, 'code' => 0, 'message' => '失败']);
        }

    }

    //课题组关联课题号
    public function userItem(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $class = input('post.class');
        $user = db('user')->where('class',$class)->select();
        $arr= array();
        foreach($user as $vo){
            $item = db('item')->where('uid',$vo['id'])->select();
            foreach($item as $v){
                $v['uname'] = $vo['name'];
                $arr[] = $v;
            }
        }

        if(!empty($arr)){
            return json(['data'=>$arr,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data'=>$arr,'code'=>0,'message'=>'失败']);
        }

    }

    //生成差旅费报销单
    public function makeTravelApply(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $num = input('post.num');
        $type = input('post.type');
        $uid = input('post.uid');
        $ticketType = input('post.ticketType');
        $id = input('post.id');
        $tid = explode(',',$id);

        //差旅费费
        $traffic = db('t_bx_d_clfbxdj')->order('id desc')->limit(1)->find();

        $newid = $traffic['id']+1;
        //报销单
        $bxd = db('t_bx_d_bxd')->order('id desc')->limit(1)->find();
        $bxdid = $bxd['id']+1;
        $array = array();
        for($i=0;$i<count($tid);$i++){
            //判断是否已经生成报销单
            $ticketPid = db('ticket')->where('id',$tid[$i])->find();
            if($ticketPid['tappId'] != 0){
                $tt = 1;
            }else{
                $tt = 0;
            }


            // 查看是否有附件
            $acc = db('accessory')->where('tid',$ticketPid['id'])->select();
            if(empty($acc)){
                return json(['data'=>'error','code'=>2,'message'=>'必须有附件']);
            }
            //查看飞机票是否有 政府采购码
            if($ticketPid['type'] == 3){
                $plane = db('ticket_plane')->where('pid',$ticketPid['id'])->find();
                if(empty($plane['purchase'])){
                    return json(['data'=>'error','code'=>2,'message'=>'政府采购码不能为空']);
                }
            }


            //判断座位等级是否可以生成报销单
            $user = db('user')->where('num',$num)->find();
            $train = db('ticket_train')->where('pid',$ticketPid['id'])->find();
            if($user['rid'] == 4 && ($train['level'] == '软座' || $train['level'] == '软卧' || $train['level'] == '一等座')){
                return json(['data'=>'error','code'=>2,'message'=>'等级不够生成报销单']);
            }

            //飞机登录人 票据不一样
            $plane = db('ticket_plane')->where('pid',$ticketPid['id'])->find();
            if(!empty($plane)) {
                if ($plane['planeUname'] != $user['name']) {
                    return json(['data' => 'error', 'code' => 3, 'message' => '姓名不一致']);
                }
            }

        }



        //判断是否有人工识别两种状态
        $array1 = array();
        $array2 = array();
        $recogn = db('ticket')->where('id','in',$tid)->select();
        $recognArr = array();
        foreach($recogn as $v){
            $recognArr[] = $v['recogn'];
        }
        if(in_array('0',$recognArr)){
            return json(['data'=>'error','code'=>4,'message'=>'不能有据识票据']);
        }

        for($i=0;$i<count($tid);$i++) {
            if ($tt == 1) {
                return json(['data' => 'error', 'code' => 0, 'message' => '不能重复添加']);
            } else {
//                $ticket = db('ticket')->where('id', $tid[$i])->update(['tappId' => $newid]);
                $tprice = db('ticket')->where('id', $tid[$i])->find();
                //判断是否有人工识别两种状态
                if(in_array('1',$recognArr) && (in_array('2',$recognArr))){
                    if($tprice['recogn'] == 1){
                        $array1[] = $tprice['price'];//票据金额

                    }else{
                        $array2[] = $tprice['price'];//票据金额
                    }
                }else{
                    $array[] = $tprice['price'];//票据金额

                }
            }
        }

        //计算所有票据总额
        $appPrice = array_sum($array);
        if(!empty($array1)) {
            $appPrice1 = array_sum($array1);
            $appPrice2 = array_sum($array2);
            $count1 = count($array1);
            $count2 = count($array2);
        }


        //添加交通表
        $sub = substr($bxd['BXDBH'],1,4);
        $end = ltrim ($sub, '0');



            $user = db('user')->where('num', $num)->find();
            $end = $end + 1;
            $end2 = $end + 1;
            //判断是否有人工识别两种状态
            $recogn = db('ticket')->where('id', 'in', $tid)->select();
            $recognArr = array();
            foreach ($recogn as $v) {
                $recognArr[] = $v['recogn'];
            }

            if (in_array('1', $recognArr) && (in_array('2', $recognArr))) {
                $applyNo = 'b' . sprintf("%04d", $end);
                $applyNo2 = 'b' . sprintf("%04d", $end2);
                $time = date('Y-m-d H:i:s', time());
//                $data = ['BXDBH' => $applyNo, 'ROLES_ID' => $user['id'], 'CWHDJE' => $appPrice1, 'DJZS' => $count1, 'CJR' => $num, 'CJRQ' => $time, 'BXKM' => '1', 'type' => 0,'bxdType' =>1,'ticketType'=>$ticketType,'uid'=>$uid];
                $data = ['BXDBH' => $applyNo];
                $data2 = ['BXDBH' => $applyNo2, 'ROLES_ID' => $user['id'], 'CWHDJE' => $appPrice2, 'DJZS' => $count2, 'CJR' => $num, 'CJRQ' => $time, 'BXKM' => '差旅费', 'type' => 1,'bxdType' =>1,'ticketType'=>$ticketType,'uid'=>$uid];
                $bxd = db('t_bx_d_bxd')->insert($data);
                $bxd2 = db('t_bx_d_bxd')->insert($data2);

                if ($bxd && $bxd2) {
                    //添加审批表
                    $bxdid = db('t_bx_d_bxd')->limit('1')->order('id desc')->find();
                    $apply_flow = db('apply_flow')->insert(['appid' => $bxdid['id']-1]);
                    $apply_flow2 = db('apply_flow')->insert(['appid' => $bxdid['id']]);
                    if($apply_flow && $apply_flow2) {

                        $data = ['DJBH' => 1, 'BXDBH' => $applyNo, 'CJRQ' => $time, 'CJR' => $num, 'bxdId' => $bxdid['id']-1];
                        $data2 = ['DJBH' => 1, 'BXDBH' => $applyNo2, 'CJRQ' => $time, 'CJR' => $num, 'bxdId' => $bxdid['id']];
                        $apply = db('t_bx_d_clfbxdj')->insert($data);
                        $apply2 = db('t_bx_d_clfbxdj')->insert($data2);
                        if($apply && $apply2){
                            $cl = db('t_bx_d_clfbxdj')->order('id desc')->find();
                            for($i=0;$i<count($tid);$i++) {
                                $tkt = db('ticket')->where('id',$tid[$i])->find();
                                if($tkt['recogn'] == 1){
                                    $ticket1 = db('ticket')->where('id', $tid[$i])->update(['tappId' => $cl['id']-1]);
                                }else{
                                    $ticket2 = db('ticket')->where('id', $tid[$i])->update(['tappId' => $cl['id']]);
                                }

                            }
                            if($ticket1 && $ticket2){
                                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                            }else{
                                return json(['data'=>'error','code'=>0,'message'=>'失败end2']);
                            }

                        }else{
                            return json(['data'=>'error','code'=>0,'message'=>'失败end1']);
                        }

                    }else {
                        return json(['data'=>'error','code'=>0,'message'=>'失败2']);
                    }
                } else {
                    return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
                }
            } else {
                $applyNo = 'b' . sprintf("%04d", $end);

                $time = date('Y-m-d H:i:s', time());
                $data = ['BXDBH' => $applyNo, 'ROLES_ID' => $user['id'], 'CWHDJE' => $appPrice, 'DJZS' => count($tid), 'CJR' => $num, 'CJRQ' => $time, 'BXKM' => '差旅费', 'type' => 0,'bxdType' =>1,'ticketType'=>$ticketType,'uid'=>$uid];
                $bxd = db('t_bx_d_bxd')->insert($data);
                if ($bxd) {
                    //添加审批表
                    $bxdid = db('t_bx_d_bxd')->limit('1')->order('id desc')->find();
                    $apply_flow = db('apply_flow')->insert(['appid' => $bxdid['id']]);
                    if($apply_flow) {
                        $data = ['DJBH' => 1, 'BXDBH' => $applyNo, 'CJRQ' => $time, 'CJR' => $num, 'bxdId' => $bxdid['id']];
                        $apply = db('t_bx_d_clfbxdj')->insert($data);
                        if($apply){
                            $cl = db('t_bx_d_clfbxdj')->order('id desc')->find();
                            for($i=0;$i<count($tid);$i++) {
                                $ticket = db('ticket')->where('id', $tid[$i])->update(['tappId' => $cl['id']]);
                            }
                            if($ticket){
                                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                            }else{
                                return json(['data'=>'error','code'=>0,'message'=>'失败end2']);
                            }

                        }else{
                            return json(['data'=>'error','code'=>0,'message'=>'失败end1']);
                        }

                    }else {
                        return json(['data'=>'error','code'=>0,'message'=>'失败2']);
                    }
                } else {
                    return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
                }
            }

    }


    //保存差旅费报销单
    public function saveTravelApply(){
        //token验证
        $token = input('post');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $appReason = input('post.SY');
        $aitemNo = input('post.DFKT');
        $CFSJ = input('post.CFSJ');
        $FHSJ = input('post.FHSJ');
        $MDD = input('post.MDD');
        $JTGZBM = input('post.JTGZBM');
        $JTFY = input('post.JTFY');
        $ZSF = input('post.ZSF');
        $HCTB = input('post.HCTB');
        $CCBZ = input('post.CCBZ');
        $id = input('post.id');
        $num = input('post.num');
        $settlement = input('post.JSFS');
        $openBank = input('post.SKDWKHH');
        $bankName = input('post.FKRNAME');
        $bankNo = input('post.SKDWZH');
        $bankprice = input('post.CWHDJE');
        $companyNo = input('post.SKDWQC');
        $BZ = input('post.BZ');
        $BXHJ = input('post.BXHJ');
//        $BXRBM = input('post.BXRBM');

        //报销人签字
        $appPerson = request()->file('appPerson');
        if(!empty($appPerson)) {
            $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
            $image = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
        }else{
            $image = '';
        }

        $time = date('Y-m-d H:i:s',time());
        $cj = db('t_bx_d_clfbxdj')->where('bxdId',$id)->find();
        $data = ['CFSJ' => $CFSJ,'FHSJ' => $FHSJ,'MDD' => $MDD,'JTGZBM' => $JTGZBM,'JTFY' => $JTFY,'ZSF' => $ZSF,'HCTB' => $HCTB,'CCBZ'=>$CCBZ,'XGR' => $num,'XGRQ' => $time];
        $apply = db('t_bx_d_clfbxdj')->where('id',$cj['id'])->update($data);

        if($apply){
            $data = ['SY' => $appReason,'DFKT' => $aitemNo,'JSFS' => $settlement,'SKDWKHH' => $openBank,'FKRNAME' => $bankName,'SKDWZH' => $bankNo,'CWHDJE' => $bankprice,'SKDWQC' => $companyNo,'BZ' => $BZ,'BXHJ'=>$BXHJ,'appPerson'=>$image];
            $bxd = db('t_bx_d_bxd')->where('id',$id)->update($data);
            if($bxd){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else{
                return json(['data'=>'error','code'=>0,'message'=>'失败2']);
            }

        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败']);
        }


    }

    //提交差旅费报销单
    public function submitTravel(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        $appReason = input('post.SY');
        $aitemNo = input('post.DFKT');
        $CFSJ = input('post.CFSJ');
        $FHSJ = input('post.FHSJ');
        $MDD = input('post.MDD');
        $JTGZBM = input('post.JTGZBM');
        $JTFY = input('post.JTFY');
        $ZSF = input('post.ZSF');
        $HCTB = input('post.HCTB');
        $CCBZ = input('post.CCBZ');
        $settlement = input('post.JSFS');
        $openBank = input('post.SKDWKHH');
        $bankName = input('post.FKRNAME');
        $bankNo = input('post.SKDWZH');
        $bankprice = input('post.CWHDJE');
        $companyNo = input('post.SKDWQC');
        $BZ = input('post.BZ');
        $BXHJ = input('post.BXHJ');
//        $BXRBM = input('post.BXRBM');
        $item = db('item')->where('id',$aitemNo)->find();

        $id = input('post.id');
        $num = input('post.num');
        $time = date('Y-m-d H:i:s',time());


        //判断是否已经有流水单号
        $apply = db('t_bx_d_bxd')->where('id',$id)->find();
        if($apply['bxdOnlyNo'] ==0) {
            $appNum = 'Bx' . time() . mt_rand(1000, 9999);//流水单号
            $post['bxdOnlyNo'] = $appNum;
        }

        //报销人签字
        $appPerson = request()->file('appPerson');
        if(!empty($appPerson)) {
            $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
            $image = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
        }else{
            $image = '';
        }


        $flow = db('apply_flow')->where('appid',$id)->find();
        if($flow['oneApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneApprove'=>1]);
        }else if($flow['twoApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoApprove'=>1]);
            //审批人签字
            $bxd = db('t_bx_d_bxd')->where('id',$id)->find();
            if($bxd['SPRNAME'] == '') {
                $SPRNAME = '';
                $appPerson = request()->file('appPerson');
                if(!empty($appPerson)) {
                    $info = $appPerson->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
                    $SPRNAME = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
                }
            }else{
                $SPRNAME = $bxd['appPerson'];
            }

            $post['SPRNAME'] = $SPRNAME;


        }else if($flow['threeApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['threeApprove'=>1]);
        }else if($flow['sign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['sign'=>1]);
        }else if($flow['oneSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneSign'=>1]);
        }else if($flow['twoSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoSign'=>1]);
        }else if($flow['threeSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['threeSign'=>1]);
        }
        if($update){
            $appl = db('t_bx_d_bxd')->where('id',$id)->update($post);
            if($appl) {
                $data = ['CFSJ' => $CFSJ,'FHSJ' => $FHSJ,'MDD' => $MDD,'JTGZBM' => $JTGZBM,'JTFY' => $JTFY,'ZSF' => $ZSF,'HCTB' => $HCTB,'CCBZ'=>$CCBZ,'XGR' => $num,'XGRQ' => $time];
                $clf = db('t_bx_d_clfbxdj')->where('bxdId',$apply['id'])->update($data);
                if($clf) {
                    $data = ['SY' => $appReason, 'DFKT' => $aitemNo, 'JSFS' => $settlement, 'SKDWKHH' => $openBank, 'FKRNAME' => $bankName, 'SKDWZH' => $bankNo, 'CWHDJE' => $bankprice, 'SKDWQC' => $companyNo, 'BZ' => $BZ,'BXHJ'=>$BXHJ,'appPerson'=>$image,'approverId'=>$item['uid']];
                    $bxd = db('t_bx_d_bxd')->where('id', $id)->update($data);
                    if ($bxd) {
                        return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
                    } else {
                        return json(['data' => 'error', 'code' => 0, 'message' => '失败4']);
                    }
                }else{
                    return json(['data'=>'error','code'=>0,'message'=>'失败3']);
                }

            }else{
                return json(['data'=>'error','code'=>0,'message'=>'提交失败1']);
            }
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败1']);
        }


    }


    //删除差旅费报销单
    public function delTravel(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

        $id  = input('post.id');

        $delFlow = db('apply_flow')->where('appid',$id)->delete();
        if($delFlow){
            $travle = db('t_bx_d_clfbxdj')->where('bxdId',$id)->find();
            $ticket = db('ticket')->where('tappId',$travle['id'])->update(['tappId'=>0]);
            if($ticket){
                $deljt = db('t_bx_d_clfbxdj')->where('bxdId',$id)->delete();
                if($deljt){
                    $bxd = db('t_bx_d_bxd')->delete($id);
                    if($bxd){
                        return json(['data'=>'success','code'=>1,'message'=>'成功']);
                    }else{
                        return json(['data'=>'error','code'=>0,'message'=>'失败4']);
                    }

                }else{
                    return json(['data'=>'error','code'=>0,'message'=>'失败3']);
                }
            }else{
                return json(['data'=>'error','code'=>0,'message'=>'失败2']);
            }
        }else{
            return json(['data'=>'error','code'=>0,'message'=>'失败1']);
        }

    }


    //审批
    public function sign(){
        //token验证
        $token = input('post.token');
        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }
        //$SPRNAME
        $id = input('post.id');
        $num = input('post.num');
        
        $bxd = db('t_bx_d_bxd')->where('id', $id)->find();
        $item = db('item')->where('id',$bxd['DFKT'])->find();

        $file = request()->file('SPRNAME');
        if(!empty($file)) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'sign');
            $image = 'http://120.27.49.216:8088/uploads/sign/' . $info->getSaveName();
        }
        $flow = db('apply_flow')->where('appid',$id)->find();
        if($flow['oneApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneApprove'=>1]);
            if($update){
                $bxd = db('t_bx_d_bxd')->where('id', $id)->update(['SPRNAME'=>$image]);
                if($bxd){
                    return json(['data'=>'success','code'=>1,'message'=>'成功']);
                }else{
                    return json(['data'=>'error','code'=>0,'message'=>'失败end']);
                }
            }else{
                return json(['data'=>'error','code'=>0,'message'=>'失败1']);
            }
        }else if($flow['twoApprove'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoApprove'=>1]);
//            $update = 1;
            if($update) {
                //财务签字
                $updateBxd = db('t_bx_d_bxd')->where('id', $id)->update(['SPRNAME' => $image]);
                if ($updateBxd) {
                    $y = date('Y', time());
                    $m = date('m', time());
                    $d = date('d', time());
                    $vouNo = $y . $m . $d . mt_rand('100', '999');
                    $addtime = date('Y-m-d', time());

                    if ($bxd['bxdType'] == 0) {
                        $vouInfo1 = "   .50010202110101..".$item['itemNo'].".*.科研支出-财政项目-商品和服务支出-交通费-市内交通费.*.李玉杰&1F17N10&项目-重点部署项目-重点部署项目经费-0&智能云";
                        $vouInfo2 = '   .10110204010101..0000000000.*.零余额账号用款额度-项目-专项基础科研2060206.*.**';
                    } else {
                        $vouInfo1 = "   .50010202110102..".$item['itemNo'].".*.科研支出-财政项目-商品和服务支出-差旅费-国内差旅费.*.王春恒&1F17N10&项目-重点部署项目-重点部署项目经费-0&智能云";
                        $vouInfo2 = '   .10110204010102..0000000000.*.零余额账号用款额度-项目-专项基础科研2060206.*.**';
                    }
                    $user = db('user')->where('num',$num)->find();
                    $main = $bxd['SY'];
                    $passPerson = $user['name'];
                    $borrowPrice = $bxd['CWHDJE'];
                    $loanPrice = $bxd['CWHDJE'];
                    $allPrice = $bxd['CWHDJE'];
                    $data1 = ['vouNo' => $vouNo, 'addtime' => $addtime, 'main' => $main, 'vouInfo' => $vouInfo1, 'borrowPrice' => $borrowPrice, 'allPrice' => $allPrice,'bid'=>$bxd['id'],'passPerson'=>$passPerson];
                    $data2 = ['vouNo' => $vouNo, 'addtime' => $addtime, 'main' => $main, 'vouInfo' => $vouInfo2, 'loanPrice' => $loanPrice, 'allPrice' => $allPrice,'bid'=>$bxd['id'],'passPerson'=>$passPerson];
                    $voucher1 = db('voucher')->insert($data1);
                    $voucher2 = db('voucher')->insert($data2);
                    if ($voucher1 && $voucher2) {
                        return json(['data' => 'success', 'code' => 1, 'message' => '添加成功']);
                    } else {
                        return json(['data' => 'error', 'code' => 0, 'message' => '添加失败']);
                    }
                } else {
                    return json(['data' => 'error', 'code' => 0, 'message' => '签字失败2']);
                }
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '签字失败']);
            }

        }else if($flow['threeApprove'] == 0){

        }else if($flow['sign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['sign'=>1]);
        }else if($flow['oneSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['oneSign'=>1]);
        }else if($flow['twoSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['twoSign'=>1]);
        }else if($flow['threeSign'] == 0){
            $update = db('apply_flow')->where('appid',$id)->update(['threeSign'=>1]);
        }




    }

    //不合格
    public function noPass(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }else if(empty($token)){
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }

        $id = input('post.id');
        $signBecause = input('post.signBecause');
        $apply = db('t_bx_d_bxd')->where('id',$id)->find();
        $applyFlow = db('apply_flow')->where('appid',$apply['id'])->update(['threeSign'=>1]);
        if($applyFlow){
            $update = db('t_bx_d_bxd')->where('id',$id)->update(['signBecause'=>$signBecause]);
            if($update){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '失败2']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }

    }

    //查询flow状态
    public function flow(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
//        $checkToken = cache::get($uid.'_token');
//        $time = $token - $checkToken;
//        if($time > 7*24*3600){
//            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
//        }else if(empty($token)){
//            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
//        }
        //0待提交 1待审批 2待付款 3已付款 4已拒签
        $flowType = input('post.flowType');
        $num = input('post.num');
        $item = db('item')->where('uid',$uid)->find();
        if($item){
            switch ($flowType)
            {
                case 0:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['f.oneApprove'=>0,'b.cjr'=>$num])->whereOr('b.approverId',$uid)->select();
                    break;
                case 1:
//                    echo 1111111;
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['b.cjr'=>$num])->whereOr('b.approverId',$uid)->where(['f.oneApprove'=>1,'f.twoApprove'=>0,'f.threeSign'=>0])->select();
                    break;
                case 2:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['b.cjr'=>$num])->whereOr('b.approverId',$uid)->where(['f.twoApprove'=>1,'f.threeApprove'=>0])->select();
                    break;
                case 3:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['b.cjr'=>$num])->whereOr('b.approverId',$uid)->where(['f.threeApprove'=>1,'f.threeSign'=>0])->select();
                    break;
                case 4:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['b.cjr'=>$num])->whereOr('b.approverId',$uid)->where('f.threeSign',1)->select();
                    break;
                default:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f','b.id = f.appid','left')->field('b.*')->where(['b.cjr'=>$num])->whereOr('b.approverId',$uid)->where('f.threeSign',1)->select();
            }

        }else {
            switch ($flowType) {
                case 0:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where(['f.oneApprove' => 0, 'b.cjr' => $num])->select();
                    break;
                case 1:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where('b.cjr', $num)->where(['f.oneApprove' => 1, 'f.twoApprove' => 0, 'f.threeSign' => 0])->select();
                    break;
                case 2:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where('b.cjr', $num)->where(['f.twoApprove' => 1, 'f.threeApprove' => 0])->select();
                    break;
                case 3:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where('b.cjr', $num)->where(['f.threeApprove' => 1, 'f.threeSign' => 0])->select();
                    break;
                case 4:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where('b.cjr', $num)->where('f.threeSign', 1)->select();
                    break;
                default:
                    $arr = db('t_bx_d_bxd')->alias('b')->join('apply_flow f', 'b.id = f.appid', 'left')->field('b.*')->where('b.cjr', $num)->where('f.threeSign', 1)->select();
            }
        }

        $i = 1;
        foreach($arr as $k =>$v){
            if(empty($v)){
                unset($arr[$k]);
            }
            //临时字段 假排序
            $a = $i++;
            $applyNo = 'b'.sprintf("%04d", $a);
            $arr[$k]['order'] = $applyNo;

            $appflow = db('apply_flow')->where('appid',$v['id'])->find();
            if($appflow['threeSign'] == 1){
                $arr[$k]['flow'] = '已拒签';
            }else if($appflow['twoSign'] == 1){
                $arr[$k]['flow'] = '部门负责人拒签';
            }else if($appflow['oneSign'] == 1){
                $arr[$k]['flow'] = '课题负责人拒签';
            }else if($appflow['sign'] == 1){
                $arr[$k]['flow'] = '待签字';
            }else if($appflow['threeApprove'] == 1){
                $arr[$k]['flow'] = '已付款';
            }else if($appflow['twoApprove'] == 1) {
                $arr[$k]['flow'] = '待付款';
            }else if($appflow['oneApprove'] == 1) {
                $arr[$k]['flow'] = '待审批';
            }else{
                $arr[$k]['flow'] = '待提交';
            }
        }

        if($arr){
            return json(['data' => $arr, 'code' => 1, 'message' => '成功']);
        }else{
            return json(['data' => $arr, 'code' => 0, 'message' => '失败']);
        }


    }


}