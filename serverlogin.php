<?php
session_start();
include "inc/header.inc.php";

//serverdaten einbinden
include "inc/serverdata.inc.php";
include "functions.php";
include 'content/de/lang/'.($_SESSION['ums_language'] ?? 1).'_serverlogin.lang.php';

//ip adresse feststellen
$ip=getenv("REMOTE_ADDR");
$parts=explode(".",$ip);
$ip=$parts[0].'.x.'.$parts[2].'.'.$parts[3];

$fastlogin=isset($_REQUEST["fastlogin"]) ? intval($_REQUEST["fastlogin"]) : 0;
$servertag=$_REQUEST["servertag"] ?? '';


//zuerst über rpc einen loginschlüssel generieren und auf diesen dann per url weiterleiten

//zielserver
$target=intval($_REQUEST["server"]);

//evtl. zielserver über servertag bestimmen
if($fastlogin>0){
	//server nach tag durchsuchen
	for ($i=0;$i<=$sindex;$i++)  {
		if(strtoupper($serverdata[$i][0])==strtoupper($servertag))		{
			$target=$i;
			break;	
		}
	}
}

//das passwort verschlüsselt aus der db auslesen und übertragen
$db_daten = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT pass FROM ls_user WHERE user_id=?",
    [$_SESSION['ums_user_id']]
);
$row = mysqli_fetch_array($db_daten, MYSQLI_ASSOC);
$pass='&pass='.$row["pass"];
$accountverwaltung_passwort=$row["pass"];

//accountdaten vom server
//if($_SESSION['ums_user_id']==1){
  $databaseKey = $serverdata[$target]['databaseKey'] ?? '';

  $db_temp = mysqli_connect(
      $GLOBALS['env_databaseKey'][$databaseKey]['host'], 
      $GLOBALS['env_databaseKey'][$databaseKey]['user'], 
      $GLOBALS['env_databaseKey'][$databaseKey]['password'], 
      $GLOBALS['env_databaseKey'][$databaseKey]['database']
  );
  
  // Fehlerbehandlung für die Verbindung
  if (mysqli_connect_errno()) {
      die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
  }

	//das aktuelle Passwort setzen und einen Loginkey vergeben

	//Loginkey erzeugen
    $pwstring='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $loginkey=$pwstring[rand(0, strlen($pwstring)-1)];
    for($i=1; $i<16; $i++){
		$loginkey.=$pwstring[rand(0, strlen($pwstring)-1)];
	}
  $result=$loginkey;
  
  if($serverdata[$target][8]!=5){
    //DE
    mysqli_execute_query(
        $db_temp,
        "UPDATE de_login SET loginkey=?, loginkeytime=UNIX_TIMESTAMP(), loginkeyip=?, pass=? WHERE owner_id=?",
        [$loginkey, $ip, $accountverwaltung_passwort, $_SESSION['ums_user_id']]
    );
  }else{
    //Andalur
    mysqli_execute_query(
        $db_temp,
        "UPDATE db_user_data SET loginkey=?, loginkeytime=UNIX_TIMESTAMP(), loginkeyip=? WHERE owner_id=?",
        [$loginkey, $ip, $_SESSION['ums_user_id']]
    );
  }

$url="https://".$serverdata[$target][5].$serverdata[$target][6].'index.php?loginkey='.$result;

if($result!='error'){
  //echo $result;
  header("Location: ".$url);
  exit;
}

?>
<!DOCTYPE HTML>
<html>
<head>
<?php
include "cssinclude.php";
?>
<title><?php echo $serverlogin_lang['title'];?></title>
</head>
<body>
<div id="hauptbody">
<?php
if($result=='error'){
	echo '<br><div align="center">'.$serverlogin_lang['msg_1'].'</div>';
	echo '<br>Dies kann daran liegen, dass man nicht eingeloggt ist. Ggf. liegt es auch an Browsererweiterungen wie z.B. Ghostery. Deaktiviere diese bitte testweise um herauszufinden ob diese das Problem verursachen.';
}else{
  //url zum externen login ausgeben
  echo '<div align="center">';
  echo '<br><b>'.$serverlogin_lang['msg_2'].'</b><br><br><a href="'.$url.'">'.$url.'</a>';
  echo '<br><br>'.$serverlogin_lang['msg_3'].': '.$ip.'<br><br>';
  echo '</div>';

}

?>
</div>
</body>
</html>