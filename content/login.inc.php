<?php
include 'content/de/lang/'.$ums_language.'_login.lang.php';

//logindaten überprüfen
if( (isset($_REQUEST["loginname"]) && $_REQUEST["loginname"]!='') || (isset($_REQUEST["pass"]) && $_REQUEST["pass"]!='')){


	$_REQUEST["loginname"] = strip_tags($_REQUEST["loginname"]);

	$_REQUEST["pass"] = strip_tags($_REQUEST["pass"]);

	$passwordOK=false;
	$use_newpass=false;

	//Login per Daten aus den Eingabefeldern
	$sql = "SELECT * FROM ls_user WHERE	loginname = ? OR reg_mail = ?;";
	$result = mysqli_execute_query($GLOBALS['dbi'], $sql, [$_REQUEST['loginname'], $_REQUEST['loginname']]);
	$num = mysqli_num_rows($result);

	//wenn ein Datensatz gefunden worden ist, dann das Passwort überprüfen
	if($num==1){
		$row = mysqli_fetch_array($result);

		//Passwort überprüfen
		if(password_verify(trim($_REQUEST['pass']), $row['pass']) || password_verify(trim($_REQUEST['pass']), $row['newpass'])){
			$passwordOK=true;

			//Cookies setzen
			echo '
<script>
let expires = new Date();
expires.setTime(expires.getTime() + (3600 * 24 * 360 * 1000));

document.cookie = "cuser='.$_REQUEST["loginname"].'; expires=" + expires.toUTCString() + "; path=/";
document.cookie = "cpass='.md5($row['pass']).'; expires=" + expires.toUTCString() + "; path=/";
</script>';
		}

		if(password_verify(trim($_REQUEST['pass']), $row['newpass'])){
			$use_newpass=true;
		}

	}

	//wenn ein datensatz gefunden wurde, dann einloggen
	if($passwordOK){

		$ums_status=$row["acc_status"];
		if($ums_status==1){ //alles richtig eingegen, spieler einloggen
			//Spielerdaten auswerten
			$_SESSION['ums_user_id']=$row["user_id"];
			$_SESSION['ums_spielername']=$row["spielername"];
			$_SESSION['ums_logins']=$row["logins"];

			//logins hochzählen und ip-adresse speichern
			$ip=getenv("REMOTE_ADDR");
			$parts=explode(".",$ip);
			$ip=$parts[0].'.x.'.$parts[2].'.'.$parts[3];

			mysqli_execute_query(
				$GLOBALS['dbi'],
				"UPDATE ls_user SET logins=logins+1, last_login=NOW(), last_ip=? WHERE user_id=?",
				[$ip, $_SESSION['ums_user_id']]
			);

			//hat er das alternative pw benutzt?
			if($use_newpass){
				mysqli_execute_query(
					$GLOBALS['dbi'],
					"UPDATE ls_user SET pass=newpass WHERE user_id=?",
					[$_SESSION['ums_user_id']]
				);
				mysqli_execute_query(
					$GLOBALS['dbi'],
					"UPDATE ls_user set newpass='' WHERE user_id=?",
					[$_SESSION['ums_user_id']]
				);
			}

			echo '
			<script>
			window.location.href = "index.php";
			</script>';
			exit;

		}
		elseif($ums_status==2) echo '<div class="hinweis hinweis-fehler">'.$login_lang['msg_1'].'</div>';
	}
	else echo '<div class="hinweis hinweis-fehler">'.$login_lang['msg_2'].'</div>';
}

echo '<h1>Login per E-Mail</h1>';

echo '<form class="formular" action="index.php?command=login" method="post">';

$cuser=isset($_COOKIE["cuser"]) ? $_COOKIE["cuser"] : '';
$cpass=isset($_COOKIE["cpass"]) ? $_COOKIE["cpass"] : '';

echo '
<div class="feld">
	<label for="loginname">E-Mail</label>
	<input type="text" name="loginname" id="loginname" value="'.$cuser.'" tabindex="1" autocomplete="username">
</div>
<div class="feld">
	<label for="pass">'.$login_lang['passwort'].'</label>
	<input type="password" name="pass" id="pass" value="'.$cpass.'" tabindex="2" autocomplete="current-password">
</div>
<div class="formular-aktionen">
	<input class="knopf knopf-primaer knopf-versal" type="Submit" name="login" value="'.$login_lang['login'].'">
	<a href="index.php?command=pwsend">'.$login_lang['pwvergessen'].'</a>
</div>
</form>';
?>
