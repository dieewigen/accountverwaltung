<?php
/**
 * Verbindung zur Logging-Datenbank (gameserverlogdata),
 * zusätzlich zur Hauptverbindung $GLOBALS['dbi'] aus ../inccon.php.
 */
require_once __DIR__ . '/../inc/env.inc.php';

if (!isset($GLOBALS['dbi_log'])) {
    // Fehlende Zugangsdaten klar melden, statt anonym an localhost zu verbinden
    foreach (['host', 'user', 'password', 'database'] as $log_key) {
        if (!isset($GLOBALS['env_db_logging_' . $log_key])) {
            error_log("ourdetool: \$GLOBALS['env_db_logging_$log_key'] fehlt in inc/env.inc.php");
            http_response_code(500);
            die('Logging-Datenbank nicht konfiguriert: env_db_logging_* fehlt in inc/env.inc.php.');
        }
    }

    $GLOBALS['dbi_log'] = mysqli_connect(
        $GLOBALS['env_db_logging_host'],
        $GLOBALS['env_db_logging_user'],
        $GLOBALS['env_db_logging_password'],
        $GLOBALS['env_db_logging_database']
    ) or die("Keine Verbindung zur Logging-Datenbank möglich.");
    $GLOBALS['dbi_log']->set_charset("utf8mb4");
}
