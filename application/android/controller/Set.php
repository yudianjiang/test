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
use app\extend\common\common;
use think\Cache;

Class Set
{
    //设置委托人
    public function setEntrust()
    {
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


        $setNum = input('post.setNum');
        $setName = input('post.setName');

        $entrust = db('entrust')->where('uid',$uid)->find();

        if(empty($entrust)){
            $data = ['uid'=>$uid,'setNum'=>$setNum,'setName'=>$setName];
            $addEntrust = db('entrust')->insert($data);
            if($addEntrust){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else{
                return json(['data'=>'error','code'=>0,'message'=>'失败']);
            }


        }else{
            $updateTime = date('Y-m-d H:i:s',time());
            $data = ['setNum'=>$setNum,'setName'=>$setName,'updateTime'=>$updateTime];
            $saveEntrust = db('entrust')->where('uid',$uid)->update($data);
            if($saveEntrust){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else {
                return json(['data' =>'error', 'code' => 0, 'message' => '失败']);
            }
        }


    }

    //查询委托人
    public function selectEntrust()
    {
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


        
        $entrust = db('entrust')->where('uid',$uid)->find();
        if($entrust){
            return json(['data'=>$entrust,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data' =>$entrust, 'code' => 0, 'message' => '失败']);
        }
    }


    //输入密码
    public function selectPassword()
    {

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


        $password = input('post.password');
        $id = input('post.id');
        $pwd = md5($password);
        $user = db('user')->where('id', $id)->find();
        if ($user['password'] == $pwd) {
            return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
        } else {
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
    }

    //修改密码
    public function savePassword(){

        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }
        if(!empty($token)){
            if($checkToken != $token){
                return json(['data' => 'error', 'code' => 0, 'message' => '输入正确信息']);
            }
        }else{
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }



        $password = input('post.password');
        $newPassword = input('post.newPassword');
        $id = input('post.id');
        $pwd = md5($password);
        $newpwd = md5($newPassword);
        $user = db('user')->where(['id'=>$id,'password'=>$pwd])->find();
        if($user){
            $update = db('user')->where('id',$id)->update(['password'=>$newpwd]);
            if($update){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else{
                return json(['data' =>'error', 'code' => 0, 'message' => '失败']);
            }

        }else{
            return json(['data' =>'error', 'code' => 0, 'message' => '密码错误']);
        }
    }


    //默认课题号
    public function userItem(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }
        if(!empty($token)){
            if($checkToken != $token){
                return json(['data' => 'error', 'code' => 0, 'message' => '输入正确信息']);
            }
        }else{
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }
        
        $item = input('post.item');

        $uItem = db('user_item')->where('uid',$uid)->find();
        if(empty($uItem)){
            $add = db('user_item')->insert(['uid'=>$uid,'uItem'=>$item]);
            if($add){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else{
                return json(['data' =>'error','code' => 0,'message' => '失败1']);
            }
        }else{
            $update = db('user_item')->where('uid',$uid)->update(['uItem'=>$item]);
            if($update){
                return json(['data'=>'success','code'=>1,'message'=>'成功']);
            }else{
                return json(['data' =>'error','code' => 0,'message' => '失败2']);
            }
        }


    }

     //查询默认课题
    public function selectUserItem(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }
        if(!empty($token)){
            if($checkToken != $token){
                return json(['data' => 'error', 'code' => 0, 'message' => '输入正确信息']);
            }
        }else{
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }

        
        $userItem = db('user_item')->where('uid',$uid)->find();
        $item = db('item')->where('id',$userItem['uItem'])->find();
        if($item){
            return json(['data'=>$item,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data' =>$item,'code' => 0,'message' => '失败']);
        }

    }


     //查询委托人下所有人
    public function entrustAll(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }
        if(!empty($token)){
            if($checkToken != $token){
                return json(['data' => 'error', 'code' => 0, 'message' => '输入正确信息']);
            }
        }else{
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }



        $num = input('post.num');
        $select = db('entrust')->where('setNum',$num)->select();
        if($select){
            return json(['data'=>$select,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data' =>$select,'code' => 0,'message' => '失败']);
        }


    }


    //自动查询名字
    public function selectName(){
        $uid = input('post.uid');
        $token = input('post.token');
        //token验证
        $checkToken = cache::get($uid.'_token');
        $time = $token - $checkToken;
        if($time > 7*24*3600){
            return json(['data' => 'error', 'code' => 2, 'message' => 'token已过期']);
        }
        if(!empty($token)){
            if($checkToken != $token){
                return json(['data' => 'error', 'code' => 0, 'message' => '输入正确信息']);
            }
        }else{
            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
        }



        $num = input('post.num');
        $select = db('user')->where('num',$num)->find();
        if($select){
            return json(['data'=>$select,'code'=>1,'message'=>'成功']);
        }else{
            return json(['data' =>$select,'code' => 0,'message' => '失败']);
        }


    }





}