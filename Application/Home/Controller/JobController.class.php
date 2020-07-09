<?php

namespace Home\Controller;

require __DIR__ . '/../../../vendor/autoload.php';


use Common\Controller\BaseController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Think\Controller;
use Parsedown;
use Dompdf\Dompdf;
use League\HTMLToMarkdown\HtmlConverter;

class JobController extends BaseController
{
    public function index()
    {



    }


    public function getDataField()
    {

        $Model = M('city');
        $Model1 = M('advert');
        $Model2 = M('station');
        $dataIn = I();

        $data1 = $Model->where('1=1')->select();
        $data2 = $Model1->where('1=1')->select();
        $data3 = $Model2->where('1=1')->select();

        foreach ($data1 as $k=>$value){
            $data1[$k]['id'] = $value['city_id'];
            $data1[$k]['name'] = $value['city_name'];
            unset($data1[$k]['city_id']);
            unset($data1[$k]['city_name']);
        }
        foreach ($data2 as $k=>$value){
            $data2[$k]['id'] = $value['advert_id'];
            unset($data2[$k]['advert_id']);
        }
        foreach ($data3 as $k=>$value){
            $data3[$k]['id'] = $value['station_id'];
            unset($data3[$k]['station_id']);
        }

        $back_data['city'] = array_values($data1);
        $back_data['advert'] = array_values($data2);
        $back_data['station'] = array_values($data3);

        $flag = $dataIn['flag'];

        if (!$flag){
            dataBack('获取成功', 0, $back_data);
        }

        dataBack('获取成功', 0, $back_data[$flag]);

    }

    public function JobInfoList()
    {

        $dataIn = I();

        $map = array();

        $artcle_model = M('zhao_pin');


        $page = intval($dataIn ['page']) ? intval($dataIn ['page']) : 1;//分页的页码

        $limit = intval($dataIn ['limit']) ? intval($dataIn ['limit']) : 10;//每页的限制数量

        $strtlimit = ($page - 1) * $limit;


        $map['status'] = 1;

        $datadictpro = $this->getDataPro();

        if ($dataIn['city_id']) {
            $map['city_id'] = $dataIn['city_id'];
        }
        if ($dataIn['advert_id']) {
            $map['advert_id'] = $dataIn['advert_id'];
        }
        if ($dataIn['station_id']) {
            $map['station_id'] = $dataIn['station_id'];
        }
        if ($dataIn['key_words']) {

            $mapro['advert_title'] = array('like', '%' . $dataIn['key_words'] . '%');
            $mapro['advert_info'] = array('like', '%' . $dataIn['key_words'] . '%');
            $mapro['advert_text'] = array('like', '%' . $dataIn['key_words'] . '%');
            $mapro['_logic'] = 'or';

            $map['_complex'] = $mapro;
        }


        $pagecount = $artcle_model->where($map)->count();

        $pageinfo = array(
            'page' => $page,
            'page_size' => $limit,
            'page_total' => ceil($pagecount / $limit),
            'page_count' => $pagecount
        );

        $artcle_list = $artcle_model->where($map)->limit($strtlimit, $limit)->select();


        foreach ($artcle_list as $key => $value) {
            $artcle_list[$key]['city'] = $this->getDataPro('city',$value['city_id']);
            $artcle_list[$key]['advert'] = $this->getDataPro('advert',$value['advert_id']);
            $artcle_list[$key]['station'] = $this->getDataPro('station',$value['station_id']);
            $artcle_list[$key]['create_time'] = date('Y-m-d', $value['create_time']);
        }

        dataBack('获取成功', 0, $artcle_list, $pageinfo);
    }

