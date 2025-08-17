<?php
include 'content/de/lang/'.$ums_language.'_createaccount.lang.php';

$target=intval($_REQUEST["server"]);
$gametyp=$serverdata[$target][8];

//überprüfen ob man die voraussetzungen für den server hat
$hasall=1;
//platz in der globalen rangliste
$db_daten = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT COUNT(*) AS wert FROM ls_user WHERE tlscore > (SELECT tlscore FROM ls_user WHERE user_id = ?)",
    [$_SESSION['ums_user_id']]
);
$row = mysqli_fetch_array($db_daten, MYSQLI_ASSOC);
$vb_ranglistenplatz=$row["wert"]+1;

if($vb_ranglistenplatz>$serverdata[$target][11][0])$hasall=-1;
//auf betauser testen
if(isset($serverdata[$target][11][1]) && $serverdata[$target][11][1]==1)
{
  $result = mysqli_execute_query(
      $GLOBALS['dbi'],
      "SELECT * FROM ls_user WHERE user_id = ?",
      [$_SESSION['ums_user_id']]
  );
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  if($row['betatester']==0)$hasall=-2;
}

//fehlermeldung ausgeben, wenn nicht alle vorbedinungen erfüllt sind, sonst ok-meldung ausgeben
if($hasall==1){
	//echo '<font color="#00FF00">'.$createaccount_lang['vorbedingungerfuellt'].'</font><br><br>';
}else {
	if($hasall==-1){
		echo '<font color="#FF0000">'.$createaccount_lang['vorbedingungnichterfuellt'].'<br>'.$createaccount_lang['vorbedingungranglistenplatz'].': '.$serverdata[$target][11][0].'</font><br><br>';
	}elseif($hasall==-2){
		echo '<font color="#FF0000">'.$createaccount_lang['vorbedingungnichterfuellt'].'<br>'.$createaccount_lang['vorbedingungbetatester'].'</font><br><br>';
	}
}


////////////////////////////////////////////////////////
//account anlegen
////////////////////////////////////////////////////////
$errmsg='';
$spielername='';
$createok=0;
if(isset($_POST['button']) AND $hasall==1){
  //$target=intval($_REQUEST["server"]);
  $spielername=$_REQUEST["spielername"] ?? '';
  $rasse=$_REQUEST["rasse"];

  
  //rasse überprüfen
  if($gametyp==1)
  switch($rasse[0]){
    case 'E':
      $gewrasse=1;
      break;
    case 'I':
      $gewrasse=2;
      break;
    case 'K':
      $gewrasse=3;
      break;
    case 'Z':
      $gewrasse=4;
      break;
    default:
      $errmsg.='<font color="FF0000"><b>'.$createaccount_lang['msg_1'].'</b></font>';
      break;
  }

  //wenn die rasse ok ist, �berpr�fen ob er einen spielernamen eingegeben hat und ob der die erlaubten zeichen enth�lt
	if($errmsg==''){
		//da es bei alu keine spielernamenvergabe �ber den hauptaccount gibt, dort als spielername die account-id vom hauptaccount vorbelegen
		if($gametyp==3)$spielername=$_SESSION['ums_user_id'];
		if($gametyp==4)$spielername=$_SESSION['ums_user_id'];
		
		if($spielername!=''){
			//spielernamen auf g�ltige zeichen �berpr�fen
			if(!preg_match("/^[[:alpha:]0-9äöü_=-]*$/i", $spielername)){
				$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_2'].': _-=).</font>';
			}else{
				//fehlende daten für das erstellen des accounts auslesen
				$result = mysqli_execute_query(
				    $GLOBALS['dbi'],
				    "SELECT * FROM ls_user WHERE user_id = ?",
				    [$_SESSION['ums_user_id']]
				);
				$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

				$email=$row["reg_mail"];
				$vorname=$row["vorname"];
				$nachname=$row["nachname"];
				$strasse=$row["strasse"];
				$plz=$row["plz"];
				$ort=$row["ort"];
				$land=$row["land"];
				$telefon=$row["telefon"];
				$tag=$row["tag"];
				$monat=$row["monat"];
				$jahr=$row["jahr"];
				$geschlecht=$row["geschlecht"];
				$patime=$row["patime"];
				$werberid=$row['werberid'];
				
				//wenn es keine fehler gibt versuchen den account anzulegen
				//echo 'B:'.$serverdata[$target][6];
				//echo 'C:'.$serverdata[$target][5];
				$result=doPost($serverdata[$target][6].'rpc.php', 'authcode='.$GLOBALS['env_rpc_authcode'].'&createaccount=1&id='.$_SESSION['ums_user_id'].
					'&spielername='.urlencode($spielername).
					'&rasse='.urlencode($gewrasse).
					'&email='.urlencode($email).
					'&vorname='.urlencode($vorname).
					'&nachname='.urlencode($nachname).
					'&strasse='.urlencode($strasse).
					'&plz='.urlencode($plz).
					'&ort='.urlencode($ort).
					'&land='.urlencode($land).
					'&telefon='.urlencode($telefon).
					'&tag='.urlencode($tag).
					'&monat='.urlencode($monat).
					'&jahr='.urlencode($jahr).
					'&geschlecht='.urlencode($geschlecht).
					'&patime='.urlencode($patime).
					'&werberid='.urlencode($werberid)
					, $serverdata[$target][5]);
				//ergebnis auswerten
				switch($result){
					case '1':
						//account ohne fehler angelegt
						$errmsg.='<font color="00FF00">'.$createaccount_lang['msg_3'].'</font>';
						$createok=1;
					break;
					case '2':
						//spielername ist bereits vergeben
						$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_4'].'</font>';
					break;
					case '3':
						//test auf emailadresse
						$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_5_1'].' '.$email.' '.$createaccount_lang['msg_5_2'].'</font>';
					break;
					case '4':
						//test auf owner_id
						$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_6'].'</font>';
					break;
					default:
						$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_7'].'</font>';
				}//ende switch $result
			}
		}else{
			$errmsg.='<font color="FF0000">'.$createaccount_lang['msg_8'].'</font>';
		}
	}
}


