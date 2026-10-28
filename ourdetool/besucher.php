<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

/*
 * Besucher der Hauptdomain www.die-ewigen.com und der Weg bis zum Spielkonto, Grundlage ist ls_besuch.
 * Die Hauptdomain meldet jeden Seitenaufruf über das Zählpixel besuch.php, die Accountverwaltung zählt das
 * Registrierungsformular, die abgeschlossene Registrierung und das angelegte Spielkonto selbst
 * (besuch_zaehlen() in functions.php). Ein Besucher ist eine Kennung je Tag, gebildet aus Tag, IP und Browser
 * und nicht zurückrechenbar; Summen über mehrere Tage sind deshalb Besuchertage. „Neu“ heißt ohne Login-Cookie,
 * also kein bekannter Spieler. Crawler und Linkvorschauen werden nicht gezählt. Kampagne ist die Kennung aus
 * Links mit ?von=…, die die Hauptdomain bis zur Registrierung weiterreicht.
 */

$page_title = 'Besucher';
include_once "inc.layout.top.php";

// Zeiträume in Tagen, der heutige Tag zählt mit
$zeitraeume = ['7' => '7 Tage', '30' => '30 Tage', '90' => '90 Tage', '365' => '1 Jahr'];
$zeitraum = req_str('zeitraum', '30');
if (!isset($zeitraeume[$zeitraum])) {
    $zeitraum = '30';
}
$von = date('Y-m-d', strtotime('-' . ((int)$zeitraum - 1) . ' days'));

function besucher_zahl(float $n, int $stellen = 0): string
{
    return number_format($n, $stellen, ',', '.');
}

function besucher_quote(int $teil, int $ganz): string
{
    return $ganz > 0 ? besucher_zahl(100 * $teil / $ganz, 1) . ' %' : '&ndash;';
}

// Tageswerte: Startseite (Besucher, davon neu, Aufrufe), Registrierungsformular, Registrierungen, Spielkonten
$tage = mysqli_fetch_all(mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT DATE(zeit) AS tag,
        COUNT(DISTINCT IF(seite = 'start', besucher, NULL)) AS start_besucher,
        COUNT(DISTINCT IF(seite = 'start' AND bekannt = 0, besucher, NULL)) AS start_neu,
        SUM(seite = 'start') AS start_aufrufe,
        COUNT(DISTINCT IF(seite = 'registrieren', besucher, NULL)) AS formular,
        SUM(seite = 'registriert') AS registriert,
        SUM(seite = 'spielkonto') AS spielkonten
     FROM ls_besuch WHERE zeit >= ? GROUP BY tag ORDER BY tag DESC",
    [$von]
), MYSQLI_ASSOC);

$summe = ['start_besucher' => 0, 'start_neu' => 0, 'start_aufrufe' => 0, 'formular' => 0, 'registriert' => 0, 'spielkonten' => 0];
foreach ($tage as $tag) {
    foreach ($summe as $spalte => $wert) {
        $summe[$spalte] += (int)$tag[$spalte];
    }
}

// Zeitraum-Auswahl, gilt für alles darunter
echo '<nav class="zeitraum" aria-label="Zeitraum">';
foreach ($zeitraeume as $schluessel => $beschriftung) {
    echo '<a href="?zeitraum=' . $schluessel . '"' . ((string)$schluessel === $zeitraum ? ' aria-current="page"' : '') . '>' . $beschriftung . '</a>';
}
echo '</nav>';

if (!$tage) {
    echo '<div class="flash flash-warn">Im gew&auml;hlten Zeitraum liegen keine Werte vor. Die Hauptdomain meldet ihre Aufrufe &uuml;ber besuch.php,'
        . ' sobald das Z&auml;hlpixel dort eingespielt ist; Registrierungen z&auml;hlt die Accountverwaltung selbst.</div>';
    include_once "inc.layout.bottom.php";
    exit;
}
?>
<div class="kpis">
  <div class="kpi"><div class="kpi-label">Neue Besucher</div><div class="kpi-wert"><?= besucher_zahl($summe['start_neu']) ?></div><div class="kpi-sub">Startseite, dazu <?= besucher_zahl($summe['start_besucher'] - $summe['start_neu']) ?> bekannte Spieler</div></div>
  <div class="kpi"><div class="kpi-label">Registrierungsformular</div><div class="kpi-wert"><?= besucher_zahl($summe['formular']) ?></div><div class="kpi-sub"><?= besucher_quote($summe['formular'], $summe['start_neu']) ?> der neuen Besucher</div></div>
  <div class="kpi"><div class="kpi-label">Registrierungen</div><div class="kpi-wert"><?= besucher_zahl($summe['registriert']) ?></div><div class="kpi-sub"><?= besucher_quote($summe['registriert'], $summe['start_neu']) ?> der neuen Besucher</div></div>
  <div class="kpi"><div class="kpi-label">Spielkonten</div><div class="kpi-wert"><?= besucher_zahl($summe['spielkonten']) ?></div><div class="kpi-sub"><?= besucher_quote($summe['spielkonten'], $summe['registriert']) ?> der Registrierungen</div></div>
