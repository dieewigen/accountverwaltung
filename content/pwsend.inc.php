<?php
include 'content/de/lang/'.$ums_language.'_pwsend.lang.php';

$emailhassend=0;
if( (isset($_POST["email"]) && $_POST["email"]) ){ //schauen ob was eingegeben worden ist
	$email=$_POST["email"];
	$email = strip_tags($email);

	$sql="SELECT user_id, loginname, reg_mail, vorname, nachname FROM ls_user WHERE reg_mail = ? LIMIT 1;";

	$result=mysqli_execute_query($GLOBALS['dbi'], $sql, [$email]);
	$num = mysqli_num_rows($result);

	if($num==1){ //user existiert
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		//alternativpasswort setzen und versenden

		//neues pw generieren
		$pwstring='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$newpass=$pwstring[rand(0, strlen($pwstring)-1)];
		for($i=1; $i<=6; $i++) $newpass.=$pwstring[rand(0, strlen($pwstring)-1)];
		
		$newpass_crypt=password_hash($newpass, PASSWORD_DEFAULT);		

		//passwort in db eintragen
		$uid=$row["user_id"];
		mysqli_execute_query(
			$GLOBALS['dbi'],
			"UPDATE ls_user set newpass=? WHERE user_id=?",
			[$newpass_crypt, $uid]
		);
		//passwort versenden
		$text=utf8_decode($pwsend_lang['msg_1']);

		//Paswort und Login-Name eintragen
		$text=str_replace("{LOGIN}",$row["reg_mail"],$text);
		$text=str_replace("{PASS}",$newpass,$text);
		$text=str_replace("{EMAIL}",$row["reg_mail"],$text);
		//////////////////////////////////////////////////////
		//mail Senden:
		//////////////////////////////////////////////////////
		require 'lib/phpmailer/class.phpmailer.php';
		require 'lib/phpmailer/class.smtp.php';

		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = $GLOBALS['env_mail_server'];
		$mail->SMTPAuth = true;
		$mail->Username = $GLOBALS['env_mail_user'];
		$mail->Password = $GLOBALS['env_mail_password'];
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;

		$mail->setFrom($GLOBALS['env_mail_noreply'], 'Die Ewigen');
		//Set an alternative reply-to address
		$mail->addReplyTo($GLOBALS['env_mail_noreply'], 'Die Ewigen');
		//Set who the message is to be sent to
		$mail->addAddress($row["reg_mail"]);
		//Set the subject line
		$mail->Subject = $pwsend_lang['passwortanforderung'];
		$mail->Body = $text;

		//send the message, check for errors
		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			//echo "Message sent!";
			echo '<br><font size="2" color="#00FF00">'.$pwsend_lang['msg_2'].'<br><br><a href="index.php">'.$pwsend_lang['zumlogin'].'</a>';
		}
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////
		$emailhassend=1;
	}
	else echo '<br><font size="2" color="#FF0000">'.$pwsend_lang['msg_3'].'</font>';
}
if($emailhassend < 1)
{
?>
<form action="index.php?command=pwsend" method="POST">
<div style="width: 100%; max-width: 650px; margin-left: auto; margin-right: auto;">
<table width="100%" border="0" cellpadding="3" cellspacing="0">
<tr align="center">
<td colspan="2"><b><?=$pwsend_lang['passwortanfordern']?></b></td>
</tr>
<tr align="center">
<td colspan="2"><?=$pwsend_lang['msg_4']?></td>
</tr>

<tr align="center">
<td><?=$pwsend_lang['emailadresse']?></td>
<td><input type="text" name="email" value=""></td>
</tr>

<tr align="center">
<td colspan="2"><input type="submit" name="send_pass" value="<?=$pwsend_lang['passwortanfordern']?>"></td>
</tr>
</table>
</div>
</form>
<?php
}
