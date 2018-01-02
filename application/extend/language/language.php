<?php
namespace app\extend\language;
use think\Cookie;

class language{

    public function _initialize() {
        Cookie::init(['prefix'=>'think_','expire'=>3600,'path'=>'/']);
    }

    public function lang(){
        $lang = Cookie::get('lang');
        if($lang == 'ch' || $lang == ''){
            $lang = 'chinese.xml';
        }
        if($lang == 'en'){
            $lang = "english.xml";
        }
        $keylang = "../application/extend/language/".$lang;
        if (file_exists("$keylang")) {
            $xml = simplexml_load_file("$keylang"); 
            return $xml; 
        }else {
            return '缺少语言包'; 
        }
    }

}