</div>

<div class="card">
  <h2>Je Tag</h2>
  <p class="dim">
    Besucher sind Kennungen je Tag ohne gespeicherte IP, Crawler z&auml;hlen nicht. Neu = ohne Login-Cookie, also kein bekannter Spieler.
    Formular = Besucher des Registrierungsformulars, Registriert = angelegte Accounts, Spielkonten = auf einem Server angelegt.
  </p>
  <table>
    <tr><th>Tag</th><th class="num">Besucher</th><th class="num">davon neu</th><th class="num">Aufrufe</th><th class="num">Formular</th><th class="num">Registriert</th><th class="num">Spielkonten</th></tr>
<?php foreach ($tage as $tag): ?>
    <tr>
      <td><?= date('d.m.Y', strtotime($tag['tag'])) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['start_besucher']) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['start_neu']) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['start_aufrufe']) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['formular']) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['registriert']) ?></td>
      <td class="num"><?= besucher_zahl((int)$tag['spielkonten']) ?></td>
    </tr>
<?php endforeach; ?>
    <tr>
      <th>Summe</th>
      <th class="num"><?= besucher_zahl($summe['start_besucher']) ?></th>
      <th class="num"><?= besucher_zahl($summe['start_neu']) ?></th>
      <th class="num"><?= besucher_zahl($summe['start_aufrufe']) ?></th>
      <th class="num"><?= besucher_zahl($summe['formular']) ?></th>
      <th class="num"><?= besucher_zahl($summe['registriert']) ?></th>
      <th class="num"><?= besucher_zahl($summe['spielkonten']) ?></th>
    </tr>
  </table>
<?php
// Abgeschickte Formulare mit Ergebnis: detail ist 'ok', 'honeypot' oder die Liste der Prüfgründe (register.inc.php)
$gesendet = ['ok' => 0, 'honeypot' => 0, 'fehler' => 0];
$gruende = [];
$gruend_namen = ['name' => 'Spielername ung&uuml;ltig', 'namevergeben' => 'Spielername vergeben', 'mail' => 'E-Mail ung&uuml;ltig',
    'mailvergeben' => 'E-Mail schon registriert', 'agb' => 'Regeln nicht best&auml;tigt', 'leer' => 'Feld leer'];
$res = mysqli_execute_query($GLOBALS['dbi'], "SELECT detail, COUNT(*) AS anzahl FROM ls_besuch WHERE zeit >= ? AND seite = 'gesendet' GROUP BY detail", [$von]);
while ($zeile = mysqli_fetch_assoc($res)) {
    if (isset($gesendet[$zeile['detail']])) {
        $gesendet[$zeile['detail']] += (int)$zeile['anzahl'];
        continue;
    }
    $gesendet['fehler'] += (int)$zeile['anzahl'];
    foreach (explode(',', $zeile['detail']) as $grund) {
        $gruende[$grund] = ($gruende[$grund] ?? 0) + (int)$zeile['anzahl'];
    }
}
arsort($gruende);
$gruende_text = [];
foreach ($gruende as $grund => $anzahl) {
    $gruende_text[] = ($gruend_namen[$grund] ?? htmlspecialchars($grund)) . ' ' . besucher_zahl($anzahl);
}
?>
  <p class="dim">
    Formular abgeschickt: <?= besucher_zahl(array_sum($gesendet)) ?>, davon durchgegangen <?= besucher_zahl($gesendet['ok']) ?>,
    an der Pr&uuml;fung gescheitert <?= besucher_zahl($gesendet['fehler']) ?><?= $gruende_text ? ' (' . implode(', ', $gruende_text) . ')' : '' ?>,
    vom Honeypot abgefangen <?= besucher_zahl($gesendet['honeypot']) ?>. Gez&auml;hlt seit dem 28.10.2026, ein Fehlversuch kann mehrere Gr&uuml;nde haben.
  </p>
