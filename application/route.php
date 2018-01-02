<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//Route::get('register/', 'index/register/index');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],


    /****   pc  ****/
    'register/' => ['index/register/index', ['method' => 'post']],




    /****    安卓app接口    ***/
    'login' => ['android/login/login', ['method' => 'post']],//登录
    '/apply/[:name]' => ['android/apply/index', ['method' => 'post']],//安卓首页
    'sName' => ['android/apply/sName', ['method' => 'post']],//安卓查询名字
    'delApp' => ['android/apply/delapp', ['method' => 'post']],//安卓删除根据票据单号
    'getTree' => ['android/apply/getTree', ['method' => 'post']],//安卓人员列表接口
    'makeTaxiApply' => ['android/apply/makeTaxiApply', ['method' => 'post']],//安卓生成报销单
    'tAppData' => ['android/apply/tAppData', ['method' => 'post']],//安卓添加报销单后查询
    'saveApply' => ['android/apply/saveApply', ['method' => 'post']],//生成报销单后保存报销单
    'submitAp' => ['android/apply/submitAp', ['method' => 'post']],//生成报销单后提交报销单
    'delAp' => ['android/apply/delAp', ['method' => 'post']],//生成报销单后删除报销单
    'tsApply' => ['android/apply/tsApply', ['method' => 'post']],//查询报销单里面的关联票据
    'sItem' => ['android/apply/selectItem', ['method' => 'post']],//查询课题号
    'uItem' => ['android/apply/userItem', ['method' => 'post']],//查询课题组有课题号的用户
    'mtApply' => ['android/apply/makeTravelApply', ['method' => 'post']],//生成差旅费报销单
    'dTravel' => ['android/apply/delTravel', ['method' => 'post']],//删除差旅费报销单
    'vTravel' => ['android/apply/saveTravelApply', ['method' => 'post']],//保存差旅费报销单
    'bTravel' => ['android/apply/submitTravel', ['method' => 'post']],//提交差旅费报销单
    'sign' => ['android/apply/sign', ['method' => 'post']],//审批签字
    'noPass' => ['android/apply/noPass', ['method' => 'post']],//不通过
    'flow' => ['android/apply/flow', ['method' => 'post']],//不通过

    '/ticket/[:name]' => ['android/ticket/index', ['method' => 'post']],//安卓票据首页
    'onlyTicket' => ['android/ticket/onlyTicket', ['method' => 'post']],//安卓票据下拉框 支持多选
    'tAllDel' => ['android/ticket/tAllDel', ['method' => 'post']],//安卓票据首页
    'sTaxi' => ['android/ticket/selectTaxi', ['method' => 'post']],//安卓出租车票查询
    'vTaxi' => ['android/ticket/saveTaxi', ['method' => 'post']],//安卓出租车票保存
    'dTaxi' => ['android/ticket/delTaxi', ['method' => 'post']],//安卓出租车票删除
    'sTrain' => ['android/ticket/selectTrain', ['method' => 'post']],//安卓火车票查询
    'vTrain' => ['android/ticket/saveTrain', ['method' => 'post']],//安卓火车票保存
    'dTrain' => ['android/ticket/delTrain', ['method' => 'post']],//安桌火车票删除
    'sPlane' => ['android/ticket/selectPlane', ['method' => 'post']],//安卓飞机票查询
    'aPlane' => ['android/ticket/allPlane', ['method' => 'post']],//安卓飞机票查询
    'vPlane' => ['android/ticket/savePlane', ['method' => 'post']],//安卓飞机票保存
    'dPlane' => ['android/ticket/delPlane', ['method' => 'post']],//安桌飞机票删除
    'sVat' => ['android/ticket/selectVat', ['method' => 'post']],//安卓增值票查询
    'vVat' => ['android/ticket/saveVat', ['method' => 'post']],//安卓增值票保存
    'dVat' => ['android/ticket/deleteVat', ['method' => 'post']],//安卓增值票删除
    'sE' => ['android/ticket/selectE', ['method' => 'post']],//安卓电子票查询
    'vE' => ['android/ticket/saveE', ['method' => 'post']],//安卓电子票保存
    'dE' => ['android/ticket/deleteE', ['method' => 'post']],//安卓电子票删除
    'sOther' => ['android/ticket/selectOther', ['method' => 'post']],//安卓其他票查询
    'vOther' => ['android/ticket/saveOther', ['method' => 'post']],//安卓其他票保存
    'dOther' => ['android/ticket/deleteOther', ['method' => 'post']],//安卓其他票删除
    'sAcc' => ['android/ticket/selectAcc', ['method' => 'post']],//安卓附件查询
    'vAcc' => ['android/ticket/saveAcc', ['method' => 'post']],//安卓附件添加
    'voAcc' => ['android/ticket/saveOtherAcc', ['method' => 'post']],//安卓其他附件添加
    'dAcc' => ['android/ticket/deleteAcc', ['method' => 'post']],//安卓附件删除
    'sEp' => ['android/ticket/accExplain', ['method' => 'post']],//安卓说明查询
    'vEp' => ['android/ticket/saveExplain', ['method' => 'post']],//安卓说明删除
    'personInfo' => ['android/ticket/personInfo', ['method' => 'post']],//请求人员信息

    'seeTaxi' => ['android/discern/taxi', ['method' => 'post']],//安卓附件删除


    'setEntrust' => ['android/Set/setEntrust', ['method' => 'post']],//设置委托人
    'sEntrust' => ['android/Set/selectEntrust', ['method' => 'post']],//查询委托人
    'sPassword' => ['android/Set/selectPassword', ['method' => 'post']],//验证密码
    'vPassword' => ['android/Set/savePassword', ['method' => 'post']],//修改密码
    'userItem' => ['android/Set/userItem', ['method' => 'post']],//默认课题号
    'sUItem' => ['android/Set/selectUserItem', ['method' => 'post']],//查询默认课题号
    'entrustAll' => ['android/Set/entrustAll', ['method' => 'post']],//查询委托人下所有人
    'selectName' => ['android/Set/selectName', ['method' => 'post']],//查询姓名

    'aLeave' => ['android/Information/addLeave', ['method' => 'post']],//添加留言
    'sLeave' => ['android/Information/selectLeave', ['method' => 'post']],//查询留言



];
