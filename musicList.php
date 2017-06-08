<?php
error_reporting(E_ALL ^ E_NOTICE);

// include "getid3/getid3.php";   //引入所需要的类文件

// $getID3 = new getID3;//创建一个类的实例

$dir="./music"; //这里输入其它路径
//PHP遍历文件夹下所有文件
$handle=opendir($dir.".");
$ext = array('mp3','ogg','wav');
$data = array();
while (false !== ($file = readdir($handle))){
    $filename = iconv("gb2312","utf-8",$file);
    if ($file != "." && $file != ".." && is_file($dir . '/' . $file)) {
        $arr = explode('.', $filename);
        if(in_array($arr[1], $ext)){
            /*
        	$id3infos = $getID3->analyze('music/'.$file);
        	if(!isset($id3infos['tags']['id3v2'])){
        		$id3infos['tags']['id3v2'] = [
                    'title' => [$arr[0]],
                    'artist'=> ['未知'],
                ];
        	}
        	array_push($data, ['filename'=>$arr[0],'id3info'=>$id3infos['tags']]);
            */
           array_push($data, ['filename'=>$arr[0],'ext'=>$arr[1]]);
        }
    }
}
closedir($handle);
$result = ['status'=>200,'message'=>'ok','data'=>$data];
echo json_encode($result);die;


