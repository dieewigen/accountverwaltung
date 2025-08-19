<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";

// Aktionen verarbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_thread':
                if (!empty($_POST['topic']) && !empty($_POST['message'])) {
                    $topic = mysqli_real_escape_string($GLOBALS['dbi'], $_POST['topic']);
                    $message = mysqli_real_escape_string($GLOBALS['dbi'], $_POST['message']);
                    $current_time = time();
                    
                    // Thread erstellen
                    $thread_result = mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "INSERT INTO ls_patchnotes_threads (topic, lastposttime) VALUES (?, ?)",
                        [$topic, $current_time]
                    );
                    
                    if ($thread_result) {
                        $threadid = mysqli_insert_id($GLOBALS['dbi']);
                        
                        // Ersten Post erstellen
                        mysqli_execute_query(
                            $GLOBALS['dbi'],
                            "INSERT INTO ls_patchnotes_posts (threadid, posttime, message) VALUES (?, ?, ?)",
                            [$threadid, $current_time, $message]
                        );
                        
                        echo '<div style="color: green; margin: 10px 0;">Thread erfolgreich erstellt!</div>';
                    }
                }
                break;
                
            case 'create_post':
                if (!empty($_POST['threadid']) && !empty($_POST['message'])) {
                    $threadid = intval($_POST['threadid']);
                    $message = mysqli_real_escape_string($GLOBALS['dbi'], $_POST['message']);
                    $current_time = time();
                    
                    // Post erstellen
                    $post_result = mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "INSERT INTO ls_patchnotes_posts (threadid, posttime, message) VALUES (?, ?, ?)",
                        [$threadid, $current_time, $message]
                    );
                    
                    if ($post_result) {
                        // Thread lastposttime aktualisieren
                        mysqli_execute_query(
                            $GLOBALS['dbi'],
                            "UPDATE ls_patchnotes_threads SET lastposttime = ? WHERE threadid = ?",
                            [$current_time, $threadid]
                        );
                        
                        echo '<div style="color: green; margin: 10px 0;">Post erfolgreich erstellt!</div>';
                    }
                }
                break;
                
            case 'delete_post':
                if (!empty($_POST['postid'])) {
                    $postid = intval($_POST['postid']);
                    
                    // Post löschen
                    $delete_result = mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "DELETE FROM ls_patchnotes_posts WHERE postid = ?",
                        [$postid]
                    );
                    
                    if ($delete_result) {
                        echo '<div style="color: green; margin: 10px 0;">Post erfolgreich gelöscht!</div>';
                    }
                }
                break;
                
            case 'delete_selected':
                if (!empty($_POST['selected_posts']) && is_array($_POST['selected_posts'])) {
                    $deleted_count = 0;
                    foreach ($_POST['selected_posts'] as $postid) {
                        $postid = intval($postid);
                        $delete_result = mysqli_execute_query(
                            $GLOBALS['dbi'],
                            "DELETE FROM ls_patchnotes_posts WHERE postid = ?",
                            [$postid]
                        );
                        if ($delete_result) {
                            $deleted_count++;
                        }
                    }
                    echo '<div style="color: green; margin: 10px 0;">'.$deleted_count.' Post(s) erfolgreich gelöscht!</div>';
                }
                break;
                
            case 'edit_post':
                if (!empty($_POST['postid']) && !empty($_POST['message'])) {
                    $postid = intval($_POST['postid']);
                    $message = mysqli_real_escape_string($GLOBALS['dbi'], $_POST['message']);
                    
                    // Post aktualisieren
                    $edit_result = mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "UPDATE ls_patchnotes_posts SET message = ? WHERE postid = ?",
                        [$message, $postid]
                    );
                    
                    if ($edit_result) {
                        echo '<div style="color: green; margin: 10px 0;">Post erfolgreich bearbeitet!</div>';
                    }
                }
                break;
        }
    }
}

