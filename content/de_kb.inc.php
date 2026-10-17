<?php

////////////////////////////////////////////////////////////////////////
//Serverauswahl
////////////////////////////////////////////////////////////////////////
//erlaubte Server
$allowed_server=array('xDE', 'SDE');

$show_server=isset($_REQUEST['show_server']) ? $_REQUEST['show_server'] : '';

echo '<ul class="reiter"><li class="reiter-titel">Server:</li>';
$server_found=false;
for($i=0;$i<count($allowed_server);$i++){
	if($show_server==$allowed_server[$i]){
		$aktiv=' aria-current="page"';
		$server_found=true;
	}else{
		$aktiv='';
	}
	echo '<li><a'.$aktiv.' href="index.php?command=de_kb&show_server='.$allowed_server[$i].'">'.$allowed_server[$i].'</a></li>';
}

echo '</ul>';

if($server_found==true){

	$datum=date("Y-m-d", time()-3600*24);

	$result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_de_kb WHERE server = ? AND time LIKE ? ORDER BY atter ASC", [$show_server, $datum.'%']);



	echo '<div class="seitentitel">'.$show_server.'-Kampfberichte von gestern:</div>';

	while($row = mysqli_fetch_array($result)){
		echo '<div class="kb-bericht">'.showkampfbericht($row['kb'],$row['atter'],$row['deffer']).'</div>';
	}
}else{

	echo '<div class="hinweis hinweis-mitte">W&auml;hle bitte einen Server aus um die Kampfberichte einsehen zu k&ouml;nnen.</div>';

}

////////////////////////////////////////////////////////////////////////
// Kampfbericht im Stil des Spiels (src/View/Kampfbericht.php in de2, Block .kb in gp/de-main.scss):
// Kopf mit Ergebnis, Angreifer und Verteidiger als Karten, Einheiten beider Seiten nebeneinander,
// Statistik mit Anteilsbalken. Es gibt nur noch eine Berichtsfassung (ls_de_kb.kbversion 1, serialisiertes
// Array); die alte Fassung mit Semikolon-Werten kommt nicht mehr vor und wird als "nicht lesbar" angezeigt.
// Anders als im Spiel gibt es hier keine eigene Seite, darum weder "Dein Ergebnis" noch eigene Einheiten;
// das Ergebnis im Kopf steht in der Farbe des Angreifers (warm) oder Verteidigers (kühl).
// Datenschutz wie bisher: Angreifer und Verteidiger erscheinen nur mit den Account-IDs aus den Spalten atter
// und deffer (das Spiel trägt dort in tickler/kt_systemkampf.php die owner_id ein). Die Namenslisten mit
// Koordinaten und der Ort des Kampfes stecken zwar in den Berichtsdaten, werden aber nicht angezeigt.
// Die Rassenfarben der früheren Zellklassen k1 bis k10 sind die Zeilentönung kb-r0 bis kb-r4.
////////////////////////////////////////////////////////////////////////

//Zahl mit Tausenderpunkt
function kb_zahl($n){
	return number_format((float)$n, 0, '', '.');
}

//Anteil in Prozent mit einer Nachkommastelle
function kb_prozent($teil, $ganz){
	return number_format($ganz>0 ? $teil*100/$ganz : 0, 1, ',', '.').' %';
}

//Beteiligte einer Seite als Abzeichen: wie bisher nur die Account-IDs aus der Datenbank (mehrere durch Semikolon
//getrennt), keine Namen und keine Koordinaten
function kb_seite($ids){
	$html='';
	foreach(explode(';', (string)$ids) as $id){
		$id=trim($id);
		if($id===''){
			continue;
		}
		$html.='<span class="kb-name" title="Account-ID">'.htmlspecialchars($id, ENT_QUOTES, 'UTF-8').'</span>';
	}
	if($html===''){
		$html='<span class="kb-name">-</span>';
	}
	return $html;
}

//Zeile der Einheitentabelle: Name, dann je Seite eingesetzt, geblockt, überlebt; null statt Werten gibt Striche
function kb_zeile($rasse, $name, $a, $v){
	$html='<tr class="kb-r'.intval($rasse).'"><td>'.$name.'</td>';
	foreach(array($a, $v) as $g){
		if($g===null){
			$html.='<td class="kb-leer">-</td><td class="kb-leer">-</td><td class="kb-leer">-</td>';
		}else{
			$html.='<td>'.kb_zahl($g[0]).'</td><td>'.kb_zahl($g[1]).'</td><td>'.kb_zahl($g[2]).'</td>';
		}
	}
	return $html.'</tr>';
}

