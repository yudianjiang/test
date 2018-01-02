<?php
/**
 * Created by PhpStorm.
 * User: 秋刀鱼
 * Date: 2017/10/30
 * Time: 16:16
 */
// | 智能财务云平台i-FFSC
// +----------------------------------------------------------------------
// | 信息管理类
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\extend\common;
use think\Db;
use think\Request;


class common{
    public function _initialize() {
        //判断用户登录信息
        if(empty(session('sid'))){
            $this->error('非法访问','index/index');
        }

    }

    // 获取当前分类的父分类
    public static function getParent($company, $pid){
        $arr = array();
        foreach($company as $v){
            if($v['id'] == $pid){
                $arr[] = $v;
                $arr = array_merge(self::getParent($company, $v['pid']), $arr);
            }
        }
        return $arr;
    }

    //左边公共部分    分类树结构
    public static function getTree($company, $pid){
        $array = array();
        foreach($company as $v){
            if($v['pid'] == $pid){
                $v['son'] = self::getTree($company, $v['id']);

                //查询用户表
                $user = db('user')->select();
                foreach($user as $uk => $uv){
                    if($uv['class'] == $v['id']){
                        $v['uinfo'][] = $uv;
                    }
                }

                $array[] = $v;
            }
        }
        return $array;

    }

    //生成uuid编码
    public static function  uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return $uuid ;
    }

    //token验证
    public static function cToken($token){

        if(!empty($token)){
            $user = db('user')->where('token',$token)->find();
            if(!$user) {
                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
            }
        }

    }

    //临时字段 假排序
    public static function fOrder($a){
        $order = 'c' . sprintf("%04d", $a);
        return $order;
    }





}