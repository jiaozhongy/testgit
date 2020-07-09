<?php if (!defined('THINK_PATH')) exit();?>
<form action="/home/job/uploadfile" enctype="multipart/form-data" method="post" >
    <input type="text" name="name" value="方的一屁" />
    <input type="text" name="zp_id" value="1" />
    <input type="file" name="img[]" multiple="multiple" />
    <input type="submit" value="提交" >
</form>