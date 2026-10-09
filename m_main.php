<?php
include 'content/de/lang/'.$_SESSION['ums_language'].'_m_main.lang.php';

//Navigation in der Kopfleiste: inline für den Desktop und noch einmal im Aufklappmenü fürs Handy.
//Die aktuelle Seite bekommt aria-current="page".

$um='';
if(isset($_SESSION['ums_user_id']) && $_SESSION["ums_user_id"]>0){

	if(!isset($_REQUEST["command"]) || $_REQUEST["command"]==""){
		$_REQUEST["command"]='server_direct';
	}
	$aktuell=$_REQUEST["command"];

	//Einträge: Kommando, Beschriftung, Link
	$navigation=array();

	//server/spielen
	$navigation[]=array('server_direct', $m_main_lang['server'], 'index.php?command=server_direct');

	//accountdaten
	$navigation[]=array('account', $m_main_lang['accountdaten'], 'index.php?command=account');

	//forum
	/*
	if(isset($GLOBALS['env_enable_forum_connect']) && $GLOBALS['env_enable_forum_connect']==1){
		$navigation[]=array('forum', $m_main_lang['forum'], 'index.php?command=forum');
	}
	*/

	//Support
	if(isset($GLOBALS['env_enable_support_page']) && $GLOBALS['env_enable_support_page']==1){
		if($_REQUEST["command"]=="support"){
			//Unterseiten des Supports als Reiter, ausgegeben in content/support.inc.php
			$page=isset($_REQUEST["page"]) ? $_REQUEST["page"] : '';

			$um='<ul class="reiter">';
			$um.='<li><a'.($page=="1" ? ' aria-current="page"' : '').' href="index.php?command=support&page=1">'.$m_main_lang['ticketold'].'</a></li>';
			$um.='<li><a'.($page=="2" ? ' aria-current="page"' : '').' href="index.php?command=support&page=2">'.$m_main_lang['ticketnew'].'</a></li>';
			$um.='</ul>';
		}

		$navigation[]=array('support', $m_main_lang['support'], 'index.php?command=support&page=1');
	}

	if(isset($GLOBALS['env_enable_de_kb_db']) && $GLOBALS['env_enable_de_kb_db']==1){
		$navigation[]=array('de_kb', 'DE-KB', 'index.php?command=de_kb');
	}

	//Patchnotes
	$navigation[]=array('patchnotes', 'Patch Notes', 'index.php?command=patchnotes');

	$nav_liste='';
	foreach($navigation as $eintrag){
		$nav_liste.='<li><a'.($aktuell==$eintrag[0] ? ' aria-current="page"' : '').' href="'.$eintrag[2].'">'.$eintrag[1].'</a></li>';
	}

	//Navigation für den Desktop
	echo '<nav class="hauptnav" aria-label="Hauptnavigation"><ul>'.$nav_liste.'</ul></nav>';

	//logout
	echo '<div class="kopf-aktionen"><a class="knopf knopf-leise" href="index.php?command=logout">'.$m_main_lang['logout'].'</a></div>';

	//Aufklappmenü fürs Handy
	echo '<details class="nav-mobil"><summary>'.$m_main_lang['menu'].'</summary><nav aria-label="Hauptnavigation"><ul>'.$nav_liste.'<li><a href="index.php?command=logout">'.$m_main_lang['logout'].'</a></li></ul></nav></details>';

}else{
	$aktuell=isset($_REQUEST["command"]) ? $_REQUEST["command"] : '';
	if($aktuell==''){
		$aktuell='login';
	}

	//login
	$login='<a'.($aktuell=='login' ? ' aria-current="page"' : '').' href="index.php?command=login">'.$m_main_lang['login'].'</a>';

	//account anlegen
	$register='<a'.($aktuell=='register' ? ' aria-current="page"' : '').' href="index.php?command=register">'.$m_main_lang['accountanlegen'].'</a>';

	echo '<div class="kopf-aktionen">';
	echo '<a class="knopf knopf-leise" href="index.php?command=login">'.$m_main_lang['login'].'</a>';
	echo '<a class="knopf knopf-primaer" href="index.php?command=register">'.$m_main_lang['accountanlegen'].'</a>';
	echo '</div>';

	//Aufklappmenü fürs Handy
	echo '<details class="nav-mobil"><summary>'.$m_main_lang['menu'].'</summary><nav aria-label="Hauptnavigation"><ul><li>'.$login.'</li><li>'.$register.'</li></ul></nav></details>';
}
