<?php
/*    Using "mysqli" instead of "mysql" that is obsolete.
* Utilisation de "mysqli" ?la place de "mysql" qui est obsol鑤e.
* Change the value of parameter 3 if you have set a password on the root userid
* Changer la valeur du 3e param鑤re si vous avez mis un mot de passe ?root
* 面向对象的MySQLi 语句
*/
//ini_set('max_execution_time',0); 
header("Content-Type:application/json;charset=utf8");
//链接数据库
 $servername = "127.0.0.1";
$username = "root";
$password = "root";
$conn = new mysqli($servername, $username, $password);
 
// 检测连接
if ($conn->connect_error) {
    die('Connect Error (' . $conn->connect_errno . ') '
            . $conn->connect_error);
}
else{
	//echo 'Connection OK1';
	$conn->query("set character set 'utf8'");//读库 
	$conn->query("set names utf8");//写库 
	$sql = "use ifssc";
	$conn->query($sql);
}
if(!empty($_POST['token']))
{			
		
	if(!empty($_FILES['file']))
	{
		$type=2;
	   
	   $usernumber=$_POST['uid'];
	   
	   //给文件命名
		$sql = "select * from user where id = {$usernumber}";
		$cend = $conn->query($sql);
		$user = $cend->fetch_assoc();
		$fnum = $user['num'];
		$date = date('Y-m-d',time());

		//创建文件夹
		$date = date('Y-m-d',time());
		$file_dir="D:/phpStudy/WWW/ifssc17/public/uploads/picture/".$fnum."/".$date;
		if (!is_dir($file_dir)){  
		    $res=mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true); 
		}
		
		$file_dir=$file_dir."/".basename($_FILES['file']['name']);
		//保存图片
		$fileinfo = $_FILES['file'];
		move_uploaded_file($fileinfo['tmp_name'],$file_dir);
		//识别
		//print_r($file_dir);
		exec ("TCP.exe   ".$file_dir." ".$type." ", $info,$flag);
		
		//识别结果array 操作 取出有效信息	
		$info_select=array_slice($info,-$flag-1,$flag);
		//返回识别结果
		$arr=array();
			if($flag>50||$flag<0)
		{
			$file_dir1="http://120.27.49.216:8088/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
		$usernumber=$_POST['uid'];
		$t=time();
		//票据单号
		$sql = "select * from ticket where type = 2 order by id desc limit 0,1 ";
		$cend = $conn->query($sql);
		$end = $cend->fetch_assoc();
		$sub = substr($end['ticketNo'],1,3);
		$end = ltrim ($sub, '0');
		$end = $end+1;
		$ticketNo = 'h'.sprintf("%03d", $end);
		
		$sql = "INSERT INTO ticket(ticketNo,uid,type,image,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$t}',0)";
		$conn->query($sql);
		$sql = "select * from ticket order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		$row = $getid->fetch_assoc();
		$pid=$row["id"];
		
		$sql = "UPDATE ticket SET tid='{$pid}' where id=$pid";
        $conn->query($sql);
		
		$sql = "INSERT INTO ticket_train (pid) VALUES ('{$pid}')";
        $conn->query($sql);
		$sql = "select * from ticket_train order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		//假排序 order
		$sql = "select count(*) from ticket where type =2 and uid = $usernumber  and tappId = 0";
        $con = $conn->query($sql);
		$row = $con->fetch_assoc();
		$order = 'h' . sprintf("%03d", $row['count(*)']);
		
		
		$array=array('code' => "1",'msg'=>'拒识','order'=>$order,'tid'=>$pid,'image'=>$file_dir1);
		echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		else{
		$i=0;	
		while($i<$flag){
			$items=explode("\t", $info_select[$i]);
			$items[0]=iconv("GBK","UTF-8",$items[0]);
			$items[1]=iconv("GBK","UTF-8",$items[1]);
			$results[]=$items;
			$i=$i+1;
		}
 
		//print_r($results);
			$file_dir1="http://120.27.49.216:8088/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
			//存入数据库
		$traiNnum=NULL;
		$trainUname=NULL;
		$startLocation=NULL;
		$endLocation=NULL;
		$traintime=NULL;
		$level=NULL;
		$trainsum=NULL;
		$location1=NULL;
		$location2=NULL;
		$location3=NULL ;
		$location4=NULL;
		$location5=NULL;
		$location6=NULL;
		$location7=NULL;

		//火车票调用识别接口
		$trainNum=$results[0][1];
		$startLocation=$results[1][1];
		$endLocation=$results[2][1];
		$traintime=$results[3][1];
		$trainsum=$results[4][1];
		$level=$results[5][1];
		$trainUname=$results[6][1];
		
		//重复添加火车票
		//$sql = "select * from ticket_train";
		//$taxi = $conn->query($sql);
		 //$arr=array();  
		   //while($row=$taxi->fetch_assoc()){  
			// $arr[]=$row;  
		   //}  
		//foreach($arr as $v){
			//if($trainNum == $v['trainNum'] && $traintime == $v['traintime'] && $trainUname == $v['trainUname']){
			//	$array=array('code' => "4",'msg'=>"已有票据,添加失败");
			//	echo  json_encode($array,JSON_UNESCAPED_UNICODE);die;
			//}
			
		//}   
		
		
		$usernumber=$_POST['uid'];
		$t=time();
		//票据单号
		$sql = "select * from ticket where type = 2 order by id desc limit 0,1 ";
		$cend = $conn->query($sql);
		$cend = $cend->fetch_assoc();
		$sub = substr($cend['ticketNo'],1,3);
		$end = ltrim ($sub, '0');
		$end = $end+1;
		$ticketNo = 'h'.sprintf("%03d", $end);
		
		$sql = "INSERT INTO ticket(ticketNo,uid,type,image,price,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$trainsum}','{$t}',1)";
		$ticket = $conn->query($sql);
		$sql = "select * from ticket order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		$row = $getid->fetch_assoc();
		$pid=$row["id"];
		
		$sql = "UPDATE ticket SET tid='{$pid}' where id=$pid";
        $conn->query($sql);
		$sql = "INSERT INTO ticket_train(trainNum , trainUname, startLocation,endLocation,traintime,level,trainlocation1,trainlocation2,trainlocation3,trainlocation4,trainlocation5,trainlocation6,trainlocation7,pid)
		VALUES ('{$trainNum}','{$trainUname}','{$startLocation}','{$endLocation}','{$traintime}','{$level}','{$location1}','{$location2}','{$location3}','{$location4}','{$location5}','{$location6}','{$location7}','{$pid}')";
		$train = $conn->query($sql);
		//假排序
		$sql = "select count(*) from ticket where type =2 and uid = $usernumber  and tappId = 0";
        $con = $conn->query($sql);
		$row = $con->fetch_assoc();
		$order = 'h' . sprintf("%03d", $row['count(*)']);
		
		if($train){
			if($ticket){
				$array=array('code' => "1",'msg'=>'成功','order'=>$order,'tid'=>$pid,'image'=>$file_dir1,'data' =>$results);
				echo json_encode($array,JSON_UNESCAPED_UNICODE);
			}else{
				$array=array('code' => "0",'data' =>$results);
				echo json_encode($array,JSON_UNESCAPED_UNICODE);
			}
		}else{
			$array=array('code' => "0",'data' =>$results);
			echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		}
	}
	else{
    	$respon = array('code' => "0",'msg'=>"未传入图片");
		echo json_encode($respon,JSON_UNESCAPED_UNICODE);		
	} 
}

?> 
