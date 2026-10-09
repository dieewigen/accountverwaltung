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
    loginPerCookie();
}

//12.01.2016, die Sprache ist jetzt immer deutsch
$_SESSION['ums_language'] = 1;
$ums_language = $_SESSION["ums_language"];

include "content/de/lang/1_index.lang.php";

/*
//cookie für grafikpack
if (isset($_REQUEST["nogp"])) {
    $time = time() + 32000000;
    setcookie("cnogp", 1, $time);
}

if (!isset($_REQUEST["nogp"]) and (isset($_POST["loginname"]) or isset($_POST["pass"]))) {
    setcookie("cnogp", "", 0);
}

//cookie für mobilversion
if (isset($_REQUEST["mobi"])) {
    $time = time() + 32000000;
    setcookie("cmobi", 1, $time);
}

if (!isset($_REQUEST["mobi"]) and (isset($_POST["loginname"]) or isset($_POST["pass"]))) {
    setcookie("cmobi", "", 0);
}
*/
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
include "cssinclude.php";
?>
<title><?php echo $index_lang['title']?></title>
<meta name="theme-color" content="#05070c">

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

<META Name="keywords" Content="<?php echo $index_lang['keywords']?>">
<META Name="description" Content="<?php echo $index_lang['description']?>">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="max-age=86400">
</head>
<body>
<a class="sprung" href="#inhalt">Zum Inhalt springen</a>
<header class="kopf">
<div class="kopf-innen">
<a class="marke" href="index.php"><span class="marke-zeichen" aria-hidden="true">&infin;</span><span class="marke-spiel">Die Ewigen</span></a>
<?php
//Bereichsmarke und Spielerzahl in der Kopfleiste
include "header.php";

//menü einbinden
include "m_main.php";
?>
</div>
</header>
<main class="rahmen" id="inhalt">
<?php
$ls_eingeloggt = isset($_SESSION["ums_user_id"]) && $_SESSION["ums_user_id"] > 0;
$ls_command = isset($_REQUEST["command"]) ? $_REQUEST["command"] : '';

//die Serverliste steht als Karten frei im Rahmen, alle anderen Seiten in einem Panel
if ($ls_eingeloggt && $ls_command == "server_direct") {
    echo '<div class="serverliste">';
    $ls_panel_ende = '</div>';
} else {
    echo '<article class="seite">';
    $ls_panel_ende = '</article>';
}

//man ist eingeloggt:
if ($ls_eingeloggt) {
    //last_login updaten
    mysqli_execute_query(
        $GLOBALS['dbi'],
        "UPDATE ls_user SET last_login=NOW() WHERE user_id=?",
        [$_SESSION['ums_user_id']]
    );

    if ($_REQUEST["command"] == "logout") {
        session_destroy();

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
    } elseif ($_REQUEST["command"] == "patchnotes") {
        include "content/patchnotes.inc.php";
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
        echo '
        <script>
        window.location.href = "index.php?command=login";
        </script>';

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

//Panel bzw. Serverliste schließen
echo $ls_panel_ende;

//footer
include "footer.php";
?>
</body>
</html>