</div>

<div class="cards" style="grid-template-columns:repeat(auto-fit,minmax(360px,1fr))">
  <div class="card">
    <h2>Kampagnen</h2>
    <p class="dim">Kennung aus Links mit <code>?von=…</code> auf die Hauptdomain, z.&nbsp;B. www.die-ewigen.com/?von=discord. Sie wandert mit bis zur Registrierung.</p>
    <table>
      <tr><th>Kampagne</th><th class="num">Neue Besucher</th><th class="num">Registriert</th><th class="num">Spielkonten</th></tr>
<?php
$kampagnen = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT kampagne,
        COUNT(DISTINCT IF(seite = 'start' AND bekannt = 0, besucher, NULL)) AS neu,
        SUM(seite = 'registriert') AS registriert,
        SUM(seite = 'spielkonto') AS spielkonten
     FROM ls_besuch WHERE zeit >= ? GROUP BY kampagne ORDER BY neu DESC, registriert DESC",
    [$von]
);
while ($zeile = mysqli_fetch_assoc($kampagnen)): ?>
      <tr>
        <td><?= $zeile['kampagne'] === '' ? '<span class="dim">ohne Kennung</span>' : htmlspecialchars($zeile['kampagne']) ?></td>
        <td class="num"><?= besucher_zahl((int)$zeile['neu']) ?></td>
        <td class="num"><?= besucher_zahl((int)$zeile['registriert']) ?></td>
        <td class="num"><?= besucher_zahl((int)$zeile['spielkonten']) ?></td>
      </tr>
<?php endwhile; ?>
    </table>
  </div>

  <div class="card">
    <h2>Verweisende Seiten</h2>
    <p class="dim">Host, von dem die Besucher auf die Startseite kamen. Apps und viele Browser schicken keinen Referrer, dann hilft nur eine Kampagnenkennung.</p>
    <table>
      <tr><th>Quelle</th><th class="num">Neue Besucher</th><th class="num">Besucher</th></tr>
<?php
$quellen = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT quelle, COUNT(DISTINCT IF(bekannt = 0, besucher, NULL)) AS neu, COUNT(DISTINCT besucher) AS besucher
     FROM ls_besuch WHERE zeit >= ? AND seite = 'start' GROUP BY quelle ORDER BY neu DESC, besucher DESC LIMIT 25",
    [$von]
);
while ($zeile = mysqli_fetch_assoc($quellen)): ?>
      <tr>
        <td><?= $zeile['quelle'] === '' ? '<span class="dim">direkt oder ohne Referrer</span>' : htmlspecialchars($zeile['quelle']) ?></td>
        <td class="num"><?= besucher_zahl((int)$zeile['neu']) ?></td>
        <td class="num"><?= besucher_zahl((int)$zeile['besucher']) ?></td>
      </tr>
<?php endwhile; ?>
    </table>
  </div>
</div>

<details class="card">
  <summary>Alle gez&auml;hlten Seiten</summary>
  <table>
    <tr><th>Seite</th><th class="num">Aufrufe</th><th class="num">Besucher</th></tr>
<?php
$seiten = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT seite, detail, COUNT(*) AS aufrufe, COUNT(DISTINCT besucher) AS besucher
     FROM ls_besuch WHERE zeit >= ? GROUP BY seite, detail ORDER BY aufrufe DESC",
    [$von]
);
while ($zeile = mysqli_fetch_assoc($seiten)): ?>
    <tr>
      <td><?= htmlspecialchars($zeile['seite'] . ($zeile['detail'] !== '' ? ' (' . $zeile['detail'] . ')' : '')) ?></td>
      <td class="num"><?= besucher_zahl((int)$zeile['aufrufe']) ?></td>
      <td class="num"><?= besucher_zahl((int)$zeile['besucher']) ?></td>
    </tr>
<?php endwhile; ?>
  </table>
</details>

<?php include_once "inc.layout.bottom.php"; ?>