?>
<html>
<head>
<title>Patch Notes Editor</title>
<?php include "cssinclude.php";?>
<style>
.editor-section {
    background-color: #1a1a1a;
    padding: 20px;
    margin: 10px 0;
    border-radius: 5px;
}
.form-group {
    margin: 10px 0;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #cccccc;
}
.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 8px;
    background-color: #333333;
    border: 1px solid #555555;
    color: white;
    border-radius: 3px;
}
.form-group textarea {
    height: 120px;
    resize: vertical;
}
.btn {
    padding: 8px 15px;
    background-color: #66ccff;
    color: black;
    border: none;
    cursor: pointer;
    border-radius: 3px;
    margin: 5px 5px 5px 0;
}
.btn-danger {
    background-color: #ff6666;
    color: white;
}
.thread-list {
    background-color: #2a2a2a;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
}
.post-item {
    background-color: #333333;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
    border-left: 3px solid #666666;
}
.checkbox-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.checkbox-item input[type="checkbox"] {
    width: auto;
    margin-top: 5px;
}
.delete-controls {
    background-color: #444444;
    padding: 10px;
    margin: 10px 0;
    border-radius: 3px;
    display: flex;
    gap: 10px;
    align-items: center;
}
</style>
<script>
function toggleAllPosts(threadId, checked) {
    const checkboxes = document.querySelectorAll('input[name="selected_posts[]"][data-thread="' + threadId + '"]');
    checkboxes.forEach(checkbox => checkbox.checked = checked);
}

function confirmDelete() {
    const selected = document.querySelectorAll('input[name="selected_posts[]"]:checked');
    if (selected.length === 0) {
        alert('Bitte wählen Sie mindestens einen Post aus.');
        return false;
    }
    return confirm('Möchten Sie wirklich ' + selected.length + ' Post(s) löschen?');
}

function updateDeleteButton() {
    const selected = document.querySelectorAll('input[name="selected_posts[]"]:checked');
    const button = document.getElementById('delete-selected-btn');
    const form = button ? button.closest('form') : null;
    
    if (button) {
        button.style.display = selected.length > 0 ? 'inline-block' : 'none';
    }
    
    // Checkboxen zum Löschformular hinzufügen/entfernen
    if (form) {
        // Entferne alte Hidden-Inputs
        const oldInputs = form.querySelectorAll('input[name="selected_posts[]"]');
        oldInputs.forEach(input => input.remove());
        
        // Füge neue Hidden-Inputs hinzu
        selected.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_posts[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
    }
}

function editPost(postId) {
    const postDiv = document.getElementById('post-content-' + postId);
    const originalContent = postDiv.innerHTML;
    const textContent = postDiv.textContent || postDiv.innerText;
    
    // Erstelle Bearbeitungsformular
    postDiv.innerHTML = `
        <form method="POST" style="margin: 0;">
            <input type="hidden" name="action" value="edit_post">
            <input type="hidden" name="postid" value="${postId}">
            <textarea name="message" style="width: 100%; height: 150px; padding: 8px; background-color: #333; border: 1px solid #555; color: white; resize: vertical;">${textContent.trim()}</textarea>
            <div style="margin-top: 10px;">
                <button type="submit" class="btn" style="background-color: #4CAF50;">Speichern</button>
                <button type="button" class="btn" onclick="cancelEdit('${postId}', \`${originalContent.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)" style="background-color: #666;">Abbrechen</button>
            </div>
        </form>
    `;
}

function cancelEdit(postId, originalContent) {
    document.getElementById('post-content-' + postId).innerHTML = originalContent;
}

// Event listener für alle Checkboxen
document.addEventListener('change', function(e) {
    if (e.target.name === 'selected_posts[]') {
        updateDeleteButton();
    }
});
</script>
</head>
<body>
<?php

include "det_userdata.inc.php";

echo '<h1>Patchnotes Editor</h1>';

// Neuen Thread erstellen
echo '<div class="editor-section">';
echo '<h2>Neuen Thread erstellen</h2>';
echo '<form method="POST">';
echo '<input type="hidden" name="action" value="create_thread">';
echo '<div class="form-group">';
echo '<label>Thread-Titel:</label>';
echo '<input type="text" name="topic" required>';
echo '</div>';
echo '<div class="form-group">';
echo '<label>Nachricht:</label>';
echo '<textarea name="message" required></textarea>';
echo '</div>';
echo '<button type="submit" class="btn">Thread erstellen</button>';
echo '</form>';
echo '</div>';

// Neuen Post zu bestehendem Thread hinzufügen
echo '<div class="editor-section">';
echo '<h2>Post zu bestehendem Thread hinzufügen</h2>';
echo '<form method="POST">';
echo '<input type="hidden" name="action" value="create_post">';
echo '<div class="form-group">';
echo '<label>Thread auswählen:</label>';
echo '<select name="threadid" required>';
echo '<option value="">-- Thread auswählen --</option>';

$threads_query = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
);

