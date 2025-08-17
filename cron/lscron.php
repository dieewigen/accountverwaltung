<?php
set_time_limit(240);
include "../inc/sv.inc.php";
include "../inc/env.inc.php";
include "../inc/serverdata.inc.php";
include "../functions.php";
include "../inccon.php";
?>
<html>
<head>
</head>
<body>
<?php

echo 'Timestamp: '.time();

//der cronjob wird st�ndlich aufgerufen

////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
//nachricht per e-mail, wenn es offene supporttickets gibt
////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
//8,14,20 uhr
if(intval(date("H"))==8 || date("H")==14 || date("H")==20){
	$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets WHERE status=0  ORDER BY created DESC");
   	$num = mysqli_num_rows($db_daten);
   	if($num>0)
   	{
   		echo 'Supportticketreminder versendet.<br>';
   		
		$header  ='MIME-Version: 1.0' . "\r\n";
		$header .='Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$header .="FROM: noreply@die-ewigen.com" . "\r\n";
   		
		$body='<html><head><style>body{font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;font-size: 16px; 
color: #FFFFFF;background-color: #000000;} a {color: #f8ae56; text-align: center;} </style>
</head><body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<h1>Offene Supporttickets</h1>
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#000000">
<tr><td width="100%" align="center" style="background-image:url(http://login.die-ewigen.com/img/bg.jpg);">
<table cellspacing="0" cellpadding="0" width="600" border="0" style="background-image:url(http://login.die-ewigen.com/img/bgtr1.png); padding:10px;">
<tr><td align="left">';


   		   		
   		//kopf
  		$body.='<table width="100%">';
   		$body.='<tr><td>Betreff</td><td>User</td><td>erstellt</td><td>letzte &auml;nderung</td><td>Supporter</td></tr>';
    	
   		while($row = mysqli_fetch_array($db_daten))
   		{
   			$body.= '<tr>';
    		
   			$body.= '<td>'.$row['thema'].'</a></td>';
   			$body.= '<td>'.$row['user_id'].'</a></td>';
   			$body.= '<td>'.date("G:i:s d.m.Y", $row['created']).'</td>';
   			$body.= '<td>'.date("G:i:s d.m.Y", $row['modified']).'</td>';
   			if($row['supporter']=='')$status='noch keiner';else $status=$row['supporter'];
   			$body.= '<td>'.$status.'</td>';
    		
   			echo '</tr>';
   		}
   	
    	
   		$body.='</table>';
    	$body.='</td></tr></table></td></tr></table></body></html>';
		
		//info per e-mail an die supporter
		require_once '../lib/phpmailer/class.phpmailer.php';
		require_once '../lib/phpmailer/class.smtp.php';
	
		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = $GLOBALS['env_mail_server'];
		$mail->SMTPAuth = true;
		$mail->Username = $GLOBALS['env_mail_user'];
		$mail->Password = $GLOBALS['env_mail_password'];
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		
		$mail->setFrom($GLOBALS['env_mail_noreply'], 'Die Ewigen');
		$mail->addReplyTo($GLOBALS['env_mail_noreply'], 'Die Ewigen');
		$mail->addAddress('supportverteiler@die-ewigen.com');

		$mail->IsHTML(true); 
		$mail->Subject = 'BGAM.ES Supportticket-Reminder';
		$mail->Body = $body;
	
		//send the message, check for errors
		$mail->send();		
		
    	//mail('supportverteiler@die-ewigen.com', "BGAM.ES Supportticket-Reminder", $body, $header);
   	}
}

////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
//aktive Spieler in der Accountverwaltung innerhalb der letzten 7 Tage
////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
// 0 Uhr
if(intval(date("H"))==0){
	//aktive user
	$datum=date("Y-m-d".' 00:00:00', time()-3600*24*7);//innerhalb der letzten 7 Tage
	$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT COUNT(*) AS anzahl FROM ls_user WHERE logins > 0 AND last_login >= '$datum';");
	$row = mysqli_fetch_array($db_daten);
	$anzahl=$row['anzahl'];
	
	mysqli_query($GLOBALS['dbi'], "INSERT INTO `ls_user_count` ( `server` , `datum` , `anzahl` , `pa_anz` )VALUES (99, NOW(), '$anzahl', 0);");
	
}

////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
// nicht genutzte Accounts löschen
////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
// 1 Uhr
if(intval(date("H"))==1){
	//aktive user
	$datum=date("Y-m-d".' 00:00:00', time()-3600*24*7);
	mysqli_query($GLOBALS['dbi'], "DELETE FROM ls_user WHERE logins=0 AND register < '$datum';");
}



?>
</body>
</html>