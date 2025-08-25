-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20250711.a11cc9efbb
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 19. Aug 2025 um 17:15
-- Server-Version: 11.8.2-MariaDB-log
-- PHP-Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `loginsystem_export`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `de_chat_ignore`
--

CREATE TABLE `de_chat_ignore` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `owner_id_ignore` int(10) UNSIGNED NOT NULL,
  `score` mediumint(8) UNSIGNED NOT NULL,
  `ignore_until` bigint(20) UNSIGNED NOT NULL,
  `spielername` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `de_chat_msg`
--

CREATE TABLE `de_chat_msg` (
  `id` int(10) UNSIGNED NOT NULL,
  `channel` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `channeltyp` tinyint(3) UNSIGNED NOT NULL,
  `server_tag` varchar(5) NOT NULL,
  `spielername` varchar(20) NOT NULL DEFAULT 'anonymous',
  `message` mediumtext NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `owner_id` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `de_newsletter`
--

CREATE TABLE `de_newsletter` (
  `reg_mail` varchar(100) NOT NULL DEFAULT '',
  `sendmail` tinyint(1) NOT NULL DEFAULT 0,
  `register` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `de` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `en` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_de_kb`
--

CREATE TABLE `ls_de_kb` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `server` varchar(5) NOT NULL,
  `atter` mediumtext NOT NULL,
  `deffer` mediumtext NOT NULL,
  `kb` mediumtext NOT NULL,
  `kbversion` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_patchnotes_posts`
--

CREATE TABLE `ls_patchnotes_posts` (
  `postid` int(11) UNSIGNED NOT NULL,
  `threadid` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `posttime` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `message` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_patchnotes_threads`
--

CREATE TABLE `ls_patchnotes_threads` (
  `threadid` int(11) UNSIGNED NOT NULL,
  `topic` varchar(250) NOT NULL DEFAULT '',
  `lastposttime` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_tickets`
--

CREATE TABLE `ls_tickets` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `thema` mediumtext NOT NULL,
  `created` bigint(20) UNSIGNED NOT NULL,
  `modified` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `supporter` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_tickets_posts`
--

CREATE TABLE `ls_tickets_posts` (
  `ticket_id` mediumint(8) UNSIGNED NOT NULL,
  `created` bigint(20) UNSIGNED NOT NULL,
  `poster` varchar(40) NOT NULL,
  `message` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_title`
--

CREATE TABLE `ls_title` (
  `title_id` mediumint(9) NOT NULL,
  `title` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_user`
--

CREATE TABLE `ls_user` (
  `user_id` mediumint(9) NOT NULL,
  `loginname` varchar(100) NOT NULL DEFAULT '',
  `reg_mail` varchar(100) NOT NULL DEFAULT '',
  `pass` varchar(255) NOT NULL DEFAULT '',
  `newpass` varchar(255) NOT NULL DEFAULT '',
  `launcherkey` varchar(16) NOT NULL DEFAULT '',
  `register` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `logins` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `acc_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `last_ip` varchar(40) NOT NULL,
  `credits` int(11) DEFAULT 0,
  `patime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `spielername` varchar(20) NOT NULL DEFAULT '',
  `vorname` varchar(20) NOT NULL DEFAULT '',
  `nachname` varchar(20) NOT NULL DEFAULT '',
  `plz` varchar(5) NOT NULL DEFAULT '',
  `ort` varchar(30) NOT NULL DEFAULT '',
  `strasse` varchar(30) NOT NULL DEFAULT '',
  `land` varchar(30) NOT NULL DEFAULT '',
  `telefon` varchar(40) NOT NULL DEFAULT '',
  `tag` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `monat` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `jahr` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `geschlecht` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `werberid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `supporter` varchar(100) NOT NULL DEFAULT '',
  `tupdate` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `tickets` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `sonderaktion` tinyint(4) NOT NULL DEFAULT 0,
  `kommentar` mediumtext DEFAULT NULL,
  `showeblink` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `newslang` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `tlscore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tlplatz` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `loginkey` varchar(16) NOT NULL DEFAULT '',
  `loginkeytime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `betatester` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `forum_user_id` int(11) NOT NULL DEFAULT 0,
  `forum_nick` varchar(20) NOT NULL DEFAULT '',
  `observation_by` varchar(20) NOT NULL DEFAULT '',
  `newsletter_accept` tinyint(4) NOT NULL DEFAULT 0,
  `fb_id` mediumtext DEFAULT NULL,
  `fb_access_token` mediumtext DEFAULT NULL,
  `google_id` mediumtext DEFAULT NULL,
  `google_access_token` mediumtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_user_count`
--

CREATE TABLE `ls_user_count` (
  `server` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `anzahl` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `pa_anz` mediumint(8) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_user_log`
--

CREATE TABLE `ls_user_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `serverid` smallint(5) UNSIGNED NOT NULL,
  `userid` mediumint(8) UNSIGNED NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) NOT NULL,
  `file` varchar(25) DEFAULT NULL,
  `getpost` varchar(4096) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ls_user_title`
--

CREATE TABLE `ls_user_title` (
  `user_id` mediumint(9) NOT NULL,
  `title_id` mediumint(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `de_chat_ignore`
--
ALTER TABLE `de_chat_ignore`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indizes für die Tabelle `de_chat_msg`
--
ALTER TABLE `de_chat_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel` (`channel`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indizes für die Tabelle `de_newsletter`
--
ALTER TABLE `de_newsletter`
  ADD PRIMARY KEY (`reg_mail`);

--
-- Indizes für die Tabelle `ls_de_kb`
--
ALTER TABLE `ls_de_kb`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ls_patchnotes_posts`
--
ALTER TABLE `ls_patchnotes_posts`
  ADD PRIMARY KEY (`postid`),
  ADD KEY `threadid` (`threadid`),
  ADD KEY `threadid_2` (`threadid`),
  ADD KEY `visible` (`posttime`);

--
-- Indizes für die Tabelle `ls_patchnotes_threads`
--
ALTER TABLE `ls_patchnotes_threads`
  ADD PRIMARY KEY (`threadid`),
  ADD KEY `boardid` (`lastposttime`),
  ADD KEY `visible` (`lastposttime`);

--
-- Indizes für die Tabelle `ls_tickets`
--
ALTER TABLE `ls_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ls_tickets_posts`
--
ALTER TABLE `ls_tickets_posts`
  ADD PRIMARY KEY (`ticket_id`,`created`);

--
-- Indizes für die Tabelle `ls_title`
--
ALTER TABLE `ls_title`
  ADD PRIMARY KEY (`title_id`),
  ADD KEY `title_id` (`title_id`);

--
-- Indizes für die Tabelle `ls_user`
--
ALTER TABLE `ls_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `loginname` (`loginname`),
  ADD UNIQUE KEY `reg_mail` (`reg_mail`),
  ADD KEY `plz` (`plz`),
  ADD KEY `tlscore` (`tlscore`),
  ADD KEY `register` (`register`),
  ADD KEY `last_login` (`last_login`),
  ADD KEY `tlplatz` (`tlplatz`),
  ADD KEY `werberid` (`werberid`),
  ADD KEY `loginkey` (`loginkey`);

--
-- Indizes für die Tabelle `ls_user_log`
--
ALTER TABLE `ls_user_log`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ls_user_title`
--
ALTER TABLE `ls_user_title`
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `de_chat_ignore`
--
ALTER TABLE `de_chat_ignore`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `de_chat_msg`
--
ALTER TABLE `de_chat_msg`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ls_de_kb`
--
ALTER TABLE `ls_de_kb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ls_tickets`
--
ALTER TABLE `ls_tickets`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ls_title`
--
ALTER TABLE `ls_title`
  MODIFY `title_id` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ls_user`
--
ALTER TABLE `ls_user`
  MODIFY `user_id` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ls_user_log`
--
ALTER TABLE `ls_user_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
