<?php
// Zählpixel für die Hauptdomain www.die-ewigen.com (zaehlpixel() in deren site.inc.php):
//   besuch.php?s=start&r=www.google.com&v=discord
// s = Seite, r = Host der verweisenden Seite, v = Kampagnenkennung aus ?von=…
// Gezählt wird über besuch_zaehlen() in functions.php, ohne Cookies und ohne gespeicherte IP. Antwort ist ein 1x1-GIF.
include_once "inc/sv.inc.php";
include_once "inc/env.inc.php";
include_once "functions.php";
include_once "inccon.php";

$seiten = ['start', 'screenshots', 'downloads', 'agb', 'impressum', 'datenschutz', 'bild'];
$seite = (string)($_GET['s'] ?? '');
$quelle = strtolower((string)($_GET['r'] ?? ''));
if (in_array($seite, $seiten, true) && preg_match('/^[a-z0-9.-]{0,100}$/', $quelle)) {
    besuch_zaehlen($seite, $quelle, besuch_kampagne((string)($_GET['v'] ?? '')));
}

header('Content-Type: image/gif');
header('Cache-Control: no-store');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
