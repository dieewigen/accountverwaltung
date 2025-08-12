<?php

////////////////////////////////////////////////////////////////////////
//Serverauswahl
////////////////////////////////////////////////////////////////////////
//erlaubte Server
$allowed_server=array('xDE', 'SDE');

$show_server=isset($_REQUEST['show_server']) ? $_REQUEST['show_server'] : '';

echo '<br><div><div style="font-weight: bold;">Server: ';
$server_found=false;
for($i=0;$i<count($allowed_server);$i++){
	if($show_server==$allowed_server[$i]){
		$style=' style="font-weight: bold;"';
		$server_found=true;
	}else{
		$style=' style="font-weight: normal;"';
	}
	echo '<a'.$style.' href="index.php?command=de_kb&show_server='.$allowed_server[$i].'">'.$allowed_server[$i].'</a>&nbsp;';
}

echo '</div>';

if($server_found==true){

	$datum=date("Y-m-d", time()-3600*24);

	$result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_de_kb WHERE server = ? AND time LIKE ? ORDER BY atter ASC", [$show_server, $datum.'%']);


	echo '
	<style>
	.k1 {font-size:8pt;font-family:Tahoma;color:#3399FF;border-color:#606060;background-color:#000000;}
	.k2 {font-size:8pt;font-family:Tahoma;color:#3399FF;border-color:#606060;background:url(g/cellblack.png) repeat;}
	.k3 {font-size:8pt;font-family:Tahoma;color:#000000;border-color:#606060;background-color:#969696;}
	.k4 {font-size:8pt;font-family:Tahoma;color:#000000;border-color:#606060;background-color:#BABABA;}
	.k5 {font-size:8pt;font-family:Tahoma;color:#FFB0B0;border-color:#606060;background-color:#640404;}
	.k6 {font-size:8pt;font-family:Tahoma;color:#FFB0B0;border-color:#606060;background-color:#920606;}
	.k7 {font-size:8pt;font-family:Tahoma;color:#ED951E;border-color:#606060;background-color:#304802;}
	.k8 {font-size:8pt;font-family:Tahoma;color:#ED951E;border-color:#606060;background-color:#3A5C02;}
	.k9 {font-size:8pt;font-family:Tahoma;color:#FFFF40;border-color:#606060;background-color:#710272;}
	.k10 {font-size:8pt;font-family:Tahoma;color:#FFFF40;border-color:#606060;background-color:#7A048C;}
	</style>

		';

	echo '<br><div style="font-weight: bold;">'.$show_server.'-Kampfberichte von gestern:</div><br><br>';

	//echo '<div style="width: 100%; background-color: #FF0000;text-align: center;">';

	while($row = mysqli_fetch_array($result)){
		if($row['kbversion']==0){
			echo showkampfberichtV0($row['kb'],$row['atter'],$row['deffer']);
			echo '<br><hr style="width: 100%;"><br>';
		}elseif($row['kbversion']==1){
			echo showkampfberichtV1($row['kb'],$row['atter'],$row['deffer']);
			echo '<br><hr style="width: 100%;"><br>';
			
		}
	}
}else{
	
	echo '<div style="padding: 20px; border: 1px solid #00FF00; text-align: center; margin-top: 20px; color: #00FF00; font-weight: bold;">W&auml;hle bitte einen Server aus um die Kampfberichte einsehen zu k&ouml;nnen.</div>';
	
}
//echo '</div>';

function showkampfberichtV0($text, $atter, $deffer){
  
	$ums_rasse=1;
	
	$schiffspunkte=unserialize('a:5:{i:0;a:13:{i:0;s:3:"150";i:1;s:3:"600";i:2;s:4:"2800";i:3;s:4:"5900";i:4;s:5:"13200";i:5;s:3:"250";i:6;s:3:"400";i:7;s:5:"15500";i:8;s:4:"1500";i:9;s:3:"190";i:10;s:3:"125";i:11;s:3:"325";i:12;s:3:"550";}i:1;a:13:{i:0;s:3:"155";i:1;s:3:"550";i:2;s:4:"2900";i:3;s:4:"5900";i:4;s:5:"13900";i:5;s:3:"200";i:6;s:3:"400";i:7;s:5:"16600";i:8;s:4:"1500";i:9;s:3:"135";i:10;s:2:"75";i:11;s:3:"260";i:12;s:3:"525";}i:2;a:13:{i:0;s:3:"175";i:1;s:3:"700";i:2;s:4:"2850";i:3;s:4:"5780";i:4;s:5:"12300";i:5;s:3:"200";i:6;s:3:"400";i:7;s:5:"17200";i:8;s:4:"1750";i:9;s:3:"160";i:10;s:3:"150";i:11;s:3:"340";i:12;s:3:"615";}i:3;a:13:{i:0;s:2:"80";i:1;s:3:"500";i:2;s:4:"2800";i:3;s:4:"5000";i:4;s:5:"12100";i:5;s:3:"250";i:6;s:3:"400";i:7;s:5:"15500";i:8;s:3:"800";i:9;s:3:"170";i:10;s:2:"90";i:11;s:3:"295";i:12;s:3:"530";}i:4;a:13:{i:0;s:3:"160";i:1;s:3:"625";i:2;s:4:"2840";i:3;s:4:"5525";i:4;s:5:"12600";i:5;s:3:"225";i:6;s:3:"400";i:7;s:5:"14800";i:8;s:4:"1550";i:9;s:3:"165";i:10;s:3:"110";i:11;s:3:"300";i:12;s:3:"615";}}');
	
	$sv_anz_schiffe=8;
	$sv_anz_tuerme=5;
	$sv_anz_rassen=5;
  
  $rassenklassen[0] = array ('k1', 'k2');
  $rassenklassen[1] = array ('k3', 'k4');
  $rassenklassen[2] = array ('k5', 'k6');
  $rassenklassen[3] = array ('k7', 'k8');
  $rassenklassen[4] = array ('k9', 'k10');

  //kb in seine bestandteile zerlegen
  $kbd=explode(";",$text);
  
  //var_dump($kbd);

  //daten aus dem kb holen
  $grundindex=255;
  $kkollies=$kbd[$grundindex];
  $ksec=$kbd[$grundindex+1];
  $ksys=$kbd[$grundindex+2];
  $krassenvorhanden[0]=$kbd[$grundindex+3];
  $krassenvorhanden[1]=$kbd[$grundindex+4];
  $krassenvorhanden[2]=$kbd[$grundindex+5];
  $krassenvorhanden[3]=$kbd[$grundindex+6];
  $krassenvorhanden[4]=$kbd[$grundindex+7];
  $kturmrasse=$kbd[$grundindex+8];
  $atterliste=$kbd[$grundindex+9];
  $defferliste=$kbd[$grundindex+10];

  $grundindex=255;
  $kollieserbeutet=$kbd[$grundindex+0];
  $exp=$kbd[$grundindex+1];
  $kartefakte=$kbd[$grundindex+2];
  $srec1=$kbd[$grundindex+3];
  $srec2=$kbd[$grundindex+4];

  //eigenen spielername fett darstellen
  if($ums_rasse==1)$rflag='E';
  elseif($ums_rasse==2)$rflag='I';
  elseif($ums_rasse==3)$rflag='K';
  elseif($ums_rasse==4)$rflag='Z';
  elseif($ums_rasse==5)$rflag='D';

  
  $username=$ums_spielername.' ['.$rflag.']('.$sector.':'.$system.')';
  $atterliste=str_replace($username, '<b>'.$username.'</b>', $atterliste);
  $defferliste=str_replace($username, '<b>'.$username.'</b>', $defferliste);

  $exp=number_format($exp, 0,"",".");

  //sprachdatei einbinden
  unset($kbl_lang);
$rassennamen[0] = array ('Hornisse', 'Guillotine', 'Schakal', 'Marauder', 'Zerberus', 'Nachtmar', 'Transmitterschiff', 'Hydra');
$rassennamen[1] = array ('Caesar', 'Paladin', 'Vollstrecker', 'Imperator', 'Excalibur', 'Phalanx', 'Merlin', 'Colossus');
$rassennamen[2] = array ('Spider', 'Arctic Spider', 'Werespider', 'Tarantula', 'Black Widow', 'Hellspider', 'Netzf&auml;nger', 'Gigantula');
$rassennamen[3] = array ('Wespe', 'Feuerskorpion', 'Geisterschrecke', 'Skarab&auml;us', 'Mantis', 'H&ouml;llenk&auml;fer', 'Sammler', 'Ekelbr&uuml;ter');
$rassennamen[4] = array ('Xinth-Xc', 'Hunm-oc', 'Ez-maC', 'Zao-tuX', 'Lor-ReX', 'Xor-L2R', 'Os-mTz', 'Bi-SoX');

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
$kbl_lang['attercolwins']=" Deine Flotte hat {WERT1} Kollektor erbeutet.";
$kbl_lang['attercolwinp']=" Deine Flotte hat {WERT1} Kollektoren erbeutet.";
$kbl_lang['attercolwinsdestroy']=" Deine Flotte hat {WERT1} Kollektor zerst&ouml;rt.";
$kbl_lang['attercolwinpdestroy']=" Deine Flotte hat {WERT1} Kollektoren zerst&ouml;rt.";
$kbl_lang['angreifer']='Angreifer';
$kbl_lang['verteidiger']='Verteidiger';
$kbl_lang['erhaltenekartefakt']='Erhaltene Kriegsartefakte';
$kbl_lang['recycling']='Recycling';
$kbl_lang['angreifer']='Angreifer';
$kbl_lang['verteidiger']='Verteidiger';
$kbl_lang['eigene']='Eigene';
$kbl_lang['einheit']='Einheit';
$kbl_lang['eingesetzt']='eingesetzt';
$kbl_lang['geblockt']='geblockt';
$kbl_lang['ueberlebt']='&uuml;berlebt';
$kbl_lang['statistik']='Statistik';
$kbl_lang['tuerme']='T&uuml;rme';
$kbl_lang['typ']='Typ';
$kbl_lang['verhaeltnis']='Verh&auml;ltnis';
$kbl_lang['einheitenanzahl']='Einheitenanzahl';
$kbl_lang['verloreneeinheiten']='Verlorene Einheiten';
$kbl_lang['einheitenpunktewert']='Einheitenpunktewert';
$kbl_lang['verlorenepunkte']='Verlorene Punkte';


  //meldung f�r kollies zusammenbauen
  if ($kkollies==-1)$kolliesatz=$kbl_lang['abgewehrt'];
  else
  {
    if ($kkollies==1)$kolliesatz=$kbl_lang['deffercollosts']; else $kolliesatz=$kbl_lang['deffercollostp'];
    
	/*
    if ($kollieserbeutet>0)
    {
      if ($kollieserbeutet==1)$kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwins']); 
      else $kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinp']);
    }
    elseif($kollieserbeutet<0)
    {
      $kollieserbeutet=$kollieserbeutet*(-1);
      if ($kollieserbeutet==1)$kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinsdestroy']); 
      else $kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinpdestroy']);
    }

	 */
    //$kolliesatz=$kbl_lang['deffercollost'];
    //$kolliesatz.=$kbl_lang['attercolwin'];
  }

  //zuerst den header
  $kbstring='
<table cellSpacing=0 cellPadding=2 width=555 border=1>
<tr align="center">
<td class="k1" width="15%"><b>'.$kbl_lang['angreifer'].':</b></td>
<td class="k1" width="85%">'.$atter.'</td>
</tr>
<tr align="center">
<td class="k1""><b>'.$kbl_lang['verteidiger'].':</b></td>
<td class="k1"">'.$deffer.'</td>
</tr>
<tr align="center">
<td class="k1" colspan="2" align="left"><b>'.$kolliesatz.'
</td>
</tr>
</table>
<br>
<TABLE cellSpacing="0" cellPadding="2" width="555" border="1">
<tr align="center">
<td class="k1" width="14%">&nbsp;</td>
<td class="k1" width="43%" colSpan=3><u>'.$kbl_lang['angreifer'].'</u></td>
<td class="k1" width="43%" colSpan=3><u>'.$kbl_lang['verteidiger'].'</u></td>
</tr>
<tr align="center" width="14%">
<td class="k2">'.$kbl_lang['einheit'].'</td>
<td class="k2">'.$kbl_lang['eingesetzt'].'</td>
<td class="k2">'.$kbl_lang['geblockt'].'</td>
<td class="k2">'.$kbl_lang['ueberlebt'].'</td>
<td class="k2">'.$kbl_lang['eingesetzt'].'</td>
<td class="k2">'.$kbl_lang['geblockt'].'</td>
<td class="k2">'.$kbl_lang['ueberlebt'].'</td>
</tr>';

$geseinheiten_atter_anz=0;
$geseinheiten_deffer_anz=0;
$geseinheiten_atter_anz_verloren=0;
$geseinheiten_deffer_anz_verloren=0;
$geseinheiten_atter_score=0;
$geseinheiten_deffer_score=0;
$geseinheiten_atter_score_lost=0;
$geseinheiten_deffer_score_lost=0;

////////////////////////////////////////////////
////////////////////////////////////////////////
//schiffe
////////////////////////////////////////////////
////////////////////////////////////////////////  
  $grundindex1=0;
  for ($aktrasse=0;$aktrasse<$sv_anz_rassen;$aktrasse++)
  {
    if ($krassenvorhanden[$aktrasse]>0)
    {
      $schiffsnamen = $rassennamen[$aktrasse];
      $c1=0;$c2=0;
      for ($i=0;$i<$sv_anz_schiffe;$i++)
      {
        if ($schiffsnamen[$i]!='NA')
        {
          if ($c1==0){$c1=1; $klasse=$rassenklassen[$aktrasse][0];}
          else {$c1=0; $klasse=$rassenklassen[$aktrasse][1];}

          $grundindex2=$grundindex1+40;
          $grundindex3=$grundindex2+40;
          $grundindex4=$grundindex3+40;
          $grundindex5=$grundindex4+40;
          $grundindex6=$grundindex5+40;

          if ($rasse-1!=$aktrasse)//wenn es nicht die eigene rasse ist leer lassen
          {
            $keigene1 = '&nbsp';
            $keigene2 = $keigene1;
            $keigene3 = $keigene1;
          }
          else //ansonsten variablen in die arrays packen
          {
            $grundindex=255;
            //for ($j=0;$j<$sv_anz_schiffe;$j++){
				$keigene1 = number_format($kbd[$grundindex+$i], 0,"",".");
			//}

            //for ($j=0;$j<$sv_anz_schiffe;$j++){
				if($kbd[$grundindex+8+$i]>$kbd[$grundindex5+$i])$kbd[$grundindex+8+$i]=$kbd[$grundindex5+$i];
				$keigene2 = number_format($kbd[$grundindex+8+$i], 0,"",".");
				
			//}

            //for ($j=0;$j<$sv_anz_schiffe;$j++){
				if($kbd[$grundindex+16+$i]>$kbd[$grundindex6+$i])$kbd[$grundindex+16+$i]=$kbd[$grundindex6+$i];
				$keigene3 = number_format($kbd[$grundindex+16+$i], 0,"",".");
			//}
          }
		  


          $kbstring.='
<TR align="center">
<TD class="'.$klasse.'">'.$schiffsnamen[$i].'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex1+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex2+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex3+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex4+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex5+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex6+$i], 0,"",".").'</TD>
</TR>';
          //statistik erstellen
          $geseinheiten_atter_anz+=$kbd[$grundindex1+$i];
          $geseinheiten_deffer_anz+=$kbd[$grundindex4+$i];
          
          $geseinheiten_atter_anz_verloren+=($kbd[$grundindex1+$i]-$kbd[$grundindex3+$i]);
          $geseinheiten_deffer_anz_verloren+=($kbd[$grundindex4+$i]-$kbd[$grundindex6+$i]);
          
          $geseinheiten_atter_score+=$kbd[$grundindex1+$i]*$schiffspunkte[$aktrasse][$i];
          $geseinheiten_deffer_score+=$kbd[$grundindex4+$i]*$schiffspunkte[$aktrasse][$i];
          
          $geseinheiten_atter_score_lost+=($kbd[$grundindex1+$i]-$kbd[$grundindex3+$i])*$schiffspunkte[$aktrasse][$i];
          $geseinheiten_deffer_score_lost+=($kbd[$grundindex4+$i]-$kbd[$grundindex6+$i])*$schiffspunkte[$aktrasse][$i];
          
        }
      }
    }
    $grundindex1=$grundindex1+8;
  }

////////////////////////////////////////////////
////////////////////////////////////////////////
//t�rme
////////////////////////////////////////////////
////////////////////////////////////////////////

$grundindex1=240;
if (($kbd[$grundindex1]+$kbd[$grundindex1+1]+$kbd[$grundindex1+2]+$kbd[$grundindex1+3]+$kbd[$grundindex1+4])>0)
{
$kbstring.='
<TR align="center">
<TD class="k1" colSpan="7"><u>'.$kbl_lang['tuerme'].'</u></TD>
</TR>
';
  $aktrasse=$kturmrasse;

  $schiffsnamen = $turmnamen[$aktrasse];
  $c1=0;$c2=0;
  for ($i=0;$i<$sv_anz_tuerme;$i++)
  {
    if ($schiffsnamen[$i]!='NA')
    {
      if ($c1==0){$c1=1; $klasse=$rassenklassen[$aktrasse][0];}
      else {$c1=0; $klasse=$rassenklassen[$aktrasse][1];}

      $grundindex2=$grundindex1+5;
      $grundindex3=$grundindex2+5;

      $kbstring.='
<TR align="center">
<TD class="'.$klasse.'">'.$schiffsnamen[$i].'</TD>
<TD class="'.$klasse.'">-</TD>
<TD class="'.$klasse.'">-</TD>
<TD class="'.$klasse.'">-</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex1+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex2+$i], 0,"",".").'</TD>
<TD class="'.$klasse.'">'.number_format($kbd[$grundindex3+$i], 0,"",".").'</TD>
</TR>';
      
      //statistik erstellen
      $geseinheiten_deffer_anz+=$kbd[$grundindex1+$i];
      $geseinheiten_deffer_anz_verloren+=($kbd[$grundindex1+$i]-$kbd[$grundindex3+$i]);
      
      $geseinheiten_deffer_score+=$kbd[$grundindex1+$i]*$schiffspunkte[$aktrasse][$i+8];
      $geseinheiten_deffer_score_lost+=($kbd[$grundindex1+$i]-$kbd[$grundindex3+$i])*$schiffspunkte[$aktrasse][$i+8];
    }
    //$grundindex1=$grundindex1+5;
  }
}

$kbstring.=
'</TABLE>';
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//  statistik
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
$kbstring.='
<br><table cellSpacing=0 cellPadding=2 width=555 border=1>
<tr align="center"><td colspan="4" class="k1"><b>'.$kbl_lang['statistik'].'</b></td></tr>
<tr align="center">
<td  class="k2"><b>'.$kbl_lang['typ'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['angreifer'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['verteidiger'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['verhaeltnis'].'</b></td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_anz>0)
$verhaeltnis=$geseinheiten_deffer_anz/$geseinheiten_atter_anz;
else $verhaeltnis=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['einheitenanzahl'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_anz, 0,"",".").'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_anz, 0,"",".").'</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}

//das verh�ltnis berechnen
if ($geseinheiten_atter_anz_verloren>0)
$verhaeltnis=$geseinheiten_deffer_anz_verloren/$geseinheiten_atter_anz_verloren;
else $verhaeltnis=0;
//den prozentwert berechnen
$prozent_atter=$geseinheiten_atter_anz_verloren*100/$geseinheiten_atter_anz;
if($geseinheiten_deffer_anz>0)$prozent_deffer=$geseinheiten_deffer_anz_verloren*100/$geseinheiten_deffer_anz;
else $prozent_deffer=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['verloreneeinheiten'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_anz_verloren, 0,"",".").' ('.number_format($prozent_atter, 2,",",".").'%)</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_anz_verloren, 0,"",".").' ('.number_format($prozent_deffer, 2,",",".").'%)</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';
if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_score>0)
$verhaeltnis=$geseinheiten_deffer_score/$geseinheiten_atter_score;
else $verhaeltnis=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['einheitenpunktewert'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_score, 0,"",".").'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_score, 0,"",".").'</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_score_lost>0)
$verhaeltnis=$geseinheiten_deffer_score_lost/$geseinheiten_atter_score_lost;
else $verhaeltnis=0;
//den prozentwert berechnen
$prozent_atter=$geseinheiten_atter_score_lost*100/$geseinheiten_atter_score;
if($geseinheiten_deffer_score>0)$prozent_deffer=$geseinheiten_deffer_score_lost*100/$geseinheiten_deffer_score;
else $prozent_deffer=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['verlorenepunkte'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_score_lost, 0,"",".").' ('.number_format($prozent_atter, 2,",",".").'%)</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_score_lost, 0,"",".").' ('.number_format($prozent_deffer, 2,",",".").'%)</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

$kbstring.='</table>';

  return($kbstring);
}

function showkampfberichtV1($text, $atter, $deffer){
  
	$ums_rasse=1;
	
	$schiffspunkte=unserialize('a:5:{i:0;a:15:{i:0;i:150;i:1;i:600;i:2;i:2800;i:3;i:5900;i:4;i:13200;i:5;i:250;i:6;i:400;i:7;i:15500;i:8;i:950;i:9;i:115000;i:10;i:1500;i:11;i:190;i:12;i:125;i:13;i:325;i:14;i:550;}i:1;a:15:{i:0;i:155;i:1;i:550;i:2;i:2900;i:3;i:5900;i:4;i:13900;i:5;i:200;i:6;i:400;i:7;i:16600;i:8;i:850;i:9;i:122000;i:10;i:1500;i:11;i:135;i:12;i:75;i:13;i:260;i:14;i:525;}i:2;a:15:{i:0;i:175;i:1;i:700;i:2;i:2850;i:3;i:5780;i:4;i:12300;i:5;i:200;i:6;i:400;i:7;i:17200;i:8;i:990;i:9;i:116000;i:10;i:1750;i:11;i:160;i:12;i:150;i:13;i:340;i:14;i:615;}i:3;a:15:{i:0;i:80;i:1;i:500;i:2;i:2800;i:3;i:5000;i:4;i:12100;i:5;i:250;i:6;i:400;i:7;i:15500;i:8;i:980;i:9;i:104000;i:10;i:800;i:11;i:170;i:12;i:90;i:13;i:295;i:14;i:530;}i:4;a:15:{i:0;i:160;i:1;i:625;i:2;i:2840;i:3;i:5525;i:4;i:12600;i:5;i:225;i:6;i:400;i:7;i:16600;i:8;i:950;i:9;i:115000;i:10;i:1550;i:11;i:165;i:12;i:110;i:13;i:300;i:14;i:615;}}');
	
	$sv_anz_schiffe=10;
	$sv_anz_tuerme=5;
	$sv_anz_rassen=5;
  
	$rassenklassen[0] = array ('k1', 'k2');
	$rassenklassen[1] = array ('k3', 'k4');
	$rassenklassen[2] = array ('k5', 'k6');
	$rassenklassen[3] = array ('k7', 'k8');
	$rassenklassen[4] = array ('k9', 'k10');

	//kb in seine bestandteile zerlegen
	$kbd=unserialize($text);

	//var_dump($kbd);

	//daten aus dem kb holen
	$kkollies=$kbd['daten']['colstolen'];
	$ksec=$kbd['daten']['sector'];;
	$ksys=$kbd['daten']['system'];;
	$krassenvorhanden[0]=$kbd['daten']['rassen'][0];
	$krassenvorhanden[1]=$kbd['daten']['rassen'][1];
	$krassenvorhanden[2]=$kbd['daten']['rassen'][2];
	$krassenvorhanden[3]=$kbd['daten']['rassen'][3];
	$krassenvorhanden[4]=$kbd['daten']['rassen'][4];
	$kturmrasse=$kbd['daten']['target_rasse'];
	$atterliste=$kbd['daten']['atterliste'];
	$defferliste=$kbd['daten']['defferliste'];

  //eigenen spielername fett darstellen
  if($ums_rasse==1)$rflag='E';
  elseif($ums_rasse==2)$rflag='I';
  elseif($ums_rasse==3)$rflag='K';
  elseif($ums_rasse==4)$rflag='Z';
  elseif($ums_rasse==5)$rflag='D';

  /*
  $username=$ums_spielername.' ['.$rflag.']('.$sector.':'.$system.')';
  $atterliste=str_replace($username, '<b>'.$username.'</b>', $atterliste);
  $defferliste=str_replace($username, '<b>'.$username.'</b>', $defferliste);
*/
  //$exp=number_format($exp, 0,"",".");

  //sprachdatei einbinden
  unset($kbl_lang);
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
$kbl_lang['attercolwins']=" Deine Flotte hat {WERT1} Kollektor erbeutet.";
$kbl_lang['attercolwinp']=" Deine Flotte hat {WERT1} Kollektoren erbeutet.";
$kbl_lang['attercolwinsdestroy']=" Deine Flotte hat {WERT1} Kollektor zerst&ouml;rt.";
$kbl_lang['attercolwinpdestroy']=" Deine Flotte hat {WERT1} Kollektoren zerst&ouml;rt.";
$kbl_lang['angreifer']='Angreifer';
$kbl_lang['verteidiger']='Verteidiger';
$kbl_lang['erhaltenekartefakt']='Erhaltene Kriegsartefakte';
$kbl_lang['recycling']='Recycling';
$kbl_lang['angreifer']='Angreifer';
$kbl_lang['verteidiger']='Verteidiger';
$kbl_lang['eigene']='Eigene';
$kbl_lang['einheit']='Einheit';
$kbl_lang['eingesetzt']='eingesetzt';
$kbl_lang['geblockt']='geblockt';
$kbl_lang['ueberlebt']='&uuml;berlebt';
$kbl_lang['statistik']='Statistik';
$kbl_lang['tuerme']='T&uuml;rme';
$kbl_lang['typ']='Typ';
$kbl_lang['verhaeltnis']='Verh&auml;ltnis';
$kbl_lang['einheitenanzahl']='Einheitenanzahl';
$kbl_lang['verloreneeinheiten']='Verlorene Einheiten';
$kbl_lang['einheitenpunktewert']='Einheitenpunktewert';
$kbl_lang['verlorenepunkte']='Verlorene Punkte';


  //meldung f�r kollies zusammenbauen
  if ($kkollies==-1)$kolliesatz=$kbl_lang['abgewehrt'];
  else
  {
    if ($kkollies==1)$kolliesatz=$kbl_lang['deffercollosts']; else $kolliesatz=$kbl_lang['deffercollostp'];
    
	/*
    if ($kollieserbeutet>0)
    {
      if ($kollieserbeutet==1)$kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwins']); 
      else $kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinp']);
    }
    elseif($kollieserbeutet<0)
    {
      $kollieserbeutet=$kollieserbeutet*(-1);
      if ($kollieserbeutet==1)$kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinsdestroy']); 
      else $kolliesatz.=str_replace("{WERT1}", $kollieserbeutet,$kbl_lang['attercolwinpdestroy']);
    }

	 */
    //$kolliesatz=$kbl_lang['deffercollost'];
    //$kolliesatz.=$kbl_lang['attercolwin'];
  }

  //zuerst den header
  $kbstring='
<table cellSpacing=0 cellPadding=2 width=555 border=1>
<tr align="center">
<td class="k1" width="15%"><b>'.$kbl_lang['angreifer'].':</b></td>
<td class="k1" width="85%">'.$atter.'</td>
</tr>
<tr align="center">
<td class="k1""><b>'.$kbl_lang['verteidiger'].':</b></td>
<td class="k1"">'.$deffer.'</td>
</tr>
<tr align="center">
<td class="k1" colspan="2" align="left"><b>'.$kolliesatz.'
</td>
</tr>
</table>
<br>
<TABLE cellSpacing="0" cellPadding="2" width="555" border="1">
<tr align="center">
<td class="k1" width="14%">&nbsp;</td>
<td class="k1" width="43%" colSpan=3><u>'.$kbl_lang['angreifer'].'</u></td>
<td class="k1" width="43%" colSpan=3><u>'.$kbl_lang['verteidiger'].'</u></td>
</tr>
<tr align="center" width="14%">
<td class="k2">'.$kbl_lang['einheit'].'</td>
<td class="k2">'.$kbl_lang['eingesetzt'].'</td>
<td class="k2">'.$kbl_lang['geblockt'].'</td>
<td class="k2">'.$kbl_lang['ueberlebt'].'</td>
<td class="k2">'.$kbl_lang['eingesetzt'].'</td>
<td class="k2">'.$kbl_lang['geblockt'].'</td>
<td class="k2">'.$kbl_lang['ueberlebt'].'</td>
</tr>';

$geseinheiten_atter_anz=0;
$geseinheiten_deffer_anz=0;
$geseinheiten_atter_anz_verloren=0;
$geseinheiten_deffer_anz_verloren=0;
$geseinheiten_atter_score=0;
$geseinheiten_deffer_score=0;
$geseinheiten_atter_score_lost=0;
$geseinheiten_deffer_score_lost=0;

////////////////////////////////////////////////
////////////////////////////////////////////////
//schiffe
////////////////////////////////////////////////
////////////////////////////////////////////////  
  $grundindex1=0;
  for ($aktrasse=0;$aktrasse<$sv_anz_rassen;$aktrasse++)
  {
    if ($krassenvorhanden[$aktrasse]>0)
    {
      $schiffsnamen = $rassennamen[$aktrasse];
      $c1=0;$c2=0;
      for ($i=0;$i<$sv_anz_schiffe;$i++)
      {
        if ($schiffsnamen[$i]!='NA')
        {
          if ($c1==0){$c1=1; $klasse=$rassenklassen[$aktrasse][0];}
          else {$c1=0; $klasse=$rassenklassen[$aktrasse][1];}

			$kbstring.='
		  <TR align="center">
		  <TD class="'.$klasse.'">'.$schiffsnamen[$i].'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_atter'][0][$aktrasse][$i], 0,"",".").'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_atter'][1][$aktrasse][$i], 0,"",".").'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_atter'][0][$aktrasse][$i]-$kbd['einheiten_atter'][2][$aktrasse][$i], 0,"",".").'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_deffer'][0][$aktrasse][$i], 0,"",".").'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_deffer'][1][$aktrasse][$i], 0,"",".").'</TD>
		  <TD class="'.$klasse.'">'.number_format($kbd['einheiten_deffer'][0][$aktrasse][$i]-$kbd['einheiten_deffer'][2][$aktrasse][$i], 0,"",".").'</TD>
		  </TR>';
		  
			//statistik erstellen
			$geseinheiten_atter_anz+=$kbd['einheiten_atter'][0][$aktrasse][$i];
			$geseinheiten_deffer_anz+=$kbd['einheiten_deffer'][0][$aktrasse][$i];

			$geseinheiten_atter_anz_verloren+=$kbd['einheiten_atter'][2][$aktrasse][$i];
			$geseinheiten_deffer_anz_verloren+=$kbd['einheiten_deffer'][2][$aktrasse][$i];

			$geseinheiten_atter_score+=$kbd['einheiten_atter'][0][$aktrasse][$i]*$schiffspunkte[$aktrasse][$i];
			$geseinheiten_deffer_score+=$kbd['einheiten_deffer'][0][$aktrasse][$i]*$schiffspunkte[$aktrasse][$i];

			$geseinheiten_atter_score_lost+=$kbd['einheiten_atter'][2][$aktrasse][$i]*$schiffspunkte[$aktrasse][$i];
			$geseinheiten_deffer_score_lost+=$kbd['einheiten_deffer'][2][$aktrasse][$i]*$schiffspunkte[$aktrasse][$i];

          
        }
      }
    }
  }

////////////////////////////////////////////////
////////////////////////////////////////////////
//t�rme
////////////////////////////////////////////////
////////////////////////////////////////////////
if(($kbd['tuerme'][0][0]+$kbd['tuerme'][0][1]+$kbd['tuerme'][0][2]+$kbd['tuerme'][0][3]+$kbd['tuerme'][0][4])>0){
$kbstring.='
<TR align="center">
<TD class="k1" colSpan="7"><u>'.$kbl_lang['tuerme'].'</u></TD>
</TR>
';
  $aktrasse=$kturmrasse;

  $schiffsnamen = $turmnamen[$aktrasse];
  $c1=0;$c2=0;
  for ($i=0;$i<$sv_anz_tuerme;$i++)
  {
    if ($schiffsnamen[$i]!='NA')
    {
      if ($c1==0){$c1=1; $klasse=$rassenklassen[$aktrasse][0];}
      else {$c1=0; $klasse=$rassenklassen[$aktrasse][1];}

			  $kbstring.='
		<TR align="center">
		<TD class="'.$klasse.'">'.$schiffsnamen[$i].'</TD>
		<TD class="'.$klasse.'">-</TD>
		<TD class="'.$klasse.'">-</TD>
		<TD class="'.$klasse.'">-</TD>
		<TD class="'.$klasse.'">'.number_format($kbd['tuerme'][0][$i], 0,"",".").'</TD>
		<TD class="'.$klasse.'">'.number_format($kbd['tuerme'][1][$i], 0,"",".").'</TD>
		<TD class="'.$klasse.'">'.number_format($kbd['tuerme'][0][$i]-$kbd['tuerme'][2][$i], 0,"",".").'</TD>
		</TR>';

			  //statistik erstellen
			  $geseinheiten_deffer_anz+=$kbd['tuerme'][0][$i];
			  $geseinheiten_deffer_anz_verloren+=$kbd['tuerme'][2][$i];

			  $geseinheiten_deffer_score+=$kbd['tuerme'][0][$i]*$schiffspunkte[$aktrasse][$i+8];
			  $geseinheiten_deffer_score_lost+=$kbd['tuerme'][2][$i]*$schiffspunkte[$aktrasse][$i+8];
	  
	  
	  
    }
    //$grundindex1=$grundindex1+5;
  }
}

$kbstring.=
'</TABLE>';
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//  statistik
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
$kbstring.='
<br><table cellSpacing=0 cellPadding=2 width=555 border=1>
<tr align="center"><td colspan="4" class="k1"><b>'.$kbl_lang['statistik'].'</b></td></tr>
<tr align="center">
<td  class="k2"><b>'.$kbl_lang['typ'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['angreifer'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['verteidiger'].'</b></td>
<td  class="k2"><b>'.$kbl_lang['verhaeltnis'].'</b></td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_anz>0)
$verhaeltnis=$geseinheiten_deffer_anz/$geseinheiten_atter_anz;
else $verhaeltnis=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['einheitenanzahl'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_anz, 0,"",".").'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_anz, 0,"",".").'</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}

//das verh�ltnis berechnen
if ($geseinheiten_atter_anz_verloren>0)
$verhaeltnis=$geseinheiten_deffer_anz_verloren/$geseinheiten_atter_anz_verloren;
else $verhaeltnis=0;
//den prozentwert berechnen
$prozent_atter=$geseinheiten_atter_anz_verloren*100/$geseinheiten_atter_anz;
if($geseinheiten_deffer_anz>0)$prozent_deffer=$geseinheiten_deffer_anz_verloren*100/$geseinheiten_deffer_anz;
else $prozent_deffer=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['verloreneeinheiten'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_anz_verloren, 0,"",".").' ('.number_format($prozent_atter, 2,",",".").'%)</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_anz_verloren, 0,"",".").' ('.number_format($prozent_deffer, 2,",",".").'%)</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';
if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_score>0)
$verhaeltnis=$geseinheiten_deffer_score/$geseinheiten_atter_score;
else $verhaeltnis=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['einheitenpunktewert'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_score, 0,"",".").'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_score, 0,"",".").'</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

if ($c1==0){$c1=1;$bg='k2';}else{$c1=0;$bg='k1';}
//das verh�ltnis berechnen
if ($geseinheiten_atter_score_lost>0)
$verhaeltnis=$geseinheiten_deffer_score_lost/$geseinheiten_atter_score_lost;
else $verhaeltnis=0;
//den prozentwert berechnen
$prozent_atter=$geseinheiten_atter_score_lost*100/$geseinheiten_atter_score;
if($geseinheiten_deffer_score>0)$prozent_deffer=$geseinheiten_deffer_score_lost*100/$geseinheiten_deffer_score;
else $prozent_deffer=0;
$kbstring.='
<tr align="center">
<td  class="'.$bg.'">'.$kbl_lang['verlorenepunkte'].'</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_atter_score_lost, 0,"",".").' ('.number_format($prozent_atter, 2,",",".").'%)</td>
<td  class="'.$bg.'">'.number_format($geseinheiten_deffer_score_lost, 0,"",".").' ('.number_format($prozent_deffer, 2,",",".").'%)</td>
<td  class="'.$bg.'">1:'.number_format($verhaeltnis, 2,",",".").'</td>
</tr>';

$kbstring.='</table>';

  return($kbstring);
}

?>
