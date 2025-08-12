<?php
include "../inc/env.inc.php";

$GLOBALS['logdbi'] = mysqli_connect(
    $GLOBALS['env_db_logging_host'], 
    $GLOBALS['env_db_logging_user'], 
    $GLOBALS['env_db_logging_password'], 
    $GLOBALS['env_db_logging_database']
) or die("Keine Verbindung zur Logging-Datenbank möglich.");
?>