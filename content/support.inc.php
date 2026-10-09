<?php

//include 'content/de/lang/'.$ums_language.'_support.lang.php';

$time = time();

//Unterseiten (Bestehende Tickets, Neues Ticket erstellen) als Reiter, aufgebaut in m_main.php
if (isset($um) && $um != '') {
    echo $um;
}

//bestehende tickets
if ($_REQUEST['page'] == 1) {

    if (!isset($_REQUEST['showtid'])) {
        /* Altes Statement:
        $db_daten = mysqli_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets WHERE user_id='".intval($_SESSION['ums_user_id'])."' ORDER BY status ASC, modified DESC");
        */
        $db_daten = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT * FROM ls_tickets WHERE user_id=? ORDER BY status ASC, modified DESC",
            [$_SESSION['ums_user_id']]
        );
        $num = mysqli_num_rows($db_daten);
        if ($num > 0) {
            //kopf
            echo '<div class="tabelle">';
            echo '<table>';
            echo '<tr><th>Betreff</th><th>erstellt</th><th>letzte &auml;nderung</th><th>Status</th></tr>';

            while ($row = mysqli_fetch_array($db_daten)) {
                echo '<tr>';

                echo '<td><a href="index.php?command=support&page=1&showtid='.$row['id'].'">'.utf8_encode_fix($row['thema']).'</a></td>';
                echo '<td>'.date("G:i d.m.Y", $row['created']).'</td>';
                echo '<td>'.date("G:i d.m.Y", $row['modified']).'</td>';
                if ($row['status'] == 0) {
                    $status = 'offen';
                } else {
                    $status = 'beantwortet';
                }
                echo '<td>'.$status.'</td>';

                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>Es gibt keine Tickets.</p>';
        }
    } else { //ticket anzeigen mit Eingabemöglichkeit für eine Antwort
        $ticket_id = intval($_REQUEST['showtid']);
        /* Altes Statement:
        $db_daten = mysqli_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets WHERE user_id='".intval($_SESSION['ums_user_id'])."' AND id='$ticket_id'");
        */
        $db_daten = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT * FROM ls_tickets WHERE user_id=? AND id=?",
            [$_SESSION['ums_user_id'], $ticket_id]
        );
        $num = mysqli_num_rows($db_daten);
        if ($num > 0) {
            $row = mysqli_fetch_array($db_daten);
            //überprüfen ob das ticket dem spieler gehört
            if ($_SESSION['ums_user_id'] == $row['user_id']) {
                //überprüfen ob eine antwort eingefügt werden soll
                if (isset($_REQUEST['reply']) && $_REQUEST['reply'] == 1) {
                    $messagesql = trim($_REQUEST['nachricht']);
                    $messagesql = htmlspecialchars($messagesql, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
                    $messagesql = str_replace('\r\n', '<br>', $messagesql);
                    $messagesql = utf8_decode($messagesql);

                    //nachricht hinterlegen
                    /* Altes Statement:
                    mysqli_query($GLOBALS['dbi'], "INSERT INTO ls_tickets_posts SET ticket_id='$ticket_id', created='$time', poster='$ums_spielername', message='$messagesql';");
                    */
                    mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "INSERT INTO ls_tickets_posts SET ticket_id=?, created=?, poster=?, message=?",
                        [$ticket_id, $time, $ums_spielername, $messagesql]
                    );

                    //ticketstatus anpassen
                    /* Altes Statement:
                    mysqli_query($GLOBALS['dbi'], "UPDATE ls_tickets SET modified='$time', status=0 WHERE id='$ticket_id';");
                    */
                    mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "UPDATE ls_tickets SET modified=?, status=0 WHERE id=?",
                        [$time, $ticket_id]
                    );
                }


                //nachricht ausgeben
                echo '<div class="seitentitel">'.$row['thema'].'</div>';

                //die einzelnen posts
                /* Altes Statement:
                $db_daten = mysqli_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets_posts WHERE ticket_id='$ticket_id' ORDER BY created ASC");
                */
                $db_daten = mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "SELECT * FROM ls_tickets_posts WHERE ticket_id=? ORDER BY created ASC",
                    [$ticket_id]
                );
                while ($row = mysqli_fetch_array($db_daten)) {
                    //eigene Beiträge neutral, Antworten des Supports grün abgesetzt
                    if ($row['poster'] == $ums_spielername) {
                        $beitragklasse = 'beitrag';
                    } else {
                        $beitragklasse = 'beitrag beitrag-support';
                    }
                    echo '<div class="'.$beitragklasse.'">';
                    //header
                    echo '<div class="beitrag-kopf">'.utf8_encode_fix($row['poster']).' - '.date("G:i d.m.Y", $row['created']).'</div>';
                    //body
                    echo '<div class="beitrag-text">'.utf8_encode_fix($row['message']).'</div>';
                    echo '</div>';
                }

                //antwortformular
                echo '<form action="index.php?command=support&page=1&reply=1&showtid='.$ticket_id.'" method="POST">';
                echo '<div class="feld">';
                echo '<label for="nachricht">Nachricht</label>';
                echo '<textarea rows="12" name="nachricht" id="nachricht"></textarea>';
                echo '</div>';

                echo '<div class="formular-aktionen"><input class="knopf knopf-primaer" type="submit" name="bieten" value="Nachricht senden"></div>';

                echo '</form>';


            }
        }
    }
}
//neues ticket erstellen
elseif ($_REQUEST['page'] == 2) {
    unset($themen);
    $themen[] = 'Bitte ausw&auml;hlen';
    $themen[] = 'Accountverwaltung';
    $themen[] = 'Die Ewigen - Allgemein';
    $themen[] = 'Die Ewigen - xDE';
    $themen[] = 'Die Ewigen - SDE';
    $themen[] = 'Sonstiges';

    $hasall = 1;
    //�berpr�fen, ob ein neues ticket erstellt werden soll
    if ($_REQUEST['createticket'] == 1) {
        if ($_REQUEST['thema'] == 0) {
            echo '<div class="hinweis hinweis-fehler">W&auml;hle bitte aus worum es geht.</div>';
            $hasall = 0;
        }
        if ($_REQUEST['nachricht'] == '') {
            echo '<div class="hinweis hinweis-fehler">Die Nachricht ist leer.</div>';
            $hasall = 0;
        }
    }

    if ($hasall == 1 and $_REQUEST['createticket'] == 1) {//ticket in der db hinterlegen
        echo '<div class="hinweis hinweis-erfolg">Das Ticket wurde gespeichert und wird schnellstm&ouml;glich bearbeitet.</div>';
        $themasql = trim($themen[$_REQUEST['thema']]);

        $messagesql = trim($_REQUEST['nachricht']);
        $messagesql = htmlspecialchars($messagesql, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
        $messagesql = str_replace('\r\n', '<br>', $messagesql);
        $messagesql = utf8_decode($messagesql);

        // Neues Statement mit mysqli_execute_query (PHP 8.4+)
        mysqli_execute_query(
            $GLOBALS['dbi'],
            "INSERT INTO ls_tickets SET user_id=?, thema=?, created=?, modified=?, status=0",
            [$_SESSION['ums_user_id'], $themasql, $time, $time]
        );

        // Ticket ID abrufen
        $ticket_id = mysqli_insert_id($GLOBALS['dbi']);

        // Zweites Statement mit mysqli_execute_query
        mysqli_execute_query(
            $GLOBALS['dbi'],
            "INSERT INTO ls_tickets_posts SET ticket_id=?, created=?, poster=?, message=?",
            [$ticket_id, $time, $ums_spielername, $messagesql]
        );
        //info per e-mail an die supporter
        require_once 'lib/phpmailer/class.phpmailer.php';
        require_once 'lib/phpmailer/class.smtp.php';

        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = $GLOBALS['env_mail_server'];
        $mail->SMTPAuth = true;
        $mail->Username = $GLOBALS['env_mail_user'];
        $mail->Password = $GLOBALS['env_mail_password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($GLOBALS['env_mail_noreply'], 'Die Ewigen');
        $mail->addReplyTo($GLOBALS['env_mail_noreply'], 'Die Ewigen');
        $mail->addAddress('supportverteiler@die-ewigen.com');
        $mail->Subject = 'Neues Ticket: '.$themasql;
        $mail->Body = $messagesql;

        //send the message, check for errors
        $mail->send();

        //mail('supportverteiler@die-ewigen.com', 'Neues Ticket: '.$themasql, $messagesql, 'FROM: intern@die-ewigen.com');
    } else { //ticketeingabe anbieten
        echo '<form action="index.php?command=support&page=2&createticket=1" method="POST">';
        echo '<p>Wenn Du Fragen hast, dann kannst Du diese hier stellen und wir beantworten diese so schnell es geht.</p>';

        echo '<div class="feld formular">';
        echo '<label for="thema">Worum geht es?</label>';

        echo '
    	<select name="thema" id="thema">';
        for ($i = 0;$i < count($themen);$i++) {
            echo '<option value="'.$i.'"';
            if ($i == $_REQUEST['thema']) {
                echo ' selected';
            }
            echo '>'.$themen[$i].'</option>';

        }

        echo '</select>';
        echo '</div>';

        echo '<div class="feld">';
        echo '<label for="nachricht">Nachricht</label>';

        echo '<textarea rows="12" name="nachricht" id="nachricht">'.str_replace('\r\n', "\n", $_REQUEST['nachricht']).'</textarea>';
        echo '</div>';

        echo '<div class="formular-aktionen"><input class="knopf knopf-primaer" type="submit" name="bieten" value="Ticket erstellen"></div>';

        echo '</form>';
    }
}
