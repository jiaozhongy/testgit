<?php
namespace Home\Controller;

use Common\Controller\BaseController;
use mysql_xdevapi\Exception;
use Think\Controller;
use Think\Log;

class PdaController extends BaseController
{



    public function CheckcodeDeduplication(){

        $table = M('tray');
        $params = I('get.');
        $code = $params['code'];

        $where['left_code'] = $code;
        $where['right_code'] = $code;

        $where['_logic'] = 'or';
        $info = $table->where($where)->find();

        if ($info){
            dataBack('编码重复，请重新扫码','1',null);
        }

        dataBack('编码未重复','0',null);
    }

    public function getTrayByCode(){
        $params = I('get.');

        $table = M('tray');

        $savedata['code'] = $params['code'];
        $savedata['code_write'] = $params['code_write'];


        $checkl = substr($params['code'],0,17);



        if (!$checkl && !$savedata['code_write']){
            dataBack('code非法','1',null);
        }


        if ($savedata['code_write'] && !$checkl){

            $info = $table->where(array('code_check'=>$savedata['code_write']))->find();

            if (!$info){
                $view= $this->trayviewsself(209);

                dataBack('没有这个托盘 请输入正确编号','1',$view);
            }
            $view= $this->trayviewsself($info['id']);

            dataBack('获取成功','0',$view);


        }
        $where['left_code'] = $savedata['code'] ;
        $where['right_code'] = $savedata['code'] ;

        $where['_logic'] = 'or';
        $info = $table->where($where)->find();


        if (!$info){
            $view= $this->trayviewsself(209);

            dataBack('没有这个托盘 请添加','1',$view);
        }



        $view= $this->trayviewsself($info['id']);


        dataBack('获取成功','0',$view);

    }

    public function codeAdaptation(){
        $params = I('get.');

        $code = $params['code'];

        $tid = $params['tid'];

        $table = M('tray');

        $info = $table->where(array('id'=>$tid))->find();

        if (!$info){
            dataBack('没有这个托盘','1',null);
        }

        $checkl = substr($params['code'],0,17);

        if (!$checkl){
            dataBack('code不合法','1',null);

        }

        $retcode = $this->convsCode($code,$info['code_check']);

        Log::write($retcode,'INFO');

        dataBack('转化成功','0',$retcode);
    }

    public function getTrayIdByMacId(){
        $params = I('get.');

        $macaddress = $params['mac'];

        $table = M('tray');

        $source = $this->generate_code(12);

        $savedata['left_code'] = "";

        $savedata['right_code'] = "";

        $savedata['depot_id'] = 0;

        $savedata['create_time'] = time();

        $savedata['code_check'] = $source;

        $savedata['mac_address'] = $macaddress;

        $newId = $table->add($savedata);

        $ret['tid'] = $newId;

        $ret['code'] = $source;

        if ($newId){

            dataBack('添加成功','0',$ret);
        }

        dataBack('添加失败','1',null);

    }

    public function getTrayIdByMacIdAuto($macaddress='auto'){


        $table = M('tray');

        $source = $this->generate_code(12);

        $savedata['left_code'] = "";

        $savedata['right_code'] = "";

        $savedata['depot_id'] = 0;

        $savedata['create_time'] = time();

        $savedata['code_check'] = $source;

        $savedata['mac_address'] = $macaddress;

        $newId = $table->add($savedata);

        $ret['tid'] = $newId;

        $ret['code'] = $source;

        return $ret;


    }
    function generate_code($length = 5) {

//        $table = M('tray');

//        $numberlast = $table->order('code_check desc')->find();
        $number = S('tray_number');

        if (!$number){
            $number = 10158;
        }else{

            $number = $number;
        }

        S('tray_number',$number+1,0);

        return $number;
    }

    public function convsCode($old,$num){

        $str1 = '00000'.$num.'00';

        $strArray=str_split($str1);

        for ($i=0;$i<count($strArray);$i++){
            if ($i%2 == 1){
                $strArray[$i] = $strArray[$i].' ';
            }
        }
        $str = implode('',$strArray);

        $new = substr($str,0,strlen($str)-1);

        $checkl = substr($old,0,17);


         return str_replace($checkl,$new,$old);

    }

