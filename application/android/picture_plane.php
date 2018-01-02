<?php
/*    Using "mysqli" instead of "mysql" that is obsolete.
* Utilisation de "mysqli" ?la place de "mysql" qui est obsol鑤e.
* Change the value of parameter 3 if you have set a password on the root userid
* Changer la valeur du 3e param鑤re si vous avez mis un mot de passe ?root
* 面向对象的MySQLi 语句
*/
//ini_set('max_execution_time',0); 
// header("Content-Type:application/json;charset=utf8");
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
		
		$type=3;
	   $usernumber=$_POST['uid'];
	   $tradition=$_POST['tradition'];
	   $name=$_POST['name'];
	   $name=iconv("utf-8","gb2312//IGNORE", $name);
	   
	   // $type=3;
	   // $usernumber='731';
		//给文件命名
		$sql = "select * from user where id = {$usernumber}";
		$cend = $conn->query($sql);
		$user = $cend->fetch_assoc();
		$fnum = $user['num'];
		$date = date('Y-m-d',time());
	   
		//创建文件夹
		$file_dir="/../Ifssc/public/uploads/picture/".$fnum."/".$date;
		if (!is_dir($file_dir)){  
		    $res=mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true); 
		}
		
		$file_dir=$file_dir."/".basename($_FILES['file']['name']);
		//保存图片
		$fileinfo = $_FILES['file'];
		move_uploaded_file($fileinfo['tmp_name'],$file_dir);
		//识别
		 exec ("recogPlaneDLLTest.exe   ".$file_dir." ".$name." ".$tradition." ", $info,$flag);
		//exec ("recogPlaneDLLTest.exe ".$file_dir." ".$name." 0", $info,$flag);
		
		
		
		//返回识别结果
			if($flag!=0 && $flag !=3)
		{
			$file_dir1="http://120.27.49.216:8083/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
		$usernumber=$_POST['uid'];
		$t=time();
		
		
		//票据单号
		$sql = "select * from ticket where type = 3 order by id desc limit 0,1 ";
		$cend = $conn->query($sql);
		$end = $cend->fetch_assoc();
		$sub = substr($end['ticketNo'],1,3);
		$end = ltrim ($sub, '0');
		$end = $end+1;
		$ticketNo = 'f'.sprintf("%03d", $end);
		
		$sql = "INSERT INTO ticket(ticketNo,uid,type,image,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$t}',0)";
		$conn->query($sql);
		$sql = "select * from ticket order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		$row = $getid->fetch_assoc();
		$pid=$row["id"];
		
		$sql = "UPDATE ticket SET tid='{$pid}',recogn=0 where id=$pid";
        $conn->query($sql);
		
		$sql = "INSERT INTO ticket_plane(pid)VALUES ('{$pid}')";
        $conn->query($sql);
		$sql = "select * from ticket_plane order by id desc limit 1";
		$getid = $conn->query($sql);//执行sql
		
		//假排序 order
		$sql = "select count(*) from ticket where type =3 and uid = $usernumber and tappId = 0";
        $con = $conn->query($sql);
		$row = $con->fetch_assoc();
		$order = 'f' . sprintf("%03d", $row['count(*)']);
		
		$array=array('code' => "0",'order'=>$order,'tid'=>$pid,'image'=>$file_dir1);
		echo json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		else{
			$i=0;	
		while($i<count($info)){
			$items=explode(":", $info[$i]);
			if(count($items) >= 2){
				$items[0]=iconv("GBK","UTF-8",$items[0]);
				$items[1]=iconv("GBK","UTF-8",$items[1]);
				$results[]=$items;
			}
			$i=$i+1;
		}
		//var_dump($results);die;
			
		
		$file_dir1="http://120.27.49.216:8083/uploads/picture/".$fnum."/".$date."/".basename($_FILES['file']['name']);
			
			//var_dump($results);die;
			$planeUname = $results[0][1];
			$printing = $results[1][1];
			$passengerNo = $results[2][1];
			$purchase = $results[3][1];
			$price = $results[4][1];
			$planeInsurance = $results[5][1];
			$num=(count($results)-6)/6;
			
			$dat = array();
			$arr = array('purchase'=>$purchase,'printing'=>$printing,'price'=>$price,'planeInsurance'=>$planeInsurance,'planeUname'=>$planeUname,'passengerNo'=>$passengerNo);
			
			$t=time();
			//票据单号
				$sql = "select * from ticket where type = 3 order by id desc limit 0,1 ";
				$cend = $conn->query($sql);
				$cend = $cend->fetch_assoc();
				$sub = substr($cend['ticketNo'],1,3);
				$end = ltrim ($sub, '0');
				$end = $end+1;
				$ticketNo = 'f'.sprintf("%03d", $end);
			
				$sql = "INSERT INTO ticket(ticketNo,uid,type,image,price,addTime,recogn)VALUES ('{$ticketNo}','{$usernumber}','{$type}','{$file_dir1}','{$price}','{$t}',0)";
				$con = $conn->query($sql);
				
				$sql = "select * from ticket where type = 3 order by id desc limit 0,1";
				$con = $conn->query($sql);
				$row = $con->fetch_assoc();
				$pid = $row['id'];
				//tid赋值
				$sql = "UPDATE ticket SET tid='{$pid}',recogn=1 where id=$pid";
				$ticket = $conn->query($sql);
			
			for($i=0;$i<$num;$i++){
				$planeNumber[$i] = $results[6+$i*6][1];
				$planeSlocation[$i]  = $results[7+$i*6][1];
				$planeElocation[$i]  = $results[8+$i*6][1];
				$planelevel[$i]  = $results[9+$i*6][1];
				$planedate[$i]  = $results[10+$i*6][1];
				$planeTime[$i]  = $results[11+$i*6][1].':'.$results[11+$i*6][2];
				
				//返回安卓
				$data1 = array('planeNumber'=>$planeNumber[$i],'planeSlocation'=>$planeSlocation[$i],'planeElocation'=>$planeElocation[$i],'planelevel'=>$planelevel[$i],'planedate'=>$planedate[$i],'planeTime'=>$planeTime[$i]);
				array_push($dat,$data1);
				
				if(!empty($purchase)){
					
					
					$sql = "INSERT INTO ticket_plane(purchase,printing,passengerNo,planeInsurance,planeNumber , planeUname, planeSlocation,planeElocation,planedate,planelevel,planeTime,pid)
					VALUES ('{$purchase}','{$printing}','{$passengerNo}','{$planeInsurance}','{$planeNumber[$i]}','{$planeUname}','{$planeSlocation[$i]}','{$planeElocation[$i]}','{$planedate[$i]}','{$planelevel[$i]}','{$planeTime[$i]}','{$pid}')";
					
					$plane = $conn->query($sql);
					
				}else{
					$array=array('code' => "0",'data' =>'no purchase');
					echo json_encode($array,JSON_UNESCAPED_UNICODE);
				}
				
				
				
				
			}
			
				
			
				//假排序
				$sql = "select count(*) from ticket where type =3 and uid = $usernumber and tappId = 0";
				$con = $conn->query($sql);
				$row = $con->fetch_assoc();
				$order = 'f' . sprintf("%03d", $row['count(*)']);
				
			
			
			array_push($arr,$dat);
			
			if($plane){
					if($ticket){
						
						$array=array('code' => "1",'order'=>$order,'flag'=>$flag,'image'=>$file_dir1,'tid'=>$pid,'data' =>$arr);
						// return json_encode($array,JSON_UNESCAPED_UNICODE);
						echo json_encode($array,JSON_UNESCAPED_UNICODE);
					}else{
						$array=array('code' => "00",'data' =>$results);
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
