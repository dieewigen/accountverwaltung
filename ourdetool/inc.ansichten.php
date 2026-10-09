<?php
/**
 * Auswertung der Spielansichten (Standard, Classic, Mobil) aus der Logging-Tabelle gameserverlogdata.
 *
 * Grundlage: Jeder Seitenaufruf eines eingeloggten Spielers landet mit Skriptname ohne .php im Log
 * (inccon.php der Spielserver). Die Desktop-Rahmen werden bei jedem Login geladen:
 * dm = Standard-Ansicht, de_frameset = Classic. Die Mobilversion lädt keinen von beiden; sie wird beim
 * ersten Login per MobileDetect zugewiesen und im Cookie use_mobile_version je Gerät gemerkt.
 *
 * Ein Spieler kann deshalb am Rechner Desktop und am Handy mobil spielen. Darum wird jeder Spielertag
 * einzeln zugeordnet:
 *   Tag mit dm            = Standard
 *   Tag mit de_frameset   = Classic (bei beiden an einem Tag zählt der spätere Aufruf)
 *   Tag ohne Rahmen       = Fortsetzung der Desktop-Session, wenn der letzte Rahmenaufruf höchstens
 *                           $session_tage zurückliegt (Sessions leben zwei Tage), sonst Mobil
 * Die Hauptansicht eines Spielers ist die Ansicht mit den meisten Tagen. Wechsler haben Standard- und
 * Classic-Tage, gemischte Spieler Desktop- und Mobiltage.
 *
 * IPs, hinter denen auffällig viele Accounts stecken (eigener Bot-Server, Multis), werden ausgeschlossen.
 * Keine Abhängigkeit zum Admintool-Bootstrap, damit die Funktionen auch auf der Kommandozeile testbar sind.
 */

/** Anzeigenamen der Ansichten in der Reihenfolge der Ausgabe. */
function ansichten_namen(): array
{
    return ['standard' => 'Standard', 'classic' => 'Classic', 'mobil' => 'Mobil'];
}

/** IPs mit mindestens $schwelle verschiedenen Accounts im Zeitraum (Bot-Server, Multis). */
function ansichten_bot_ips(mysqli $db, int $serverid, int $zeitraum, int $schwelle): array
{
    $sql = "SELECT ip, COUNT(DISTINCT userid) AS accounts, COUNT(*) AS aufrufe
            FROM gameserverlogdata
            WHERE serverid = ? AND time > NOW() - INTERVAL ? DAY
            GROUP BY ip
            HAVING accounts >= ?
            ORDER BY accounts DESC";
    $res = mysqli_execute_query($db, $sql, [$serverid, $zeitraum, $schwelle]);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/** Server, die im Zeitraum ins Log geschrieben haben. */
function ansichten_server(mysqli $db, int $zeitraum): array
{
    $sql = "SELECT DISTINCT serverid FROM gameserverlogdata WHERE time > NOW() - INTERVAL ? DAY ORDER BY serverid";
    $res = mysqli_execute_query($db, $sql, [$zeitraum]);
    return array_map('intval', array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), 'serverid'));
}

/**
 * Ordnet die Spielertage eines Spielers den Ansichten zu.
 *
 * @param array $tage Zeilen mit tag (Y-m-d), standard_zuletzt, classic_zuletzt, aufsteigend nach Tag
 * @return array{standard: int, classic: int, mobil: int, letzte: string}
 */
function ansichten_tage_zuordnen(array $tage, int $session_tage): array
{
    $zaehler = ['standard' => 0, 'classic' => 0, 'mobil' => 0];
    $anker_tag = null;      // letzter Tag mit Rahmenaufruf
    $anker_ansicht = null;
    $letzte = 'mobil';

    foreach ($tage as $tag) {
        if ($tag['standard_zuletzt'] !== null || $tag['classic_zuletzt'] !== null) {
            if ($tag['classic_zuletzt'] === null) {
                $ansicht = 'standard';
            } elseif ($tag['standard_zuletzt'] === null) {
                $ansicht = 'classic';
            } else {
                // beide Rahmen an einem Tag: der spätere Aufruf zählt (Datumsstrings sind sortierbar)
                $ansicht = ($tag['standard_zuletzt'] >= $tag['classic_zuletzt']) ? 'standard' : 'classic';
            }
            $anker_tag = $tag['tag'];
            $anker_ansicht = $ansicht;
        } elseif ($anker_tag !== null && (strtotime($tag['tag']) - strtotime($anker_tag)) <= $session_tage * 86400) {
            // kein Rahmen geladen, aber die Desktop-Session kann noch laufen
            $ansicht = $anker_ansicht;
        } else {
            $ansicht = 'mobil';
        }
        $zaehler[$ansicht]++;
        $letzte = $ansicht;
    }

    $zaehler['letzte'] = $letzte;
    return $zaehler;
}

/**
 * Ansicht je Spieler eines Servers.
 *
 * @return array{spieler: array, summe: array, bots: array, gesamt: int, tage: int, mobiltage: int,
 *               spieler_mit_mobiltagen: int, spieler_nur_mobil: int}
 *   spieler: eine Zeile je Spieler mit userid, tage, tage_standard, tage_classic, tage_mobil, mobilanteil,
 *            aufrufe, zuerst, zuletzt, ansicht (Hauptansicht), wechsler, gemischt
 *   summe:   je Hauptansicht spieler, wechsler, gemischt, tage (Summe aktiver Tage) und aufrufe (Summe)
 *   bots:    ausgeschlossene IPs mit accounts und aufrufe
 */
