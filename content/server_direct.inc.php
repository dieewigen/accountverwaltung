<?php
include 'content/de/lang/'.$ums_language.'_server.lang.php';

if($_SESSION['ums_user_id']!=1){
	//die('Wartungsarbeiten. Die Ticks stehen.');
}

//alle Server darstellen
for ($i=0;$i<=$sindex;$i++){
  if($serverdata[$i][2]!='')
  $sstr=$server_lang['wtick'].': '.$serverdata[$i][2].'<br>'.
        $server_lang['ktick'].': '.$serverdata[$i][3];
  else $sstr='';
  $stip[$i] = '<b>'.$serverdata[$i][0].' - '.$serverdata[$i][1].'</b><br>'.$sstr.'"];';
}

$containerold=-1;

//serverliste ausgeben
//echo '<table width="97%" border="0" cellpadding="0" cellspacing="0">';
echo '<div style="width: 100%; position: relative; color: #000000;">';
//$stipids='';
for ($i=0;$i<=$sindex;$i++){
	//schauen ob sich der spieltyp ändert
	$hinweis='';
    if($containerold!=$serverdata[$i]['container']){
		//spielnamen ausgeben
		echo '<div style="margin-bottom: 15px; margin-top: 20px; width: 100%; float: left; font-size: 24px; color: '.$serverdata[$i]['containercolor'].'"><b>'.$gamename[$serverdata[$i]['container']].'</b></div>';
		
		//Hinweistext bzgl. Testserver
		/*
		if($gamename[$serverdata[$i]['container']]=='DIE EWIGEN'){
			$hinweis='
			<div style="padding: 5px; border: 1px solid #39F; margin-bottom: 5px; color: #FF0000; float: left; width: 98.5%;">
				ACHTUNG: DDE ist TESTSERVER. <span id="hidden_show1" style="cursor: pointer; color: #3399FF;" onclick="showHiddenInfo(1);">mehr...</span>
				<span id="hidden_info1" style="display: none;">
				DDE hat SDE tempor&auml;r als Testserver abgel&ouml;st. Dort wird eine neue DE-Version entwickelt. Wer auf die Entwicklung Einfluss haben m&ouml;chte, dem wird empfohlen sich dort zu beteiligen. Es ist nat&uuml;rlich keine Pflicht dort mitzuwirken, aber wer darauf verzichtet, sollte damit rechnen, dass seine Meinung nicht beachtet wird und sp&auml;tere Beschwerden u.U. auch nichts mehr bringen.
				</span>
			</div>';
		}
		*/

	}
    
	echo $hinweis;
	
	echo '<div class="game_box"><div class="game_box_'.($serverdata[$i]['containerbg']).'" style="background: top center url(img/game_box_'.($serverdata[$i]['containerbg']+1).'.png);">';

    //ausgabe des servernamens    
    echo '<span class="game_short" id="stip'.$i.'" title="'.$stip[$i].'" rel="tooltip"><b>'.$serverdata[$i][0].'</b></span>';
    
    //ausgabe der serverinformation
	//echo '<span style="top: 4px; left: 28px; position: absolute;"><img id="stip'.$i.'" style="vertical-align: middle;" src="img/i1.gif" border="0" title="'.$stip[$i].'"></span>';
	/*
    if($stipids!='')$stipids.=',';
	$stipids.="#stip$i";
	*/
   
    //empfehlung für neue spieler
    if($serverdata[$i][12]==1){
		echo '<span style="left: 6px; top: 28px; position: absolute; width: 148px; font-size: 12px; text-align: center;">'.$server_lang['empfehlung'].'</span>';
	}
	
	/////////////////////////////////////////////////////////////////////////
	// Spielerdaten von den einzelnen Servern holen
	/////////////////////////////////////////////////////////////////////////
	$spielerinfos='';
	//je nach Servertyp die Daten laden
	//Login-Link pauschal erstmal auf neuen Account anlegen setzen
	$login_link='<a href="index.php?command=createaccount&server='.$i.'"><span><b>Anmeldung</b></span></a>';
	$spielerstatus='';

	//$serverdata[$sindex][8]=5;//gametyp: 1=de, 2=se, 3=alu, 4=abl, 5=and
	switch($serverdata[$i][8]){

		case 1: //Die Ewigen
			if(!empty($serverdata[$i]['databaseKey'])){

				$databaseKey = $serverdata[$i]['databaseKey'] ?? '';
				try {
					$dbTemp = new mysqli($GLOBALS['env_databaseKey'][$databaseKey]['host'], $GLOBALS['env_databaseKey'][$databaseKey]['user'], $GLOBALS['env_databaseKey'][$databaseKey]['password'], $GLOBALS['env_databaseKey'][$databaseKey]['database']);
					
					$dbTemp->set_charset("utf8mb4");

					$db_daten=mysqli_execute_query(
						$dbTemp, 
						"SELECT de_login.user_id, de_login.supporter, de_login.last_login, de_login.delmode, de_login.status AS astatus, de_user_data.spielername, de_user_data.tick, de_user_data.score, de_login.status AS lstatus, de_login.last_login, de_user_data.sector, de_user_data.system, de_user_data.allytag, de_user_data.status, de_user_data.credits, de_user_data.patime, de_user_data.efta_user_id, de_user_data.sou_user_id FROM de_login left join de_user_data on(de_login.user_id = de_user_data.user_id) WHERE de_login.owner_id=?",
						[$_SESSION['ums_user_id']]
					);

					if($dbTemp->errno==0){

						$num = mysqli_num_rows($db_daten);
						if($num==1){
							$row = mysqli_fetch_array($db_daten);
							$user_id=$row["user_id"];
							$accstatus=$row["astatus"];
							$delmode=$row["delmode"];
							$efta_user_id=$row['efta_user_id'];
							$sou_user_id=$row['sou_user_id'];
							$last_login=$row['last_login'];
							$last_login=strtotime($last_login);
							$last_login=date("d.m.Y - H:i", $last_login);

							//$spielerinfos ='Server-Spieler-ID: ';

							$spielerinfos.='<b>Accountdaten</b>';

							//Spielername
							$spielerinfos.='<br>Spielername: '.$row['spielername'];

							//Punkte
							$spielerinfos.='<br>Punkte: '.number_format($row["score"], 0,"",".");

							//Accountalter in WT
							$spielerinfos.='<br>Accountalter (WT): '.number_format($row["tick"], 0,"",".");

							//Allianz
							if($row["status"]==1 AND $row["allytag"]!=''){
								$allytag=$row["allytag"];
							}
								else $allytag='-';
							$spielerinfos.='<br>Allianz: '.$allytag;

							//Credits
							$spielerinfos.='<br>Credits: '.number_format($row["credits"], 0,"",".");

							//Letzte Aktion
							if($accstatus!=3){
								$spielerinfos.='<br>Letzte Aktion: '.$last_login;
								$spielerstatus='aktiv';
							}else{
								$spielerstatus='Sondermodus';
							}

							//Accountstatus
							if($accstatus==3 AND $delmode==0){
								$spielerinfos.='<br>Der Account befindet sich bis zum folgenden Zeitpunkt im Urlaubsmodus und wird danach vom Wirtschaftstick wieder aktiviert: '.$last_login;
							}
							if($accstatus==3 AND $delmode==1){
								$spielerinfos.='<br>Der Account befindet sich bis zum folgenden Zeitpunkt im Loeschmodus und wird danach vom Wirtschaftstick entfernt, wobei ein Loginversuch den Loeschmodus in einen Urlaubsmodus umwandelt: '.$last_login;
							}
							if($accstatus==2){
								$spielerinfos.='<br>Der Account wurde gesperrt, erstelle ein Ticket falls Du fragen haben solltest.';
							}

							//Login-Link
							$login_link='<a href="serverlogin.php?server='.$i.'" target="_blank"><b>Spielen</b></a>';

						}else{
							$spielerinfos='Es wurde noch kein Account angelegt.';
						}

					}					

				} catch (mysqli_sql_exception $e) {
					// Benutzerfreundliche Fehlermeldung
					echo '<br><br><br>DB-Access-Error';
				}				
			}

		break;

		/*
		case 4: //EA/Ablyon
			if(!empty($serverdata[$i]['database'])){
				@$db_temp = mysqli_connect($GLOBALS['env_db_gameserver_1_host'],$GLOBALS['env_db_gameserver_1_user'],$GLOBALS['env_db_gameserver_1_password']);
				if(!$db_temp){
					echo '<br><br><br>DB-Access-Error';
				}else{

					try{
						mysqli_select_db ($db_temp, $serverdata[$i]['database']);

						$db_daten=mysqli_query($db_temp, "SELECT de_login.user_id, de_login.supporter, de_login.last_login, de_login.delmode, de_login.status AS astatus, de_user_data.spielername, de_user_data.tick, de_user_data.score, de_login.status AS lstatus, de_login.last_login, de_user_data.sector, de_user_data.system, de_user_data.allytag, de_user_data.status, de_user_data.credits, de_user_data.patime, de_user_data.efta_user_id, de_user_data.sou_user_id FROM de_login left join de_user_data on(de_login.user_id = de_user_data.user_id) WHERE de_login.owner_id='".$_SESSION['ums_user_id']."'");

						if($db_temp->errno==0){

							$num = mysqli_num_rows($db_daten);
							if($num==1){
								$row = mysqli_fetch_array($db_daten);
								$user_id=$row["user_id"];
								$accstatus=$row["astatus"];
								$delmode=$row["delmode"];
								$efta_user_id=$row['efta_user_id'];
								$sou_user_id=$row['sou_user_id'];
								$last_login=$row['last_login'];
								$last_login=strtotime($last_login);
								$last_login=date("d.m.Y - H:i", $last_login);

								//$spielerinfos ='Server-Spieler-ID: ';

								$spielerinfos.='<b>Accountdaten</b>';

								//Credits
								$spielerinfos.='<br>Credits: '.number_format($row["credits"], 0,"",".");

								//Letzte Aktion
								if($accstatus!=3){
									$spielerinfos.='<br>Letzte Aktion: '.$last_login;
									$spielerstatus='aktiv';
								}else{
									$spielerstatus='Sondermodus';
								}

								//Accountstatus
								if($accstatus==3 AND $delmode==0){
									$spielerinfos.='<br>Der Account befindet sich bis zum folgenden Zeitpunkt im Urlaubsmodus und wird danach vom Wirtschaftstick wieder aktiviert: '.$last_login;
								}
								if($accstatus==3 AND $delmode==1){
									$spielerinfos.='<br>Der Account befindet sich bis zum folgenden Zeitpunkt im Loeschmodus und wird danach vom Wirtschaftstick entfernt, wobei ein Loginversuch den Loeschmodus in einen Urlaubsmodus umwandelt: '.$last_login;
								}
								if($accstatus==2){
									$spielerinfos.='<br>Der Account wurde gesperrt, erstelle ein Ticket falls Du fragen haben solltest.';
								}

								//Login-Link
								if($showeblink==1){
									$login_link='<a href="serverlogin.php?server='.$i.'&eb=1"><b>Spielen</b></a>';
								}else{
									$login_link='<a href="serverlogin.php?server='.$i.'" target="_blank"><b>Spielen</b></a>';
								}

							}else{
								$spielerinfos='Es wurde noch kein Account angelegt.';
							}
						}else{
							echo '<br><br><br>DB-Access-Error';
						}

					} catch (Exception $e) {
						echo '<br><br><br>DB-Access-Error';
					}
				}

			}else{
				$spielerinfos='Error: no database';
			}
		break;

		case 5: //Andalur
			if(!empty($serverdata[$i]['database'])){
				@$db_temp = mysqli_connect($GLOBALS['env_db_gameserver_1_host'],$GLOBALS['env_db_gameserver_1_user'],$GLOBALS['env_db_gameserver_1_password']);
				if(!$db_temp){
					echo '<br><br><br>DB-Access-Error';
				}else{				
					mysqli_select_db ($db_temp, $serverdata[$i]['database']);

					$db_daten=mysqli_query($db_temp, "SELECT * FROM db_user_data WHERE owner_id='".$_SESSION['ums_user_id']."'");
					$num = mysqli_num_rows($db_daten);
					if($num==1){
						$row = mysqli_fetch_array($db_daten);
						$user_id=$row["user_id"];
						$accstatus=$row["acc_status"];
						//allianz bestimmen
						if($row["guildid"]>0 AND $row["guildstatus"]<99){
						$allyid=$row["guildid"];
						$db_ally_daten=mysqli_query($db_temp, "SELECT name FROM db_guild WHERE id='$allyid'");
						$rowa = mysqli_fetch_array($db_ally_daten);
						$allytag=$rowa["name"];
						}
						else $allytag='-';
						
						$spielerinfos.='<b>Accountdaten</b><br>';
					
						$spielerinfos.='Spielername: '.urlencode($row["spielername"]).'<br>';
						$spielerinfos.='Stufe: '.number_format($row["level"], 0,"",".").'<br>';
						$spielerinfos.='Gold: '.number_format($row["money"], 0,"",".").'<br>';
						$spielerinfos.='Credits: '.number_format($row["credits"], 0,"",".").'<br>';
						$spielerinfos.='Gilde: '.urlencode($allytag).'<br>';

						$spielerstatus='aktiv';

						//Login-Link
						if($showeblink==1){
							$login_link='<a href="serverlogin.php?server='.$i.'&eb=1"><b>Spielen</b></a>';
						}else{
							$login_link='<a href="serverlogin.php?server='.$i.'" target="_blank"><b>Spielen</b></a>';
						}					

					}else{
						$spielerinfos='Es wurde noch kein Account angelegt.';
					}
				}

			}else{
				$spielerinfos='Error: no database';
			}			

		break;
			*/
		default;
			$spielerinfos='Error: SZ02';
		break;
	}



	$status=$spielerstatus.' <img style="vertical-align: middle;" src="img/i1.gif" rel="tooltip" title="'.$spielerinfos.'">';

	//spielerstatus/informationen
    echo '<div style="top: 45px; left: 27px; position: absolute; width: 106px; text-align: center;">'.$status.'</div>';

    //login-link
    echo '<div style="bottom: 2px; left: 5px; width: 148px; position: absolute; font-size: 22px; text-align: center;">'.$login_link.'</div>';


	
	
	echo '</div></div>';
    
    $containerold=$serverdata[$i]['container'];

}
echo '</div>

