<?php
namespace Home\Controller;


require __DIR__ . '/../../../vendor/autoload.php';

use mysql_xdevapi\Exception,Think\Cache\Driver\Redis;
use Think\Controller;
use Think\Model;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;




class IndexController extends Controller  {
    public function index(){
        $xml_array= simplexml_load_file(__DIR__.'/item.xml'); //读取xml文件

        $xml_array = json_decode(json_encode($xml_array),TRUE);

//        dd($xml_array['item']);
        $str = "";
        foreach($xml_array['item'] as $tmp){
            $str .= $tmp['@attributes']['id'].';'.$tmp['@attributes']['name'].'-----描述：'.$tmp['@attributes']['dec']."\n";
        }

        file_put_contents('item.txt',$str);

    }

    public function consum(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, false, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
        };

        $channel->basic_consume('hello', '', false, true, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }






    }

}



