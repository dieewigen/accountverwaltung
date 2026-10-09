<?php
/**
 * Auswertung der Spielansichten (Standard, Classic, Mobil) aus der Logging-Tabelle gameserverlogdata.
 *
 * Grundlage: Jeder Seitenaufruf eines eingeloggten Spielers landet mit Skriptname ohne .php im Log
 * (inccon.php der Spielserver). Die Desktop-Rahmen werden bei jedem Login geladen:
 * dm = Standard-Ansicht, de_frameset = Classic. Die Mobilversion lädt keinen von beiden.
 * Daraus ergibt sich je Spieler und Zeitraum:
 *   Standard = dm geladen
 *   Classic  = de_frameset geladen
 *   Wechsler = beides geladen, gezählt wird die zuletzt genutzte Ansicht
 *   Mobil    = weder noch
 * IPs, hinter denen auffällig viele Accounts stecken (eigener Bot-Server, Multis), werden ausgeschlossen.
 *
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

/**
 * Ansicht je Spieler eines Servers.
 *
 * @return array{spieler: array, summe: array, bots: array, gesamt: int}
 *   spieler: eine Zeile je Spieler mit userid, aufrufe, tage, zuerst, zuletzt, ansicht, wechsler
 *   summe:   je Ansicht spieler, wechsler, tage (Summe) und aufrufe (Summe)
 *   bots:    ausgeschlossene IPs mit accounts und aufrufe
 */
function ansichten_statistik(mysqli $db, int $serverid, int $zeitraum, int $mindesttage, int $bot_schwelle): array
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
    $params[] = $mindesttage;

    $sql = "SELECT userid,
                   COUNT(*) AS aufrufe,
                   COUNT(DISTINCT DATE(time)) AS tage,
                   SUM(file = 'dm') AS standard_aufrufe,
                   SUM(file = 'de_frameset') AS classic_aufrufe,
                   MAX(CASE WHEN file = 'dm' THEN time END) AS standard_zuletzt,
                   MAX(CASE WHEN file = 'de_frameset' THEN time END) AS classic_zuletzt,
                   MIN(time) AS zuerst,
                   MAX(time) AS zuletzt
            FROM gameserverlogdata
            WHERE serverid = ? AND time > NOW() - INTERVAL ? DAY" . $ausschluss . "
            GROUP BY userid
            HAVING tage >= ?
            ORDER BY zuletzt DESC";
    $res = mysqli_execute_query($db, $sql, $params);

    $summe = [];
    foreach (array_keys(ansichten_namen()) as $schluessel) {
        $summe[$schluessel] = ['spieler' => 0, 'wechsler' => 0, 'tage' => 0, 'aufrufe' => 0];
    }

    $spieler = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $wechsler = false;
        if ($row['standard_aufrufe'] > 0 && $row['classic_aufrufe'] > 0) {
            // beide Rahmen geladen: die zuletzt genutzte Ansicht zählt (Datumsstrings sind sortierbar)
            $wechsler = true;
            $ansicht = ($row['standard_zuletzt'] >= $row['classic_zuletzt']) ? 'standard' : 'classic';
        } elseif ($row['standard_aufrufe'] > 0) {
            $ansicht = 'standard';
        } elseif ($row['classic_aufrufe'] > 0) {
            $ansicht = 'classic';
        } else {
            $ansicht = 'mobil';
        }

        $spieler[] = [
            'userid'   => (int)$row['userid'],
            'aufrufe'  => (int)$row['aufrufe'],
            'tage'     => (int)$row['tage'],
            'zuerst'   => $row['zuerst'],
            'zuletzt'  => $row['zuletzt'],
            'ansicht'  => $ansicht,
            'wechsler' => $wechsler,
        ];
        $summe[$ansicht]['spieler']++;
        if ($wechsler) {
            $summe[$ansicht]['wechsler']++;
        }
        $summe[$ansicht]['tage'] += (int)$row['tage'];
        $summe[$ansicht]['aufrufe'] += (int)$row['aufrufe'];
    }

    // Liste nach Ansicht gruppiert, innerhalb der Ansicht die zuletzt aktiven zuerst
    $reihenfolge = array_flip(array_keys(ansichten_namen()));
    usort($spieler, function ($a, $b) use ($reihenfolge) {
        return [$reihenfolge[$a['ansicht']], $b['zuletzt']] <=> [$reihenfolge[$b['ansicht']], $a['zuletzt']];
    });

    return ['spieler' => $spieler, 'summe' => $summe, 'bots' => $bots, 'gesamt' => count($spieler)];
}

/** Server, die im Zeitraum ins Log geschrieben haben. */
function ansichten_server(mysqli $db, int $zeitraum): array
{
    $sql = "SELECT DISTINCT serverid FROM gameserverlogdata WHERE time > NOW() - INTERVAL ? DAY ORDER BY serverid";
    $res = mysqli_execute_query($db, $sql, [$zeitraum]);
    return array_map('intval', array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), 'serverid'));
}
