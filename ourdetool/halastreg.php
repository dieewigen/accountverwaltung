<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

$page_title = 'Letzte Registrierungen';
$active_nav = 'lastreg';
include_once "inc.layout.top.php";

function modpass(string $pass): string
{
    for ($i = 0; $i < 4 && $i < strlen($pass); $i++) {
        $pass[$i] = '*';
    }
    return $pass;
}

echo '<table>';
echo '<tr>';
echo '<th>UserID</th>';
echo '<th>Loginname</th>';
echo '<th>Spielername</th>';
echo '<th>Vorname/Nachname</th>';
echo '<th>E-Mail</th>';
echo '<th>Passwort</th>';
echo '<th>Registriert</th>';
echo '<th>Letzter Login</th>';
echo '<th>letzte IP</th>';
echo '<th>Status</th>';
echo '<th>Logins</th>';
echo '<th>Werber-ID</th>';
echo '</tr>';

$result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user ORDER BY `user_id` DESC LIMIT 500");
while ($user = mysqli_fetch_array($result)) {
    $status = match ((int)$user["acc_status"]) {
        0 => 'Inaktiv',
        1 => 'Aktiv',
        2 => 'Gesperrt',
        3 => 'Urlaub',
        default => 'Status ' . (int)$user["acc_status"],
    };

    echo '<tr>';
    echo '<td><a href="info.php?uid=' . (int)$user["user_id"] . '" target="_blank" rel="noopener">' . (int)$user["user_id"] . '</a></td>';
    echo '<td>' . htmlspecialchars((string)$user["loginname"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["spielername"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["vorname"]) . ' ' . htmlspecialchars((string)$user["nachname"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["reg_mail"]) . '</td>';
    echo '<td>' . htmlspecialchars(modpass((string)$user["pass"])) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["register"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["last_login"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["last_ip"]) . '</td>';
    echo '<td>' . $status . '</td>';
    echo '<td class="num">' . (int)$user["logins"] . '</td>';
    echo '<td class="num">' . (int)$user["werberid"] . '</td>';
    echo '</tr>';
}
echo '</table>';

include_once "inc.layout.bottom.php";
