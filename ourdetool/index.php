<?php
include_once "det_userdata.inc.php";

$page_title = 'Übersicht';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";
?>
<?php if (isset($_GET['notfound'])): ?>
<div class="flash flash-warn">Kein User gefunden<?= ($_GET['q'] ?? '') !== '' ? ' für „' . htmlspecialchars((string)$_GET['q']) . '“' : '' ?>.</div>
<?php endif; ?>

<div class="card">
  <h2>Usersuche</h2>
  <form action="de_user_search.php" method="get" class="usersearch">
    <input type="text" name="sstr" autofocus placeholder="+ID &nbsp;*Spielername &nbsp;%Mail &nbsp;?Wildcard">
    <button type="submit">Suchen</button>
  </form>
  <p class="dim">Präfixe: <code>+</code> ID &middot; <code>*</code> Spielername &middot; <code>%</code> Mail &middot; <code>?[-*%~|ö]</code> Wildcard (<code>-</code> Nic, <code>~</code> IP, <code>|</code> Vor-/Nachname, <code>ö</code> Ort)</p>
</div>

<div class="card">
  <h2>Angemeldet</h2>
  <p>Hallo <?= htmlspecialchars($det_username) ?> &ndash; <?= htmlspecialchars($det_email) ?> (Level <?= (int)$det_userlevel ?>)</p>
</div>

<div class="cards">
<?php foreach ($nav_groups as $dash_group => $dash_items): ?>
  <div class="card">
    <h2><?= $dash_group ?></h2>
<?php foreach ($dash_items as $dash_item): ?>
    <a href="<?= $dash_item[0] ?>"><?= $dash_item[1] ?></a><br>
<?php endforeach; ?>
  </div>
<?php endforeach; ?>
</div>

<?php include_once "inc.layout.bottom.php"; ?>
