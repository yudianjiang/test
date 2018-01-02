<?php
/*    Using "mysqli" instead of "mysql" that is obsolete.
* Utilisation de "mysqli" ?la place de "mysql" qui est obsol鑤e.
* Change the value of parameter 3 if you have set a password on the root userid
* Changer la valeur du 3e param鑤re si vous avez mis un mot de passe ?root
* 面向对象的MySQLi 语句
*/
//ini_set('max_execution_time',0); 
header("Content-Type:application/json;charset=utf8");
 //require 'conn.php';
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
// echo $_POST['token'];
if(!empty($_POST['token']))
{			

	if(!empty($_FILES['file']))
	{
		$type=1;
	   
	   $usernumber=$_POST['uid'];
	   
	   //给文件命名
		$sql = "select * from user where id = {$usernumber}";
		$cend = $conn->query($sql);
		$user = $cend->fetch_assoc();
		$fnum = $user['num'];
		$date = date('Y-m-d',time());
		//创建文件夹
//		$file_dir="/../Ifssc/public/uploads/picture/".$fnum."/".$date;
		$file_dir="D:/phpStudy/WWW/ifssc17/public/uploads/picture/".$fnum."/".$date;

		if (!is_dir($file_dir)){ 	
		    $res=mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true); 
		}
		$file_dir=$file_dir."/".basename($_FILES['file']['name']);
		//保存图片
		$fileinfo = $_FILES['file'];
		move_uploaded_file($fileinfo['tmp_name'],$file_dir);
		//识别
		//print_r($file_dir);die;
		exec ("TCP.exe   ".$file_dir." ".$type." ", $info,$flag);
		//识别结果array 操作 取出有效信息	
		$info_select=array_slice($info,-$flag-1,$flag);
		//$info_select=array_slice($info,-$flag-5);
 $results=array();
		//返回识别结果
		//print_r($info);
		$arr=array();
			if($flag>50||$flag<0)
		{
			$file_dir1="http://120.27.49.216:8088/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
		$type=$_POST['type'];
		$usernumber=$_POST['uid'];
		$t=time();
		//票据单号
		$sql = "select * from ticket where type = 1 order by id desc limit 0,1 ";
		$cend = $conn->query($sql);
		$end = $cend->fetch_assoc();
		$sub = substr($end['ticketNo'],1,3);
		$end = ltrim ($sub, '0');
		$end = $end+1;
		$ticketNo = 'c'.sprintf("%03d", $end);
		
		
		$sql = "INSERT INTO ticket(ticketNo,uid,type,image,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$t}',0)";
		$conn->query($sql);
		$sql = "select * from ticket order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		$row = $getid->fetch_assoc();
		$pid=$row["id"];
		
		$sql = "UPDATE ticket SET tid='{$pid}' where id=$pid";
        $conn->query($sql);
		
		$sql = "INSERT INTO ticket_taxi(pid)VALUES ('{$pid}')";
        $conn->query($sql);
		//假排序 order
		$sql = "select count(*) from ticket where type =1 and uid = $usernumber  and tappId = 0";
        $con = $conn->query($sql);
		$row = $con->fetch_assoc();
		$order = 'c' . sprintf("%03d", $row['count(*)']);
		
		$array=array('code' => "1",'msg'=>"拒识",'order'=>$order,'tid'=>$pid,'image'=>$file_dir1);
		echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		else{//识别
		$i=0;	
		while($i<$flag){
			$items=explode("\t", $info_select[$i]);
			$items[0]=iconv("GBK","UTF-8",$items[0]);
			$items[1]=iconv("GBK","UTF-8",$items[1]);
			$results[]=$items;
			$i=$i+1;
		}
 
		// print_r($results);
		// print_r($file_dir);
		$file_dir1="http://120.27.49.216:8088/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
		// print_r($file_dir1);
		//存入数据库
		$code=NULL;
		$number=NULL;
		$time=NULL;
		$sum=NULL;
		$onTime=NULL;
		$offTime=NULL;
		$location1=NULL;
		$location2=NULL;
		$location3=NULL ;
		$location4=NULL;
		$location5=NULL;
		for($i=0;$i<count($results);$i++){

			if($results[$i][0]=='发票代码'){
			$code=$results[$i][1];
			$location1=implode(" ",array_slice($results[$i],2,4));
			}
			elseif($results[$i][0]=='发票号码'){
			$number=$results[$i][1];
			$location2=implode(" ",array_slice($results[$i],2,4));
			}
			elseif($results[$i][0]=='日期'){
			$time=$results[$i][1];
			$location3=implode(" ",array_slice($results[$i],2,4));
			}
			elseif($results[$i][0]=='上车'){
			$onTime=$results[$i][1];
			}	
			elseif($results[$i][0]=='下车'||$results[$i][0]=='时间'){
			$offTime=$results[$i][1];
			$location5=implode(" ",array_slice($results[$i],2,4));
			}
			elseif($results[$i][0]=='金额'){
			$sum=$results[$i][1];
			$location4=implode(" ",array_slice($results[$i],2,4));
			}
		}
		
		//重复添加出租票
		// $sql = "select * from ticket_taxi";
		// $taxi = $conn->query($sql);
		 // $arr=array();  
		   // while($row=$taxi->fetch_assoc()){  
			 // $arr[]=$row;  
		   // }  
		// foreach($arr as $v){
			// if($number == $v['ticketNumber']){
				// $array=array('code' => "4",'msg'=>"已有票据,添加失败");
				// echo  json_encode($array,JSON_UNESCAPED_UNICODE);die;
			// }
			
		// }   
		
		$usernumber=$_POST['uid'];
		$t=time();
		//票据单号
		$sql = "select * from ticket where type = 1 order by id desc limit 0,1 ";
		$cend = $conn->query($sql);
		$end = $cend->fetch_assoc();
		$sub = substr($end['ticketNo'],1,3);
		$end = ltrim ($sub, '0');
		$end = $end+1;
		$ticketNo = 'c'.sprintf("%03d", $end);
		
		$sql = "INSERT INTO ticket(ticketNo,uid,type,image,price,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$sum}','{$t}',1)";
		$ticket = $conn->query($sql);
		$sql = "select * from ticket order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		$row = $getid->fetch_assoc();
		$pid=$row["id"];//新增id
		
		$sql = "UPDATE ticket SET tid='{$pid}' where id=$pid";
        $conn->query($sql);
		
		$sql = "INSERT INTO ticket_taxi( ticketCode , ticketNumber, ticketTime,offTime,taxilocation1,taxilocation2,taxilocation3,taxilocation4,taxilocation5,pid)
		VALUES ('{$code}','{$number}','{$time}','{$offTime}','{$location1}','{$location2}','{$location3}','{$location4}','{$location5}','{$pid}')";
		$taxi = $conn->query($sql);
		//假排序
		$sql = "select count(*) from ticket where type =1 and uid = $usernumber  and tappId = 0";
        $con = $conn->query($sql);
		$row = $con->fetch_assoc();
		$order = 'c' . sprintf("%03d", $row['count(*)']);
		 
		
		if($taxi){
			if($ticket){
				$array=array('code' => "1",'msg'=>"成功",'order'=>$order,'tid'=>$pid,'image'=>$file_dir1,'data' =>$results);
				echo json_encode($array,JSON_UNESCAPED_UNICODE);
			}else{
				$array=array('code' => "0",'msg'=>"失败",'data' =>$results);
				echo json_encode($array,JSON_UNESCAPED_UNICODE);
			}
		}else{
			$array=array('code' => "0",'msg'=>"上传失败",'data' =>$results);
			echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}

		
		}
	}
	else{
    	$respon = array('code' => "0",'msg'=>"未传入图片");
		echo json_encode($respon,JSON_UNESCAPED_UNICODE);		
	} 	
 }	else{
    	$respon = array('code' => "0",'msg'=>"未传入token");
		echo json_encode($respon,JSON_UNESCAPED_UNICODE);		
	} 	

?> 
