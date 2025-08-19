<?php

// Prüfen ob eine Suche durchgeführt werden soll
if (isset($_REQUEST['search']) && !empty(trim($_REQUEST['search']))) {
    $search_term = trim($_REQUEST['search']);
    
    echo '<div style="margin-bottom: 10px;">';
    echo '<a href="index.php?command=patchnotes">← Zurück zur Übersicht</a>';
    echo '</div>';
    
    echo '<div style="font-size: 18px; margin-bottom: 15px;">Suchergebnisse für: "' . htmlspecialchars($search_term) . '"</div>';
    
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
        echo '<div style="margin-bottom: 15px; color: #999999;">' . $result_count . ' Ergebnis(se) gefunden</div>';
        
        while ($result = mysqli_fetch_array($search_query)) {
            echo '<div style="margin-bottom: 15px; padding: 15px; background-color: #222222; border-left: 3px solid #666666;">';
            
            // Thread-Name und Link
            echo '<div style="margin-bottom: 8px;">';
            echo '<a href="index.php?command=patchnotes&threadid='.$result['threadid'].'" style="color: #66ccff; text-decoration: none; font-weight: bold;">';
            echo htmlspecialchars($result['topic']);
            echo '</a>';
            echo '</div>';
            
            // Datum
            echo '<div style="font-size: 12px; color: #999999; margin-bottom: 10px;">';
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
            $message = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<blockquote style="background-color: #333; padding: 10px; margin: 10px 0; border-left: 3px solid #666; font-style: italic;">$1</blockquote>', $message);
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
            
            echo '<div style="color: #cccccc;">';
            echo $message; // Bereinigte Nachricht mit HTML-Formatierung
            echo '</div>';
            
            // Link zum Thread
            echo '<div style="margin-top: 8px;">';
            echo '<a href="index.php?command=patchnotes&threadid='.$result['threadid'].'#post'.$result['postid'].'" style="color: #66ccff; font-size: 12px;">Zum Thread →</a>';
            echo '</div>';
            
            echo '</div>';
        }
    } else {
        echo '<div style="color: #999999;">Keine Ergebnisse für "' . htmlspecialchars($search_term) . '" gefunden.</div>';
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
        
        echo '<div style="margin-bottom: 10px;">';
        echo '<a href="index.php?command=patchnotes">← Zurück zur Übersicht</a>';
        echo '</div>';
        
        echo '<div style="font-size: 18px; margin-bottom: 15px; padding: 10px; background-color: #333333;">';
        echo htmlspecialchars($thread['topic']);
        echo '</div>';
        
        // Posts des Threads laden
        $posts_query = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT * FROM ls_patchnotes_posts WHERE threadid = ? ORDER BY posttime ASC",
            [$threadid]
        );
        
        if (mysqli_num_rows($posts_query) > 0) {
            while ($post = mysqli_fetch_array($posts_query)) {
                echo '<div id="post'.$post['postid'].'" style="margin-bottom: 15px; padding: 10px; background-color: #222222; border-left: 3px solid #666666;">';
                echo '<div style="font-size: 12px; color: #999999; margin-bottom: 10px;">';
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
                $message = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<blockquote style="background-color: #333; padding: 10px; margin: 10px 0; border-left: 3px solid #666; font-style: italic;">$1</blockquote>', $message);
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
                
                echo '<div>'.$message.'</div>';
                echo '</div>';
            }
        } else {
            echo '<div style="color: #999999;">Keine Posts in diesem Thread gefunden.</div>';
        }
        
    } else {
        echo '<div style="color: #ff0000;">Thread nicht gefunden.</div>';
    }
    
} else {
    // Thread-Übersicht mit Suchfeld anzeigen
    echo '<div style="margin-bottom: 20px;">';
    echo '<form method="GET" action="index.php" style="margin-bottom: 15px;">';
    echo '<input type="hidden" name="command" value="patchnotes">';
    echo '<div style="display: flex; gap: 10px; align-items: center;">';
    echo '<input type="text" name="search" placeholder="Suche in Patchnotes..." value="'.htmlspecialchars($_REQUEST['search'] ?? '').'" style="flex: 1; padding: 8px; background-color: #333333; border: 1px solid #555555; color: white;">';
    echo '<button type="submit" style="padding: 8px 15px; background-color: #66ccff; color: black; border: none; cursor: pointer;">Suchen</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    
    // Thread-Übersicht anzeigen
    $threads_query = mysqli_execute_query(
        $GLOBALS['dbi'],
        "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
    );
    
    if (mysqli_num_rows($threads_query) > 0) {
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background-color: #333333;">';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #555555;">Thema</th>';
        echo '<th style="padding: 10px; text-align: center; border-bottom: 1px solid #555555; width: 150px;">Letzter Beitrag</th>';
        echo '</tr>';
        
        while ($thread = mysqli_fetch_array($threads_query)) {
            echo '<tr style="border-bottom: 1px solid #333333;">';
            echo '<td style="padding: 10px;">';
            echo '<a href="index.php?command=patchnotes&threadid='.$thread['threadid'].'" style="color: #66ccff; text-decoration: none;">';
            echo htmlspecialchars($thread['topic']);
            echo '</a>';
            echo '</td>';
            echo '<td style="padding: 10px; text-align: center; color: #999999;">';
            echo date("d.m.Y - H:i", $thread['lastposttime']);
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
    } else {
        echo '<div style="color: #999999;">Keine Patchnotes verfügbar.</div>';
    }
}

