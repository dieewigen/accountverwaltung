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
