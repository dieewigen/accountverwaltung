<?php

include 'content/de/lang/'.$ums_language.'_register.lang.php';

$fehlermsg = '';

function is_email($email)
{

    //check e-mail for right format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //if format ok, check for domain-blacklist
        //load domain-blacklist
        $blacklist = array();
        $handle = @fopen("inc/mogelmails.csv", "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $blacklist[] = trim($buffer);
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        //print_r($blacklist);

        $list = explode("@", $email);
        $domainpart = trim($list[1]);

        if (in_array($domainpart, $blacklist)) {
            return(0);
        } else {
            return(1);
        }
    } else {
        return(0);
    }
}

$spielername = isset($_REQUEST['spielername']) ? $_REQUEST['spielername'] : '';
$email1 = isset($_REQUEST['email1']) ? $_REQUEST['email1'] : '';
$agb = isset($_REQUEST['agb']) ? intval($_REQUEST['agb']) : 0;

$country = isset($_REQUEST['country']) ? $_REQUEST['country'] : '';


if (isset($_REQUEST['newreg'])) {

    $leeresfeld = 0;
    //schauen ob die daten korrekt eingegeben worden sind
    if ($spielername != '') {
        if (!preg_match("/^[[:alpha:]0-9äöü_=-]*$/i", $spielername)) {
            $fehlermsg .= $newreg_lang['fehlermsg1'];
        } else {
            $db_daten = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id FROM ls_user where spielername= ?", [$spielername]);
            $vorhanden = mysqli_num_rows($db_daten);
            if ($vorhanden > 0) {
                $fehlermsg .= '<br>'.$newreg_lang['fehlermsg2'];
            }
        }
    } else {
        $leeresfeld = 1;
    }

    if ($email1 != '') {
        if (is_email($email1) == 0) {
            $fehlermsg .= '<br>'.$newreg_lang['fehlermsg6'];
        } else {
            //hier noch schauen ob es sie schon gibt

            $db_daten = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id FROM ls_user where reg_mail= ?", [$email1]);
            $vorhanden = mysqli_num_rows($db_daten);
            if ($vorhanden > 0) {
                $fehlermsg .= '<br>'.$newreg_lang['fehlermsg7'];
            }
        }
    } else {
        $leeresfeld = 1;
    }

    if ($agb != 1) {
        $fehlermsg .= '<br>'.$newreg_lang['fehlermsg19'];
    }

    if ($leeresfeld == 1) {
        $fehlermsg .= '<br>'.$newreg_lang['fehlermsg20'];
    }

    if ($fehlermsg == '') {
        //hier kommt das einfügen der accountdaten rein
        $ip = getenv("REMOTE_ADDR");
        $parts = explode(".", $ip);
        $ip = $parts[0].'.x.'.$parts[2].'.'.$parts[3];


        //neues pw generieren
        $pwstring = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ123456789';
        $newpass = $pwstring[rand(0, strlen($pwstring) - 1)];
        for ($i = 1; $i <= 6; $i++) {
            $newpass .= $pwstring[rand(0, strlen($pwstring) - 1)];
        }

        /*
        if($tag==$newreg_lang['tag']) $tag=0;
        if($monat==$newreg_lang['monat']) $monat=0;
        if($jahr==$newreg_lang['jahr']) $jahr=0;
        */
        $tag = 0;
        $monat = 0;
        $jahr = 0;
        $geschlecht = 0;

        //Passwort verschlüsseln
        $newpass_crypt = password_hash($newpass, PASSWORD_DEFAULT);


        //daten in der db ablegen
        if(empty($country)){
            $sql = "INSERT INTO ls_user (
                loginname, reg_mail, pass,
                register, last_login, acc_status,
                last_ip, spielername
            ) VALUES (
                ?, ?, ?,
                NOW(), NOW(), 1,
                ?, ?
            )";
            mysqli_execute_query($GLOBALS['dbi'], $sql, [$email1, $email1, $newpass_crypt, $ip, $spielername]);

            //registrierungs-email versenden
            $loginurl = 'https://login.die-ewigen.com/';

            //Spielername, Login, Passwort und Loginseite eintragen
            $platzhalter = array(
                '{SPIELER}' => $spielername,
                '{LOGIN}' => $email1,
                '{PASS}' => $newpass,
                '{LOGINURL}' => $loginurl,
            );
            $text = strtr($newreg_lang['regmailbody'], $platzhalter);
            //in der HTML-Fassung die eingesetzten Werte escapen
            $html = strtr($newreg_lang['regmailbody_html'], array_map(fn($wert) => htmlspecialchars($wert, ENT_QUOTES, 'UTF-8'), $platzhalter));

            //HTML-Rahmen wie bei den Mails aus cron/lscron.php
            $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>body{font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;font-size: 16px;
color: #FFFFFF;background-color: #000000;} a {color: #f8ae56;} </style>
</head><body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0" bgcolor="#000000">
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#000000">
<tr><td width="100%" align="center" style="padding: 20px 10px; background-image:url('.$loginurl.'img/bg.jpg);">
<table cellspacing="0" cellpadding="0" width="600" border="0" style="width: 100%; max-width: 600px; background-image:url('.$loginurl.'img/bgtr1.png);">
<tr><td align="left" style="padding: 20px; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.4; color: #FFFFFF;">
'.$html.'
</td></tr></table></td></tr></table></body></html>';

            //mail Senden:

            require_once 'lib/phpmailer/class.phpmailer.php';
            require_once 'lib/phpmailer/class.smtp.php';

            $mail = new PHPMailer();
            $mail->CharSet = 'UTF-8';

            $mail->isSMTP();
            $mail->Host = $GLOBALS['env_mail_server'];
            $mail->SMTPAuth = true;
            $mail->Username = $GLOBALS['env_mail_user'];
            $mail->Password = $GLOBALS['env_mail_password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($GLOBALS['env_mail_noreply'], 'Die Ewigen');
            $mail->addReplyTo($GLOBALS['env_mail_noreply'], 'Die Ewigen');
            $mail->addAddress($email1, utf8_decode_fix(' '));
            $mail->Subject = $newreg_lang['regmailbetreff'];
            $mail->isHTML(true);
            $mail->Body = $body;
            $mail->AltBody = $text;

            //send the message, check for errors
            $mail->send();
        }

		echo '
		<script>
		window.location.href = "index.php?command=registered";
		</script>';
        exit;
    }
}

echo '<h1>Registrierung</h1>';

if (isset($fehlermsg) && $fehlermsg != '') {
    //führende Zeilenumbrüche aus den aneinandergehängten Meldungen entfernen
    echo '<div class="hinweis hinweis-fehler">'.preg_replace('/^(<br>)+/', '', $fehlermsg).'</div>';
}


/////////////////////////////////////////////////////////////////
// Registrierung per E-Mail
/////////////////////////////////////////////////////////////////
echo '<form class="formular" action="index.php?command=register" method="post">';

//Email 1
echo '<div class="feld">
	<label for="email1">'.$newreg_lang['email'].'</label>
	<input type="text" name="email1" id="email1" maxlength="100" value="'.$email1.'" autocomplete="email">
</div>';

//Spielername
echo '<div class="feld">
	<label for="spielername">'.$newreg_lang['spielername'].'</label>
	<input type="text" name="spielername" id="spielername" maxlength="20" value="'.$spielername.'">
</div>';

//Country - dient als Honeypot, wenn dort Werte eingetragen werden, wird der Account nicht angelegt
echo '<div class="feld country">
	<label for="country">Country:</label>
	<input type="text" name="country" id="country" maxlength="20" value="'.$country.'" tabindex="-1" autocomplete="off">
</div>';


//AGB/Datenschutz
echo '<div class="feld-check">
	<input type="Checkbox" id="agb" ';
if ($agb == "1") {
    echo "checked";
}
echo ' name="agb" value="1">
	<label for="agb">'.$newreg_lang['agb1'].'
	<a href="'.$GLOBALS['env_url_datenschutz'].'" target="_blank">'.$newreg_lang['agb2'].'</a> und die <a href="'.$GLOBALS['env_url_datenschutz'].'" target="_blank">Datenschutzerkl&auml;rung</a>. Ich bin 16 Jahre oder &auml;lter, bzw. habe die Erlaubnis meiner/meines Erziehungsberechtigten.</label>
</div>';

echo '<div class="formular-aktionen">
	<input class="knopf knopf-primaer" type="Submit" name="newreg" value="'.$newreg_lang['registrieren'].'">
</div>
</form>
<div class="hinweis hinweis-warn abstand-oben">'.$newreg_lang['hinweis'].'</div>';
