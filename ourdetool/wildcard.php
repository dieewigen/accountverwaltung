<?php
include_once "../inccon.php";
include_once "det_userdata.inc.php";

// Suchstring; Präfixzeichen bestimmt das Suchfeld, de_user_search.php
// übersetzt vorab %->$ (Wildcard-Logik unverändert beibehalten)
$sstr = req_str('sstr');

$page_title = 'Wildcard-Suche';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";

echo '<table>';
echo '<tr>';
echo '<th>UserID</th>';
echo '<th>Suche</th>';
echo '</tr>';

$UCount = 0;
if ($sstr != '') {
    switch ($sstr[0]) {
        case '-': //nic
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, nic FROM ls_user WHERE nic LIKE ?",
                ['%' . $sstr . '%']
            );

            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["nic"]) . '</td></tr>';
                $UCount++;
            }

            break;
        case '*': //spielername
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, spielername FROM ls_user WHERE spielername LIKE ?",
                ['%' . $sstr . '%']
            );

            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["spielername"]) . '</td></tr>';
                $UCount++;
            }

            break;
        case '$': //email-adresse
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, reg_mail FROM ls_user WHERE reg_mail LIKE ?",
                ['%' . $sstr . '%']
            );

            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["reg_mail"]) . '</td></tr>';
                $UCount++;
            }

            break;
        case '~': //IP
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, last_ip FROM ls_user WHERE last_ip LIKE ? ORDER BY last_ip",
                ['%' . $sstr . '%']
            );
            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["last_ip"]) . '</td></tr>';
                $UCount++;
            }

            break;
        case '|': //Vor-/Nachname
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, vorname, nachname FROM ls_user WHERE vorname LIKE ? OR nachname LIKE ?",
                ['%' . $sstr . '%', '%' . $sstr . '%']
            );
            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["vorname"]) . ' ' . htmlspecialchars((string)$UData["nachname"]) . '</td></tr>';
                $UCount++;
            }
            break;
        case 'ö': //Ort
            $sstr = str_replace($sstr[0] . $sstr[1], $sstr[1], $sstr);
            $result = mysqli_execute_query(
                $GLOBALS['dbi'],
                "SELECT user_id, ort FROM ls_user WHERE ort LIKE ?",
                ['%' . $sstr . '%']
            );
            while ($UData = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                echo '<tr><td><a href="info.php?uid=' . (int)$UData["user_id"] . '" target="_blank" rel="noopener">' . (int)$UData["user_id"] . '</a></td>';
                echo '<td>' . htmlspecialchars((string)$UData["ort"]) . '</td></tr>';
                $UCount++;
            }
            break;
        default:
            break;
    }//switch sstr ende
}

echo '</table><br>' . $UCount . ' User gefunden<br>';

include_once "inc.layout.bottom.php";
