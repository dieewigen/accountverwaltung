<?php
include_once "../inc/sv.inc.php";
include_once "../inccon.php";
// Auth/Levelprüfung VOR den Aktionen (patchnotes_editor.lvl)
include_once "det_userdata.inc.php";

$flash = '';

// Aktionen verarbeiten (Prepared Statements übernehmen das Escaping,
// das frühere mysqli_real_escape_string entfiel — es führte zu doppeltem Escaping)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    csrf_require();
    $current_time = time();

    switch ($_POST['action']) {
        case 'create_thread':
            if (!empty($_POST['topic']) && !empty($_POST['message'])) {
                $thread_result = mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "INSERT INTO ls_patchnotes_threads (topic, lastposttime) VALUES (?, ?)",
                    [req_str('topic'), $current_time]
                );

                if ($thread_result) {
                    $threadid = mysqli_insert_id($GLOBALS['dbi']);

                    // Ersten Post erstellen
                    mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "INSERT INTO ls_patchnotes_posts (threadid, posttime, message) VALUES (?, ?, ?)",
                        [$threadid, $current_time, req_str('message')]
                    );

                    $flash = 'Thread erfolgreich erstellt!';
                }
            }
            break;

        case 'create_post':
            if (!empty($_POST['threadid']) && !empty($_POST['message'])) {
                $threadid = req_int('threadid');

                $post_result = mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "INSERT INTO ls_patchnotes_posts (threadid, posttime, message) VALUES (?, ?, ?)",
                    [$threadid, $current_time, req_str('message')]
                );

                if ($post_result) {
                    mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "UPDATE ls_patchnotes_threads SET lastposttime = ? WHERE threadid = ?",
                        [$current_time, $threadid]
                    );

                    $flash = 'Post erfolgreich erstellt!';
                }
            }
            break;

        case 'delete_selected':
            if (!empty($_POST['selected_posts']) && is_array($_POST['selected_posts'])) {
                $deleted_count = 0;
                foreach ($_POST['selected_posts'] as $postid) {
                    $delete_result = mysqli_execute_query(
                        $GLOBALS['dbi'],
                        "DELETE FROM ls_patchnotes_posts WHERE postid = ?",
                        [(int)$postid]
                    );
                    if ($delete_result) {
                        $deleted_count++;
                    }
                }
                $flash = $deleted_count . ' Post(s) erfolgreich gelöscht!';
            }
            break;

        case 'edit_post':
            if (!empty($_POST['postid']) && !empty($_POST['message'])) {
                $edit_result = mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "UPDATE ls_patchnotes_posts SET message = ? WHERE postid = ?",
                    [req_str('message'), req_int('postid')]
                );

                if ($edit_result) {
                    $flash = 'Post erfolgreich bearbeitet!';
                }
            }
            break;

        default:
            break;
    }
}

