<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

/*
 * Spielerzahl im Verlauf seit Beginn der Erfassung.
 *
 * Grundlage ist ls_user_count: cron/lscron.php speichert täglich um 0 Uhr unter server=99 die Zahl der Accounts
 * der Accountverwaltung mit mindestens einem Login, deren letzter Login höchstens 7 Tage zurückliegt. Läuft der
 * Cron in dieser Stunde mehrfach, gibt es mehrere Zeilen je Tag; dann zählt der höchste Wert. Tage ohne Wert
 * fehlen im Diagramm.
 * Das Punktdiagramm zeichnet spielerzahl.js aus den eingebetteten Tageswerten. Die Monatstabelle darunter fasst
 * dieselben Werte zusammen und funktioniert auch ohne JavaScript.
 */

$page_title = 'Spielerzahl';
$page_head_extra = '<script src="spielerzahl.js" defer></script>';
include_once "inc.layout.top.php";

// Zeiträume: Schlüssel => [Beschriftung, Tage zurück; 0 = seit Beginn der Erfassung]
$zeitraeume = [
    'gesamt' => ['Gesamt', 0],
    '5j'     => ['5 Jahre', 1826],
    '1j'     => ['1 Jahr', 365],
    '90t'    => ['90 Tage', 90],
];
$zeitraum = req_str('zeitraum', 'gesamt');
if (!isset($zeitraeume[$zeitraum])) {
    $zeitraum = 'gesamt';
}
$von = $zeitraeume[$zeitraum][1] > 0 ? date('Y-m-d', strtotime('-' . $zeitraeume[$zeitraum][1] . ' days')) : '2000-01-01';

$monatsnamen = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];

function spielerzahl_zahl(float $n, int $stellen = 0): string
{
    return number_format($n, $stellen, ',', '.');
}

function spielerzahl_datum(string $datum): string
{
    return date('d.m.Y', strtotime($datum));
}

// Tageswerte im Zeitraum, je Tag der höchste Wert
$res = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT datum, MAX(anzahl) AS anzahl FROM ls_user_count WHERE server = 99 AND datum >= ? GROUP BY datum ORDER BY datum",
    [$von]
);
$werte = [];
while ($row = mysqli_fetch_assoc($res)) {
    $werte[] = [(string)$row['datum'], (int)$row['anzahl']];
}

// Beginn der Erfassung und Zahl aller Messtage
$row = mysqli_fetch_assoc(mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT MIN(datum) AS beginn, COUNT(DISTINCT datum) AS tage FROM ls_user_count WHERE server = 99 AND datum >= '2000-01-01'"
));
$beginn = $row['beginn'] ?? null;
$tage_gesamt = (int)($row['tage'] ?? 0);

// Zeilen mit einer anderen Serverkennung werden nicht mit den Werten der Accountverwaltung vermischt
$andere = mysqli_fetch_all(mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT server, COUNT(*) AS zeilen, MIN(datum) AS von, MAX(datum) AS bis FROM ls_user_count WHERE server <> 99 GROUP BY server ORDER BY server"
), MYSQLI_ASSOC);

// Zeitraum-Auswahl, gilt für alles darunter
echo '<nav class="zeitraum" aria-label="Zeitraum">';
foreach ($zeitraeume as $schluessel => [$beschriftung]) {
    echo '<a href="?zeitraum=' . $schluessel . '"' . ($schluessel === $zeitraum ? ' aria-current="page"' : '') . '>' . $beschriftung . '</a>';
}
echo '</nav>';

