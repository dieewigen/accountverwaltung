<?php
/**
 * Verbindung zur Logging-Datenbank (gameserverlogdata),
 * zusätzlich zur Hauptverbindung $GLOBALS['dbi'] aus ../inccon.php.
 */
require_once __DIR__ . '/../inc/env.inc.php';

if (!isset($GLOBALS['dbi_log'])) {
    $GLOBALS['dbi_log'] = mysqli_connect(
        $GLOBALS['env_db_logging_host'],
        $GLOBALS['env_db_logging_user'],
        $GLOBALS['env_db_logging_password'],
        $GLOBALS['env_db_logging_database']
    ) or die("Keine Verbindung zur Logging-Datenbank möglich.");
    $GLOBALS['dbi_log']->set_charset("utf8mb4");
}
