<?php

/*
[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
�����ļ�

$RCSfile: config.php,v $
$Revision: 1.58.6.5 $
$Date: 2007/06/20 19:19:13 $
*/

//--------------- ���ݿ����� ------------------------------

//SupeSite���ݿ������
//SupeSite���ݿ������(һ��Ϊ����localhost)
//$dbhost = 'localhost';
$dbhost = '127.0.0.1';
//���ݿ��û���
$dbuser='root';
//$dbuser='admin';
//SupeSite���ݿ�����
//$dbpw = 'root';
$dbpw = '';
//SupeSite���ݿ���
$dbname = 'app';
//$dbname = 'family';
//����ǰ׺()
$tablepre = 'app_';

//SupeSite���ݿ�־����� 0=�ر�, 1=��
$pconnect = 0;
//SupeSite���ݿ��ַ���
$dbcharset = 'gbk';

//Discuz!���ݿ������
//Discuz!��̳���ݿ������
//�Ƽ������,���Discuz!��̳��SupeSiteӦ����ʹ��ͬһ̨MySQL������,�����뱣��Ϊ��
//�����ȷ��ʹ�ò�ͬ��MySQL������,����дDiscuz!��̳ʹ�õ�Զ��MySQL������IP
//$dbhost_bbs = '61.186.95.117';
$dbhost_bbs = '127.0.0.1';
//Discuz!���ݿ��û���
$dbuser_bbs = 'root';
$dbpw_bbs = '';
//Discuz!���ݿ���(�����SupeSite��װ��ͬһ�����ݿ⣬���ռ���)
$dbname_bbs = 'app';
//$dbname_bbs = 'family';
//Discuz!����ǰ׺
$tablepre_bbs = 'app_';

//Discuz!���ݿ�־����� 0=�ر�, 1=��
$pconnect_bbs = 0;
//Discuz!���ݿ��ַ���
$dbcharset_bbs = 'gbk';

$dbreport = 0;//�Ƿ������ݿ���󱨸�? 0=��, 1=��


//--------------- ��Ʒ��Ƶ����ƷչʾͼƬ�����ͼƬ����Ʒ����ͼƬ��ŵ��ļ���--------------------------

$shopvideodir = "video/";
$shopimagedir = "image/";
$adimgdir = "ad/";
$shopcoverimgdir = "cover/";
$isodir = "iso/";

//--------------- URL���� ------------------------------

//SupeSite/X-Space�����ļ�����Ŀ¼��URL���ʵ�ַ
//������д�� http:// ��ͷ������URL��Ҳ������д���URL��ĩβ��Ҫ�� /
//��������޷��Զ���ȡ��������ֹ��޸�Ϊ http://www.yourwebsite.com/supesite ��ʽ
//$siteurl = 'http://surfingmail.cn/tv';
$siteurl = 'http://127.0.0.1/app';

//��̳URL��ַ
//������д��http://��ͷ������URL��Ҳ������д���URL��ĩβ��Ҫ�� /
$bbsurl = 'http://paike.hn.vnet.cn/ss-xs/bbs';

//��̳����Ŀ¼URL��ַ(Ϊ����ϵͳ������̳Ĭ�ϸ���·����������޸�����̳Ĭ�ϸ�������Ŀ¼�������ø�ѡ��)
$bbsattachurl = '';

//--------------- COOKIE���� ------------------------------

//Cookieǰ׺
$cookiepre = 'ac_';

//cookie ������
//ע��:Ϊ������̳ͬ����¼��������Ϊ .yourdomain.com ��ʽ����ͬʱ�޸���̳config.inc.php�ļ���cookie��������֮��ͬ
$cookiedomain = '';

//cookie ����·��
$cookiepath = '/';

//--------------- �ַ������� --------------------------------

//ǿ�������ַ���,ֻ����ʱʹ��
$headercharset = 1;
//ҳ���ַ���(��ѡ 'gbk', 'big5', 'utf-8')
$charset = 'gbk';

//--------------- �ʼ��������� ------------------------------

$adminemail = 'digitalfamily@163.com';//ϵͳEmail
$sendmail_silent = 1;//�����ʼ������е�ȫ��������ʾ, 1=��, 0=��

//�ʼ����ͷ�ʽ
//0=�������κ��ʼ�
$mailsend = 2;

if($mailsend == 1) {

	//1=ͨ�� PHP ������ UNIX sendmail ����(�Ƽ��˷�ʽ)

} elseif($mailsend == 2) {

	//2=ͨ�� SOCKET ���� SMTP ����������(֧�� ESMTP ��֤)
	$mailcfg = array();
	$mailcfg['server'] = 'smtp.163.com';//SMTP ������
	$mailcfg['port'] = '25';//SMTP �˿�, Ĭ�ϲ����޸�
	$mailcfg['auth'] = 1;//�Ƿ���Ҫ AUTH LOGIN ��֤, 1=��, 0=��
	$mailcfg['from'] = '���ּ�ͥ<digitalfamily@163.com>';//�����˵�ַ (�����Ҫ��֤,����Ϊ����������ַ)
	$mailcfg['auth_username'] = 'digitalfamily@163.com';//��֤�û���
	$mailcfg['auth_password'] = '123456.';//��֤����

} elseif($mailsend == 3) {
	
	//3=ͨ�� PHP ���� SMTP ���� Email(�� win32 ����Ч, ��֧�� ESMTP)
	$mailcfg = array();
	$mailcfg['server'] = 'smtp.126.com';// SMTP ������
	$mailcfg['port'] = '25';// SMTP �˿�, Ĭ�ϲ����޸�
}

//--------------- ͼƬ������� ------------------------------

//�û�����ͼƬ����,һ�ο����ϴ���ͼƬ��Ŀ
//���˹���,���������ϴ���ʱ��ʧ��
$uploadimgpernum = 6;

//--------------- ����ϵͳ���� ------------------------------

//�����Զ�ӵ�пռ���û���ID������
//�û���û�������ռ�ǰ,ϵͳ���Զ�Ϊ�俪ͨһ������̳Ϊ���Ŀռ�,��������ֹһЩ�û���ʹ�ñ�����,���޸ı��������
$blackgroupids = array(4,5,6,7,8);

//��̳�汾(ѡ��Discuz!��̳�İ汾����ѡֵ��4, 5)
$bbsver = '5';

//���ݿ��ͥ��ҳ�����ֱ�����
//ϵͳ������û�uid��Χ�����Զ��ֱ�������Ϊ0�����ñ�����
$perspacenum = 10000;//��Ҫ����޸�

//XS�������������(��Ҫ����޸�)
$xsdomain = 'ns.supesite.com';

//RED5������
$red5_server = $_SERVER['SERVER_NAME'];

//socket������
$socket_server = $_SERVER['SERVER_NAME'];
//socket�˿ڷ�Χ
$socket_port = mt_rand(1024, 65536);

//���ģ���Զ�ˢ�¿��� 0=�ر�, 1=�򿪡������Ŀռ侭�����ֿհף���������Ϊ�رա�
//�رպ����޸�ģ��ҳ�����Ҫ�ֹ��������Ա��̨=>������� ����һ��ģ���ļ�������գ����ܿ����޸ĵ�Ч����
$tplrefresh = 1;

?>
