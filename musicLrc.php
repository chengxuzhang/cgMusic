<?php
if(!isset($_GET['lrc']) || !isset($_GET['ext'])){
    echo json_encode(['status'=>400,'message'=>'参数错误！']);die;
}
$lrc = $_GET['lrc'];
$ext = $_GET['ext'];

$path =  './lryic/' . $lrc . '.lrc';
$path = iconv("utf-8","gb2312",$path);

if(file_exists($path)){
    $content = file_get_contents($path);
}else{
    $musicPath = './music/'.$lrc.'.'.$ext;
    $musicPath = iconv("utf-8","gb2312",$musicPath);
    include "getid3/getid3.php";   //引入所需要的类文件
    $getID3 = new getID3;//创建一个类的实例
    $id3infos = $getID3->analyze($musicPath);
    if(isset($id3infos['id3v2']['comments']['unsynchronised_lyric'])){
        $content = $id3infos['id3v2']['comments']['unsynchronised_lyric'][0];
        file_put_contents($path, $content);
    }else{
        // loadWebLrc($lrc);
        $res = ['status'=>404,'message'=>'暂无歌词!'];
        echo json_encode($res);die;
    }
}
preg_match_all('/\[[a-z]{2}\:(.*?)\]/is', $content, $match);
$arr = explode("\n", $content);
$data = array();
$timeTemp = array();
$dataTemp = array();
foreach ($arr as $key => $val) {
    preg_match_all('/\[[0-9]{2}\:[0-9]{2}\.[0-9]{2}\]/is', $val, $times);
    if(is_array($times[0]) && $times[0]){
        foreach ($times[0] as $n) {
            $timeLen = getTimeLen($n);
            $timeTemp[ count($timeTemp) ] = $timeLen;
            $dataTemp[$timeLen] = preg_replace('/\[[0-9]{2}\:[0-9]{2}\.[0-9]{2}\]/is', '', $val);
        }
    }
}

sortTime($timeTemp); // 排序时间
foreach ($timeTemp as $k => $v) {
    $temp = [
        'lrc' => $dataTemp[ $v ],
        'time' => $v,
    ];
    array_push($data, $temp);
}

function getTimeLen($formatTime){
    $time = str_replace(array('[',']','.'),array('','',':'),$formatTime);
    $t = explode(":", $time);
    $num0 = intval($t[0]) * 60;
    $num1 = intval($t[1]);
    $num = ($num0 + $num1) * 1000; // 毫秒
    $num2 = intval($t[2]) * 10;
    return $num + $num2;
}

function sortTime(&$timeTemp){
    $len = count($timeTemp);//6
    for($k=0;$k<=$len;$k++){
        for($j=$len-1;$j>$k;$j--){
            if($timeTemp[$j]<$timeTemp[$j-1]){
                $temp = $timeTemp[$j];
                $timeTemp[$j] = $timeTemp[$j-1];
                $timeTemp[$j-1] = $temp;
            }
        }
    }
}

/**
 * 加载网络歌词
 * @return [type] [description]
 */
function loadWebLrc($lrc){
    include "extend/curl.php";
    $res = Curl::get("http://geci.me/api/lyric/{$lrc}");
    return $res;
}

$res = ['status'=>200,'message'=>'ok','data'=>$data];
echo json_encode($res);die;