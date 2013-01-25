<?php

/*
[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
配置文件

$RCSfile: config.php,v $
$Revision: 1.58.6.5 $
$Date: 2007/06/20 19:19:13 $
*/

//--------------- 数据库设置 ------------------------------

//SupeSite数据库服务器
//SupeSite数据库服务器(一般为本地localhost)
//$dbhost = 'localhost';
$dbhost = '127.0.0.1';
//数据库用户名
$dbuser='root';
//$dbuser='admin';
//SupeSite数据库密码
//$dbpw = 'root';
$dbpw = '';
//SupeSite数据库名
$dbname = 'app';
//$dbname = 'family';
//表名前缀()
$tablepre = 'app_';

//SupeSite数据库持久连接 0=关闭, 1=打开
$pconnect = 0;
//SupeSite数据库字符集
$dbcharset = 'gbk';

//Discuz!数据库服务器
//Discuz!论坛数据库服务器
//推荐情况下,你的Discuz!论坛与SupeSite应该是使用同一台MySQL服务器,所以请保留为空
//如果你确认使用不同的MySQL服务器,请填写Discuz!论坛使用的远程MySQL服务器IP
//$dbhost_bbs = '61.186.95.117';
$dbhost_bbs = '127.0.0.1';
//Discuz!数据库用户名
$dbuser_bbs = 'root';
$dbpw_bbs = '';
//Discuz!数据库名(如果与SupeSite安装在同一个数据库，留空即可)
$dbname_bbs = 'app';
//$dbname_bbs = 'family';
//Discuz!表名前缀
$tablepre_bbs = 'app_';

//Discuz!数据库持久连接 0=关闭, 1=打开
$pconnect_bbs = 0;
//Discuz!数据库字符集
$dbcharset_bbs = 'gbk';

$dbreport = 0;//是否发送数据库错误报告? 0=否, 1=是


//--------------- 商品视频、商品展示图片、广告图片、商品封面图片存放的文件夹--------------------------

$shopvideodir = "video/";
$shopimagedir = "image/";
$adimgdir = "ad/";
$shopcoverimgdir = "cover/";
$isodir = "iso/";

//--------------- URL设置 ------------------------------

//SupeSite/X-Space程序文件所在目录的URL访问地址
//可以填写以 http:// 开头的完整URL，也可以填写相对URL。末尾不要加 /
//如果程序无法自动获取，请务必手工修改为 http://www.yourwebsite.com/supesite 形式
//$siteurl = 'http://surfingmail.cn/tv';
$siteurl = 'http://127.0.0.1/app';

//论坛URL地址
//可以填写以http://开头的完整URL，也可以填写相对URL。末尾不要加 /
$bbsurl = 'http://paike.hn.vnet.cn/ss-xs/bbs';

//论坛附件目录URL地址(为空则系统将用论坛默认附件路径，如果您修改了论坛默认附件保存目录，请设置该选项)
$bbsattachurl = '';

//--------------- COOKIE设置 ------------------------------

//Cookie前缀
$cookiepre = 'ac_';

//cookie 作用域
//注意:为了与论坛同步登录，请设置为 .yourdomain.com 形式，并同时修改论坛config.inc.php文件的cookie作用域与之相同
$cookiedomain = '';

//cookie 作用路径
$cookiepath = '/';

//--------------- 字符集设置 --------------------------------

//强制设置字符集,只乱码时使用
$headercharset = 1;
//页面字符集(可选 'gbk', 'big5', 'utf-8')
$charset = 'gbk';

//--------------- 邮件发送配置 ------------------------------

$adminemail = 'digitalfamily@163.com';//系统Email
$sendmail_silent = 1;//屏蔽邮件发送中的全部错误提示, 1=是, 0=否

//邮件发送方式
//0=不发送任何邮件
$mailsend = 2;

if($mailsend == 1) {

	//1=通过 PHP 函数及 UNIX sendmail 发送(推荐此方式)

} elseif($mailsend == 2) {

	//2=通过 SOCKET 连接 SMTP 服务器发送(支持 ESMTP 验证)
	$mailcfg = array();
	$mailcfg['server'] = 'smtp.163.com';//SMTP 服务器
	$mailcfg['port'] = '25';//SMTP 端口, 默认不需修改
	$mailcfg['auth'] = 1;//是否需要 AUTH LOGIN 验证, 1=是, 0=否
	$mailcfg['from'] = '数字家庭<digitalfamily@163.com>';//发信人地址 (如果需要验证,必须为本服务器地址)
	$mailcfg['auth_username'] = 'digitalfamily@163.com';//验证用户名
	$mailcfg['auth_password'] = '123456.';//验证密码

} elseif($mailsend == 3) {
	
	//3=通过 PHP 函数 SMTP 发送 Email(仅 win32 下有效, 不支持 ESMTP)
	$mailcfg = array();
	$mailcfg['server'] = 'smtp.126.com';// SMTP 服务器
	$mailcfg['port'] = '25';// SMTP 端口, 默认不需修改
}

//--------------- 图片相册设置 ------------------------------

//用户创建图片主题,一次可以上传的图片数目
//不宜过多,否则容易上传因超时而失败
$uploadimgpernum = 6;

//--------------- 其他系统参数 ------------------------------

//不能自动拥有空间的用户组ID黑名单
//用户在没有升级空间前,系统会自动为其开通一个以论坛为主的空间,如果您想禁止一些用户组使用本功能,请修改本数组变量
$blackgroupids = array(4,5,6,7,8);

//论坛版本(选择Discuz!论坛的版本，可选值：4, 5)
$bbsver = '5';

//数据库家庭主页缓存表分表设置
//系统会根据用户uid范围进行自动分表处理。设置为0则不启用本功能
$perspacenum = 10000;//不要随便修改

//XS免费域名服务器(不要随便修改)
$xsdomain = 'ns.supesite.com';

//RED5服务器
$red5_server = $_SERVER['SERVER_NAME'];

//socket服务器
$socket_server = $_SERVER['SERVER_NAME'];
//socket端口范围
$socket_port = mt_rand(1024, 65536);

//风格模板自动刷新开关 0=关闭, 1=打开。如果你的空间经常出现空白，建议设置为关闭。
//关闭后，你修改模板页面后，需要手工进入管理员后台=>缓存更新 进行一下模板文件缓存清空，才能看到修改的效果。
$tplrefresh = 1;

?>
