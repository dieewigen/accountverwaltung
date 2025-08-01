<?php
session_start();
$ums_language = 1; // immer deutsch
include_once "inc/header.inc.php";
//serverdaten einbinden
include_once "inc/serverdata.inc.php";

include_once "functions.php";

//////////////////////////////////////////////////////////////
// Werber-ID in die Session packen
//////////////////////////////////////////////////////////////
if (!empty($_REQUEST['a'])) {
    $_SESSION['werber_id'] = intval($_REQUEST['a']);
}

//////////////////////////////////////////////////////////////
// Login per Cookie
//////////////////////////////////////////////////////////////
if ((!isset($_SESSION['ums_user_id']) || $_SESSION['ums_user_id'] < 1) && isset($_COOKIE['cpass']) && isset($_COOKIE["cuser"])) {

    $sql = "SELECT * FROM ls_user WHERE	loginname = ? OR reg_mail = ?;";
    $result = mysqli_execute_query($GLOBALS['dbi'], $sql, [$_COOKIE["cuser"], $_COOKIE["cuser"]]);

    $num = mysqli_num_rows($result);

    $passwordOK = false;
    //wenn ein Datensatz gefunden worden ist, dann das Passwort �berpr�fen
    if ($num == 1) {
        $row = mysqli_fetch_array($result);

        //Passwort überprüfen
        if ($_COOKIE['cpass'] == MD5($row['pass'])) {
            $passwordOK = true;
        }
    }

    //wenn ein datensatz gefunden wurde, dann einloggen
    if ($passwordOK) {
        $ums_status = $row['acc_status'];
        if ($ums_status == 1) { //alles richtig, spieler einloggen
            session_regenerate_id(true);
            $_SESSION['ums_user_id'] = $row["user_id"];
            $_SESSION['ums_spielername'] = $row["spielername"];
            $_SESSION['ums_logins'] = $row["logins"];

            //schauen ob die grafikpacks deaktiviert werden sollen
            if (isset($_COOKIE["cnogp"]) && $_COOKIE["cnogp"] == 'off') {
                $_SESSION['ums_nogp'] = 1;
            } else {
                $_SESSION['ums_nogp'] = 0;
            }

            //schauen ob man die mobilversion gew�hlt hat
            if ((isset($_COOKIE["cmobi"]) && $_COOKIE["cmobi"] == 'off') || (isset($_REQUEST["mobi"]) && $_REQUEST["mobi"] == 'off')) {
                $_SESSION['ums_mobi'] = 1;
            } else {
                $_SESSION['ums_mobi'] = 0;
            }

            //ip-adresse speichern
            $ip = getenv("REMOTE_ADDR");
            $parts = explode(".", $ip);
            $ip = $parts[0].'.x.'.$parts[2].'.'.$parts[3];

            mysqli_execute_query(
                $GLOBALS['dbi'],
                "UPDATE ls_user SET last_login=NOW(), last_ip=? WHERE user_id=?",
                [$ip, $_SESSION['ums_user_id']]
            );

        }
    }
}

//12.01.2016, die Sprache ist jetzt immer deutsch
$_SESSION['ums_language'] = 1;
$ums_language = $_SESSION["ums_language"];

include "content/de/lang/1_index.lang.php";

//cookie f�r grafikpack
if (isset($_REQUEST["nogp"])) {
    $time = time() + 32000000;
    setcookie("cnogp", 1, $time);
}

if (!isset($_REQUEST["nogp"]) and (isset($_POST["loginname"]) or isset($_POST["pass"]))) {
    setcookie("cnogp", "", 0);
}

//cookie f�r mobilversion
if (isset($_REQUEST["mobi"])) {
    $time = time() + 32000000;
    setcookie("cmobi", 1, $time);
}

if (!isset($_REQUEST["mobi"]) and (isset($_POST["loginname"]) or isset($_POST["pass"]))) {
    setcookie("cmobi", "", 0);
}
?>
<!DOCTYPE HTML>
<html>
<head>
<?php
include "cssinclude.php";
?>
<title><?php echo $index_lang['title']?></title>

