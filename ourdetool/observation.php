<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

$uid = req_int('uid');

$page_title = 'Beobachtungsliste';
include_once "inc.layout.top.php";

// Beobachtungs-Markierung entfernen
if ($uid > 0) {
    csrf_require();
    mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET observation_by = '' WHERE user_id = ?", [$uid]);
    echo '<div class="flash flash-ok">User ' . $uid . ' von der Beobachtungsliste entfernt.</div>';
}

echo '
  <table>
    <tr>
      <th>Account-ID</th>
      <th>Loginname</th>
      <th>letzte IP</th>
      <th>letzter Login</th>
      <th>Status</th>
      <th>Beobachter</th>
      <th></th>
    </tr>
';

//abfrage ob es fälle zur beobachtung gibt
$db_daten = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE observation_by <>'' ORDER BY observation_by, user_id");
while ($row = mysqli_fetch_array($db_daten)) {
    $status = match ((int)$row['acc_status']) {
        0 => 'vor Aktivierung',
        1 => 'Aktiv',
        2 => 'gesperrt',
        3 => 'Urlaub',
        default => 'Aktiv',
    };
    echo '
    <tr>
      <td align="center"><a href="info.php?uid=' . (int)$row['user_id'] . '" target="_blank" rel="noopener">' . (int)$row['user_id'] . '</a></td>
      <td align="center"><a href="info.php?uid=' . (int)$row['user_id'] . '" target="_blank" rel="noopener">' . htmlspecialchars((string)$row['loginname']) . '</a></td>
      <td class="num">' . htmlspecialchars((string)$row['last_ip']) . '</td>
      <td align="center">' . htmlspecialchars((string)$row['last_login']) . '</td>
      <td align="center">' . $status . '</td>
      <td align="center">' . htmlspecialchars((string)$row['observation_by']) . '</td>
      <td align="center"><a href="' . csrf_url('observation.php?uid=' . (int)$row['user_id']) . '" data-confirm="User ' . (int)$row['user_id'] . ' von der Beobachtungsliste entfernen?">entfernen</a></td>
    </tr>
  ';
}

echo '
  </table>
';

include_once "inc.layout.bottom.php";
