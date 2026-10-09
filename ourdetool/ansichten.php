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

function ansichten_prozent(float $anteil): string
{
    return ansichten_zahl(100 * $anteil, 0) . ' %';
}
?>
<div class="card">
  <form method="get" class="inline">
    Zeitraum: letzte <?= ansichten_auswahl('zeitraum', [7, 14, 30, 60, 90], $zeitraum) ?> Tage &nbsp;
    mindestens <?= ansichten_auswahl('mindesttage', [1, 2, 3, 5, 7, 10], $mindesttage) ?> aktive Tage &nbsp;
    IP ab <input type="number" name="bots" id="bots" aria-label="IP-Schwelle in Accounts" min="2" max="10000" value="<?= $bot_schwelle ?>" style="width: 5em"> Accounts ausschlie&szlig;en &nbsp;
    <button type="submit">Anzeigen</button>
  </form>
  <p class="dim">
    Jeder Spielertag wird einzeln zugeordnet: Standard, wenn dm.php geladen wurde, Classic bei de_frameset.php.
    Ein Tag ohne Rahmen z&auml;hlt noch zur Desktop-Session, wenn der letzte Rahmenaufruf h&ouml;chstens zwei Tage
    zur&uuml;ckliegt, sonst als Mobil. Die Hauptansicht eines Spielers ist die Ansicht mit den meisten Tagen.
    Wechsler haben Standard- und Classic-Tage, gemischte Spieler Desktop- und Mobiltage, etwa Rechner zu Hause und
    Handy unterwegs. Die Mobilversion wird beim ersten Login per Ger&auml;teerkennung gesetzt und je Ger&auml;t im
    Cookie gemerkt. Die Mindestzahl aktiver Tage filtert Einmal-Logins heraus, die IP-Schwelle den eigenen Bot-Server
    und Multis.
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

    // Zusammenfassung nach Hauptansicht
    echo '<table>
      <tr><th>Hauptansicht</th><th class="r">Spieler</th><th class="r">Anteil</th><th class="r">davon Wechsler</th><th class="r">davon gemischt</th><th class="r">&Oslash; aktive Tage</th><th class="r">&Oslash; Aufrufe je Tag</th></tr>';
    foreach ($namen as $schluessel => $name) {
        $s = $statistik['summe'][$schluessel];
        echo '<tr>
          <td>' . $name . '</td>
          <td class="r">' . $s['spieler'] . '</td>
          <td class="r">' . ansichten_prozent($s['spieler'] / $statistik['gesamt']) . '</td>
          <td class="r">' . $s['wechsler'] . '</td>
          <td class="r">' . $s['gemischt'] . '</td>
          <td class="r">' . ($s['spieler'] > 0 ? ansichten_zahl($s['tage'] / $s['spieler']) : '-') . '</td>
          <td class="r">' . ($s['tage'] > 0 ? ansichten_zahl($s['aufrufe'] / $s['tage']) : '-') . '</td>
        </tr>';
    }
    echo '<tr>
          <td><b>Gesamt</b></td>
          <td class="r"><b>' . $statistik['gesamt'] . '</b></td>
          <td class="r">100 %</td><td></td><td></td><td></td><td></td>
        </tr>
    </table>';

    // Mobilnutzung unabhängig von der Hauptansicht
    echo '<p>Spielertage: <b>' . $statistik['tage'] . '</b>, davon mobil <b>' . $statistik['mobiltage'] . '</b>'
        . ' (' . ansichten_prozent($statistik['tage'] > 0 ? $statistik['mobiltage'] / $statistik['tage'] : 0) . ').'
        . ' Spieler mit mindestens einem Mobiltag: <b>' . $statistik['spieler_mit_mobiltagen'] . '</b>,'
        . ' davon nur mobil: <b>' . $statistik['spieler_nur_mobil'] . '</b>.</p>';

    // alle Spieler, nach Hauptansicht gruppiert
    echo '<h3>Spieler auf ' . htmlspecialchars($tag !== '' ? $tag : $titel) . '</h3>
    <table>
      <tr><th>User-ID</th><th>Hauptansicht</th><th class="r">Tage Standard</th><th class="r">Tage Classic</th><th class="r">Tage Mobil</th><th class="r">Mobilanteil</th><th class="r">Aufrufe</th><th>erster Aufruf</th><th>letzter Aufruf</th></tr>';
    foreach ($statistik['spieler'] as $spieler) {
        $id = $tag !== ''
            ? '<a href="https://' . strtolower($tag) . '.bgam.es/ourdetool/idinfo.php?UID=' . $spieler['userid'] . '" target="_blank" rel="noopener">' . $spieler['userid'] . '</a>'
            : (string)$spieler['userid'];
        $marken = '';
        if ($spieler['wechsler']) {
            $marken .= ' <span class="badge badge-warn">Wechsler</span>';
        }
        if ($spieler['gemischt']) {
            $marken .= ' <span class="badge">gemischt</span>';
        }
        echo '<tr>
          <td>' . $id . '</td>
          <td>' . $namen[$spieler['ansicht']] . $marken . '</td>
          <td class="r">' . $spieler['tage_standard'] . '</td>
          <td class="r">' . $spieler['tage_classic'] . '</td>
          <td class="r">' . $spieler['tage_mobil'] . '</td>
          <td class="r">' . ansichten_prozent($spieler['mobilanteil']) . '</td>
          <td class="r">' . $spieler['aufrufe'] . '</td>
          <td>' . htmlspecialchars($spieler['zuerst']) . '</td>
          <td>' . htmlspecialchars($spieler['zuletzt']) . '</td>
        </tr>';
    }
    echo '</table>';
}

include_once "inc.layout.bottom.php";
