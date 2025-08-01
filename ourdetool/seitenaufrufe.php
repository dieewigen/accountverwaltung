<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
?>
<html>
<head>
<title>Seitenaufrufe</title>
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

$targetdata = array('localhost', 'dbuser', 'c0j9XIrL5Rwm', 'gameserverlogdata', 'gameserverlogdata');
$dblog = mysqli_connect($GLOBALS['env_db_logging_host'], $GLOBALS['env_db_logging_user'], $GLOBALS['env_db_logging_password'], $GLOBALS['env_db_logging_database']) or die("C: Keine Verbindung zur Datenbank möglich.");

//CDE 13, DDE 4, EDE 3, RDE 11, SDE 2, xDE 1 

$server=array();
$server[0][0]=1;
$server[0][1]='xDE';

$server[1][0]=2;
$server[1][1]='SDE';


for($s=0;$s<count($server);$s++){

  $serverid=$server[$s][0];

  echo '<br><br>SERVER: '.$server[$s][1];
  //echo '<br>'.$sql;

  echo '
  <table>
    <tr><td>User-ID</td><td>Seitenaufrufe</td><tr>
  ';

  $sql = "SELECT userid, COUNT(*) AS anzahl FROM `gameserverlogdata` WHERE serverid=? GROUP BY serverid, userid ORDER BY anzahl DESC";
  $db_daten = mysqli_execute_query($dblog, $sql, [$serverid]);
  while($row = mysqli_fetch_array($db_daten)){
    if($row['anzahl']>1000){
      echo '
      <tr>
        <td><a href="https://'.strtolower($server[$s][1]).'.bgam.es/ourdetool/idinfo.php?UID='.$row['userid'].'" target="_blank">'.$row['userid'].'</a></td>
        <td>'.$row['anzahl'].'</td>
      </tr>';
    }
  }

  echo '</table>';

}

$time_end = getmicrotime();
$ltime = number_format($time_end - $time_start,2,".","");

?>

 <br>
 Seite in <? echo $ltime; ?> Sekunden erstellt.
</body>
</html>