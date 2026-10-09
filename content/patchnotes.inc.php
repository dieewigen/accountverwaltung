<?php

// Prüfen ob eine Suche durchgeführt werden soll
if (isset($_REQUEST['search']) && !empty(trim($_REQUEST['search']))) {
    $search_term = trim($_REQUEST['search']);

    echo '<a class="zurueck" href="index.php?command=patchnotes">← Zurück zur Übersicht</a>';

    echo '<div class="seitentitel">Suchergebnisse für: "' . htmlspecialchars($search_term) . '"</div>';

    // Suchfunktion - Posts mit dazugehörigen Thread-Informationen
    $search_query = mysqli_execute_query(
        $GLOBALS['dbi'],
        "SELECT p.*, t.topic
         FROM ls_patchnotes_posts p
         INNER JOIN ls_patchnotes_threads t ON p.threadid = t.threadid
         WHERE p.message LIKE ? OR t.topic LIKE ?
         ORDER BY p.posttime DESC",
        ['%' . $search_term . '%', '%' . $search_term . '%']
    );

    if (mysqli_num_rows($search_query) > 0) {
        $result_count = mysqli_num_rows($search_query);
        echo '<p class="leise">' . $result_count . ' Ergebnis(se) gefunden</p>';

        while ($result = mysqli_fetch_array($search_query)) {
            echo '<div class="eintrag">';

            // Thread-Name und Link
            echo '<div class="eintrag-titel">';
            echo '<a href="index.php?command=patchnotes&threadid='.$result['threadid'].'">';
            echo htmlspecialchars($result['topic']);
            echo '</a>';
            echo '</div>';

            // Datum
            echo '<div class="eintrag-datum">';
            echo date("d.m.Y - H:i", $result['posttime']);
            echo '</div>';

            // Vollständiger Post-Inhalt anzeigen
            $message = $result['message'];
            // Escape-Sequenzen auflösen (für \r\n etc.)
            $message = stripcslashes($message);
            // Diskussionsthread-Links entfernen (alle Varianten)
            $message = preg_replace('/Diskussionsthread:\s*\[URL\].*?\[\/URL\]/i', '', $message);
            $message = preg_replace('/Diskussionsthread:\s*\[url=.*?\].*?\[\/url\]/i', '', $message);
            $message = preg_replace('/Diskussionsthread:\s*Diskussionen dazu:\s*\[URL=.*?\].*?\[\/URL\]/i', '', $message);
            $message = preg_replace('/Diskussionen dazu:\s*\[URL=.*?\].*?\[\/URL\]/i', '', $message);
            // BBCode formatieren
            $message = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<blockquote class="zitat">$1</blockquote>', $message);
            $message = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $message);
            $message = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $message);
            $message = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $message);
            // Unerwünschte BBCode-Tags entfernen
            $message = preg_replace('/\[SIZE=\d+\]/i', '', $message);
            $message = preg_replace('/\[\/SIZE\]/i', '', $message);
            $message = preg_replace('/\[COLOR=\w+\]/i', '', $message);
            $message = preg_replace('/\[\/COLOR\]/i', '', $message);
            $message = preg_replace('/\[LIST\]/i', '', $message);
            $message = preg_replace('/\[\/LIST\]/i', '', $message);
            // Zeilenumbrüche konvertieren
            $message = nl2br($message);
            $message = trim($message);

            echo '<div class="eintrag-text">';
            echo $message; // Bereinigte Nachricht mit HTML-Formatierung
            echo '</div>';

            // Link zum Thread
            echo '<div class="eintrag-fuss">';
            echo '<a href="index.php?command=patchnotes&threadid='.$result['threadid'].'#post'.$result['postid'].'">Zum Thread →</a>';
            echo '</div>';

            echo '</div>';
        }
    } else {
        echo '<p class="leise">Keine Ergebnisse für "' . htmlspecialchars($search_term) . '" gefunden.</p>';
    }

} else if (isset($_REQUEST['threadid']) && intval($_REQUEST['threadid']) > 0) {
    $threadid = intval($_REQUEST['threadid']);

    // Thread-Details laden
    $thread_query = mysqli_execute_query(
        $GLOBALS['dbi'],
        "SELECT * FROM ls_patchnotes_threads WHERE threadid = ?",
        [$threadid]
    );

    if (mysqli_num_rows($thread_query) > 0) {
        $thread = mysqli_fetch_array($thread_query);

        echo '<a class="zurueck" href="index.php?command=patchnotes">← Zurück zur Übersicht</a>';

        echo '<div class="seitentitel">';
        echo htmlspecialchars($thread['topic']);
        echo '</div>';

        // Posts des Threads laden
        $posts_query = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT * FROM ls_patchnotes_posts WHERE threadid = ? ORDER BY posttime DESC",
            [$threadid]
        );

        if (mysqli_num_rows($posts_query) > 0) {
            while ($post = mysqli_fetch_array($posts_query)) {
                echo '<div id="post'.$post['postid'].'" class="eintrag">';
                echo '<div class="eintrag-datum">';
                echo date("d.m.Y - H:i", $post['posttime']);
                echo '</div>';

                // Nachricht formatieren (HTML-Tags sind bereits im alten Forum enthalten)
                $message = $post['message'];
                // Escape-Sequenzen auflösen (für \r\n etc.)
                $message = stripcslashes($message);
                // Diskussionsthread-Links entfernen (alle Varianten)
                $message = preg_replace('/Diskussionsthread:\s*\[URL\].*?\[\/URL\]/i', '', $message);
                $message = preg_replace('/Diskussionsthread:\s*\[url=.*?\].*?\[\/url\]/i', '', $message);
                $message = preg_replace('/Diskussionsthread:\s*Diskussionen dazu:\s*\[URL=.*?\].*?\[\/URL\]/i', '', $message);
                $message = preg_replace('/Diskussionen dazu:\s*\[URL=.*?\].*?\[\/URL\]/i', '', $message);
                // BBCode formatieren
                $message = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<blockquote class="zitat">$1</blockquote>', $message);
                $message = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $message);
                $message = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $message);
                $message = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $message);
                // Unerwünschte BBCode-Tags entfernen
                $message = preg_replace('/\[SIZE=\d+\]/i', '', $message);
                $message = preg_replace('/\[\/SIZE\]/i', '', $message);
                $message = preg_replace('/\[COLOR=\w+\]/i', '', $message);
                $message = preg_replace('/\[\/COLOR\]/i', '', $message);
                $message = preg_replace('/\[LIST\]/i', '', $message);
                $message = preg_replace('/\[\/LIST\]/i', '', $message);
                // Zeilenumbrüche konvertieren
                $message = nl2br($message);
                $message = trim($message);

                echo '<div class="eintrag-text">'.$message.'</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="leise">Keine Posts in diesem Thread gefunden.</p>';
        }

    } else {
        echo '<div class="hinweis hinweis-fehler">Thread nicht gefunden.</div>';
    }

} else {
    // Thread-Übersicht mit Suchfeld anzeigen
    echo '<form class="suchzeile" method="GET" action="index.php">';
    echo '<input type="hidden" name="command" value="patchnotes">';
    echo '<input type="text" name="search" placeholder="Suche in Patchnotes..." value="'.htmlspecialchars($_REQUEST['search'] ?? '').'" aria-label="Suche in Patchnotes">';
    echo '<button class="knopf knopf-primaer" type="submit">Suchen</button>';
    echo '</form>';

    // Thread-Übersicht anzeigen
    $threads_query = mysqli_execute_query(
        $GLOBALS['dbi'],
        "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
    );

    if (mysqli_num_rows($threads_query) > 0) {
        echo '<div class="tabelle">';
        echo '<table>';
        echo '<tr>';
        echo '<th>Thema</th>';
        echo '<th class="mitte">Letzter Beitrag</th>';
        echo '</tr>';

        while ($thread = mysqli_fetch_array($threads_query)) {
            echo '<tr>';
            echo '<td>';
            echo '<a href="index.php?command=patchnotes&threadid='.$thread['threadid'].'">';
            echo htmlspecialchars($thread['topic']);
            echo '</a>';
            echo '</td>';
            echo '<td class="mitte leise">';
            echo date("d.m.Y - H:i", $thread['lastposttime']);
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';

    } else {
        echo '<p class="leise">Keine Patchnotes verfügbar.</p>';
    }
}

