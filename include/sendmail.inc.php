<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	邮件发送文件

	$RCSfile: sendmail.inc.php,v $
	$Revision: 1.6 $
	$Date: 2007/01/24 09:44:06 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if($sendmail_silent) {
	error_reporting(0);
}

$maildelimiter = !empty($maildelimiter) ? "\r\n" : "\n";
$mailusername = isset($mailusername) ? $mailusername : 1;

if(empty($_SCONFIG['sitename'])) $_SCONFIG['sitename'] = 'SupeSite/X-Space';
$email_subject = '=?'.$charset.'?B?'.base64_encode(str_replace("\r", '', str_replace("\n", '', '['.$_SCONFIG['sitename'].'] '.$email_subject))).'?=';
$email_message = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $email_message)))))));

$email_from = $email_from == '' ? '=?'.$charset.'?B?'.base64_encode($bbname)."?= <$adminemail>" : (preg_match('/^(.+?) \<(.+?)\>$/',$email_from, $from) ? '=?'.$charset.'?B?'.base64_encode($from[1])."?= <$from[2]>" : $email_from);

foreach(explode(',', $email_to) as $touser) {
	$tousers[] = preg_match('/^(.+?) \<(.+?)\>$/',$touser, $to) ? ($mailusername ? '=?'.$charset.'?B?'.base64_encode($to[1])."?= <$to[2]>" : $to[2]) : $touser;
}
$email_to = implode(',', $tousers);

$headers = "From: $email_from{$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/plain; charset=$charset{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";

if($mailsend == 1 && function_exists('mail')) {

	@mail($email_to, $email_subject, $email_message, $headers);

} elseif($mailsend == 2) {

	if(!$fp = fsockopen($mailcfg['server'], $mailcfg['port'], $errno, $errstr, 30)) {
		errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) CONNECT - Unable to connect to the SMTP server, please check your \"config.php\".", 0);
	}
 	stream_set_blocking($fp, true);

	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != '220') {
		errorlog('SMTP', "$mailcfg[server]:$mailcfg[port] CONNECT - $lastmessage", 0);
	}

	fputs($fp, ($mailcfg['auth'] ? 'EHLO' : 'HELO')." SupeSite\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
		errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) HELO/EHLO - $lastmessage", 0);
	}

	while(1) {
		if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
 			break;
 		}
 		$lastmessage = fgets($fp, 512);
	}

	if($mailcfg['auth']) {
		fputs($fp, "AUTH LOGIN\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) AUTH LOGIN - $lastmessage", 0);
		}

		fputs($fp, base64_encode($mailcfg['auth_username'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) USERNAME - $lastmessage", 0);
		}

		fputs($fp, base64_encode($mailcfg['auth_password'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235) {
			errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) PASSWORD - $lastmessage", 0);
		}

		$email_from = $mailcfg['from'];
	}

	fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) MAIL FROM - $lastmessage", 0);
		}
	}

	$email_tos = array();
	foreach(explode(',', $email_to) as $touser) {
		$touser = trim($touser);
		if($touser) {
			fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250) {
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) RCPT TO - $lastmessage", 0);
			}
		}
	}

	fputs($fp, "DATA\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 354) {
		errorlog('SMTP', "($mailcfg[server]:$mailcfg[port]) DATA - $lastmessage", 0);
	}

	$headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: Discuz! Mailer{$maildelimiter}";

	fputs($fp, "Date: ".gmdate('r')."\r\n");
	fputs($fp, "To: ".$email_to."\r\n");
	fputs($fp, "Subject: ".$email_subject."\r\n");
	fputs($fp, $headers."\r\n");
	fputs($fp, "\r\n\r\n");
	fputs($fp, "$email_message\r\n.\r\n");
	fputs($fp, "QUIT\r\n");

} elseif($mailsend == 3) {

	ini_set('SMTP', $mailcfg['server']);
	ini_set('smtp_port', $mailcfg['port']);
	ini_set('sendmail_from', $email_from);

	@mail($email_to, $email_subject, $email_message, $headers);

}

?>