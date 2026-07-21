<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
include_once "det_userdata.inc.php";

$time = time();
$ticket_id = req_int('showtid');

$page_title = 'Supporttickets';
include_once "inc.layout.top.php";

if ($ticket_id > 0) {
    $db_daten = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets WHERE id=?", [$ticket_id]);
    $ticket = $db_daten ? mysqli_fetch_array($db_daten) : null;

    if ($ticket) {
        $user_id = (int)$ticket['user_id'];

        //überprüfen ob eine antwort eingefügt werden soll
        if (req_int('reply') === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_require();
            $messagesql = trim(req_str('nachricht'));
            $messagesql = htmlspecialchars($messagesql);
            $messagesql = nl2br($messagesql, false);

            //nachricht hinterlegen
            mysqli_execute_query(
                $GLOBALS['dbi'],
                "INSERT INTO ls_tickets_posts SET ticket_id=?, created=?, poster=?, message=?",
                [$ticket_id, $time, $det_email, $messagesql]
            );

            //ticketstatus anpassen
            mysqli_execute_query(
                $GLOBALS['dbi'],
                "UPDATE ls_tickets SET modified=?, status=?, supporter=? WHERE id=?",
                [$time, 1, $det_email, $ticket_id]
            );
            $ticket['status'] = 1;
            echo '<div class="flash flash-ok">Antwort gespeichert, Ticket geschlossen.</div>';
        }

        if (req_int('close') === 1) {
            csrf_require();
            //ticketstatus anpassen ohne antwort
            mysqli_execute_query(
                $GLOBALS['dbi'],
                "UPDATE ls_tickets SET modified=?, status=?, supporter=? WHERE id=?",
                [$time, 1, $det_email, $ticket_id]
            );
            $ticket['status'] = 1;
            echo '<div class="flash flash-ok">Ticket geschlossen.</div>';
        }

        $status = ((int)$ticket['status'] === 0) ? 'Ticket ist offen' : 'Ticket ist geschlossen';

        echo '<div class="ticket-thread">';
        echo '<div class="ticket-subject">' . htmlspecialchars((string)$ticket['thema']) . ' <span class="dim">(' . $status . ')</span></div>';

        //die einzelnen posts (Nachrichten liegen als beim Insert escapetes HTML mit <br> vor)
        $db_daten = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_tickets_posts WHERE ticket_id=? ORDER BY created ASC", [$ticket_id]);
        $spielername = '';
        while ($post = mysqli_fetch_array($db_daten)) {
            if ($spielername == '') {
                $spielername = $post['poster'];
            }
            $is_owner = ($post['poster'] == $spielername);

            echo '<div class="ticket-post' . ($is_owner ? '' : ' supporter') . '">';
            echo '<div class="ticket-post-head">';
            if ($is_owner) {
                echo '<a href="info.php?uid=' . $user_id . '">' . htmlspecialchars((string)$post['poster']) . '</a>';
            } else {
                echo htmlspecialchars((string)$post['poster']);
            }
            echo ' &ndash; ' . date("G:i:s d.m.Y", (int)$post['created']) . '</div>';
            echo '<div class="ticket-post-body">' . $post['message'] . '</div>';
            echo '</div>';
        }
        echo '</div>';

        //antwortformular
        echo '<form action="tickets.php?reply=1&showtid=' . $ticket_id . '" method="post">';
        echo csrf_field();
        echo '<h3>Nachricht</h3>';
        echo '<textarea rows="12" name="nachricht" cols="75"></textarea><br><br>';
        echo '<input type="submit" name="bieten" value="Nachricht senden">';
        echo '</form>';

        echo '<p>Das Ticket ben&ouml;tigt keine Antwort: <a href="' . csrf_url('tickets.php?close=1&showtid=' . $ticket_id) . '" data-confirm="Ticket ' . $ticket_id . ' ohne Antwort schlie&szlig;en?">Ticket schlie&szlig;en</a></p>';
    } else {
        echo '<div class="flash flash-warn">Ticket nicht gefunden.</div>';
    }
} else {
    foreach ([['Offene Tickets', 0, ''], ['Beantwortete Tickets', 1, ' LIMIT 50']] as [$ueberschrift, $tstatus, $limit]) {
        echo '<h2>' . $ueberschrift . '</h2>';

        $db_daten = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT t.*, u.spielername FROM ls_tickets t LEFT JOIN ls_user u ON u.user_id = t.user_id WHERE t.status=? ORDER BY t.created DESC" . $limit,
            [$tstatus]
        );
        if (mysqli_num_rows($db_daten) > 0) {
            echo '<table>';
            echo '<tr><th>Betreff</th><th>User</th><th>erstellt</th><th>letzte &Auml;nderung</th><th>Supporter</th></tr>';

            while ($row = mysqli_fetch_array($db_daten)) {
                echo '<tr>';
                echo '<td><a href="tickets.php?showtid=' . (int)$row['id'] . '">' . htmlspecialchars((string)$row['thema']) . '</a></td>';
                echo '<td><a href="info.php?uid=' . (int)$row['user_id'] . '">' . htmlspecialchars((string)$row['spielername']) . '</a></td>';
                echo '<td>' . date("H:i:s d.m.Y", (int)$row['created']) . '</td>';
                echo '<td>' . date("H:i:s d.m.Y", (int)$row['modified']) . '</td>';
                echo '<td>' . ($row['supporter'] == '' ? '<span class="dim">noch keiner</span>' : htmlspecialchars((string)$row['supporter'])) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo '<p class="dim">' . ($tstatus === 0 ? 'Es gibt keine offenen Tickets.' : 'Es gibt keine beantworteten Tickets.') . '</p>';
        }
    }
}

include_once "inc.layout.bottom.php";