    public function convsCode1($num){

        $str1 = '00000'.$num.'00';

        $strArray=str_split($str1);

        for ($i=0;$i<count($strArray);$i++){
            if ($i%2 == 1){
                $strArray[$i] = $strArray[$i].' ';
            }
        }
        $str = implode('',$strArray);

        $new = substr($str,0,strlen($str)-1);

        return $new;

    }


    public function codeSaveNew(){

        $params = I('get.');

        $table = M('tray');

        $tid = $params['tid'];

        $ret['tid'] = 0;
        $ret['code'] = 0;

        $info = $table->where(array('id'=>$tid))->find();

        if ($params['left_code']){
            $savedata['left_code'] = $params['left_code'];
            $checkl = substr($params['left_code'],0,17);

        }
        if ($params['right_code']){

            $savedata['right_code'] = $params['right_code'];
            $checkr = substr($params['right_code'],0,17);

        }
        $savedata['depot_id'] = 0;

        $info['code_check'] = $this->convsCode1($info['code_check']);

        if ($checkl && $checkl!=$info['code_check']){
            dataBack('左边码不对，请检查','1',$ret);
        }

        if ($checkr && $checkr!=$info['code_check']){
            dataBack('右边码不对，请检查','1',$ret);
        }


        try{

            $newid = $table->where(array('id'=>$tid))->save($savedata);


        }catch (\Exception $e){

            dataBack('添加异常','1',$ret);

        }

//        if (!$newid){
//            dataBack('添加失败','1',$ret);
//        }

        $ret = $this->getTrayIdByMacIdAuto();

        dataBack('保存编码成功','0',$ret);

    }

    public function codeSave(){
        $params = I('get.');

        $savedata['left_code'] = $params['left_code'];
        $savedata['right_code'] = $params['right_code'];
        $savedata['depot_id'] = 0;

        $savedata['create_time'] = time();
        $table = M('tray');

        $checkl = substr($params['left_code'],0,17);

        $checkr = substr($params['right_code'],0,17);


        if ($checkl != $checkr && $checkl!=''){
            dataBack('左右标签编码不同，左边'.$checkl.'右边'.$checkr,'1',null);
        }



        $savedata['code_check'] = $checkl;

        $info = $table->where(array('code_check'=>$checkl))->find();

        if ($info){
            dataBack('标签码重复，请重新扫','1',null);
        }


        $where['left_code'] = $params['left_code'];
        $where['right_code'] = $params['right_code'];

        $where['_logic'] = 'or';
        $info = $table->where($where)->find();

        if ($info){

            dataBack('标签码重复，请更换','1',null);
        }

        try{

            $newid = $table->add($savedata);


        }catch (\Exception $e){

            dataBack('添加异常','1',$e->getMessage());

        }

        if (!$newid){
            dataBack('添加失败','1',null);
        }
        dataBack('添加成功','0',$newid);

    }

    public function depotSave(){
        $params = I('get.');

        $savedata['depot_id'] = $params['depot_id'];
        $tid = $params['tid'];

        $table = M('tray');

        if (!$params['depot_id']){
            dataBack('没有库id','1',null);

        }
        if (!$tid){
            dataBack('没有tid','1',null);

        }

        try{

            $newid = $table->where(array('id'=>$tid))->save($savedata);


        }catch (\Exception $e){

            dataBack('保存异常','1',$e->getMessage());

        }

        if (!$newid && $newid!=0){
            dataBack('保存失败','1',null);
        }
        dataBack('保存成功','0',$newid);

    }

    public function inventorydelete(){
        $params = I('get.');
        $id = $params['inventory_id'];

        if (!$id){
            dataBack('缺失参数inventory_id','1',null);
        }
        $tableinve = M('tray_inventory');

        $info = $tableinve->where(array('id'=>$id))->find();

        if (!$info){
            dataBack('没有这个id的信息，请检查','1',null);
        }

        $ret  = $tableinve->where(array('id'=>$id))->delete();
    if ($ret){
    dataBack('删除成功','0',null);

    }else{
    dataBack('删除失败','1',null);

    }

    }

