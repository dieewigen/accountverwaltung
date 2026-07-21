<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";
include_once "log_dbconnect.php";

$page_title = 'Seitenaufrufe';
include_once "inc.layout.top.php";

//CDE 13, DDE 4, EDE 3, RDE 11, SDE 2, xDE 1
$server = [
    [1, 'xDE'],
    [2, 'SDE'],
];

echo '<p class="dim">User mit mehr als 1000 Seitenaufrufen je Server.</p>';

foreach ($server as [$serverid, $servertag]) {
    echo '<h3>SERVER: ' . $servertag . '</h3>';

    echo '
    <table>
      <tr><th>User-ID</th><th>Seitenaufrufe</th></tr>
    ';

    $sql = "SELECT userid, COUNT(*) AS anzahl FROM `gameserverlogdata` WHERE serverid=? GROUP BY serverid, userid ORDER BY anzahl DESC";
    $db_daten = mysqli_execute_query($GLOBALS['dbi_log'], $sql, [$serverid]);
    while ($row = mysqli_fetch_array($db_daten)) {
        if ($row['anzahl'] > 1000) {
            echo '
            <tr>
              <td><a href="https://' . strtolower($servertag) . '.bgam.es/ourdetool/idinfo.php?UID=' . (int)$row['userid'] . '" target="_blank" rel="noopener">' . (int)$row['userid'] . '</a></td>
              <td class="num">' . (int)$row['anzahl'] . '</td>
            </tr>';
        }
    }

    echo '</table>';
}

include_once "inc.layout.bottom.php";
