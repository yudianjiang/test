<?php
/*    Using "mysqli" instead of "mysql" that is obsolete.
* Utilisation de "mysqli" ?la place de "mysql" qui est obsol鑤e.
* Change the value of parameter 3 if you have set a password on the root userid
* Changer la valeur du 3e param鑤re si vous avez mis un mot de passe ?root
* 面向对象的MySQLi 语句
*/

//header("Content-Type:text/html;charset=GBK");
$servername = "127.0.0.1";
//$username = "root";
//$password ="";
$username = "root";
$password = "root";

  //-----------------------------创建连接-----------------------------
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
	$sql = "use new_db";
	$conn->query($sql);
}


?> 
