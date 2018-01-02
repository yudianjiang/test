<?php
// | 智能财务云平台i-FFSC  安卓端
// +----------------------------------------------------------------------
// | 安卓首页
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2026
// +----------------------------------------------------------------------
// | Author: 于殿江 <13895789841@163.com> <QQ:442206510>
// +----------------------------------------------------------------------
namespace app\index\model;


use think\Model;

Class TicketTaxi extends Model
{
    protected $autoWriteTimestamp = 'timestamp';
    //save方法
    public function oneSave($data,$id){
        return $this->allowField(true)->save($data,['id'=>$id]);
    }

}