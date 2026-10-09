<?php
//sprachdatei laden
include 'content/de/lang/'.$ums_language.'_header.lang.php';
include 'content/de/lang/'.$ums_language.'_m_main.lang.php';

//////////////////////////////////////////////////////////////////
//obere Infoleiste: Bereichsmarke und Spielerzahl neben dem Namen in der Kopfleiste
//////////////////////////////////////////////////////////////////

echo '<span class="marke-bereich">'.$header_lang['accountverwaltung'].'</span>';

if(isset($_SESSION['ums_user_id']) && $_SESSION['ums_user_id']>0){
	$db_daten_uc=mysqli_query($GLOBALS['dbi'], "SELECT anzahl FROM ls_user_count ORDER BY datum DESC LIMIT 1;");
	$row_uc = mysqli_fetch_array($db_daten_uc);
	$anzahl_uc=isset($row_uc["anzahl"]) ? $row_uc["anzahl"] : '?';

	echo '<span class="spieler-marke">Spieler: '.$anzahl_uc.'</span>';
}