<link rel="manifest" href="/manifest.json">
<script type="text/javascript">
if ("serviceWorker" in navigator) {
	navigator.serviceWorker
		.register("./service-worker.js")
		.then(function() { console.log("Service Worker Registered"); });
}
</script>

<link rel="apple-touch-icon" sizes="76x76" href="/img/icon-76x76.png" />
<link rel="apple-touch-icon" sizes="120x120" href="/img/icon-120x120.png" />
<link rel="apple-touch-icon" sizes="152x152" href="/img/icon-152x152.png" />
<link rel="apple-touch-icon" sizes="180x180" href="/img/icon-180x180.png" />
<link rel="icon" sizes="192x192" href="/img/icon-192x192.png">

<meta name="viewport" content="width=device-width, initial-scale=1">
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META Name="keywords" Content="<?php echo $index_lang['keywords']?>">
<META Name="description" Content="<?php echo $index_lang['description']?>">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="max-age=86400"> 
<script src="js/jquery-3.7.1.min.js"></script>
<script src="js/ls.js"></script>
</head>
<body>
<?php

include "header.php";

//menü einbinden
include "m_main.php";

echo '<div id="content_bg">';

echo '<div id="content">';

//man ist eingeloggt:
if (isset($_SESSION["ums_user_id"]) && $_SESSION["ums_user_id"] > 0) {
    //last_login updaten
    mysqli_execute_query(
        $GLOBALS['dbi'],
        "UPDATE ls_user SET last_login=NOW() WHERE user_id=?",
        [$_SESSION['ums_user_id']]
    );

    if ($_REQUEST["command"] == "logout") {
        session_destroy();
        session_start();

        //beim Logout Cookies löschen und zur Startseite weiterleiten
		echo '
<script>
let expires = new Date();
expires.setTime(expires.getTime() - 1000);

document.cookie = "cuser=; expires=" + expires.toUTCString() + "; path=/";
document.cookie = "cpass=; expires=" + expires.toUTCString() + "; path=/";

window.location.href = "index.php";
</script>';
        exit;

    } elseif ($_REQUEST["command"] == "server_direct") {
        include "content/server_direct.inc.php";
    } elseif ($_REQUEST["command"] == "createaccount") {
        include "content/createaccount.inc.php";
    } elseif ($_REQUEST["command"] == "account") {
        include "content/account.inc.php";
    } elseif ($_REQUEST["command"] == "support" && isset($GLOBALS['env_enable_support_page']) && $GLOBALS['env_enable_support_page'] == 1) {
        include "content/support.inc.php";
    } elseif ($_REQUEST["command"] == "de_kb" && isset($GLOBALS['env_enable_de_kb_db']) && $GLOBALS['env_enable_de_kb_db'] == 1) {
        include "content/de_kb.inc.php";
    }
} else {	//man ist nicht eingeloggt
    $urlparts = explode('/', $_SERVER["REQUEST_URI"]);
    $page = $urlparts[1];
    //remove parameter
    $url_parameter = explode('?', $page);
    $page = $url_parameter[0];

    if (isset($_REQUEST["command"]) && $_REQUEST["command"] == "logout") {
        //include "content/register.inc.php";
        session_destroy();
        header("Location: index.php?command=login");
    } elseif (isset($_REQUEST["command"]) && $_REQUEST["command"] == "register") {
        include "content/register.inc.php";
    } elseif (isset($_REQUEST["command"]) && $_REQUEST["command"] == "registered") {
        include "content/registered.inc.php";
        include "content/login.inc.php";
    } elseif (isset($_REQUEST["command"]) && $_REQUEST["command"] == "login") {
        include "content/login.inc.php";
    } elseif (isset($_REQUEST["command"]) && $_REQUEST["command"] == "pwsend") {
        include "content/pwsend.inc.php";
    } else {
        //Standardseite ist Login
        include "content/login.inc.php";
    }
}

//div contentright end
echo '</div>';

//footer
include "footer.php";
?>
</body>
</html>