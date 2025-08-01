<?php

include 'content/de/lang/'.$ums_language.'_register.lang.php';

$fehlermsg = '';

echo '<div id="register-page">';

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

            // Aktivierungs-Link
            $text = $newreg_lang['regmailbody'];
            //Paswort und Login-Name eintragen
            $text = str_replace("{SPIELER}", utf8_decode_fix($spielername), $text);
            $text = str_replace("{LOGIN}", $email1, $text);
            $text = str_replace("{PASS}", $newpass, $text);

            //mail Senden:

            require_once 'lib/phpmailer/class.phpmailer.php';
            require_once 'lib/phpmailer/class.smtp.php';

            $mail = new PHPMailer();

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
            $mail->Body = $text;

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

echo '<form action="index.php?command=register" method="post">';

//echo '<h1>'.$newreg_lang['accountregistrierung'].'</h1>';

if (isset($fehlermsg) && $fehlermsg != '') {
    echo '<font color="#CC0000">'.$fehlermsg.'</font><br><br>';
}


/////////////////////////////////////////////////////////////////
// Registrierung per E-Mail
/////////////////////////////////////////////////////////////////
echo '
<div class="box-right">
	<h2>Registrierung</h2>
';

//Email 1
echo '<div>'.$newreg_lang['email'].':</div>';
echo '<div class="mt5"></div>';
echo '<div><input type="text" name="email1" size="30" maxlength="100" value="'.$email1.'"></div>';

//Spielername
echo '<div class="mt15"></div>';
echo '<div>'.$newreg_lang['spielername'].':</div>';
echo '<div class="mt5"></div>';
echo '<div><input type="text" name="spielername" size="30" maxlength="20" value="'.$spielername.'"></div>';

//Country - dient als Honeypot, wenn dort Werte eingetragen werden, wird der Account nicht angelegt
echo '
<div class="mt15 country">
    <div>Country:</div>
    <div class="mt5"></div>
    <div><input type="text" name="country" size="30" maxlength="20" value="'.$country.'"></div>
</div>';


//AGB/Datenschutz
echo '<div class="mt15">
<input type="Checkbox" ';
if ($agb == "1") {
    echo "checked";
}
echo ' name="agb" value="1">'.$newreg_lang['agb1'].' 
	<a href="https://portal.die-ewigen.com/agb.php" target="_blank">'.$newreg_lang['agb2'].'</a> und die <a href="https://portal.die-ewigen.com/datenschutz.php" target="_blank">Datenschutzerkl&auml;rung. Ich bin 16 Jahre oder &auml;lter, bzw. habe die Erlaubnis meiner/meines Erziehungsberechtigten.</a>
</div>';

echo '<div class="mt15"></div>';
echo '		<input class="btn1" type="Submit" name="newreg" value="'.$newreg_lang['registrieren'].'">

			</div>
	
		</form>
	<div style="font-weight: bold; color: #CC0000; margin-top: 20px; width: 100%; text-align: center;">'.$newreg_lang['hinweis'].'</div>
</div>';
