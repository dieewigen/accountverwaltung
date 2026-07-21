<?php
include_once "../inccon.php";
include_once "../inc/sv.inc.php";
include_once "det_userdata.inc.php";
include_once "../inc/serverdata.inc.php";
include_once "../functions.php";

$uid = req_int('uid');

function modpass(string $pass): string
{
    for ($i = 0; $i < 4 && $i < strlen($pass); $i++) {
        $pass[$i] = '*';
    }
    return $pass;
}

$flash = '';

//beobachter setzen (Aktions-Link mit CSRF-Token)
if ($uid > 0 && req_int('observationgo') === 1) {
    csrf_require();
    mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET observation_by = ? WHERE user_id = ?", [$det_username, $uid]);
    $flash = 'User ' . $uid . ' steht jetzt auf der Beobachtungsliste von ' . htmlspecialchars($det_username) . '.';
}

// Formular-Aktionen: jeder der drei Buttons speichert (wie früher) auch die Datenfelder
if ($uid > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    if (isset($_POST['stataktiv'])) {
        mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET acc_status = 1 WHERE user_id = ?", [$uid]);
    }
    if (isset($_POST['statgesperrt'])) {
        mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET acc_status = 2, supporter = ? WHERE user_id = ?", [$det_email, $uid]);
    }

    mysqli_execute_query(
        $GLOBALS['dbi'],
        "UPDATE ls_user SET kommentar = ?, loginname = ?, reg_mail = ?, spielername = ? WHERE user_id = ?",
        [req_str('kommentartext'), req_str('loginname'), req_str('email'), req_str('spielername'), $uid]
    );
    $flash = ($flash !== '' ? $flash . ' ' : '') . 'Daten gespeichert.';
}

$page_title = 'Userinfo';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";
include_once "inc.usertoolbar.php";

if ($flash !== '') {
    echo '<div class="flash flash-ok">' . $flash . '</div>';
}

$row = null;
if ($uid > 0) {
    $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE user_id = ?", [$uid]);
    $row = $result ? mysqli_fetch_array($result) : null;
}

if ($row) {
    echo '<form action="info.php?uid=' . $uid . '" method="post">';
    echo csrf_field();

    echo '<div class="card"><h2>Account</h2>';
    echo '<table>';
    echo '<tr><th>Account ID</th><td>' . $uid
        . ' (<a href="' . csrf_url('info.php?observationgo=1&uid=' . $uid) . '" data-confirm="User ' . $uid . ' auf die Beobachtungsliste setzen?">beobachten</a>)</td></tr>';
    echo '<tr><th>Beobachter</th><td>' . htmlspecialchars((string)$row['observation_by']) . '</td></tr>';
    echo '<tr><th>Loginname</th><td><input type="text" name="loginname" value="' . htmlspecialchars((string)$row["loginname"]) . '"></td></tr>';
    echo '<tr><th>Spielername</th><td><input type="text" name="spielername" value="' . htmlspecialchars((string)$row["spielername"]) . '"></td></tr>';
    echo '<tr><th>E-Mail</th><td><input type="text" name="email" value="' . htmlspecialchars((string)$row["reg_mail"]) . '"><br><a href="mailto:' . htmlspecialchars((string)$row["reg_mail"]) . '">' . htmlspecialchars((string)$row["reg_mail"]) . '</a></td></tr>';
    echo '<tr><th>Letzte IP</th><td><a href="sameip.php?lip=' . urlencode((string)$row["last_ip"]) . '" target="_blank" rel="noopener">' . htmlspecialchars((string)$row["last_ip"]) . '</a>
          <a href="https://apps.db.ripe.net/db-web-ui/query?searchtext=' . urlencode((string)$row["last_ip"]) . '" target="_blank" rel="noopener">[Info]</a></td></tr>';
    echo '<tr><th>Registriert</th><td>' . htmlspecialchars((string)$row["register"]) . '</td></tr>';
    echo '<tr><th>Zuletzt online</th><td>' . htmlspecialchars((string)$row["last_login"]) . '</td></tr>';
    echo '<tr><th>Logins</th><td>' . (int)$row["logins"] . '</td></tr>';
    echo '<tr><th>Passwort</th><td>' . htmlspecialchars(modpass((string)$row["pass"])) . '</td></tr>';
    echo '</table></div>';

    echo '<div class="card"><h2>Spielerstatus ver&auml;ndern</h2>';
    echo '<input type="submit" name="stataktiv" value="Aktiv" style="width:130px;"> ';
    echo '<input type="submit" name="statgesperrt" value="Gesperrt" class="btn-danger" style="width:130px;" data-confirm="User ' . $uid . ' wirklich sperren?">';
    echo '</div>';

    // altes Speicherformat: \r\n wurde teils literal abgelegt
    $kommentar = str_replace('\r\n', "\r\n", (string)$row["kommentar"]);
    echo '<div class="card"><h2>Kommentar</h2>';
    echo '<textarea name="kommentartext" id="kommentartext" cols="130" rows="16">' . htmlspecialchars($kommentar) . '</textarea><br>';
    echo '<input type="submit" name="kommentar" value="aktuelle Daten speichern">';
    echo '</div>';

    echo '</form>';

    // Serverübersicht: per RPC prüfen, auf welchen Gameservern der Account existiert
    echo '<div class="card"><h2>Server&uuml;bersicht</h2>';
    echo '<table>
          <tr>
            <th>Server</th>
            <th>Account-ID</th>
          </tr>';

    for ($i = 0; $i <= $sindex; $i++) {
        $hasaccount = doPost($serverdata[$i][6] . 'rpc.php', 'authcode=' . $GLOBALS['env_rpc_authcode'] . '&isaccount=1&id=' . $uid, $serverdata[$i][5]);

        echo '<tr>
                <td>' . $serverdata[$i][0] . ' - ' . $serverdata[$i][1] . '</td>';
        echo '<td align="center">' . htmlspecialchars((string)$hasaccount) . ' <a href="https://' . $serverdata[$i][5] . '/ourdetool/idinfo.php?UID=' . urlencode((string)$hasaccount) . '" target="_blank" rel="noopener">LINK</a></td>
              </tr>';
    }
    echo '</table></div>';
} else {
    echo '<div class="flash flash-warn">Kein User ausgew&auml;hlt.</div>';
}

include_once "inc.layout.bottom.php";