while ($thread = mysqli_fetch_array($threads_query)) {
    echo '<option value="'.$thread['threadid'].'">'.htmlspecialchars($thread['topic']).'</option>';
}

echo '</select>';
echo '</div>';
echo '<div class="form-group">';
echo '<label>Nachricht:</label>';
echo '<textarea name="message" required></textarea>';
echo '</div>';
echo '<button type="submit" class="btn">Post erstellen</button>';
echo '</form>';
echo '</div>';

// Bestehende Threads und Posts verwalten
echo '<div class="editor-section">';
echo '<h2>Bestehende Threads verwalten</h2>';

echo '<div class="delete-controls">';
echo '<form method="POST" onsubmit="return confirmDelete()" style="display: inline;">';
echo '<input type="hidden" name="action" value="delete_selected">';
echo '<button type="submit" id="delete-selected-btn" class="btn btn-danger" style="display: none;">Ausgewählte Posts löschen</button>';
echo '</form>';
echo '</div>';

$threads_query = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
);

while ($thread = mysqli_fetch_array($threads_query)) {
    echo '<div class="thread-list">';
    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
    echo '<div>';
    echo '<h3>'.htmlspecialchars($thread['topic']).' <small>(ID: '.$thread['threadid'].')</small></h3>';
    echo '<small>Letzter Post: '.date("d.m.Y - H:i", $thread['lastposttime']).'</small>';
    echo '</div>';
    echo '<div>';
    echo '<label style="cursor: pointer; color: #66ccff;">';
    echo '<input type="checkbox" onchange="toggleAllPosts('.$thread['threadid'].', this.checked)" style="margin-right: 5px;"> ';
    echo 'Alle Posts auswählen';
    echo '</label>';
    echo '</div>';
    echo '</div>';
    
    // Posts des Threads laden
    $posts_query = mysqli_execute_query(
        $GLOBALS['dbi'],
        "SELECT * FROM ls_patchnotes_posts WHERE threadid = ? ORDER BY posttime ASC",
        [$thread['threadid']]
    );
    
    if (mysqli_num_rows($posts_query) > 0) {
        echo '<div style="margin-top: 10px;">';
        while ($post = mysqli_fetch_array($posts_query)) {
            echo '<div class="post-item">';
            echo '<div class="checkbox-item">';
            echo '<input type="checkbox" name="selected_posts[]" value="'.$post['postid'].'" data-thread="'.$thread['threadid'].'" onchange="updateDeleteButton()">';
            echo '<div style="flex: 1;">';
            echo '<div style="display: flex; justify-content: space-between; align-items: flex-start;">';
            echo '<small>Post ID: '.$post['postid'].' - '.date("d.m.Y - H:i", $post['posttime']).'</small>';
            echo '<button type="button" class="btn" onclick="editPost('.$post['postid'].')" style="font-size: 11px; padding: 4px 8px; margin-left: 10px;">Bearbeiten</button>';
            echo '</div>';
            
            $message = $post['message'];
            // Diskussionsthread-Links entfernen für Vorschau
            $message = preg_replace('/Diskussionsthread:\s*\[URL\].*?\[\/URL\]/i', '', $message);
            $message = preg_replace('/Diskussionsthread:\s*\[url=.*?\].*?\[\/url\]/i', '', $message);
            
            echo '<div id="post-content-'.$post['postid'].'" style="margin: 8px 0; color: #cccccc;">'.nl2br($message).'</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div style="color: #999999; margin-top: 10px;">Keine Posts in diesem Thread.</div>';
    }
    
    echo '</div>';
}

echo '</div>';

?>

</body>
</html>