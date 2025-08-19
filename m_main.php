<?php
include 'content/de/lang/'.$_SESSION['ums_language'].'_m_main.lang.php';


$um='';
if(isset($_SESSION['ums_user_id']) && $_SESSION["ums_user_id"]>0){
	echo '<div class="topnav" id="ls_topnav">';
	
	if(!isset($_REQUEST["command"]) || $_REQUEST["command"]==""){
		$_REQUEST["command"]='server_direct';
	}
	//server/spielen
	$cssclass='';
	echo '<a '.$cssclass.'href="index.php?command=server_direct">'.$m_main_lang['server'].'</a>';


	//accountdaten
	echo '<a '.$cssclass.'href="index.php?command=account">'.$m_main_lang['accountdaten'].'</a>';
	
	
	//forum
	/*
	if(isset($GLOBALS['env_enable_forum_connect']) && $GLOBALS['env_enable_forum_connect']==1){
		echo '<a '.$cssclass.'href="index.php?command=forum">'.$m_main_lang['forum'].'</a>';
	}
	*/
  
	//Support
	if(isset($GLOBALS['env_enable_support_page']) && $GLOBALS['env_enable_support_page']==1){
		if($_REQUEST["command"]=="support"){
			$um='<div style="width: 100%">';

			if($_REQUEST["page"]=="1"){$cssclass=' class="button1 textbold"';}else{$cssclass=' class="button1"';}
			$um.='<a '.$cssclass.'href="index.php?command=support&page=1">'.$m_main_lang['ticketold'].'</a>';

			if($_REQUEST["page"]=="2"){$cssclass=' class="button1 textbold"';}else{$cssclass=' class="button1"';}			
			$um.='<a '.$cssclass.'href="index.php?command=support&page=2">'.$m_main_lang['ticketnew'].'</a>';

			$um.='</div>';
			//fetter supportbutton
			$cssclass=' class="button1 textbold"';
		}else{
			$cssclass=' class="button1"';
		}

		echo '<a href="index.php?command=support&page=1">'.$m_main_lang['support'].'</a>';
	}
	
	if(isset($GLOBALS['env_enable_de_kb_db']) && $GLOBALS['env_enable_de_kb_db']==1){
		echo '<a href="index.php?command=de_kb">DE-KB</a>';
	}

	//Patchnotes
	echo '<a href="index.php?command=patchnotes">Patch Notes</a>';
  
	//logout
	echo '<a href="index.php?command=logout">'.$m_main_lang['logout'].'</a>';
  
	//untermenü ausgeben
	//echo $um;

	echo '
	<a href="javascript:void(0);" class="icon" onclick="burgerMenu()">&#9776;</a>
  
  <script>
function burgerMenu() {
    var x = document.getElementById("ls_topnav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";
    }
}
</script>


	';

	//div menucontainer end
	echo '</div>';

}else{
	echo '<div class="topnav_nli">';

	if(!isset($cssclass)){
		$cssclass='';
	}

	//login
	//if($_REQUEST["command"]=="login" OR $_REQUEST["command"]==""){$cssclass=' class="button1 textbold"';}else{$cssclass=' class="button1"';}
	echo '<a '.$cssclass.'href="index.php?command=login">'.$m_main_lang['login'].'</a>';
	
	//account anlegen
	//if($_REQUEST["command"]=="register"){$cssclass=' class="button1 textbold"';}else{$cssclass=' class="button1"';}
	echo '<a '.$cssclass.'href="index.php?command=register">'.$m_main_lang['accountanlegen'].'</a>';

	//div menucontainer end
	echo '</div>';	
}


?>