    public function inventoryTraySave(){
        $params = I('get.');

        $sid = $params['sid'];
        $main = $params['main_amount'];
        $sec = $params['sec_amount'];
        $tid = $params['tid'];

        if (!$sid || !$tid){
            dataBack('缺失参数tid 或者 sid','1',null);
        }

        $tableinve = M('tray_inventory');


        $savedata['main_amount'] = $main;
        $savedata['sec_amount'] = $sec;
        $savedata['sid'] = $sid;
        $savedata['tid'] = $tid;

        $where['sid'] = $sid;
        $where['tid'] = $tid;

        $iset = $tableinve->where($where)->find();



        try{
            if (!$iset){
               $aa =  $tableinve->add($savedata);
            }else{
               $aa =  $tableinve->where(array('id'=>$iset['id']))->save($savedata);
            }
        }catch (\Exception $e){

            dataBack('保存异常','1',$e->getMessage());

        }
if ($aa || $aa==0){
    dataBack('保存成功','0',json_encode($savedata));

}else{
    dataBack('保存失败','1',json_encode($savedata));

}
    }

    public function inventoryGet(){

        $dicttable = M('inventory');
        $table1 = M('tray_inventory');

        $words = I('get.key_words')?I('get.key_words'):'';
        $tid = I('get.tid')?I('get.tid'):0;

        $keywords['name'] = array('like','%'.$words.'%');

        $keywords['scode'] = array('like','%'.$words.'%');
        $keywords['_logic'] = 'or';


        if (!$words){
            $listinfo  = $dicttable->where($keywords)->limit(5)->select();

        }else{
            $listinfo  = $dicttable->where($keywords)->select();

        }


        if ($tid>0){
            foreach ($listinfo as $key=>$value){

                $where['sid'] = $value['sid'];
                $where['tid'] = $tid;
                $invoiceinfo = $table1->where($where)->find();
                if ($invoiceinfo){
                   $listinfo[$key]['main_amount'] = $invoiceinfo['main_amount'];
                   $listinfo[$key]['sec_amount'] = $invoiceinfo['sec_amount'];
                }

            }
        }

        if (!$listinfo){
            $listinfo = array();
        }

        dataBack('获取成功','0',$listinfo);
    }

    public function traylist(){

        $dataIn = I('get.');

        $page = intval($dataIn ['page']) ? intval($dataIn ['page']) : 1;//分页的页码

        $limit = intval($dataIn ['limit']) ? intval($dataIn ['limit']) : 10;//每页的限制数量

        $strtlimit = ($page - 1) * $limit;

        $table = M('tray');

        $map = '1=1';
        $pagecount = $table->where($map)->count();

        $pageinfo = array(
            'page' => $page,
            'page_size' => $limit,
            'page_total' => ceil($pagecount / $limit),
            'page_count' => $pagecount
        );

        $tlists = $table->where($map)->limit($strtlimit, $limit)->select();

        dataBack('获取成功', 0, $tlists, $pageinfo);

    }

    public function trayviews(){
        $id = I('get.id');

        if (!$id){
            dataBack('缺失参数id','1',null);
        }

        $table = M('tray');
        $tableinve = M('tray_inventory');
        $depotmodel = M('depot');

        $view = $table->where(array('id'=>$id))->find();

        $deinfo = $depotmodel->where(array('id'=>$view['depot_id']))->find();
        $deinfo_p = $depotmodel->where(array('id'=>$deinfo['pid']))->find();
        $deinfo_p1 = $depotmodel->where(array('id'=>$deinfo_p['pid']))->find();


        if ($view['depot_id']==0){
            $view['depot_name'] = '未选择仓库';

        }else{
            $view['depot_name'] = $deinfo_p1['name'].'-'.$deinfo_p['name'].'-'.$deinfo['name'];

        }

        $vlists = $tableinve->join('bd_inventory ON bd_inventory.sid = bd_tray_inventory.sid')->where(array('tid'=>$id))->select();


//        if ($vlists){
//            foreach ($vlists as $key=>$value){
//                $vlists[$key]['main_amount'] = $value['main_amount'].$value['kg'];
//                $vlists[$key]['sec_amount'] = $value['sec_amount'].$value['uname'];
//            }
//        }

        $view['inventory'] = $vlists;

        dataBack('获取成功','0',$view);

    }


