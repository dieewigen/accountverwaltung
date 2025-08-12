<?php
include 'inc/sv.inc.php';
include 'inc/serverdata.inc.php';

unset($sl);

for($i=0;$i<=$sindex;$i++)
{
  $sl[$i]['gametype']=$serverdata[$i][8];
  $sl[$i]['servertag']=$serverdata[$i][0];
}

$data = array ('serverlist' => $sl);
echo json_encode($data);



?>