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
			echo '<div class="hinweis hinweis-fehler">Mailer Error: ' . $mail->ErrorInfo . '</div>';
		} else {
			//echo "Message sent!";
			echo '<div class="hinweis hinweis-erfolg">'.$pwsend_lang['msg_2'].'</div><p><a class="knopf" href="index.php">'.$pwsend_lang['zumlogin'].'</a></p>';
		}
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////
		$emailhassend=1;
	}
	else echo '<div class="hinweis hinweis-fehler">'.$pwsend_lang['msg_3'].'</div>';
}
if($emailhassend < 1)
{
?>
<h1><?=$pwsend_lang['passwortanfordern']?></h1>
<p><?=$pwsend_lang['msg_4']?></p>
<form class="formular" action="index.php?command=pwsend" method="POST">
<div class="feld">
	<label for="email"><?=$pwsend_lang['emailadresse']?></label>
	<input type="text" name="email" id="email" value="" autocomplete="email">
</div>
<div class="formular-aktionen">
	<input class="knopf knopf-primaer" type="submit" name="send_pass" value="<?=$pwsend_lang['passwortanfordern']?>">
</div>
</form>
<?php
}