function ansichten_statistik(mysqli $db, int $serverid, int $zeitraum, int $mindesttage, int $bot_schwelle, int $session_tage = 2): array
{
    $bots = ansichten_bot_ips($db, $serverid, $zeitraum, $bot_schwelle);

    $params = [$serverid, $zeitraum];
    $ausschluss = '';
    if ($bots) {
        $ausschluss = ' AND ip NOT IN (' . implode(',', array_fill(0, count($bots), '?')) . ')';
        foreach ($bots as $bot) {
            $params[] = $bot['ip'];
        }
    }

    $sql = "SELECT userid,
                   DATE(time) AS tag,
                   COUNT(*) AS aufrufe,
                   MAX(CASE WHEN file = 'dm' THEN time END) AS standard_zuletzt,
                   MAX(CASE WHEN file = 'de_frameset' THEN time END) AS classic_zuletzt,
                   MIN(time) AS zuerst,
                   MAX(time) AS zuletzt
            FROM gameserverlogdata
            WHERE serverid = ? AND time > NOW() - INTERVAL ? DAY" . $ausschluss . "
            GROUP BY userid, DATE(time)
            ORDER BY userid, tag";
    $res = mysqli_execute_query($db, $sql, $params);

    // Spielertage je Spieler sammeln
    $je_spieler = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $je_spieler[(int)$row['userid']][] = $row;
    }

    $summe = [];
    foreach (array_keys(ansichten_namen()) as $schluessel) {
        $summe[$schluessel] = ['spieler' => 0, 'wechsler' => 0, 'gemischt' => 0, 'tage' => 0, 'aufrufe' => 0];
    }
    $ergebnis = [
        'spieler' => [], 'summe' => $summe, 'bots' => $bots, 'gesamt' => 0,
        'tage' => 0, 'mobiltage' => 0, 'spieler_mit_mobiltagen' => 0, 'spieler_nur_mobil' => 0,
    ];

    foreach ($je_spieler as $userid => $tage) {
        if (count($tage) < $mindesttage) {
            continue;
        }
        $zuordnung = ansichten_tage_zuordnen($tage, $session_tage);
        $anzahl = count($tage);

        // Hauptansicht: die meisten Tage, bei Gleichstand die zuletzt genutzte Ansicht
        $ansicht = $zuordnung['letzte'];
        $meiste = $zuordnung[$ansicht];
        foreach (array_keys(ansichten_namen()) as $schluessel) {
            if ($zuordnung[$schluessel] > $meiste) {
                $meiste = $zuordnung[$schluessel];
                $ansicht = $schluessel;
            }
        }

        $wechsler = $zuordnung['standard'] > 0 && $zuordnung['classic'] > 0;
        $gemischt = $zuordnung['mobil'] > 0 && ($zuordnung['standard'] + $zuordnung['classic']) > 0;
        $aufrufe = (int)array_sum(array_column($tage, 'aufrufe'));

        $ergebnis['spieler'][] = [
            'userid'        => $userid,
            'tage'          => $anzahl,
            'tage_standard' => $zuordnung['standard'],
            'tage_classic'  => $zuordnung['classic'],
            'tage_mobil'    => $zuordnung['mobil'],
            'mobilanteil'   => $zuordnung['mobil'] / $anzahl,
            'aufrufe'       => $aufrufe,
            'zuerst'        => $tage[0]['zuerst'],
            'zuletzt'       => $tage[$anzahl - 1]['zuletzt'],
            'ansicht'       => $ansicht,
            'wechsler'      => $wechsler,
            'gemischt'      => $gemischt,
        ];

        $ergebnis['gesamt']++;
        $ergebnis['tage'] += $anzahl;
        $ergebnis['mobiltage'] += $zuordnung['mobil'];
        if ($zuordnung['mobil'] > 0) {
            $ergebnis['spieler_mit_mobiltagen']++;
        }
        if ($zuordnung['mobil'] === $anzahl) {
            $ergebnis['spieler_nur_mobil']++;
        }
        $ergebnis['summe'][$ansicht]['spieler']++;
        $ergebnis['summe'][$ansicht]['wechsler'] += $wechsler ? 1 : 0;
        $ergebnis['summe'][$ansicht]['gemischt'] += $gemischt ? 1 : 0;
        $ergebnis['summe'][$ansicht]['tage'] += $anzahl;
        $ergebnis['summe'][$ansicht]['aufrufe'] += $aufrufe;
    }

    // Liste nach Hauptansicht gruppiert, innerhalb der Ansicht die zuletzt aktiven zuerst
    $reihenfolge = array_flip(array_keys(ansichten_namen()));
    usort($ergebnis['spieler'], function ($a, $b) use ($reihenfolge) {
        return [$reihenfolge[$a['ansicht']], $b['zuletzt']] <=> [$reihenfolge[$b['ansicht']], $a['zuletzt']];
    });

    return $ergebnis;
}
