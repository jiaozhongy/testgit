<?php
/**
 * Created by PhpStorm.
 * User: shounakayou
 * Date: 2020-06-27
 * Time: 13:08
 */

namespace APP\Model;

use Think\Model;

class VideoModel extends Model{
    protected $trueTableName = 'adtrunk_action';
    protected $pk = 'id';

    public function select($options = array())
    {
        return parent::select($options); // TODO: Change the autogenerated stub
    }
}
