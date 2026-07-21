<?php
/**
 * AJAX-Endpoint für de_user_logviewer.php – liefert HTML-Fragmente,
 * daher KEIN Layout. Ausgabeformat unverändert.
 *
 * @author Rainer Zerbe - rz.php-projects@i-it-s.de (Original 2009)
 *
 * Portiert auf mysqli mit Prepared Statements; die frühere
 * dbExtend/cfg-Klassenschicht (logviewer/class/) wurde entfernt.
 */
include_once "../inccon.php";
include_once "det_userdata.inc.php";
include_once "log_dbconnect.php";

/** Alle Zeilen einer Abfrage als Array von Assoziativ-Arrays, optional nach Spalte indiziert. */
function logdb_all(string $sql, array $params = [], ?string $index = null): array
{
    $result = mysqli_execute_query($GLOBALS['dbi_log'], $sql, $params);
    if (!$result) {
        echo '<div style="color: red;">SQL-Fehler: ' . htmlspecialchars(mysqli_error($GLOBALS['dbi_log'])) . '</div>';
        return [];
    }
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if ($index !== null && isset($row[$index])) {
            $data[$row[$index]] = $row;
        } else {
            $data[] = $row;
        }
    }
    return $data;
}

$job = req_str('job');
$ajax_uid = req_int('uid');
$ajax_sid = req_int('sid');

// Top-Übersichten: gleiche Abfrage für drei verschiedene Dateien
$top_jobs = [
    'loadTopGenerator' => 'imagegenerator',
    'loadTopSecstat'   => 'secstatus',
    'loadTopSysnews'   => 'sysnews',
];
if (isset($top_jobs[$job])) {
    $d = logdb_all(
        "SELECT serverid, userid, COUNT(userid) AS c FROM gameserverlogdata WHERE file = ? AND serverid = ? GROUP BY serverid, userid ORDER BY c DESC LIMIT 20",
        [$top_jobs[$job], $ajax_sid]
    );
    echo '<table><thead><th> Server ID</th><th> User ID</th><th> Clicks </th></thead><tbody>';
    foreach ($d as $row) {
        echo '<tr><td>' . (int)$row['serverid'] . '</td><td>' . (int)$row['userid'] . '</td><td>' . (int)$row['c'] . '</td></tr>';
    }
    echo '</tbody></table>';
}

if ($job == 'loadClicks') {
    $d = logdb_all(
        "SELECT file, COUNT(file) AS clicks FROM gameserverlogdata WHERE userid = ? AND serverid = ? GROUP BY file ORDER BY clicks DESC",
        [$ajax_uid, $ajax_sid]
    );

    if (empty($d)) {
        die('Keine Daten gefunden!');
    }

    $times = logdb_all(
        "SELECT MAX(time) AS max, MIN(time) AS min FROM gameserverlogdata WHERE userid = ? AND serverid = ?",
        [$ajax_uid, $ajax_sid]
    );
    $min_time = $times[0]['min'] ?? 'unbekannt';
    $max_time = $times[0]['max'] ?? 'unbekannt';

    echo "<br><h3>Klicks f&uuml;r den Zeitraum vom " . htmlspecialchars((string)$min_time) . " bis " . htmlspecialchars((string)$max_time) . '</h3>';
    echo '<table><thead><th> Anzeigen</th><th> File</th><th> Clicks</th></thead><tbody>';
    foreach ($d as $row) {
        echo '<tr><td><input type="checkbox" name="show[' . htmlspecialchars((string)$row['file']) . ']" value="1" checked></td><td>' . htmlspecialchars((string)$row['file']) . '</td><td>' . (int)$row['clicks'] . '</td></tr>';
    }
    echo '</tbody></table>';
}