    public function trayviewsself($id){

        if (!$id){
            dataBack('缺失参数id','1',null);
        }

        $table = M('tray');
        $tableinve = M('tray_inventory');
        $depotmodel = M('depot');

        $view = $table->where(array('id'=>$id))->find();

        $deinfo = $depotmodel->where(array('id'=>$view['depot_id']))->find();
        $deinfo_p = $depotmodel->where(array('id'=>$deinfo['pid']))->find();
        $deinfo_p1 = $depotmodel->where(array('id'=>$deinfo_p['pid']))->find();


        if ($view['depot_id']==0){
            $view['depot_name'] = '未选择仓库';

        }else{
            $view['depot_name'] = $deinfo_p1['name'].'-'.$deinfo_p['name'].'-'.$deinfo['name'];

        }

        $vlists = $tableinve->join('bd_inventory ON bd_inventory.sid = bd_tray_inventory.sid')->where(array('tid'=>$id))->select();

//        if ($vlists){
//            foreach ($vlists as $key=>$value){
//                $vlists[$key]['main_amount'] = $value['main_amount'].$value['kg'];
//                $vlists[$key]['sec_amount'] = $value['sec_amount'].$value['uname'];
//            }
//        }


        $view['inventory'] = $vlists;

        return $view;
    }
    public function depotList(){

        $table = M('depot');

        $list = $table->select();

        $tree = $this->tree($list);


        dataBack('获取成功','0',$tree);

    }


    public function kuweitianjia(){

        $table = M('depot');

        $aa = $table->where('pid !=0')->select();


        foreach ($aa as $k=>$v){
            for ($i=1;$i<5;$i++){
                $aa['name'] = $i;
                $aa['pid'] = $v['id'];
                $table->add($aa);
            }
        }
    }



    public function tree($data,$p_id=0){
        foreach($data as $row){
            if($row['pid']==$p_id){
                $tmp = $this->tree($data,$row['id']);
                if($tmp){
                    $row['child']=$tmp;
                }else{
                    $row['leaf'] = true;
                }
                $tree[]=$row;
            }
        }
        Return array_values($tree);
    }

    public function getyello(){

        $keyword = I('get.text');

        $model  = M('videos');
        $url = 'http://m.se04.xyz/index/Get/search';

        $data['page']  = 1;
        $data['title']  = $keyword;

        $list = curl_request($url,'post',$data);

        $list = json_decode($list,true);

        $array = array();
        foreach ($list['data']['videos'] as $item) {

            $item['out_id'] = $item['id'];
            $aa= $model->where(array('out_id'=>$item['id']))->find();

            if ($aa){
                continue;
            }

            unset($item['id']);


            $array[] = $item;
        }

        $model->addAll($array);

    }


    public function getmp4(){

        $model = M('videos');

        $info = $model->where(['out_id'=>1])->find();
        $url = $info['link'];

        if(!file_exists('./tmp/')) {
            if(!mkdir('./tmp/')) {
                die('请手动在当前目录创建tmp目录');
            }
        }

        $indexPage = file_get_contents($url);
        preg_match_all('/.*\.ts/', $indexPage, $matches);
        if(empty($matches)) {
            die('m3u8 文件格式错误');
        }

        go(function() use($matches) {
            $chan = new chan(100); //最大并发数
            foreach($matches['0'] as $key => $value) {
                if(file_exists('./tmp/'.$key.'.ts')) {
                    continue;
                }
                $chan->push('xx');
                go(function() use($key, $value, $chan) {
                    echo "\nAdd task:".$key;
                    while(1) {
                        $rs = co_curl($value);
                        if(strlen($rs) > 0) {
                            file_put_contents('./tmp/'.$key.'.ts', $rs);
                            break;
                        }
                    }
                    echo "\nTask ok:".$key;
                    $chan->pop();
                });
            }
            //确保所有下载已经完成
            for($i = 0; $i < 100; $i++) {
                $chan->push('over');
            }
            //合并文件
            foreach ($matches['0'] as $key => $value) {
                file_put_contents('out.mp4', file_get_contents('./tmp/'.$key.'.ts'), FILE_APPEND);
                unlink('./tmp/'.$key.'.ts');
            }
            echo "\n 下载完成，转换成功 (out.mp4)";
        });


    }

}