if (!$werte) {
    echo '<div class="flash flash-warn">'
        . ($beginn === null
            ? 'In ls_user_count liegen noch keine Werte vor. cron/lscron.php tr&auml;gt sie t&auml;glich um 0 Uhr ein.'
            : 'Im gew&auml;hlten Zeitraum liegen keine Werte vor.')
        . '</div>';
} else {
    // Kennzahlen: aktueller Wert, Höchst- und Tiefstwert im Zeitraum (bei Gleichstand der jüngste Tag), Beginn
    $letzter = $werte[count($werte) - 1];
    $hoch = $werte[0];
    $tief = $werte[0];
    foreach ($werte as $wert) {
        if ($wert[1] >= $hoch[1]) {
            $hoch = $wert;
        }
        if ($wert[1] <= $tief[1]) {
            $tief = $wert;
        }
    }
    ?>
<div class="kpis">
  <div class="kpi"><div class="kpi-label">Aktuell</div><div class="kpi-wert"><?= spielerzahl_zahl($letzter[1]) ?></div><div class="kpi-sub">am <?= spielerzahl_datum($letzter[0]) ?></div></div>
  <div class="kpi"><div class="kpi-label">H&ouml;chstwert</div><div class="kpi-wert"><?= spielerzahl_zahl($hoch[1]) ?></div><div class="kpi-sub">am <?= spielerzahl_datum($hoch[0]) ?></div></div>
  <div class="kpi"><div class="kpi-label">Tiefstwert</div><div class="kpi-wert"><?= spielerzahl_zahl($tief[1]) ?></div><div class="kpi-sub">am <?= spielerzahl_datum($tief[0]) ?></div></div>
  <div class="kpi"><div class="kpi-label">Erfasst seit</div><div class="kpi-wert"><?= spielerzahl_datum((string)$beginn) ?></div><div class="kpi-sub"><?= spielerzahl_zahl($tage_gesamt) ?> Messtage<?= $zeitraum !== 'gesamt' ? ', im Zeitraum ' . spielerzahl_zahl(count($werte)) : '' ?></div></div>
</div>

<div class="card">
  <h2>Aktive Spieler je Tag</h2>
  <p class="dim">
    Accounts der Accountverwaltung mit mindestens einem Login, deren letzter Login h&ouml;chstens 7 Tage zur&uuml;ckliegt,
    ein Punkt je Tag. cron/lscron.php speichert den Wert t&auml;glich um 0 Uhr, Tage ohne Wert fehlen.
    Maus, Finger oder Pfeiltasten zeigen den Wert eines Tages.
  </p>
  <figure class="chart" id="spielerzahl-chart" data-reihe="aktive Spieler">
    <noscript><p class="dim">Das Diagramm braucht JavaScript, die Werte stehen in der Monatstabelle.</p></noscript>
  </figure>
  <script type="application/json" id="spielerzahl-daten"><?= json_encode($werte, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</div>
<?php
    // Monatswerte, neuester Monat zuerst
    $monate = [];
    foreach ($werte as [$datum, $anzahl]) {
        $monat = substr($datum, 0, 7);
        if (!isset($monate[$monat])) {
            $monate[$monat] = ['summe' => 0, 'tage' => 0, 'min' => $anzahl, 'max' => $anzahl];
        }
        $monate[$monat]['summe'] += $anzahl;
        $monate[$monat]['tage']++;
        $monate[$monat]['min'] = min($monate[$monat]['min'], $anzahl);
        $monate[$monat]['max'] = max($monate[$monat]['max'], $anzahl);
    }
    krsort($monate);

    echo '<details class="card">
  <summary>Monatswerte als Tabelle</summary>
  <table>
    <tr><th>Monat</th><th class="num">&Oslash; aktive Spieler</th><th class="num">Tiefstwert</th><th class="num">H&ouml;chstwert</th><th class="num">Messtage</th></tr>';
    foreach ($monate as $monat => $m) {
        echo '
    <tr>
      <td>' . $monatsnamen[(int)substr($monat, 5, 2) - 1] . ' ' . substr($monat, 0, 4) . '</td>
      <td class="num">' . spielerzahl_zahl($m['summe'] / $m['tage']) . '</td>
      <td class="num">' . spielerzahl_zahl($m['min']) . '</td>
      <td class="num">' . spielerzahl_zahl($m['max']) . '</td>
      <td class="num">' . $m['tage'] . '</td>
    </tr>';
    }
    echo '
  </table>
</details>';
}

if ($andere) {
    $liste = [];
    foreach ($andere as $zeile) {
        $liste[] = 'Kennung ' . (int)$zeile['server'] . ' (' . spielerzahl_zahl((int)$zeile['zeilen']) . ' Zeilen, '
            . spielerzahl_datum((string)$zeile['von']) . ' bis ' . spielerzahl_datum((string)$zeile['bis']) . ')';
    }
    echo '<p class="dim">ls_user_count enth&auml;lt au&szlig;erdem Werte mit anderer Serverkennung: ' . implode(', ', $liste)
        . '. Angezeigt wird nur die Kennung 99 der Accountverwaltung.</p>';
}

include_once "inc.layout.bottom.php";
