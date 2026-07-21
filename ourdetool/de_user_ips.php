<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";
include_once "log_dbconnect.php";

$uid = req_int('uid');

$page_title = 'IP-Adressen';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";
include_once "inc.usertoolbar.php";

echo '<p class="dim">Hier werden nur die IP-&Auml;nderungen und nicht die kompletten Zugriffe angezeigt.</p>';

echo '
<table>
<tr>
<th>IP-Adresse</th>
<th>Uhrzeit</th>
</tr>';

//alle ips laden, nur Wechsel ausgeben
$ipadresse = '127.0.0.1';
$result = mysqli_execute_query(
    $GLOBALS['dbi_log'],
    "SELECT time, ip FROM gameserverlogdata WHERE serverid = ? AND userid = ? ORDER BY time DESC",
    [$sv_servid, $uid]
);
while ($row = mysqli_fetch_array($result)) {
    if ($ipadresse != $row["ip"]) {
        echo '<tr><td>' . htmlspecialchars((string)$row["ip"]) . '</td><td>' . htmlspecialchars((string)$row["time"]) . '</td></tr> ';
        $ipadresse = $row["ip"];
    }
}
echo '</table>';

include_once "inc.layout.bottom.php";
