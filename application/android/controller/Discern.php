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
use think\File;
use app\extend\common\common;

Class discern
{

    //出租车识别
    public function taxi(){
//        $token = input('post.token');
//        if(!empty($token)){
//            $user = db('user')->where('token',$token)->find();
//            if(!$user) {
//                return json(['data' => 'error', 'code' => 0, 'message' => '别瞎嘚瑟']);
//            }
//        }

        // 获取表单上传文件 例如上传了001.jpg
//            $files = request()->file('file');
//            $type = input('post.type');
//            $uid = input('post.uid');
//            $num = input('post.num');
//            $uid = 2;
//            $type = 1;
//            if(!empty($files)){
//                $new = date('Y-m-d',time()).mt_rand(100,999);//重命名
                // 移动到框架应用根目录/public/uploads/ 目录下
              //  $file = $files->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'picture');

                //识别
               // $file_dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'picture'.$file->getSaveName();
                $file_dir = "Img_temp.jpg";
                exec ("TCP.exe   ".$file_dir." 1 ", $info,$flag);
//                exec ("./../Android/texts_new/TCP.exe 2>&1", $info,$flag);
//                识别结果array 操作 取出有效信息
                $info_select=array_slice($info,-$flag-1,$flag);
                //返回识别结果
                dump($info);
                dump($flag);
                dump($info_select);die;


//             $arr=array();
//                if($flag>50||$flag<0)
//                {
//                    $file_dir1="http://120.27.49.216:8082/texts_new2/"."image_temp/".$usernumber."/".basename($_FILES['file']['name']);
//                    //添加数据库
//                    $ticket = db('ticket')->order('id desc')->limit(1)->find();
//                    $tid = $ticket['id']+1;//tid
//                    $addTime =  time();//添加时间
////                $price = $info['price'];//金额
//                    $price = 2;//金额
//                    $image = $file_dir.'\\'.$file->getSaveName();
//
//                    $data = ['uid' => $uid, 'tid' => $tid,'type'=>$type,'addTime'=>$addTime,'price'=>$price,'image'=>$image];
//                    $add = db('ticket')->insert($data);//主表
//
//                    $sql = "INSERT INTO ticket_taxi(taxiUnumber,taxiimage,taxistate,pid)VALUES ('{$usernumber}','{$file_dir1}',0,'{$pid}')";
//                    $conn->query($sql);
//                    $sql = "select * from ticket_taxi order by id desc limit 1";
//                    $getid = $conn->query($sql);//执行sql
//                    $row = $getid->fetch_assoc();
//                    if($type==1||$type==-1) $c='c';
//                    if($type==2||$type==-2) $c='h';
//                    if($type==3||$type==-3) $c='f';
//                    if($type==4||$type==-4) $c='z';
//                    if($type==5||$type==-5) $c='d';
//                    if($type==6||$type==-6) $c='o';
//                    $name=$c.$row["id"];
//                    $sql="UPDATE ticket_taxi SET taxiTname='{$name}' WHERE id='".$row["id"]."'";
//                    $conn->query($sql);
//                    $sql="UPDATE ticket SET ticketNo='{$name}' WHERE id='{$pid}'";
//                    $conn->query($sql);
//                    $array=array('code' => "1",'msg'=>"拒识",'name'=>$name,'image'=>$file_dir1);
//                    echo json_encode($array,JSON_UNESCAPED_UNICODE);
//                }
//
//
//            }else{
//                return json(['data' => '未传入图片', 'code' => 0, 'message' => '失败']);
//            }


    }






}