<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

$okt = req_int('okt', 4);
if ($okt < 1 || $okt > 4) {
    $okt = 4;
}
// stat2 gesetzt: gesperrte Accounts ausblenden (historischer Parameter)
$stat2 = isset($_REQUEST['stat2']);

$page_title = $stat2 ? 'Multi-IP ohne gesperrte' : 'Multi-IP mit gesperrten';
$active_nav = $stat2 ? 'multi2' : 'multi1';
include_once "inc.layout.top.php";

function modpass(string $pass): string
{
    for ($i = 0; $i < 4 && $i < strlen($pass); $i++) {
        $pass[$i] = '*';
    }
    return $pass;
}

$oktlinks = '';
for ($i = 1; $i <= 4; $i++) {
    $oktlinks .= ' <a href="multi.php?okt=' . $i . ($stat2 ? '&stat2=1' : '') . '">[ ' . $i . ' ]</a>';
}
echo '<p>IP-Adressen des gleichen Oktetts:' . $oktlinks . '</p>';

// nur Accounts mit Login in den letzten 30 Tagen betrachten
$last_login = date("Y-m-d H:i:s", time() - 86400 * 30);

$sql = "SELECT SUBSTRING_INDEX(last_ip, '.', ?) AS last_ip, COUNT(last_ip) 'zaehler' FROM ls_user WHERE last_ip<>'127.0.0.1' AND last_login>? GROUP BY last_ip ORDER BY `zaehler`, last_ip DESC";
$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$okt, $last_login]);

$gesuser = 0;

while ($row = mysqli_fetch_array($db_daten)) {
    if (($row["zaehler"] > 1) && ($row["last_ip"] <> '')) {
        $z = $row["zaehler"];
        $ip = $row["last_ip"];
        $ipz = $ip;
        if ($ipz == '212.227.110.246') {
            $ipz = '!!! 1&amp;1 !!!';
        } else {
            $ipz = htmlspecialchars($ipz);
        }
        //kopf mit ip und anzahl
        echo '<h3>IP: ' . $ipz . ' &ndash; Anzahl: ' . (int)$z . '</h3>';

        echo '<table>';
        echo '<tr>';
        echo '<th>UserID</th>';
        echo '<th>Loginname</th>';
        echo '<th>E-Mail</th>';
        echo '<th>Passwort</th>';
        echo '<th>Registriert</th>';
        echo '<th>Letzter Login</th>';
        echo '<th>Status</th>';
        echo '<th>Logins</th>';
        echo '<th>Ort</th>';
        echo '</tr>';

        $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE last_ip LIKE CONCAT(?, '%') ORDER BY pass", [$ip]);
        $oldpass = null;
        while ($user = mysqli_fetch_array($result)) {
            // gleiche Passwörter (sortiert) rot markieren
            $str = ($oldpass === $user["pass"]) ? ' class="r"' : '';
            $oldpass = $user["pass"];

            // stat2: gesperrte Accounts ausblenden
            if ($stat2 && (int)$user["acc_status"] === 2) {
                continue;
            }

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
            echo '<td>' . htmlspecialchars((string)$user["reg_mail"]) . '</td>';
            echo '<td' . $str . '>' . htmlspecialchars(modpass((string)$user["pass"])) . '</td>';
            echo '<td>' . htmlspecialchars((string)$user["register"]) . '</td>';
            echo '<td>' . htmlspecialchars((string)$user["last_login"]) . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td class="num">' . (int)$user["logins"] . '</td>';
            echo '<td>' . htmlspecialchars((string)$user["ort"]) . '</td>';
            echo '</tr>';
            $gesuser++;
        }
        echo '</table>';
    }
}
echo '<p>Verd&auml;chtige: ' . $gesuser . '</p>';

include_once "inc.layout.bottom.php";