//Zeile der Bilanz: Angreifer links, Verteidiger rechts, dazwischen der Anteil beider Seiten, rechts das Verhältnis (1 : x)
function kb_bilanz($label, $a, $v, $atext, $vtext){
	$summe=$a+$v;
	$pa=$summe>0 ? round($a*100/$summe) : 50;
	$verh=$a>0 ? '1 : '.number_format($v/$a, 2, ',', '.') : '-';
	return '<tr><td>'.$label.'</td><td>'.$atext.'</td><td><div class="kb-anteil"><span class="kb-anteil-a" style="width: '.$pa.'%"></span><span class="kb-anteil-v" style="width: '.(100-$pa).'%"></span></div></td><td>'.$vtext.'</td><td class="kb-verh">'.$verh.'</td></tr>';
}

//Kampfbericht: $text ist das serialisierte Array mit daten, einheiten_atter, einheiten_deffer und tuerme
//(je [eingesetzt, geblockt, verloren] je Rasse und Einheit), 10 Einheiten je Rasse, 5 Türme; Punkte der Türme
//ab Index 10 wie im Spiel. $atter und $deffer sind die Account-IDs aus der Datenbank.
function showkampfbericht($text, $atter, $deffer){

	$schiffspunkte=unserialize('a:5:{i:0;a:15:{i:0;i:150;i:1;i:600;i:2;i:2800;i:3;i:5900;i:4;i:13200;i:5;i:250;i:6;i:400;i:7;i:15500;i:8;i:950;i:9;i:115000;i:10;i:1500;i:11;i:190;i:12;i:125;i:13;i:325;i:14;i:550;}i:1;a:15:{i:0;i:155;i:1;i:550;i:2;i:2900;i:3;i:5900;i:4;i:13900;i:5;i:200;i:6;i:400;i:7;i:16600;i:8;i:850;i:9;i:122000;i:10;i:1500;i:11;i:135;i:12;i:75;i:13;i:260;i:14;i:525;}i:2;a:15:{i:0;i:175;i:1;i:700;i:2;i:2850;i:3;i:5780;i:4;i:12300;i:5;i:200;i:6;i:400;i:7;i:17200;i:8;i:990;i:9;i:116000;i:10;i:1750;i:11;i:160;i:12;i:150;i:13;i:340;i:14;i:615;}i:3;a:15:{i:0;i:80;i:1;i:500;i:2;i:2800;i:3;i:5000;i:4;i:12100;i:5;i:250;i:6;i:400;i:7;i:15500;i:8;i:980;i:9;i:104000;i:10;i:800;i:11;i:170;i:12;i:90;i:13;i:295;i:14;i:530;}i:4;a:15:{i:0;i:160;i:1;i:625;i:2;i:2840;i:3;i:5525;i:4;i:12600;i:5;i:225;i:6;i:400;i:7;i:16600;i:8;i:950;i:9;i:115000;i:10;i:1550;i:11;i:165;i:12;i:110;i:13;i:300;i:14;i:615;}}');

	$sv_anz_schiffe=10;
	$sv_anz_tuerme=5;
	$sv_anz_rassen=5;

	//kb in seine bestandteile zerlegen
	$kbd=@unserialize($text);
	if(!is_array($kbd) || !isset($kbd['daten']) || !is_array($kbd['daten'])){
		return '<div class="kb"><div class="kb-keine">Dieser Kampfbericht ist nicht lesbar.</div></div>';
	}
	$daten=$kbd['daten'];

	//daten aus dem kb holen; sector, system, atterliste und defferliste aus $daten werden nicht angezeigt
	$kkollies=(int)($daten['colstolen'] ?? 0);
	$krassenvorhanden=(isset($daten['rassen']) && is_array($daten['rassen'])) ? $daten['rassen'] : array();
	$kturmrasse=(int)($daten['target_rasse'] ?? 0);

	$rassennamen[0] = array ('Hornisse', 'Guillotine', 'Schakal', 'Marauder', 'Zerberus', 'Nachtmar', 'Transmitterschiff', 'Hydra', 'Frachtschiff', 'Hyperion');
	$rassennamen[1] = array ('Caesar', 'Paladin', 'Vollstrecker', 'Imperator', 'Excalibur', 'Phalanx', 'Merlin', 'Colossus', 'Frachtbarke', 'Dragonfire');
	$rassennamen[2] = array ('Spider', 'Arctic Spider', 'Werespider', 'Tarantula', 'Black Widow', 'Hellspider', 'Netzf&auml;nger', 'Gigantula', 'Frachtnetz', 'Titanspider');
	$rassennamen[3] = array ('Wespe', 'Feuerskorpion', 'Geisterschrecke', 'Skarab&auml;us', 'Mantis', 'H&ouml;llenk&auml;fer', 'Sammler', 'Ekelbr&uuml;ter', 'Frachttr&auml;ger', 'Die K&ouml;nigin');
	$rassennamen[4] = array ('Xinth-Xc', 'Hunm-oc', 'Ez-maC', 'Zao-tuX', 'Lor-ReX', 'Xor-L2R', 'Os-mTz', 'Bi-SoX', 'Facht-SoX', 'Titan-X');

	$turmnamen[0] = array ('J&auml;gergarnison', 'Raketenturm', 'Laserturm', 'Autokanonenturm', 'Plasmaturm');
	$turmnamen[1] = array ('Brechergarnison', 'Balistenturm', 'Laserlanzenturm', 'Bolzenkanonenturm', 'Plasmalanzenturm');
	$turmnamen[2] = array ('Schwarm der Nestverteidiger', 'Sporendr&uuml;se', 'Lichtdr&uuml;se', 'Materiedr&uuml;se', 'Plasmadr&uuml;se');
	$turmnamen[3] = array ('Larvenstock', 'Speichelbatterie', 'Bodenstachel', 'Giftstachelbatterie', 'Feuerstachelbatterie');
	$turmnamen[4] = array ('Xinth-Base', 'EMP-Kanonen-Styx', 'X-Magma-Styx', 'Zermalmer-Styx', 'ER-Plasmawerfer-Styx');

	$kbl_lang['abgewehrt']='Der Angriff wurde abgewehrt und es wurden keine Kollektoren gestohlen.';
	$kbl_lang['col']='Kollektor';
	$kbl_lang['cols']='Kollektoren';
	$kbl_lang['deffercollosts']="Der Angegriffene hat $kkollies Kollektor verloren.";
	$kbl_lang['deffercollostp']="Der Angegriffene hat $kkollies Kollektoren verloren.";
	$kbl_lang['angreifer']='Angreifer';
	$kbl_lang['verteidiger']='Verteidiger';
	$kbl_lang['einheit']='Einheit';
	$kbl_lang['eingesetzt']='eingesetzt';
	$kbl_lang['geblockt']='geblockt';
	$kbl_lang['ueberlebt']='&uuml;berlebt';
	$kbl_lang['statistik']='Statistik';
	$kbl_lang['tuerme']='T&uuml;rme';
	$kbl_lang['verhaeltnis']='Verh&auml;ltnis';
	$kbl_lang['einheitenanzahl']='Einheitenanzahl';
	$kbl_lang['verloreneeinheiten']='Verlorene Einheiten';
	$kbl_lang['einheitenpunktewert']='Einheitenpunktewert';
	$kbl_lang['verlorenepunkte']='Verlorene Punkte';

	//meldung für kollies zusammenbauen; das Ergebnis im Kopf ohne eigene Seite in der Farbe des Angreifers
	//(Kollektoren erobert) oder des Verteidigers (abgewehrt)
	if ($kkollies==-1){
		$kolliesatz=$kbl_lang['abgewehrt'];
		$chip='Angriff abgewehrt';
		$ton=' kb-ergebnis-v';
	}else{
		if ($kkollies==1)$kolliesatz=$kbl_lang['deffercollosts']; else $kolliesatz=$kbl_lang['deffercollostp'];
		$chip=kb_zahl($kkollies).' '.($kkollies==1 ? $kbl_lang['col'] : $kbl_lang['cols']).' erobert';
		$ton=$kkollies>0 ? ' kb-ergebnis-a' : '';
	}

	//schiffe: je beteiligter Rasse alle Einheiten, Angreifer und Verteidiger nebeneinander;
	//Summen je Seite: Anzahl, verloren, Punktewert, verlorene Punkte
	$zeilen='';
	$ges=array('a'=>array(0, 0, 0, 0), 'v'=>array(0, 0, 0, 0));
	for ($aktrasse=0;$aktrasse<$sv_anz_rassen;$aktrasse++)
	{
		if (!empty($krassenvorhanden[$aktrasse]))
		{
			$schiffsnamen = $rassennamen[$aktrasse];
			for ($i=0;$i<$sv_anz_schiffe;$i++)
			{
				if ($schiffsnamen[$i]!='NA')
				{
					$punkte=(float)($schiffspunkte[$aktrasse][$i] ?? 0);
					$gruppen=array();
					foreach(array('a'=>'einheiten_atter', 'v'=>'einheiten_deffer') as $s=>$key){
						$ein=(int)($kbd[$key][0][$aktrasse][$i] ?? 0);
						$geb=(int)($kbd[$key][1][$aktrasse][$i] ?? 0);
						$verl=(int)($kbd[$key][2][$aktrasse][$i] ?? 0);
						$gruppen[$s]=array($ein, $geb, $ein-$verl);

						//statistik erstellen
						$ges[$s][0]+=$ein;
						$ges[$s][1]+=$verl;
						$ges[$s][2]+=$ein*$punkte;
						$ges[$s][3]+=$verl*$punkte;
					}
					$zeilen.=kb_zeile($aktrasse, $schiffsnamen[$i], $gruppen['a'], $gruppen['v']);
				}
			}
		}
	}

	//türme des verteidigers, in der Farbe der angegriffenen Rasse
	$turmsumme=0;
	for ($i=0;$i<$sv_anz_tuerme;$i++){
		$turmsumme+=(int)($kbd['tuerme'][0][$i] ?? 0);
	}
	if ($turmsumme>0)
	{
		$zeilen.='<tr class="kb-trenner"><td colspan="7">'.$kbl_lang['tuerme'].'</td></tr>';
		$schiffsnamen = $turmnamen[$kturmrasse] ?? array();
		for ($i=0;$i<$sv_anz_tuerme;$i++)
		{
			if (($schiffsnamen[$i] ?? 'NA')!='NA')
			{
				$ein=(int)($kbd['tuerme'][0][$i] ?? 0);
				$geb=(int)($kbd['tuerme'][1][$i] ?? 0);
				$verl=(int)($kbd['tuerme'][2][$i] ?? 0);
				$punkte=(float)($schiffspunkte[$kturmrasse][$i+10] ?? 0);

				//statistik erstellen
				$ges['v'][0]+=$ein;
				$ges['v'][1]+=$verl;
				$ges['v'][2]+=$ein*$punkte;
				$ges['v'][3]+=$verl*$punkte;

				$zeilen.=kb_zeile($kturmrasse, $schiffsnamen[$i], null, array($ein, $geb, $ein-$verl));
			}
		}
	}

	//Hitpoints entscheiden den Sieg: weniger anteilige Verluste gewinnen (nur in neueren Berichten enthalten)
	$hp=null;
	$besser='';
	$quote=array('a'=>0, 'v'=>0);
	if(isset($daten['atter_hp']) || isset($daten['deffer_hp'])){
		$hp=array(
			'a'=>array((float)($daten['atter_hp'] ?? 0), (float)($daten['atter_hp_lost'] ?? 0)),
			'v'=>array((float)($daten['deffer_hp'] ?? 0), (float)($daten['deffer_hp_lost'] ?? 0)),
		);
		foreach(array('a', 'v') as $s){
			$quote[$s]=$hp[$s][0]>0 ? $hp[$s][1]*100/$hp[$s][0] : 0;
		}
		if($hp['a'][0]>0 && $hp['v'][0]>0 && $quote['a']!=$quote['v']){
			$besser=$quote['a']<$quote['v'] ? 'a' : 'v';
		}
	}

	//Kopf: Titel und Ergebnis; der Ort des Kampfes wird nicht genannt
	$html='<div class="kb">';
	$html.='<div class="kb-kopf"><b class="kb-ort">Kampfbericht</b><span class="kb-ergebnis'.$ton.'">'.$chip.'</span></div>';

	//beide Seiten, nur mit Account-IDs
	$html.='<div class="kb-seiten">';
	foreach(array('a'=>array($kbl_lang['angreifer'], $atter), 'v'=>array($kbl_lang['verteidiger'], $deffer)) as $s=>$p){
		$html.='<div class="kb-seite kb-seite-'.$s.'"><div class="kb-seite-kopf"><span class="mod-typ">'.$p[0].'</span>'
			.($besser==$s ? '<span class="mod-chip mod-chip-gruen" title="Diese Seite hat anteilig weniger Hitpoints verloren.">weniger Verluste</span>' : '')
			.'</div><div class="kb-namen">'.kb_seite($p[1]).'</div></div>';
	}
	$html.='</div>';
	$html.='<p class="kb-text">'.$kolliesatz.'</p>';

	//Einheiten: Angreifer und Verteidiger nebeneinander, Zeilen in der Rassenfarbe
	$html.='<table class="kb-einheiten"><thead><tr class="kb-gruppen"><th></th><th colspan="3" class="kb-ga">'.$kbl_lang['angreifer'].'</th><th colspan="3" class="kb-gv">'.$kbl_lang['verteidiger'].'</th></tr>'
		.'<tr class="kb-unter"><th>'.$kbl_lang['einheit'].'</th>'.str_repeat('<th>'.$kbl_lang['eingesetzt'].'</th><th>'.$kbl_lang['geblockt'].'</th><th>'.$kbl_lang['ueberlebt'].'</th>', 2).'</tr></thead>'
		.'<tbody>'.$zeilen.'</tbody></table>';

	//Statistik: Bilanz beider Seiten
	$html.='<div class="kb-statistik"><span class="mod-typ">'.$kbl_lang['statistik'].'</span>'
		.'<table class="kb-bilanz"><thead><tr><th></th><th>'.$kbl_lang['angreifer'].'</th><th></th><th>'.$kbl_lang['verteidiger'].'</th><th>'.$kbl_lang['verhaeltnis'].'</th></tr></thead><tbody>'
		.kb_bilanz($kbl_lang['einheitenanzahl'], $ges['a'][0], $ges['v'][0], kb_zahl($ges['a'][0]), kb_zahl($ges['v'][0]))
		.kb_bilanz($kbl_lang['verloreneeinheiten'], $ges['a'][1], $ges['v'][1], kb_zahl($ges['a'][1]).' <small>'.kb_prozent($ges['a'][1], $ges['a'][0]).'</small>', kb_zahl($ges['v'][1]).' <small>'.kb_prozent($ges['v'][1], $ges['v'][0]).'</small>')
		.kb_bilanz($kbl_lang['einheitenpunktewert'], $ges['a'][2], $ges['v'][2], kb_zahl($ges['a'][2]), kb_zahl($ges['v'][2]))
		.kb_bilanz($kbl_lang['verlorenepunkte'], $ges['a'][3], $ges['v'][3], kb_zahl($ges['a'][3]).' <small>'.kb_prozent($ges['a'][3], $ges['a'][2]).'</small>', kb_zahl($ges['v'][3]).' <small>'.kb_prozent($ges['v'][3], $ges['v'][2]).'</small>');
	if(is_array($hp)){
		$html.=kb_bilanz('Verlorene Hitpoints', $hp['a'][1], $hp['v'][1], kb_zahl($hp['a'][1]).' <small>'.number_format($quote['a'], 1, ',', '.').' %</small>', kb_zahl($hp['v'][1]).' <small>'.number_format($quote['v'], 1, ',', '.').' %</small>');
	}
	$html.='</tbody></table></div>';

	//Siegregel wie im Spiel, nur wenn beide Seiten Hitpoints hatten
	if($besser!=''){
		$html.='<details class="kb-regel"><summary>Wie wird der Sieger bestimmt?</summary><p>Es gewinnt die Seite, die weniger Hitpoints im Verh&auml;ltnis zu ihren gesamten Hitpoints verloren hat. In den Hitpoints sind alle Boni eingerechnet.<br>'
			.'Verluste '.$kbl_lang['angreifer'].': '.number_format($quote['a'], 2, ',', '.').' %<br>Verluste '.$kbl_lang['verteidiger'].': '.number_format($quote['v'], 2, ',', '.').' %</p></details>';
	}

	$html.='</div>';
	return $html;
}
