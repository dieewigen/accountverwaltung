<?php
//if($disablegzip!=1)ob_start("ob_gzhandler");
//ignore_user_abort(true);
//include_once "inc/sv.inc.php";
include_once "inc/env.inc.php";

$GLOBALS['dbi'] = mysqli_connect($GLOBALS['env_db_loginsystem_host'], $GLOBALS['env_db_loginsystem_user'], $GLOBALS['env_db_loginsystem_password'], $GLOBALS['env_db_loginsystem_database']) or die("Keine Verbindung zur Datenbank möglich.");
$GLOBALS['dbi']->set_charset("utf8mb4");

/*
//TODO: bei Bedarf mit gameserverlogdata verbinden
if(isset($_SESSION['ums_user_id']) && $_SESSION['ums_user_id']>0){ //post und get-variablen mitloggen
	if(isset($_POST["accountcheck"]) && $_POST["accountcheck"]==0){
		$datenstring='';  
		$variableSets = array(
		"P:" => $_POST,
		"G:" => $_GET);

		function printElementHtml( $value, $key ) 
		{
		global $datenstring;
		//passwrter rausfiltern
		if($key=='pass')$value='****';
		if($key=='newpass')$value='****';
		if($key=='oldpass')$value='****';
		if($key=='pass1')$value='****';
		if($key=='pass2')$value='****';
		if($key=='delpass')$value='****';
		if($key=='urlpass')$value='****';
		if($key=='launcherkey')$value='****';
	
		$datenstring.=$key. ": ".$value."\n";
		//echo $key . " => ";
		//print_r( $value );
		//echo "<br>";
		}

		foreach ( $variableSets as $setName => $variableSet ) 
		{
		if ( isset( $variableSet ) ) 
		{
			//echo "<br><br><hr size='1'>";
			//echo "$setName<br>";
			$datenstring.=$setName."\n";
			array_walk( $variableSet, 'printElementHtml' );
		}
		}

		$ip=$_SERVER['REMOTE_ADDR'];
		$parts=explode(".",$ip);
		$ip=$parts[0].'.x.'.$parts[2].'.'.$parts[3];
		
		if(strlen($datenstring)==6)$datenstring='';

		$scriptname=$_SERVER['PHP_SELF'];
		if($scriptname[0]=='/')$scriptname = substr($scriptname,1);
		$scriptname=str_replace('.php','',$scriptname);
		mysqli_query("INSERT INTO ls_user_log (serverid, userid, time, ip, file, getpost) VALUES('$sv_servid','".intval($_SESSION['ums_user_id'])."',NOW(), '$ip', '$scriptname', '$datenstring')",$db); 
		
		$datenstring='';
  	}
}
*/
?>
