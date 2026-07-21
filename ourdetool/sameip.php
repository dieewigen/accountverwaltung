<?php
include_once "../inccon.php";
include_once "../inc/sv.inc.php";
include_once "det_userdata.inc.php";

$lip = req_str('lip');

$page_title = 'Gleiche IP';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";

function modpass(string $pass): string
{
    for ($i = 0; $i < 4 && $i < strlen($pass); $i++) {
        $pass[$i] = '*';
    }
    return $pass;
}

//kopf mit ip
echo '<h3>IP: ' . htmlspecialchars($lip) . '</h3>';

echo '<table>';
echo '<tr>';
echo '<th>User ID</th>';
echo '<th>Loginname</th>';
echo '<th>Spielername</th>';
echo '<th>E-Mail</th>';
echo '<th>Passwort</th>';
echo '<th>Registriert</th>';
echo '<th>Letzter Login</th>';
echo '<th>Status</th>';
echo '<th>Logins</th>';
echo '</tr>';

$gesuser = 0;
$result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE last_ip = ? ORDER BY pass", [$lip]);
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
    echo '<td>' . htmlspecialchars((string)$user["reg_mail"]) . '</td>';
    echo '<td>' . htmlspecialchars(modpass((string)$user["pass"])) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["register"]) . '</td>';
    echo '<td>' . htmlspecialchars((string)$user["last_login"]) . '</td>';
    echo '<td>' . $status . '</td>';
    echo '<td class="num">' . (int)$user["logins"] . '</td>';
    echo '</tr>';
    $gesuser++;
}
echo '</table>';
echo '<p>' . $gesuser . ' Spieler mit der selben IP gefunden</p>';

include_once "inc.layout.bottom.php";