    public function jobView()
    {
        $dataIn = I();
        $datadictpro = $this->getDataPro();
        $artcle_id = intval($dataIn ['id']) ? intval($dataIn ['id']) : 0;//分页的页码

        if (!$artcle_id) {
            dataBack('id丢失 获取失败', 1, null);
        }
        $artcle_model = M('zhao_pin');


        $map['status'] = 1;
        $map['zp_id'] = $artcle_id;

        $artcle_info = $artcle_model->where($map)->find();

        if (!$artcle_info) {
            dataBack('此职位已关闭', 1, null);

        }

        $artcle_info['city'] = $this->getDataPro('city',$artcle_info['city_id']);
        $artcle_info['advert'] =$this->getDataPro('advert',$artcle_info['advert_id']);
        $artcle_info['station'] = $this->getDataPro('station',$artcle_info['station_id']);

        $artcle_info['create_time'] = date('Y-m-d', $artcle_info['create_time']);
        $artcle_info['advert_info'] = htmlspecialchars_decode($artcle_info['advert_info']);
        $artcle_info['advert_text'] = htmlspecialchars_decode($artcle_info['advert_text']);

        $conComm['zp_id'] = array('neq',$artcle_id);

        $recommont = $artcle_model->where($conComm)->limit(5)->order('rand()')->select();

        foreach ($recommont as $key=>$value){
            $recommont[$key]['city'] = $this->getDataPro('city',$value['city_id']);
            $recommont[$key]['advert'] = $this->getDataPro('advert',$value['advert_id']);
            $recommont[$key]['station'] = $this->getDataPro('station',$value['station_id']);
            $recommont[$key]['create_time'] = date('Y-m-d', $value['create_time']);
        }

        $artcle_info['recommont'] = $recommont;
        dataBack('获取成功', 0, $artcle_info);

    }

    public function getDataPro($type,$id)
    {
        $Model = M('city');
        $Model1 = M('advert');
        $Model2 = M('station');
        $dataIn = I();

        $data1 = $Model->where('1=1')->select();
        $data2 = $Model1->where('1=1')->select();
        $data3 = $Model2->where('1=1')->select();

        $back_data['city'] = array_column($data1,'city_name','city_id');
        $back_data['advert'] = array_column($data2,'name','advert_id');
        $back_data['station'] = array_column($data3,'name','station_id');

        return $back_data[$type][$id];
    }


    /**
     * 发送邮件
     *
     * @param string $to 发送给谁（邮箱）
     * @param string $subject 邮件的标题
     * @param string $content 邮件内容
     * @param array $cc 抄送列表
     * @return bool
     */
    public function sendMail($to, $subject, $content, $path=array())
    {
        require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host = 'smtp.qq.com';                         // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                                   // Enable SMTP authentication
            $mail->Username = C('SEND_EMAIL');                    // SMTP username
            $mail->Password = C('PASS_WORD');                         // SMTP password
            $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to
            $mail->CharSet = 'utf-8';

            $mail->setFrom(C('SEND_EMAIL'), '用户投递');
            $mail->addAddress($to);// Add a recipient
            if ($path){
                foreach ($path as $value){
                    $mail->addAttachment($value);         // Add attachments

                }
            }

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $content;
            $mail->AltBody = '用户投递';
            $mail->send();
            return true;
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
    public function  uploadfile(){
        $Absolute_Path=$_SERVER['SCRIPT_FILENAME'];
        $Absolute_Path=substr($Absolute_Path,0,-9);

        $dataIn = I();


        $name = $dataIn['name'];

        $zp_id = $dataIn['zp_id'];

        if (!$name || !$zp_id){
            dataBack('缺少必要参数！',1,null);
        }

        $models = M('zhao_pin');

        $map['zp_id'] = $zp_id;

        $zpinfo = $models->where($map)->find();

        $filename = $name.'+'.$zpinfo['advert_title'];

        $upload = new \Think\Upload();
        $upload->maxSize   =     10485760 ;// 设置附件上传大小
        $upload->exts      =     array('pdf');// 设置附件上传类型
        $upload->rootPath  =     './Public/Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName  =     array('uniqid',file_name_create($name,$zpinfo['advert_title'])); // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息

            dataBack('上传失败',1,$upload->getError());

        }else{
            // 上传成功
            foreach ($info as $k=>$value){
                $filepath[] = $Absolute_Path.$upload->rootPath.$value['savepath'].$value['savename'];
            }

            $ret = $this->sendMail('jiaozhongyang@bjyunrui.com',$filename,'简历在附件',$filepath);

            if ($ret){
                dataBack('上传成功',0,null);
            }
        }



    }

