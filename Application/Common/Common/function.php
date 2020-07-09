<?php
/**
* +---------------------------------------------------------------------
* | 楼服宝
* +---------------------------------------------------------------------
* | Copyright (c) 2015 http://www.loufubao.com  All rights reserved.
* +---------------------------------------------------------------------
* | Author: yangguang
* +---------------------------------------------------------------------
* | 公共类
*/

// 检查token
function checkToken($token, $redis, $phone) {
	// 从redis取得token，验证是否正确
	$rToken = $redis->get ( "token:$phone" );
	if ($token === $rToken) {
		return true;
	} else {
		return false;
	}
}

/**
 * 生成新token
 *
 * @param int $uid
 *        	用户ID
 * @param object $redis
 *        	redis连接对象
 * @return string &token
 *
 */
function generateToken($userCode, $redis) {
	$token = md5 ( time () );
	// redis设置token，后返回
	$redis->set ( "Token:$userCode", $token ,3600*24*365);
	return $token;
}

function em_getallheaders()
{
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}


function download_by_path($path_name, $save_name){
    ob_end_clean();
    $hfile = fopen($path_name, "rb") or die("Can not find file: $path_name\n");
    Header("Content-type: application/octet-stream");
    Header("Content-Transfer-Encoding: binary");
    Header("Accept-Ranges: bytes");
    Header("Content-Length: ".filesize($path_name));
    Header("Content-Disposition: attachment; filename=\"$save_name\"");
    while (!feof($hfile)) {
        echo fread($hfile, 32768);
    }
    fclose($hfile);
}

/**
 * 发送短信
 *
 * @param string $mobile
 *        	手机
 * @param string $content
 *        	短信内容
 * @return bool 是否成功
 */
function sendSms($mobile, $content) {
	$content = urlencode($content);
	$gate = C ( 'SMS_GATE' );
	$user = C ( 'SMS_USER' );
	$pwd = C ( 'SMS_PWD' );
	$params = "/MongateSendSubmit?userId=$user&password=$pwd&pszMobis=$mobile&pszMsg=$content&iMobiCount=1&pszSubPort=*";
	$url = $gate . $params;
	$result = cRequest ( $url );
	$result = simplexml_load_string ( $result );

	if (strlen($result[0]) > 10) {
		return true;
	} else {
		return false;
	}
}


/**
 * 查询短信余额
 *
 * @return int 余额
 */
function smsBalance() {
	$gate = C ( 'SMS_GATE' );
	$user = C ( 'SMS_USER' );
	$pwd = C ( 'SMS_PWD' );
	$params = "/MongateQueryBalance?userId=$user&password=$pwd";
	$url = $gate . $params;
	cRequest ( $url );
}

/**
 * curl请求数据
 *
 * @param string $url
 *        	请求地址
 * @param string $data
 *        	传输的数据
 * @return object $result 请求结果
 */
function cRequest($url, $data = null) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
	if (! empty ( $data )) {
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	}
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	return $result;
}
function cRequest1($url, $data = null) {
    $url = C ( 'API_URL' ) . $url;
    $data ['platform'] = 'do';
    $dataRequest ['data'] = json_encode ( $data );

    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    if (! empty ( $dataRequest )) {
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $dataRequest );
    }
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $result = curl_exec ( $ch );

    if (curl_errno ( $ch )) {
        echo 'Curl error: ' . curl_error ( $ch );
        exit ();
    }
    curl_close ( $ch );
    return json_decode ( $result, true );
}
/**
 * 解析data数据
 *
 * @param string $data
 *        	数据data
 * @return string $result 解析后的data
 */
