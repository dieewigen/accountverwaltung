<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
?>
<html>
<head>
<title>Multiliste</title>
<?php include "cssinclude.php";?>
<style >
 body { scrollbar-face-color: #000000;scrollbar-shadow-color: #000000;scrollbar-highlight-color: #333333; scrollbar-3dlight-color: #8CA0B4;scrollbar-darkshadow-color: #333333;scrollbar-track-color: #000000;
  scrollbar-arrow-color: #8CA0B4; padding: 0px; color: #3399FF; margin-left: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px;
  font-family: helvetica, arial,geneva, sans-serif;  font-size: 10pt;}
 table { border: 1px solid #00366C; }
 td { font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; white-space: nowrap; border: 1px solid #00366C; }
 td.r { color: #ff0000; }
 a { color: #3399ff; text-decoration: underline }
 a:hover { color: #3399ff; text-decoration: none }
</style>

</head>
<body>
IP Adressen des gleichen Oktetts:
<a href="multi.php?okt=1">[ 1 ]</a> -
<a href="multi.php?okt=2">[ 2 ]</a> -
<a href="multi.php?okt=3">[ 3 ]</a> -
<a href="multi.php?okt=4">[ 4 ]</a> <br><br><br>
<?php
if(!isset($_GET['okt']))
$okt=4;
else
$okt = intval($_GET['okt']);

include "det_userdata.inc.php";

 function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
 }

$tis=time()-86400*30;
$last_login=date("Y-m-d H:i:s",$tis);
 
 
$time_start = getmicrotime();

$sql = "SELECT SUBSTRING_INDEX(last_ip, '.', ?) AS last_ip, COUNT(last_ip) 'zaehler' FROM ls_user WHERE last_ip<>'127.0.0.1' AND last_login>? GROUP BY last_ip ORDER BY `zaehler`, last_ip DESC";
$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$okt, $last_login]);

$gesuser=0;

while($row = mysqli_fetch_array($db_daten))
{
  if (($row["zaehler"]>1)&&($row["last_ip"]<>''))
  {
    $z=$row["zaehler"]; $ip=$row["last_ip"];
    $ipz=$ip;
    if ($ipz=='212.227.110.246') $ipz='!!! 1&1 !!!';
    //kopf mit ip und anzahl
    echo '<table border="0" cellpadding="2" cellspacing="0" width="200">';
    echo '<tr>';
    echo '<td align="center">IP: '.$ipz.' Anzahl: '.$z.'</td>';
    echo '</tr>';
    echo '</table>';

    echo '<table border="0" cellpadding="2" cellspacing="0">';
    echo '<tr>';
    echo '<td width="50">UserID</td>';
    echo '<td width="150">Loginname</td>';
    echo '<td width="200">E-Mail</td>';
    echo '<td width="150">Passwort</td>';
    echo '<td width="140">Registriert</td>';
    echo '<td width="140">Letzter Login</td>';
    echo '<td width="70">Status</td>';
    echo '<td width="40">Logins</td>';
    echo '<td width="40">Ort</td>';
    echo '</tr>';


    $sql = "SELECT * FROM ls_user WHERE last_ip LIKE CONCAT(?, '%') ORDER BY pass";
    $result = mysqli_execute_query($GLOBALS['dbi'], $sql, [$ip]);
    while($user = mysqli_fetch_array($result))
    {
     if ($oldpass==$user["pass"]) $str=' class="r"'; else $str='';
     $oldpass=$user["pass"];

     if ($user["acc_status"]==0) $status='Inaktiv';
     if ($user["acc_status"]==1) $status='Aktiv';
     if ($user["acc_status"]==2) $status='Gesperrt';

     if (isset($stat2)) {
      if ($user["status"]!=2) {
       echo '<tr>';
       echo '<td><a href="idinfo.php?UID='.$user["user_id"].'" target="_blank">'.$user["user_id"].'</a></td>';
       echo '<td>'.$user["loginname"].'</td>';
       echo '<td>'.$user["reg_mail"].'</td>';
       echo '<td'.$str.'>'.modpass($user["pass"]).'</td>';
       echo '<td>'.$user["register"].'</td>';
       echo '<td>'.$user["last_login"].'</td>';
       $status.=' <a href="de_set_user_status.php?uid='.$user["user_id"].'&status=2" target="setuserstatus">[S]</a>';
       echo '<td>'.$status.'</td>';
       echo '<td>'.$user["logins"].'</td>';
       echo '<td>'.$user["ort"].'</td>';
       echo '</tr>';
       $gesuser++;
      }
     }
     else {
      echo '<tr>';
      echo '<td><a href="idinfo.php?UID='.$user["user_id"].'" target="_blank">'.$user["user_id"].'</a></td>';
      echo '<td>'.$user["loginname"].'</td>';
      echo '<td>'.$user["reg_mail"].'</td>';
      echo '<td'.$str.'>'.modpass($user["pass"]).'</td>';
      echo '<td>'.$user["register"].'</td>';
      echo '<td>'.$user["last_login"].'</td>';
      $status.=' <a href="de_set_user_status.php?uid='.$user["user_id"].'&status=2" target="setuserstatus">[S]</a>';
      echo '<td>'.$status.'</td>';
      echo '<td>'.$user["logins"].'</td>';
      echo '<td>'.$user["ort"].'</td>';
      echo '</tr>';
      $gesuser++;
     }
    }
    echo '</table><br><br>';
  }
}
echo 'Verdächtige: '.$gesuser;
/*
select last_ip, count(last_ip) "zaehler" from de_login group by last_ip ORDER BY `zaehler` DESC LIMIT 0, 30
update de_login set status=2 where last_ip='217.225.120.26'
select * from de_login where last_ip= '217.225.120.26'*/

  $time_end = getmicrotime();
  $ltime = number_format($time_end - $time_start,2,".","");

function modpass($pass)
{
  $pass[0]="*";
  $pass[1]="*";
  $pass[2]="*";
  $pass[3]="*";
  return($pass);
}
?>

 <br>
 Seite in <? echo $ltime; ?> Sekunden erstellt.
</body>
</html>