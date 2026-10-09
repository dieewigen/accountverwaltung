<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";
include_once "log_dbconnect.php";
include_once "inc.ansichten.php";

$page_title = 'Spielansichten';
include_once "inc.layout.top.php";

// Parameter mit Grenzen
$zeitraum     = max(1, min(365, req_int('zeitraum', 30)));
$mindesttage  = max(1, min(60, req_int('mindesttage', 3)));
$bot_schwelle = max(2, min(10000, req_int('bots', 25)));

// Server-Tags zur serverid (sv_servid der Spielserver), Server ohne Eintrag heißen "Server N"
$servertags = [1 => 'xDE', 2 => 'SDE', 3 => 'EDE', 4 => 'DDE', 11 => 'RDE', 13 => 'CDE'];
$namen = ansichten_namen();

function ansichten_auswahl(string $name, array $werte, int $aktuell): string
{
    $html = '<select name="' . $name . '">';
    foreach ($werte as $wert) {
        $html .= '<option value="' . $wert . '"' . ($wert === $aktuell ? ' selected' : '') . '>' . $wert . '</option>';
    }
    return $html . '</select>';
}

function ansichten_zahl(float $wert, int $stellen = 1): string
{
    return number_format($wert, $stellen, ',', '.');
}
?>
<div class="card">
  <form method="get" class="inline">
    Zeitraum: letzte <?= ansichten_auswahl('zeitraum', [7, 14, 30, 60, 90], $zeitraum) ?> Tage &nbsp;
    mindestens <?= ansichten_auswahl('mindesttage', [1, 2, 3, 5, 7, 10], $mindesttage) ?> aktive Tage &nbsp;
    IP ab <input type="number" name="bots" min="2" max="10000" value="<?= $bot_schwelle ?>" style="width: 5em"> Accounts ausschlie&szlig;en &nbsp;
    <button type="submit">Anzeigen</button>
  </form>
  <p class="dim">
    Standard = dm.php geladen, Classic = de_frameset.php geladen, Mobil = keines von beiden.
    Die Rahmen werden bei jedem Login geladen, Sessions leben h&ouml;chstens zwei Tage.
    Wechsler haben im Zeitraum beide Rahmen geladen und z&auml;hlen bei der zuletzt genutzten Ansicht.
    Die Mindestzahl aktiver Tage filtert Einmal-Logins heraus, die IP-Schwelle den eigenen Bot-Server und Multis.
  </p>
</div>

<?php
$serverids = ansichten_server($GLOBALS['dbi_log'], $zeitraum);
if (!$serverids) {
    echo '<div class="flash flash-warn">Im gew&auml;hlten Zeitraum gibt es keine Logeintr&auml;ge.</div>';
}

foreach ($serverids as $serverid) {
    $tag = $servertags[$serverid] ?? '';
    $titel = $tag !== '' ? $tag . ' (Server ' . $serverid . ')' : 'Server ' . $serverid;
    $statistik = ansichten_statistik($GLOBALS['dbi_log'], $serverid, $zeitraum, $mindesttage, $bot_schwelle);

    echo '<h2>' . htmlspecialchars($titel) . '</h2>';

    // ausgeschlossene IPs
    if ($statistik['bots']) {
        $liste = [];
        foreach ($statistik['bots'] as $bot) {
            $liste[] = htmlspecialchars($bot['ip']) . ' (' . (int)$bot['accounts'] . ' Accounts, ' . (int)$bot['aufrufe'] . ' Aufrufe)';
        }
        echo '<p class="dim">Ausgeschlossen: ' . implode(', ', $liste) . '</p>';
    }

    if ($statistik['gesamt'] === 0) {
        echo '<p class="dim">Keine Spieler mit mindestens ' . $mindesttage . ' aktiven Tagen.</p>';
        continue;
    }

    // Zusammenfassung
    echo '<table>
      <tr><th>Ansicht</th><th class="r">Spieler</th><th class="r">Anteil</th><th class="r">davon Wechsler</th><th class="r">&Oslash; aktive Tage</th><th class="r">&Oslash; Aufrufe je Tag</th></tr>';
    foreach ($namen as $schluessel => $name) {
        $s = $statistik['summe'][$schluessel];
        $anteil = $statistik['gesamt'] > 0 ? 100 * $s['spieler'] / $statistik['gesamt'] : 0;
        echo '<tr>
          <td>' . $name . '</td>
          <td class="r">' . $s['spieler'] . '</td>
          <td class="r">' . ansichten_zahl($anteil, 0) . ' %</td>
          <td class="r">' . $s['wechsler'] . '</td>
          <td class="r">' . ($s['spieler'] > 0 ? ansichten_zahl($s['tage'] / $s['spieler']) : '-') . '</td>
          <td class="r">' . ($s['tage'] > 0 ? ansichten_zahl($s['aufrufe'] / $s['tage']) : '-') . '</td>
        </tr>';
    }
    echo '<tr>
          <td><b>Gesamt</b></td>
          <td class="r"><b>' . $statistik['gesamt'] . '</b></td>
          <td class="r">100 %</td><td></td><td></td><td></td>
        </tr>
    </table>';

    // alle Spieler, nach Ansicht gruppiert
    echo '<h3>Spieler auf ' . htmlspecialchars($tag !== '' ? $tag : $titel) . '</h3>
    <table>
      <tr><th>User-ID</th><th>Ansicht</th><th class="r">aktive Tage</th><th class="r">Aufrufe</th><th>erster Aufruf</th><th>letzter Aufruf</th></tr>';
    foreach ($statistik['spieler'] as $spieler) {
        $id = $tag !== ''
            ? '<a href="https://' . strtolower($tag) . '.bgam.es/ourdetool/idinfo.php?UID=' . $spieler['userid'] . '" target="_blank" rel="noopener">' . $spieler['userid'] . '</a>'
            : (string)$spieler['userid'];
        echo '<tr>
          <td>' . $id . '</td>
          <td>' . $namen[$spieler['ansicht']] . ($spieler['wechsler'] ? ' <span class="badge badge-warn">Wechsler</span>' : '') . '</td>
          <td class="r">' . $spieler['tage'] . '</td>
          <td class="r">' . $spieler['aufrufe'] . '</td>
          <td>' . htmlspecialchars($spieler['zuerst']) . '</td>
          <td>' . htmlspecialchars($spieler['zuletzt']) . '</td>
        </tr>';
    }
    echo '</table>';
}

include_once "inc.layout.bottom.php";