    public function ceshishangchuan(){
        $this->display('Job/index');
    }

    public function sendMdToEmail(){


        $dataIn = I();

        $Absolute_Path=$_SERVER['SCRIPT_FILENAME'];
        $Absolute_Path=substr($Absolute_Path,0,-9);
        $md = $dataIn['markdown'];

        if (!$md){
            dataBack('文档内容不能为空',1,null);

        }

        $name = $dataIn['name'];

        $zp_id = $dataIn['zp_id'];


        if (!$name || !$zp_id){
            dataBack('缺少必要参数！',1,null);
        }

        $models = M('zhao_pin');

        $map['zp_id'] = $zp_id;

        $zpinfo = $models->where($map)->find();


        $addInfo['content'] =$md;
        $addInfo['status'] =0;
        $addInfo['name'] =$name;
        $addInfo['zp_title'] =$zpinfo['advert_title'];


        M('cd_task_job')->add($addInfo);


        dataBack('上传成功',0,null);


//        $markdown = new MarkDowner; //实例化
//
//
//        $aa = $markdown->convertMarkdownToHtml($md);
//
//
//
//        $stylest = '<body style="font-family:simsun">'.htmlspecialchars_decode($aa).'</body>';
//        $dompdf = new Dompdf();
//        $dompdf->loadHtml($stylest);
//
//        $dompdf->setPaper('A4', 'landscape');
//
//        $dompdf->render();
//
//        $path = './Public/Uploads/'.time().'.pdf';
//        file_put_contents($path, $dompdf->output());
//
//        $filepath[] = $Absolute_Path.$path;
//
//        $this->sendMail('jiaozhongyang@bjyunrui.com','自动附件里','简历在附件',$filepath);
//
//        dataBack('上传成功',0,null);
    }


    public function cronTab(){

        $markdown = new MarkDowner; //实例化

        $dompdf = new Dompdf();

        $Absolute_Path=$_SERVER['SCRIPT_FILENAME'];
        $Absolute_Path=substr($Absolute_Path,0,-9);

        $mode = M('task_job');

        $map['status'] = 0;

        $list = $mode->where($map)->select();



        if (!$list){
            return false;
        }

        foreach ($list as $k=>$value){


            $aa = $markdown->convertMarkdownToHtml($value['content']);

            $stylest = '<body style="font-family:simsun">'.$aa.'</body>';


            $dompdf->loadHtml($stylest);

            $dompdf->setPaper('A4', 'landscape');

            $dompdf->render();


            $filename = $value['name'].'+'.$value['zp_title'];

            $path = './Public/Uploads/'.$filename.'.pdf';

            file_put_contents($path, $dompdf->output());

            $filepath[] = $Absolute_Path.$path;

            $this->sendMail('jiaozhongyang@bjyunrui.com','自动附件里','简历在附件',$filepath);

            $where['id'] = $value['id'];

            $save['status'] = 1;

            $mode->where($where)->save($save);


        }
        return true;
    }


}


class MarkDowner
{
    /**
     * @var HtmlConverter
     */
    protected $htmlConverter;

    /**
     * @var Parsedown
     */
    protected $markdownConverter;

    /**
     * Markdowner constructor.
     */
    public function __construct()
    {
        $this->htmlConverter = new HtmlConverter();

        $this->markdownConverter = new Parsedown();
    }

    /**
     * Convert Markdown To Html.
     *  markdown转换html
     * @param $markdown
     * @return string
     */
    public function convertMarkdownToHtml($markdown)
    {
        return $this->markdownConverter->setBreaksEnabled(true)->text($markdown);
    }




    /**
     * Convert Html To Markdown.
     *  html转换markdown
     * @param $html
     * @return string
     */
    public function convertHtmlToMarkdown($html)
    {
        return $this->htmlConverter->convert($html);
    }
}