if($errmsg!='')echo $errmsg;

//wenn es keinen spielernamen gibt den standardnamen auslesen
if($spielername==''){
	$result = mysqli_execute_query(
	    $GLOBALS['dbi'],
	    "SELECT spielername FROM ls_user WHERE user_id = ?",
	    [$_SESSION['ums_user_id']]
	);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	$spielername=$row["spielername"];
}

//daten abfragen
if($createok!=1){

echo '<form action="index.php?command=createaccount&server='.$_REQUEST["server"].'" method="POST">';
echo '<table border="0" cellpadding="3" cellspacing="0" style="margin: 0px auto;">';

//spielernamen wählen, nur bei se und de
if($gametyp==1 || $gametyp==2 || $gametyp==5)
echo '<tr>
        <td colspan="2" align="center"><b>'.$createaccount_lang['accounterstellen'].'</b></td>
      </tr>
      <tr>
        <td colspan="2" align="center">'.$createaccount_lang['msg_9_1'].' '.$serverdata[$_REQUEST["server"]][0].'-'.$createaccount_lang['msg_9_2'].'</td>
      </tr>
      <tr>
        <td width="180">'.$createaccount_lang['spielername'].':</td>
        <td><input type="text" maxlength="20" name="spielername" value="'.$spielername.'"></td>
      </tr>';
//bei de noch die rasse abfragen
if($gametyp==1)
{
echo ' <tr>
        <td>'.$createaccount_lang['rasse'].':</td>
        <td><select name="rasse">';
            if (!isset($rasse) || $rasse=='')$rasse=$createaccount_lang['bittewaehlen'];
            echo '<option selected>'.$rasse.'</option>';
            echo'
            <option>'.$createaccount_lang['e'].'</option>
            <option>'.$createaccount_lang['i'].'</option>
            <option>'.$createaccount_lang['k'].'</option>
            <option>'.$createaccount_lang['z'].'</option>
            </select>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <img id="rdesc1" src="img/derassenlogo1.png" title="'.$createaccount_lang['e'].'&'.$createaccount_lang['edesc'].'">
          <img id="rdesc2" src="img/derassenlogo2.png" title="'.$createaccount_lang['i'].'&'.$createaccount_lang['idesc'].'">
          <img id="rdesc3" src="img/derassenlogo3.png" title="'.$createaccount_lang['k'].'&'.$createaccount_lang['kdesc'].'">
          <img id="rdesc4" src="img/derassenlogo4.png" title="'.$createaccount_lang['z'].'&'.$createaccount_lang['zdesc'].'">
<script language="javascript">
for(i=1;i<=4;i++)
$("#rdesc"+i).tooltip({ 
      track: true, 
      delay: 0, 
      showURL: false, 
      showBody: "�",
      extraClass: "design1", 
      fixPNG: true, 
      opacity: 0.95
	  });
</script>
          
        </td>
        
      </td>
      
      
      
      ';
}            
echo '<tr>
        <td colspan="2" align="center"><input type="Submit" name="button" value="'.$createaccount_lang['datenbestaetigen'].'"></td>
      </tr>
      ';

echo '</table>';
echo '</form><br><br>';
}

?>