<script>
//setTooltip();
</script>
';
//echo '</table>';

/*
echo "
<script type=\"text/javascript\" language=\"javascript\">
$(document).ready(function() {

//per javascript den accountstatus auslesen	  
var http_request = new Array();
var showeblink = ".$showeblink.";

http_request[0] = false;
http_request[1] = false;
http_request[2] = false;
http_request[3] = false;
http_request[4] = false;
http_request[5] = false;
http_request[6] = false;
http_request[7] = false;
http_request[8] = false;
http_request[9] = false;
http_request[10] = false;
http_request[11] = false;
http_request[12] = false;
http_request[13] = false;
http_request[14] = false;
http_request[15] = false;

var url = 'rpcls.php';
function accountcheck(target)
{
  http_request[target] = false;

  if (window.XMLHttpRequest)  //FF
  {
    http_request[target] = new XMLHttpRequest();
    if (http_request[target].overrideMimeType)
    {
      http_request[target].overrideMimeType('text/xml');
    }
  }
  else if (window.ActiveXObject)// IE
  {
    try
    {
      http_request[target] = new ActiveXObject(\"Msxml2.XMLHTTP\");
    }
    catch (e)
    {
      try
      {
        http_request[target] = new ActiveXObject(\"Microsoft.XMLHTTP\");
      }
      catch (e)
      {}
    }
  }
  if (!http_request[target])
  {
    alert('$server_lang[msg_7]');
    return false;
  }
  http_request[target].open('POST', url, true);
  http_request[target].setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  if(target==0)http_request[target].onreadystatechange = checkdata0;
  if(target==1)http_request[target].onreadystatechange = checkdata1;
  if(target==2)http_request[target].onreadystatechange = checkdata2;
  if(target==3)http_request[target].onreadystatechange = checkdata3;
  if(target==4)http_request[target].onreadystatechange = checkdata4;
  if(target==5)http_request[target].onreadystatechange = checkdata5;
  if(target==6)http_request[target].onreadystatechange = checkdata6;
  if(target==7)http_request[target].onreadystatechange = checkdata7;
  if(target==8)http_request[target].onreadystatechange = checkdata8;
  if(target==9)http_request[target].onreadystatechange = checkdata9;
  if(target==10)http_request[target].onreadystatechange = checkdata10;
  if(target==11)http_request[target].onreadystatechange = checkdata11;
  if(target==12)http_request[target].onreadystatechange = checkdata12;
  if(target==13)http_request[target].onreadystatechange = checkdata13;
  if(target==14)http_request[target].onreadystatechange = checkdata14;
  if(target==15)http_request[target].onreadystatechange = checkdata15;

  http_request[target].send('accountcheck=1&target='+target);
}

function managedata(data){
  //alert(data);

  var a = data.split(';');
  if(a[1]>0){
    var istr='';
    if(a[2]==1){

      sstr = unescape(a[9]);
      atip[a[0]] = '<b>$server_lang[accountdaten]'+'</b><br>'+sstr;

      document.getElementById('id'+a[0]+'_1').innerHTML = '$server_lang[aktiv]' + ' <img id=\"statusimg_'+a[0]+'\" style=\"vertical-align: middle;\" src=\"img/i1.gif\" border=\"0\" rel=\"tooltip\" title=\"'+atip[a[0]]+'\">';
      if(showeblink==1) istr=istr+' <a href=\"serverlogin.php?server='+a[0]+'&eb=1\"><span id=\"loginimg_'+a[0]+'\"><b>".$server_lang['login']."</b></span></a>';
      else istr=istr+'<a href=\"serverlogin.php?server='+a[0]+'\" target=\"_blank\"><span id=\"loginimg_'+a[0]+'\"><b>".$server_lang['login']."</b></span></a>';
      //if(servertyp[a[0]]==2 && $tickets>0)istr=istr+' <a href=\"index.php?command=getplaytime&server='+a[0]+'\"><img src=\"img/i4.gif\" border=\"0\" onMouseOver=\"stm(btip[5],Style[0])\" onMouseOut=\"htm()\"></a>';
      document.getElementById('id'+a[0]+'_2').innerHTML = istr;
      if(servertyp[a[0]]==1)document.getElementById('id'+a[0]+'_3').innerHTML='<a href=\"index.php?command=credittransfer&server='+a[0]+'\"><img src=\"img/credits.gif\"></a>';

    }else if(a[2]==0){
      
      atip[a[0]] = '<b>$server_lang[accounteinrichtung]</b><br>$server_lang[msg_8]';
      document.getElementById('id'+a[0]+'_1').innerHTML = '$server_lang[wirdeingerichtet]' + ' <img id=\"statusimg_'+a[0]+'\" style=\"vertical-align: middle;\" src=\"img/i1.gif\" border=\"0\" rel=\"tooltip\" title=\"'+atip[a[0]]+'\">';
      document.getElementById('id'+a[0]+'_2').innerHTML = '-';

    }else if(a[2]==2){

      sstr = unescape(a[9]);
      atip[a[0]] = '<b>$server_lang[accountdaten]'+'</b><br>'+sstr;
          
      document.getElementById('id'+a[0]+'_1').innerHTML = '$server_lang[gesperrt]' + ' <img id=\"statusimg_'+a[0]+'\" style=\"vertical-align: middle;\" src=\"img/i1.gif\" border=\"0\" rel=\"tooltip\" title=\"'+atip[a[0]]+'\">';
      document.getElementById('id'+a[0]+'_2').innerHTML = '-';
    
    }else if(a[2]==3){

      sstr = unescape(a[9]);
      atip[a[0]] = '<b>$server_lang[accountdaten]'+'</b><br>'+sstr;
          
      document.getElementById('id'+a[0]+'_1').innerHTML = '$server_lang[urlaubsmodus]'+' <img id=\"statusimg_'+a[0]+'\" style=\"vertical-align: middle;\" src=\"img/i1.gif\" border=\"0\" rel=\"tooltip\" title=\"'+atip[a[0]]+'\">';
      if(showeblink==1) istr=istr+' <a href=\"serverlogin.php?server='+a[0]+'&eb=1\"><img id=\"loginimg_'+a[0]+'\" src=\"img/i2.gif\" border=\"0\" rel=\"tooltip\" title=\"<b>$server_lang[urlaubsmodusbeenden]</b><br>$server_lang[msg_4]\"></a>';
      else istr=istr+'<a href=\"serverlogin.php?server='+a[0]+'\" target=\"_blank\"><img id=\"loginimg_'+a[0]+'\" src=\"img/i2.gif\" border=\"0\" rel=\"tooltip\" title=\"<b>$server_lang[urlaubsmodusbeendet]</b><br>$server_lang[msg_4]\"></a>';
      document.getElementById('id'+a[0]+'_2').innerHTML = istr;
      
    }else if(a[2]==4){

      sstr = unescape(a[9]);
      atip[a[0]] = '<b>$server_lang[accountdaten]'+'</b><br>'+sstr;
          
      document.getElementById('id'+a[0]+'_1').innerHTML = '$server_lang[umzugsmodus]'+' <img id=\"statusimg_'+a[0]+'\" style=\"vertical-align: middle;\" src=\"img/i1.gif\" border=\"0\" rel=\"tooltip\" title=\"'+atip[a[0]]+'\">';
      document.getElementById('id'+a[0]+'_2').innerHTML = '-';
    }
  }else{
    document.getElementById('id'+a[0]+'_1').innerHTML = '-';
    document.getElementById('id'+a[0]+'_2').innerHTML = '<a href=\"index.php?command=createaccount&server='+a[0]+'\"><span id=\"loginimg_'+a[0]+'\"><b>".$server_lang['anmeldung']."</b></span></a>';
    
  }
  setTooltip();
}
";

for ($i=0;$i<=$sindex;$i++){
  if(in_array($ums_cooperation, $serverdata[$i][10]))
  echo "
  function checkdata".$i."()
  {
    if (http_request[".$i."].readyState == 4)
    {
      if (http_request[".$i."].status == 200)
      {
        if(http_request[".$i."].responseText!='-1')
        {
          managedata(http_request[".$i."].responseText);
        }
      }else{
        alert('$server_lang[msg_9]');
      }
    }
  }";
}

//die ajax-instanzen starten
for ($i=0;$i<=$sindex;$i++){
  if(in_array($ums_cooperation, $serverdata[$i][10]))echo "accountcheck('".$i."');";
}
?>
});
</script>
*/

?>