-- Besucherzählung der Hauptdomain und des Registrierungswegs: besuch_zaehlen() in functions.php,
-- Zählpixel besuch.php, Auswertung ourdetool/besucher.php. In der Datenbank der Accountverwaltung einspielen.
CREATE TABLE `ls_besuch` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `zeit` datetime NOT NULL,
  `seite` varchar(20) NOT NULL,
  `quelle` varchar(100) NOT NULL DEFAULT '',
  `kampagne` varchar(30) NOT NULL DEFAULT '',
  `detail` varchar(30) NOT NULL DEFAULT '',
  `besucher` char(16) NOT NULL,
  `bekannt` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `zeit` (`zeit`),
  KEY `seite` (`seite`,`zeit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