function parseData($data) {
	$data = htmlspecialchars_decode ( $data );
	$data = json_decode ( $data, true );

	return $data;
}

   /**
    * 转换一个String字符串为byte数组
    * @param $str 需要转换的字符串
    * @param $bytes 目标byte数组
    * @author Zikie
    */

    function getBytes($str) {

       $len = strlen($str);
       $bytes = array();
          for($i=0;$i<$len;$i++) {
              if(ord($str[$i]) >= 128){
                  $byte = ord($str[$i]) - 256;
              }else{
                  $byte = ord($str[$i]);
              }
            $bytes[] =  $byte ;
       }
       return $bytes;
   }

   /**
    * 将字节数组转化为String类型的数据
    * @param $bytes 字节数组
    * @param $str 目标字符串
    * @return 一个String类型的数据
    */

   function toStr($bytes) {
       $str = '';
       foreach($bytes as $ch) {
           $str .= chr($ch);
       }

          return $str;
   }

   /**
    * 转换一个int为byte数组
    * @param $byt 目标byte数组
    * @param $val 需要转换的字符串
    * @author Zikie
    */

    function integerToBytes($val) {
       $byt = array();
       $byt[0] = ($val & 0xff);
       $byt[1] = ($val >> 8 & 0xff);
       $byt[2] = ($val >> 16 & 0xff);
       $byt[3] = ($val >> 24 & 0xff);
       return $byt;
   }

   /**
    * 从字节数组中指定的位置读取一个Integer类型的数据
    * @param $bytes 字节数组
    * @param $position 指定的开始位置
    * @return 一个Integer类型的数据
    */

    function bytesToInteger($bytes, $position) {
       $val = 0;
       $val = $bytes[$position + 3] & 0xff;
       $val <<= 8;
       $val |= $bytes[$position + 2] & 0xff;
       $val <<= 8;
       $val |= $bytes[$position + 1] & 0xff;
       $val <<= 8;
       $val |= $bytes[$position] & 0xff;
       return $val;
   }

   /**
    * 转换一个shor字符串为byte数组
    * @param $byt 目标byte数组
    * @param $val 需要转换的字符串
    * @author Zikie
    */

  function shortToBytes($val) {
       $byt = array();
       $byt[0] = ($val & 0xff);
       $byt[1] = ($val >> 8 & 0xff);
       return $byt;
   }

   /**
    * 从字节数组中指定的位置读取一个Short类型的数据。
    * @param $bytes 字节数组
    * @param $position 指定的开始位置
    * @return 一个Short类型的数据
    */

    function bytesToShort($bytes, $position) {
       $val = 0;
       $val = $bytes[$position + 1] & 0xFF;
       $val = $val << 8;
       $val |= $bytes[$position] & 0xFF;
       return $val;
   }

   /*
    * b百度提土 通过经纬度 计算2点直接按的距离 $len_type=1 单位m $len_type=2 km $decimal 保留的位数
    * */
    function getDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2){
        $EARTH_RADIUS = 6378.137;
        $PI = 3.1415926;
        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * $PI / 180.0) - ($lng2 * $PI / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 1000);
        /*if ($len_type > 1) {
            $s /= 1000;
        }*/
        if($s < 2000){

        } else if($s >= 2000 && $s <= 8000 ){
            $s = $s * 1.2;
        } else if($s > 8000){
            $s = $s * 1.4;
        }

        return round($s,$decimal);
    }

/**
 * 打印并中断程序 调试用
 *
 * @param string $data
 */
function dd(...$data)
{
    foreach ($data as $item) {
        dump($item);
    }

    die;
}

function strip_html_tags($tags,$str,$content=0){
    if($content){
        $html=array(); foreach ($tags as $tag)
        {
            $html[]='/(<'.$tag.'.*?>[\s|\S]*?<\/'.$tag.'>)/';
        }
        $data=preg_replace($html,'',$str);
    }else{
        $html=array();
        foreach ($tags as $tag)
        {
            $html[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
        }
        $data=preg_replace($html, '', $str);
    }
    return $data;
}

function dataBack($message, $status, $response,$pageinfo="") {
    $dataOut ['message'] = $message;
    $dataOut ['status'] = $status;
    if (is_array($response) && !$response ){
        $dataOut ['data'] = array();
    }else{
        $dataOut ['data'] = $response?$response:'';

    }
    if ($pageinfo){
        $dataOut ['page_info'] = $pageinfo;

    }

    die ( json_encode ( $dataOut,JSON_UNESCAPED_UNICODE ) );
}

function file_name_create($name,$title){
    return $name.'+'.$title.rand(0,1000);
}

function curl_request($url,$method='get',$data=null,$https=true){
    //1.初识化curl
    $ch = curl_init($url);
    //2.根据实际请求需求进行参数封装
    //返回数据不直接输出
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    //如果是https请求
    if($https === true){
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    }
    //如果是post请求
    if($method === 'post'){
        //开启发送post请求选项
        curl_setopt($ch,CURLOPT_POST,true);
        //发送post的数据
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    //3.发送请求
    $result = curl_exec($ch);
    //4.返回返回值，关闭连接
    curl_close($ch);
    return $result;
}


function co_curl($url, $cookies = '', $data = array(), $userHeaders = array(), $retJson = 0)
{
    while(1) {
        $urlInfo  = parse_url($url);
        $domain   = $urlInfo['host'];
        if($urlInfo['scheme'] == 'https') {
            $port = 443;
            $ssl = true;
        } else {
            $port = isset($urlInfo['port']) ? $urlInfo['port'] : 80;
            $ssl = false;
        }
        $filename = $urlInfo['path'];
        $filename .= isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '';

        $cli     = new Swoole\Coroutine\Http\Client($domain, $port, $ssl);
        $headers = [
            'Host'            => $domain,
            "User-Agent"      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ];
        if ($userHeaders) {
            $headers = array_merge($headers, $userHeaders);
            $headers = $userHeaders;
        }
        if ($cookies) {
            $headers['Cookie'] = $cookies;
        }
        $cli->setHeaders($headers);
        $cli->set(['timeout' => 60]);
        if ($data) {
            if($data == 'post') {
                $data = '';
            }
            $cli->post($filename, $data);
        } else {
            $cli->get($filename);
        }

        $body = $cli->body;
        $cli->close();

        if($cli->statusCode < 1 || ($retJson  && empty(json_decode($body, true)))) {
            // echo "\n status code:" . $cli->statusCode;
            // echo "\n body: ".$body;
            // echo "\n retry...";
        } else {
            return $body;
        }
    }
}


