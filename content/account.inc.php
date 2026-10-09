<?php
include 'content/de/lang/'.$ums_language.'_account.lang.php';
$errmsg = '';

$db_daten = mysqli_execute_query(
    $GLOBALS['dbi'],
    "SELECT * FROM ls_user WHERE user_id = ?",
    [$_SESSION['ums_user_id']]
);
$row = mysqli_fetch_array($db_daten, MYSQLI_ASSOC);

//account löschen
if (isset($_POST['delpass']) || isset($_POST['delcheck1']) || isset($_POST['delcheck2']) || isset($_POST['delbutton'])) {
    $delpass = trim($_POST['delpass']);

	//Passwort überprüfen
	$passwordOK=false;
	if(password_verify($delpass, $row['pass']) || password_verify($delpass, $row['newpass'])){
		$passwordOK=true;
	}

    if ($passwordOK) { //das passwort ist korrekt

        //löschen
        if ($_POST['delcheck1'] == "1" and $_POST['delcheck2'] == "1") {
            //überprüfen, ob er noch einen aktiven account hat
            $anzacc = 0;
            for ($i = 0;$i <= $sindex;$i++) {
                $result = doPost($serverdata[$i][6].'rpc.php', 'authcode='.$GLOBALS['env_rpc_authcode'].'&isaccount=1&id='.intval($_SESSION['ums_user_id']), $serverdata[$i][5]);
                $anzacc = $anzacc + $result;
            }
            //wenn er keinen aktiven spielaccount hat, dann den hauptaccount löschen
            if ($anzacc == 0) {
                //account löschen
                mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "DELETE FROM ls_user WHERE user_id = ?",
                    [$_SESSION['ums_user_id']]
                );

                session_destroy();
                echo '
				<script>
				window.location.href = "index.php";
				</script>';
				exit;
            } else {
                $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_1'].'</div>';
            }

        } else {
            $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_2'].'</div>';
        }
    } else {
        $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_3'].'</div>';
    }
}

//neues passwort setzen
if (isset($_POST['oldpass']) || isset($_POST['newpass']) || isset($_POST['pass1']) || isset($_POST['pass2'])) {
    $oldpass = trim($_POST['oldpass']);
    $pass1 = trim($_POST['pass1']);
    $pass2 = trim($_POST['pass2']);

    $passwordOK = false;
    if (password_verify(trim($oldpass), $row['pass'])) {
        $passwordOK = true;
    }

    if ($passwordOK) { //oldpass ist korrekt
        $pass1 = trim($pass1);
        $pass2 = trim($pass2);
        if ($pass1 == $pass2) {
            $minpwchars = 6;
            if (strlen($pass1) > $minpwchars - 1) {
                $pass1_crypt = password_hash($pass1, PASSWORD_DEFAULT);
                mysqli_execute_query(
                    $GLOBALS['dbi'],
                    "UPDATE ls_user SET pass = ?, newpass='' WHERE user_id = ?",
                    [$pass1_crypt, $_SESSION['ums_user_id']]
                );
                $errmsg .= '<div class="hinweis hinweis-erfolg">'.$account_lang['msg_7'].'</div>';
            } else {
                $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_4'].': '.$minpwchars.').</div>';
            }
        } else {
            $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_5'].'</div>';
        }
    } else {
        $errmsg .= '<div class="hinweis hinweis-fehler">'.$account_lang['msg_6'].'</div>';
    }
}

echo $errmsg;

//Accountdaten
echo '<h1>'.$account_lang['accountdaten'].'</h1>';
echo '<dl class="daten">
	<dt>'.$account_lang['spielername'].'</dt>
	<dd>'.$row["spielername"].'</dd>
	<dt>'.$account_lang['accountid'].'</dt>
	<dd>ID'.$_SESSION['ums_user_id'].'</dd>
	<dt>E-Mail</dt>
	<dd>'.$row['reg_mail'].'</dd>
</dl>';

//Passwort ändern
echo '<h2>'.$account_lang['passwortaendern'].'</h2>';
echo '<form class="formular" action="index.php?command=account" method="POST">';
echo '<div class="feld">
	<label for="oldpass">'.$account_lang['altespasswort'].'</label>
	<input type="password" name="oldpass" id="oldpass" value="" autocomplete="current-password">
</div>
<div class="feld">
	<label for="pass1">'.$account_lang['neuespasswort'].'</label>
	<input type="password" name="pass1" id="pass1" value="" autocomplete="new-password">
</div>
<div class="feld">
	<label for="pass2">'.$account_lang['neuespasswortwiederholen'].'</label>
	<input type="password" name="pass2" id="pass2" value="" autocomplete="new-password">
</div>
<div class="formular-aktionen">
	<input class="knopf knopf-primaer" type="Submit" name="newpass" value="'.$account_lang['passwortaendern'].'">
</div>';
echo '</form>';

//Account löschen
echo '<h2>'.$account_lang['accountloeschen'].'</h2>';
echo '<p>'.$account_lang['msg_8'].'</p>';
echo '<form class="formular" action="index.php?command=account" method="POST">';
echo '<div class="feld">
	<label for="delpass">'.$account_lang['passwort'].'</label>
	<input type="password" name="delpass" id="delpass" value="" autocomplete="current-password">
</div>
<div class="feld-check">
	<input name="delcheck1" id="delcheck1" type="checkbox" value="1">
	<label for="delcheck1">'.$account_lang['bestaetigung'].' 1</label>
</div>
<div class="feld-check">
	<input name="delcheck2" id="delcheck2" type="checkbox" value="1">
	<label for="delcheck2">'.$account_lang['bestaetigung'].' 2</label>
</div>
<div class="formular-aktionen">
	<input class="knopf knopf-gefahr" type="Submit" name="delbutton" value="'.$account_lang['accountloeschen'].'">
</div>';
echo '</form>';

function rahmen_oben($text)
{
    echo '<table border="0" rahmen0padding="0" rahmen0spacing="0" width="100%;">
        <tr>
          <td align="center" class="rahmen0" style="font-weight: bold;">'.$text.'</td>
        </tr>
        <tr>
        <td>';
}

function rahmen_unten()
{
    echo '</td>
        </tr>
        </table><br>';
}
?>