if ($job == 'loadDay') {
    // Datum validieren (YYYY-MM-DD), sonst wie "fehlend" behandeln
    $day = req_str('day');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
        echo '<tr><td colspan="26">Fehler: Kein Datum angegeben</td></tr>';
    } else {
        $d = logdb_all(
            "SELECT HOUR(time) AS h, COUNT(HOUR(time)) AS clicks FROM gameserverlogdata WHERE time >= ? AND time <= ADDDATE(?, 1) AND userid = ? AND serverid = ? GROUP BY HOUR(time) ORDER BY HOUR(time) ASC LIMIT 30",
            [$day, $day, $ajax_uid, $ajax_sid],
            'h'
        );

        echo '<tr><td class="remove">remove</td><td>' . htmlspecialchars($day) . '</td>';
        for ($h = 0; $h <= 23; $h++) {
            $clicks = (int)($d[$h]['clicks'] ?? 0);
            echo '<td class="hour" hour="' . htmlspecialchars($day) . ' ' . $h . ':00:00">' . $clicks . '</td>';
        }
        echo '</tr>';
    }
}

if ($job == 'loadLog') {
    // Startdatum: YYYY-MM-DD oder YYYY-MM-DD H:MM:SS, sonst auf heute zurückfallen
    $startDate = req_str('startDate', req_str('day', date('Y-m-d')));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{1,2}:\d{2}:\d{2})?$/', $startDate)) {
        $startDate = date('Y-m-d');
    }

    // logType bestimmt die Datei-Liste und ob nur Aufrufe mit Eingaben zählen
    $log_types = [
        'communication' => [['hyperfunk', 'efta_chat', 'chat'], true],
        'military'      => [['military', 'militarybs'], true],
        'scan'          => [['secret'], true],
        'militaryscan'  => [['secret', 'military', 'militarybs'], true],
        'sekstatsek'    => [['sector', 'secstatus'], false],
        'bk'            => [['bkmenu'], false],
    ];

    $logType = req_str('logType');
    $withdata = false;
    if (isset($log_types[$logType])) {
        [$files, $withdata] = $log_types[$logType];
    } elseif (isset($_GET['show']) && is_array($_GET['show'])) {
        // "alle (nach Konfiguration)": Datei-Liste aus den Checkboxen
        $files = array_map('strval', array_keys($_GET['show']));
    } else {
        $files = [];
    }

    $sql = "SELECT time, ip, file, getpost FROM gameserverlogdata WHERE time >= ? AND userid = ? AND serverid = ?";
    $params = [$startDate, $ajax_uid, $ajax_sid];
    if (count($files) > 0) {
        $sql .= " AND file IN (" . implode(',', array_fill(0, count($files), '?')) . ")";
        $params = array_merge($params, $files);
    }
    if ($withdata) {
        $sql .= " AND CHAR_LENGTH(getpost) > 22";
    }
    $sql .= " ORDER BY time ASC LIMIT 30";

    $d = logdb_all($sql, $params);

    echo '<table><thead><tr><th>IP</th><th>Time</th><th>File</th><th>getpost</th></tr></thead><tbody>';

    if (empty($d)) {
        echo '<tr><td colspan="4">Keine Daten f&uuml;r diesen Zeitraum gefunden.</td></tr>';
        echo '</tbody></table>';
        return;
    }

    $num = 0;
    $lastRow = $d[0];
    $index = (floor(strtotime($d[0]['time']) / 60) * 60) + 60;

    foreach ($d as $row) {
        // Zeilenfarbe pro angefangener Minute wechseln
        if (strtotime($row['time']) > $index) {
            $num = abs($num - 1);
            $index = (floor(strtotime($row['time']) / 60) * 60) + 60;
        }

        $getpost = (string)$row['getpost'];
        $ipClass = ($lastRow['ip'] != $row['ip']) ? ' class="ipChanged"' : '';
        $timeDiff = ' (' . (strtotime($row['time']) - strtotime($lastRow['time'])) . 's)';

        echo '<tr class="row' . $num . '">'
            . '<td' . $ipClass . '>' . htmlspecialchars((string)$row['ip']) . '</td>'
            . '<td>' . htmlspecialchars((string)$row['time']) . $timeDiff . '</td>'
            . '<td>' . htmlspecialchars((string)$row['file']) . '</td>'
            . '<td class="tooltip" alt="<pre>' . htmlspecialchars($getpost) . '</pre>">'
            . htmlspecialchars(substr($getpost, 0, 70)) . ((strlen($getpost) > 70) ? '...' : '') . '</td></tr>';

        $lastRow = $row;
    }

    echo '</tbody></table><input type="hidden" name="startDate" value="' . htmlspecialchars((string)$lastRow['time']) . '">';
}
