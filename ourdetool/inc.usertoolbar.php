<?php
/**
 * User-Kontext-Toolbar für alle Detailseiten eines Users
 * (Ersatz für das frühere idinfo.php-Frameset mit Suchleiste).
 *
 * Erwartet: $uid (int) – die betrachtete User-ID; $GLOBALS['dbi'] offen.
 * Optional: $active_usertab – Tab-Schlüssel (Default: Script-Basename).
 * Einbinden direkt nach inc.layout.top.php.
 */
$uid = (int)($uid ?? 0);
$user_tabs = [
    'info'              => ['info.php', 'Info'],
    'de_user_ips'       => ['de_user_ips.php', 'IPs'],
    'de_user_logviewer' => ['de_user_logviewer.php', 'Logviewer'],
];
$active_usertab = $active_usertab ?? basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
?>
<div class="usertoolbar">
  <form action="de_user_search.php" method="get" class="usersearch">
    <input type="text" name="sstr" placeholder="+ID &nbsp;*Spielername &nbsp;%Mail &nbsp;?Wildcard"
           title="+ ID, * Spielername, % Mail, ?[-*$~|ö] Wildcard">
    <button type="submit">Suchen</button>
  </form>
<?php if ($uid > 0): ?>
<?php
    $utb_name = '';
    $utb_login = '';
    $utb_status = null;
    if (isset($GLOBALS['dbi'])) {
        $utb_res = mysqli_execute_query(
            $GLOBALS['dbi'],
            "SELECT loginname, spielername, acc_status FROM ls_user WHERE user_id=?",
            [$uid]
        );
        if ($utb_res && ($utb_row = mysqli_fetch_array($utb_res))) {
            $utb_login = (string)$utb_row['loginname'];
            $utb_name = (string)$utb_row['spielername'];
            $utb_status = $utb_row['acc_status'];
        }
    }
    $utb_badge = '';
    if ($utb_status !== null) {
        $utb_badge = match ((int)$utb_status) {
            0 => '<span class="badge">inaktiv</span>',
            1 => '<span class="badge badge-ok">aktiv</span>',
            2 => '<span class="badge badge-danger">gesperrt</span>',
            3 => '<span class="badge badge-warn">Urlaub</span>',
            default => '<span class="badge">Status ' . (int)$utb_status . '</span>',
        };
    }
?>
  <div class="userident">
    <strong>UID <?= $uid ?></strong>
    <?php if ($utb_login !== ''): ?>&middot; <?= htmlspecialchars($utb_login) ?><?php endif; ?>
    <?php if ($utb_name !== '' && $utb_name !== $utb_login): ?><span class="dim">(<?= htmlspecialchars($utb_name) ?>)</span><?php endif; ?>
    <?= $utb_badge ?>
  </div>
  <nav class="usertabs">
<?php foreach ($user_tabs as $utb_key => $utb_item): ?>
    <a href="<?= $utb_item[0] ?>?uid=<?= $uid ?>"<?= $utb_key === $active_usertab ? ' class="active"' : '' ?>><?= $utb_item[1] ?></a>
<?php endforeach; ?>
  </nav>
<?php endif; ?>
</div>
