<?php
function loginPerCookie(){
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

function doPost($uri,$postdata,$host){

	error_reporting(E_ALL);
    //echo "https://$host/$uri?$postdata";
    $ch = curl_init("https://$host/$uri?$postdata");
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //curl_setopt($ch, CURLOPT_POSTREDIR, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response=curl_exec($ch);
  
    //echo curl_error($ch);

    return $response;
}	

function utf8_encode_fix($string)
{
    return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
}

function utf8_decode_fix($string)
{
    return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
}

/**
 * Besucherzählung in ls_besuch, ohne Cookies und ohne gespeicherte IP-Adresse.
 * Je Aufruf eine Zeile: Seite, Herkunft (Host der verweisenden Seite), Kampagne (?von=…), Detail und eine
 * Besucherkennung aus Tag, IP und Browser, verschlüsselt mit dem RPC-Authcode. Sie wechselt täglich und lässt
 * sich nicht zur IP zurückrechnen. Browser mit Login-Cookie (cuser) gelten als bekannte Spieler, Crawler und
 * Linkvorschauen werden nicht gezählt. Die Hauptdomain meldet ihre Aufrufe über besuch.php,
 * die Auswertung steht im Admintool unter ourdetool/besucher.php.
 */
function besuch_zaehlen(string $seite, string $quelle = '', string $kampagne = '', string $detail = ''): void
{
    $browser = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($browser === '' || preg_match('/bot|crawl|spider|slurp|fetch|preview|monitor|scan|curl|wget|python|java|php|go-http|headless|lighthouse|pingdom|uptime|facebookexternalhit|whatsapp/i', $browser)) {
        return;
    }
    $kennung = substr(hash_hmac('sha256', date('Y-m-d') . '|' . ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $browser, (string)($GLOBALS['env_rpc_authcode'] ?? '')), 0, 16);
    mysqli_execute_query(
        $GLOBALS['dbi'],
        "INSERT INTO ls_besuch (zeit, seite, quelle, kampagne, detail, besucher, bekannt) VALUES (NOW(), ?, ?, ?, ?, ?, ?)",
        [substr($seite, 0, 20), substr($quelle, 0, 100), substr($kampagne, 0, 30), substr($detail, 0, 30), $kennung, isset($_COOKIE['cuser']) ? 1 : 0]
    );
}

/** Host der verweisenden Seite für die Besucherzählung, leer ohne Referrer oder bei eigenen Domains. */
function besuch_quelle(): string
{
    $host = strtolower((string)parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST));
    return str_ends_with($host, 'die-ewigen.com') ? '' : $host;
}

/** Kampagnenkennung aus ?von=… (Kleinbuchstaben, Ziffern, - und _, bis 30 Zeichen), sonst leer. */
function besuch_kampagne(string $wert): string
{
    return preg_match('/^[a-z0-9_-]{1,30}$/', $wert) ? $wert : '';
}
