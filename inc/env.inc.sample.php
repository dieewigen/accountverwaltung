<?php
//in env.inc.php umbenennen und die Daten hinterlegen

//Admin-E-Mail-Adresse
$GLOBALS['env_admin_email']='';

//internes Supportsystem aktivieren?
$GLOBALS['env_enable_support_page']=1;

//internes Supportsystem aktivieren?
$GLOBALS['env_enable_support_page']=1;

//DE-KB-DB aktivieren?
$GLOBALS['env_enable_de_kb_db']=1;

//Forenmenüpunkt aktivieren?
$GLOBALS['env_enable_forum_connect']=0;

//URL-Liste
$GLOBALS['env_url_portal']='';
$GLOBALS['env_url_impressum']='';
$GLOBALS['env_url_datenschutz']='';
$GLOBALS['env_url_agb']='';

//Accountverwaltung
$GLOBALS['env_db_loginsystem_host']='';
$GLOBALS['env_db_loginsystem_user']='';
$GLOBALS['env_db_loginsystem_password']='';
$GLOBALS['env_db_loginsystem_database']='';

//Logging-DB
$GLOBALS['env_db_logging_host']='';
$GLOBALS['env_db_logging_user']='';
$GLOBALS['env_db_logging_password']='';
$GLOBALS['env_db_logging_database']='';

//SMTP-Postfach
$GLOBALS['env_mail_server']='';
$GLOBALS['env_mail_noreply']='';
$GLOBALS['env_mail_user']='';
$GLOBALS['env_mail_password']='';

//RPC Authcode
$GLOBALS['env_rpc_authcode']='';

//Datenbankverbindung für den Spielserver, erreichbar per databaseKey
$GLOBALS['env_databaseKey']['xde']['host']='';
$GLOBALS['env_databaseKey']['xde']['database']='';
$GLOBALS['env_databaseKey']['xde']['user']='';
$GLOBALS['env_databaseKey']['xde']['password']='';

$GLOBALS['env_databaseKey']['sde']['host']='';
$GLOBALS['env_databaseKey']['sde']['database']='';
$GLOBALS['env_databaseKey']['sde']['user']='';
$GLOBALS['env_databaseKey']['sde']['password']='';


?>