$page_title = 'Patch Notes Editor';
$active_nav = 'patchnotes';
$page_head_extra = '<script>var CSRF_TOKEN = ' . json_encode(csrf_token()) . ';</script>
<style>
.thread-list {
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 14px;
    margin: 10px 0;
}
.post-item {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-left: 3px solid var(--border);
    border-radius: 3px;
    padding: 8px 10px;
    margin: 5px 0;
}
.checkbox-item { display: flex; align-items: flex-start; gap: 10px; }
.checkbox-item input[type="checkbox"] { width: auto; margin-top: 5px; }
.form-group { margin: 10px 0; }
.form-group label { display: block; margin-bottom: 5px; color: var(--text-dim); }
.form-group input, .form-group textarea, .form-group select { width: min(100%, 640px); }
.form-group textarea { height: 120px; resize: vertical; }
.thread-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.post-head { display: flex; justify-content: space-between; align-items: flex-start; }
</style>
<script>
function toggleAllPosts(threadId, checked) {
    const checkboxes = document.querySelectorAll(\'input[name="selected_posts[]"][data-thread="\' + threadId + \'"]\');
    checkboxes.forEach(checkbox => checkbox.checked = checked);
}

function confirmDelete() {
    const selected = document.querySelectorAll(\'input[name="selected_posts[]"]:checked\');
    if (selected.length === 0) {
        alert(\'Bitte mindestens einen Post auswählen.\');
        return false;
    }
    return confirm(\'Wirklich \' + selected.length + \' Post(s) löschen?\');
}

function updateDeleteButton() {
    const selected = document.querySelectorAll(\'input[name="selected_posts[]"]:checked\');
    const button = document.getElementById(\'delete-selected-btn\');
    const form = button ? button.closest(\'form\') : null;

    if (button) {
        button.style.display = selected.length > 0 ? \'inline-block\' : \'none\';
    }

    // Checkboxen zum Löschformular hinzufügen/entfernen
    if (form) {
        const oldInputs = form.querySelectorAll(\'input[name="selected_posts[]"]\');
        oldInputs.forEach(input => input.remove());

        selected.forEach(checkbox => {
            const hiddenInput = document.createElement(\'input\');
            hiddenInput.type = \'hidden\';
            hiddenInput.name = \'selected_posts[]\';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
    }
}

function editPost(postId) {
    const postDiv = document.getElementById(\'post-content-\' + postId);
    const originalContent = postDiv.innerHTML;
    const textContent = postDiv.textContent || postDiv.innerText;

    // Bearbeitungsformular an Ort und Stelle aufbauen
    postDiv.innerHTML = \'\';
    const form = document.createElement(\'form\');
    form.method = \'POST\';
    form.style.margin = \'0\';

    const mk = (name, value) => {
        const i = document.createElement(\'input\');
        i.type = \'hidden\'; i.name = name; i.value = value;
        form.appendChild(i);
    };
    mk(\'action\', \'edit_post\');
    mk(\'postid\', postId);
    mk(\'t\', CSRF_TOKEN);

    const ta = document.createElement(\'textarea\');
    ta.name = \'message\';
    ta.style.cssText = \'width: 100%; height: 150px; resize: vertical;\';
    ta.value = textContent.trim();
    form.appendChild(ta);

    const bar = document.createElement(\'div\');
    bar.style.marginTop = \'10px\';
    const save = document.createElement(\'button\');
    save.type = \'submit\'; save.textContent = \'Speichern\';
    const cancel = document.createElement(\'button\');
    cancel.type = \'button\'; cancel.textContent = \'Abbrechen\';
    cancel.addEventListener(\'click\', function () { postDiv.innerHTML = originalContent; });
    bar.appendChild(save);
    bar.appendChild(document.createTextNode(\' \'));
    bar.appendChild(cancel);
    form.appendChild(bar);

    postDiv.appendChild(form);
}

// Event listener für alle Checkboxen
document.addEventListener(\'change\', function(e) {
    if (e.target.name === \'selected_posts[]\') {
        updateDeleteButton();
    }
});
</script>';

include_once "inc.layout.top.php";

if ($flash !== '') {
    echo '<div class="flash flash-ok">' . htmlspecialchars($flash) . '</div>';
}

// Neuen Thread erstellen
echo '<div class="card">';
echo '<h2>Neuen Thread erstellen</h2>';
echo '<form method="POST">';
echo csrf_field();
echo '<input type="hidden" name="action" value="create_thread">';
echo '<div class="form-group">';
echo '<label for="new-topic">Thread-Titel:</label>';
echo '<input type="text" id="new-topic" name="topic" required>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="new-message">Nachricht:</label>';
echo '<textarea id="new-message" name="message" required></textarea>';
echo '</div>';
echo '<button type="submit">Thread erstellen</button>';
echo '</form>';
echo '</div>';

// Neuen Post zu bestehendem Thread hinzufügen
echo '<div class="card">';
echo '<h2>Post zu bestehendem Thread hinzuf&uuml;gen</h2>';
echo '<form method="POST">';
echo csrf_field();
echo '<input type="hidden" name="action" value="create_post">';
echo '<div class="form-group">';
echo '<label for="post-thread">Thread ausw&auml;hlen:</label>';
echo '<select id="post-thread" name="threadid" required>';
echo '<option value="">-- Thread ausw&auml;hlen --</option>';

$threads_query = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
);

while ($thread = mysqli_fetch_array($threads_query)) {
    echo '<option value="' . (int)$thread['threadid'] . '">' . htmlspecialchars((string)$thread['topic']) . '</option>';
}

echo '</select>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="post-message">Nachricht:</label>';
echo '<textarea id="post-message" name="message" required></textarea>';
echo '</div>';
echo '<button type="submit">Post erstellen</button>';
echo '</form>';
echo '</div>';

// Bestehende Threads und Posts verwalten
echo '<div class="card">';
echo '<h2>Bestehende Threads verwalten</h2>';

echo '<form method="POST" onsubmit="return confirmDelete()" class="inline">';
echo csrf_field();
echo '<input type="hidden" name="action" value="delete_selected">';
echo '<button type="submit" id="delete-selected-btn" class="btn-danger" style="display: none;">Ausgew&auml;hlte Posts l&ouml;schen</button>';
echo '</form>';

$threads_query = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT * FROM ls_patchnotes_threads ORDER BY lastposttime DESC"
);

while ($thread = mysqli_fetch_array($threads_query)) {
    echo '<div class="thread-list">';
    echo '<div class="thread-head">';
    echo '<div>';
    echo '<h3>' . htmlspecialchars((string)$thread['topic']) . ' <small class="dim">(ID: ' . (int)$thread['threadid'] . ')</small></h3>';
    echo '<small class="dim">Letzter Post: ' . date("d.m.Y - H:i", (int)$thread['lastposttime']) . '</small>';
    echo '</div>';
    echo '<div>';
    echo '<label style="cursor: pointer;">';
    echo '<input type="checkbox" onchange="toggleAllPosts(' . (int)$thread['threadid'] . ', this.checked)" style="margin-right: 5px;"> ';
    echo 'Alle Posts ausw&auml;hlen';
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
            echo '<input type="checkbox" name="selected_posts[]" value="' . (int)$post['postid'] . '" data-thread="' . (int)$thread['threadid'] . '" onchange="updateDeleteButton()">';
            echo '<div style="flex: 1;">';
            echo '<div class="post-head">';
            echo '<small class="dim">Post ID: ' . (int)$post['postid'] . ' - ' . date("d.m.Y - H:i", (int)$post['posttime']) . '</small>';
            echo '<button type="button" class="btn-xs" onclick="editPost(' . (int)$post['postid'] . ')" style="margin-left: 10px;">Bearbeiten</button>';
            echo '</div>';

            $message = (string)$post['message'];
            // Diskussionsthread-Links entfernen für Vorschau
            $message = preg_replace('/Diskussionsthread:\s*\[URL\].*?\[\/URL\]/i', '', $message);
            $message = preg_replace('/Diskussionsthread:\s*\[url=.*?\].*?\[\/url\]/i', '', $message);

            echo '<div id="post-content-' . (int)$post['postid'] . '" style="margin: 8px 0;">' . nl2br(htmlspecialchars($message), false) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="dim" style="margin-top: 10px;">Keine Posts in diesem Thread.</div>';
    }

    echo '</div>';
}

echo '</div>';

include_once "inc.layout.bottom.php";
