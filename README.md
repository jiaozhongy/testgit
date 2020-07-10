1. 解压目录；要求php5.6 mysql 5.6 Apache 2.4 
2. chmod  755 zhaopin-php/
3. cd zhaopin-php/Public
4. mkdir Uploads
5. chmod 755 Uploads
6. cd zhaopin-php/ 
7. composer install 
8. cd vendor/dompdf/dompdf/
9. php load_font.php simsun simsun.ttf  //这一步如果报错 那需要检查错误
10. 


#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
curl -sS --connect-timeout 10 -m 60 'http://didit.loufubao.com/home/job/cronTab'
echo "----------------------------------------------------------------------------"
endDate=`date +"%Y-%m-%d %H:%M:%S"`
echo "★[$endDate] Successful"
echo "----------------------------------------------------------------------------"

111223344
55


