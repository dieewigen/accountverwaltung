<html>
<head>
<title>IP-Adressen</title>
<?php include "cssinclude.php";?>
</head>
<body>
<div align="center">
<?php
include "../inc/sv.inc.php";
//include "../inccon.php";
include "det_userdata.inc.php";

include "log_dbconnect.php";

$uid=intval($_REQUEST["uid"]);

//alle ips laden und ausgeben

echo 'Hier werden nur die IP-�nderungen und nicht die kompletten Zugriffe angezeigt.<br>';

echo '
<table border="0">
<colgroup>
<col width="150">
<col width="150">
</colgroup>
<tr>
<td class="cell1" align="center"><b>IP-Adresse</b></td>
<td class="cell1" align="center"><b>Uhrzeit</b></td>
</tr>';

//echo "SELECT time, ip FROM gameserverlogdata WHERE serverid='$sv_servid' AND userid='$uid' ORDER BY time DESC";
$ipadresse='127.0.0.1';
$result = mysqli_execute_query($GLOBALS['logdbi'], "SELECT time, ip FROM gameserverlogdata WHERE serverid = ? AND userid = ? ORDER BY time DESC", [$sv_servid, $uid]);
while($row = mysqli_fetch_array($result))
{
  if($ipadresse!=$row["ip"])
  {
    echo '<tr align="center"><td>'.$row["ip"].'</td><td>'.$row["time"].'</td></tr> ';
    $ipadresse=$row["ip"];
  }
}
echo '</table>';
?>
</div>
</body>
</html> 