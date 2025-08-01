<?php
include 'content/de/lang/'.$ums_language.'_account.lang.php';
$errmsg = '';

echo '<div style="width: 100%;">';

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
                $errmsg .= '<font color="FF0000">'.$account_lang['msg_1'].'</font>';
            }

        } else {
            $errmsg .= '<font color="FF0000">'.$account_lang['msg_2'].'</font>';
        }
    } else {
        $errmsg .= '<font color="FF0000">'.$account_lang['msg_3'].'</font>';
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
                $errmsg .= '<font color="00FF00">'.$account_lang['msg_7'].'</font>';
            } else {
                $errmsg .= '<font color="FF0000">'.$account_lang['msg_4'].': '.$minpwchars.').</font>';
            }
        } else {
            $errmsg .= '<font color="FF0000">'.$account_lang['msg_5'].'</font>';
        }
    } else {
        $errmsg .= '<font color="FF0000">'.$account_lang['msg_6'].'</font>';
    }
}

echo $errmsg;

echo '<form action="index.php?command=account" method="POST">';
echo '<table border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="2" align="center"><b>'.$account_lang['accountdaten'].'</b></td>
      </tr>
      <tr>
        <td width="50%">'.$account_lang['spielername'].':</td>
        <td width="50%">'.$row["spielername"].'</td>
      </tr>
      <tr>
        <td>'.$account_lang['accountid'].':</td>
        <td>ID'.$_SESSION['ums_user_id'].'</td>
      </tr>
      <tr>
        <td>E-Mail:</td>
        <td>'.$row['reg_mail'].'</td>
      </tr>';

echo '</table><br>';

echo '</table>';

echo '</form>';


echo '<form action="index.php?command=account" method="POST">';

echo '<table border="0" cellpadding="3" cellspacing="0">';
echo '<tr>
        <td colspan="2">&nbsp;</td>
      </tr>

      <tr>
        <td colspan="2" align="center"><b>'.$account_lang['passwortaendern'].'</b></td>
      </tr>
      <tr>
        <td width="300">'.$account_lang['altespasswort'].':</td>
        <td width="350"><input type="password" name="oldpass" value=""></td>
      </tr>
      <tr>
        <td>'.$account_lang['neuespasswort'].':</td>
        <td><input type="password" name="pass1" value=""></td>
      </tr>
      <tr>
        <td>'.$account_lang['neuespasswortwiederholen'].':</td>
        <td><input type="password" name="pass2" value=""></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><input type="Submit" name="newpass" value="'.$account_lang['passwortaendern'].'"></td>
      </tr>
      ';

echo '</table>';

echo '</form>';
echo '<form action="index.php?command=account" method="POST">';

echo '<table border="0" cellpadding="3" cellspacing="0">';
echo '<tr>
        <td colspan="2">&nbsp;</td>
      </tr>

      <tr>
        <td colspan="2" align="center"><b>'.$account_lang['accountloeschen'].'</b></td>
      </tr>
      <tr>
        <td colspan="2" align="center">'.$account_lang['msg_8'].'</td>
      </tr>

      <tr>
        <td width="50%">'.$account_lang['passwort'].':</td>
        <td width="50%"><input type="password" name="delpass" value=""></td>
      </tr>
      <tr>
        <td><input name="delcheck1" type="checkbox" value="1">'.$account_lang['bestaetigung'].' 1</td>
        <td><input name="delcheck2" type="checkbox" value="1">'.$account_lang['bestaetigung'].' 2</td>
      </tr>
      <tr>
        <td colspan="2" align="center"><input type="Submit" name="delbutton" value="'.$account_lang['accountloeschen'].'"></td>
      </tr>
      ';

echo '</table>';
echo '</form></div>';

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

<script>
$('div, img, a').tooltip({ 
    track: true, 
    delay: 0, 
    showURL: false, 
    showBody: "&",
    extraClass: "design1", 
    fixPNG: true,
    opacity: 0.15,
    left: 0
});
</script>