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

Class Pay
{

    //发送请求
    public function send(){
        //token验证
        $uid = input('post.uid');
        $token = input('post.token');
//        if(!empty($token)){
//            $checkToken = cache::get('token');
//            if($checkToken[0] != $uid || $checkToken[1] != $token){
//                return json(['data' => 'error', 'code' => 0, 'message' => '验证超时']);
//            }
//        }else{
//            return json(['data' => 'error', 'code' => 2, 'message' => '别瞎嘚瑟']);
//        }
//
        $id = input('post.id');//报销单id
        $accountNo = input('post.accountNo');//转出账号
        $toAccountName = input('post.toAccountName');//转入账户名
        $toAccountNo = input('post.toAccountNo');//转入账号
        $amount = input('post.amount');//余额，发生额
        $itemNo = input('post.itemNo');//余额，发生额
//        $bxd = db('t_bx_d_bxd')->where('id',$id)->find();

        $y = date('Y',time());
        $m = date('m',time());
        $d = date('d',time());
        $h = date('H',time());
        $i = date('i',time());
        $s = date('s',time());

        $count = db('send_bank')->order('id desc')->find();
        $end = substr($count['TransCode'],-6);
        $trim = ltrim($end,0);
        $TransCode = 'b2e'.sprintf("%06d", $trim+1);
        $TransCode = 'b2e004001';

        $end2 = substr($count['BatchID'],-8);
        $trim2 = ltrim($end2,0);
        $BatchID = '2450517057'.$y.$m.$d.sprintf("%08d", $trim2+1);

        $JnlDate = $y.$m.$d;
        $JnlTime = $h.$i.$s;
        $end3 = substr($count['ClientPatchID'],-4);
        $trim3 = ltrim($end3,0);
        $ClientPatchID = $JnlDate.$JnlTime.sprintf("%04d", $trim3+1);
        $note = '差旅费';
        $note = iconv("UTF-8", "GBK", $note);
        $toAccountName = '中科院自动化研究所';
        $toAccountName = iconv("UTF-8", "GBK", $toAccountName);
        $toBank = '中国光大银行';
        $toBank = iconv("UTF-8", "GBK", $toBank);
        $transferType = '2122';
        $accountNo = '35500188067883131';
        $toAccountNo = '35500188067883049';

//        dump($BatchID);die;
        $xmldata =  "<?xml version=\"1.0\" encoding=\"GBK\"?>
<Transaction>
	<SystemHead>
		<Language>zh_CN</Language>
		<Encodeing></Encodeing>
		<Version></Version>
		<ServiceName></ServiceName>
		<CifNo>2450517057</CifNo>
		<UserID>002</UserID>
		<SyMacFlag></SyMacFlag>
		<MAC></MAC>
		<SyPinFlag></SyPinFlag>
		<PinSeed></PinSeed>
		<LicenseId></LicenseId>
		<Flag></Flag>
		<Note></Note>
	</SystemHead>
	<TransHead>
		<TransCode>$TransCode</TransCode>
		<BatchID>$BatchID</BatchID>
		<JnlDate>$JnlDate</JnlDate>
		<JnlTime>$JnlTime</JnlTime>
	</TransHead>
	<TransContent>
		<ReqData>
		<ClientPatchID>$ClientPatchID</ClientPatchID>
		<transferType>$transferType</transferType>
<beyondDealFlag></beyondDealFlag>
<accountNo>$accountNo</accountNo>
		<toAccountName>$toAccountName</toAccountName>
	    <toAccountNo>$toAccountNo</toAccountNo>
		<toBank>$toBank</toBank>
		<amount>$amount</amount>
		<toLocation></toLocation>
		<clientSignature></clientSignature>
		<checkNo></checkNo>
		<checkPassword></checkPassword>
		<note>$note</note>
		<noteOther></noteOther>
		<bankNo></bankNo>
		<isUrgent>0</isUrgent>
		<cellphone></cellphone>
		<perOrEnt>0</perOrEnt>
		<IsAudit></IsAudit>
		<matchRule></matchRule>
		</ReqData>
	</TransContent>
</Transaction>";

        $data = ['TransCode'=>$TransCode,'BatchID'=>$BatchID,'JnlDate'=>$JnlDate,'JnlTime'=>$JnlTime,'ClientPatchID'=>$ClientPatchID,'transferType'=>$transferType,'accountNo'=>$accountNo,'toAccountName'=>$toAccountName,'toAccountNo'=>$toAccountNo,'toBank'=>$toBank,'amount'=>$amount,'note'=>$note,'itemNo'=>$itemNo,'bxdId'=>$id];
        $insert = db('send_bank')->insert($data);

        $url = "http://127.0.0.1:8000/ent/b2e004001.do?usrID=2000013811&userPassword=123456&Sigdata=1";


        $header[] = "Content-type:text/xml";//定义content-type为xml
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
        $response = curl_exec($ch);
        $xml=simplexml_load_string($response);
        $data = json_decode(json_encode($xml),TRUE);
        if (array_key_exists("error",$data))
        {
            return json(['data' => 'error', 'code' => 0, 'message' => '交易错误']);
        }

        if($insert){
            $send = db('send_bank')->order('id desc')->find();
            $data1 = ['ReturnCode'=>$data['TransContent']['ReturnCode'],'ReturnMsg'=>$data['TransContent']['ReturnMsg'],'ReturnNote'=>$data['TransContent']['ReturnNote']];
            $update = db('send_bank')->where('id',$send['id'])->update($data1);
            if($update){
                return json(['data' => 'success', 'code' => 1, 'message' => '成功']);
            }else{
                return json(['data' => 'error', 'code' => 0, 'message' => '失败2']);
            }

        }else{
            return json(['data' => 'error', 'code' => 0, 'message' => '失败']);
        }
        if(curl_errno($ch))
        {
            print curl_error($ch);
        }
        curl_close($ch);






    }







}