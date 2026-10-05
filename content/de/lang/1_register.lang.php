<?php
//Textfassung der Registrierungsmail (AltBody)
$newreg_lang['regmailbody']='Hallo {SPIELER},

willkommen bei Die Ewigen! Deine Anmeldung hat geklappt. Mit diesen Daten loggst Du Dich ein:

Login (Deine E-Mail-Adresse): {LOGIN}
Passwort: {PASS}

Zur Loginseite: {LOGINURL}

So geht es weiter:
1. Logge Dich auf der Loginseite mit Deiner E-Mail-Adresse und dem Passwort ein.
2. Nach dem Login siehst Du die Spielserver. Klicke bei einem Server auf „Anmeldung“. Server mit dem Hinweis „Für neue Spieler!“ eignen sich besonders für den Einstieg.
3. Dein Spielername ist schon eingetragen. Wähle Deine Rasse und klicke auf „Account anlegen“. Für den Einstieg empfehlen wir die Ewigen.
4. Nach etwa 2-3 Minuten ist Dein Spielkonto eingerichtet. Dann klickst Du beim Server auf „Spielen“.

Dein Passwort wurde automatisch erzeugt. Nach dem Login kannst Du es unter „Account“ > „Passwort ändern“ durch ein eigenes ersetzen.

Bitte logge Dich innerhalb von 7 Tagen ein. Accounts, mit denen sich bis dahin niemand eingeloggt hat, werden automatisch gelöscht.

Solltest Du dich nicht bei Die Ewigen angemeldet haben, so ignoriere diese E-Mail einfach, vermutlich hat jemand versehentlich Deine E-Mail-Adresse eingegeben.

Impressum:
Tino Tauchmann
Eckstrasse 32
66440 Blieskastel
Deutschland

E-Mail: issomad@die-ewigen.com
Telefon: 03212 - 1046989 (kein Support)
Gerichtsstand: Amtsgericht Homburg
';

//HTML-Fassung der Registrierungsmail, der Rahmen kommt aus register.inc.php
$newreg_lang['regmailbody_html']='<h1 style="font-size: 24px; margin: 0 0 15px 0;">Willkommen bei Die Ewigen!</h1>
<p>Hallo {SPIELER},</p>
<p>Deine Anmeldung hat geklappt. Mit diesen Daten loggst Du Dich ein:</p>
<table cellspacing="0" cellpadding="4" border="0">
<tr><td style="color: #FFFFFF;">Login (Deine E-Mail-Adresse):</td><td style="color: #FFFFFF;"><b>{LOGIN}</b></td></tr>
<tr><td style="color: #FFFFFF;">Passwort:</td><td style="color: #FFFFFF;"><b>{PASS}</b></td></tr>
</table>
<p>Zur Loginseite: <a href="{LOGINURL}" style="color: #f8ae56;"><b>{LOGINURL}</b></a></p>
<h2 style="font-size: 18px; margin: 25px 0 10px 0;">So geht es weiter</h2>
<ol style="padding-left: 20px;">
<li>Logge Dich auf der Loginseite mit Deiner E-Mail-Adresse und dem Passwort ein.</li>
<li>Nach dem Login siehst Du die Spielserver. Klicke bei einem Server auf „Anmeldung“. Server mit dem Hinweis „Für neue Spieler!“ eignen sich besonders für den Einstieg.</li>
<li>Dein Spielername ist schon eingetragen. Wähle Deine Rasse und klicke auf „Account anlegen“. Für den Einstieg empfehlen wir die Ewigen.</li>
<li>Nach etwa 2-3 Minuten ist Dein Spielkonto eingerichtet. Dann klickst Du beim Server auf „Spielen“.</li>
</ol>
<p>Dein Passwort wurde automatisch erzeugt. Nach dem Login kannst Du es unter „Account“ &gt; „Passwort ändern“ durch ein eigenes ersetzen.</p>
<p>Bitte logge Dich innerhalb von 7 Tagen ein. Accounts, mit denen sich bis dahin niemand eingeloggt hat, werden automatisch gelöscht.</p>
<p>Solltest Du dich nicht bei Die Ewigen angemeldet haben, so ignoriere diese E-Mail einfach, vermutlich hat jemand versehentlich Deine E-Mail-Adresse eingegeben.</p>
<p style="font-size: 12px;">Impressum:<br>
Tino Tauchmann<br>
Eckstrasse 32<br>
66440 Blieskastel<br>
Deutschland<br>
<br>
E-Mail: issomad@die-ewigen.com<br>
Telefon: 03212 - 1046989 (kein Support)<br>
Gerichtsstand: Amtsgericht Homburg</p>
';

$newreg_lang['laenderliste']='<option>Ägypten</option>
<option>Argentinien</option>
<option>Australien</option>
<option>Bangladesh</option>
<option>Belgien</option>
<option>Belgien</option>
<option>Belgien</option>
<option>Bolivien</option>
<option>Brasilien</option>
<option>Bulgarien</option>
<option>Chile</option>
<option>China</option>
<option>Dänemark</option>
<option>Deutschland</option>
<option>Ekuador</option>
<option>Estland</option>
<option>Finnland</option>
<option>Frankreich</option>
<option>Griechenland</option>
<option>Großbritannien</option>
<option>Indien</option>
<option>Indonesien</option>
<option>Irland</option>
<option>Israel</option>
<option>Italien</option>
<option>Japan</option>
<option>Kanada</option>
<option>Kolumbien</option>
<option>Korea</option>
<option>Kroatien</option>
<option>Litauen</option>
<option>Luxemburg</option>
<option>Malaysia</option>
<option>Mexiko</option>
<option>Neuseeland</option>
<option>Niederlande</option>
<option>Norwegen</option>
<option>Österreich</option>
<option>Pakistan</option>
<option>Paraguay</option>
<option>Peru</option>
<option>Philippinen</option>
<option>Polen</option>
<option>Portugal</option>
<option>Rumänien</option>
<option>Russland</option>
<option>Schweden</option>
<option>Schweiz</option>
<option>Singapur</option>
<option>Slowakien</option>
<option>Slowenien</option>
<option>Spanien</option>
<option>Sri Lanka</option>
<option>Südafrika</option>
<option>Taiwan</option>
<option>Thailand</option>
<option>Tschechische Republik</option>
<option>Türkei</option>
<option>Ungarn</option>
<option>Uruguay</option>
<option>USA</option>
<option>Venezuela</option>
<option>Vietnam</option>
<option>Zypern</option>
';

$newreg_lang['fehlermsg1']='Im Spielernamen d&uuml;rfen keine Sonderzeichen sein (Ausnahmen sind nur: _-=).';
$newreg_lang['fehlermsg2']='Der Spielername wird bereits verwendet.';
$newreg_lang['fehlermsg3']='Im Loginnamen d&uuml;rfen keine Sonderzeichen sein (Ausnahmen sind nur: _-=).';
$newreg_lang['fehlermsg4']='Der Loginname wird bereits verwendet.';
$newreg_lang['fehlermsg5']='Loginname und Spielername d&uuml;rfen nicht gleich sein.';
$newreg_lang['fehlermsg6']='Die eingegebene E-Mail-Adresse ist nicht g&uuml;ltig.';
$newreg_lang['fehlermsg7']='Die eingegebene E-Mail-Adresse wird bereits verwendet.';
$newreg_lang['fehlermsg8']='Die eingegebenen E-Mail-Adressen stimmen nicht &uuml;berein.';
$newreg_lang['fehlermsg9']='Im Vornamen d&uuml;rfen keine Sonderzeichen sein.';
$newreg_lang['fehlermsg10']='Im Nachnamen d&uuml;rfen keine Sonderzeichen sein.';
$newreg_lang['fehlermsg11']='Im Strassenanmen d&uuml;rfen keine Sonderzeichen sein.';
$newreg_lang['fehlermsg12']='Die Postleitzahl darf nur Zahlen enthalten.';
$newreg_lang['fehlermsg13']='Im Ortsnamen d&uuml;rfen keine Sonderzeichen sein.';
$newreg_lang['fehlermsg14']='Es wurde kein Land ausgew&auml;hlt.';
$newreg_lang['fehlermsg15']='Die Telefonnummer ist nicht korrekt.';
$newreg_lang['fehlermsg16']='Das Geburtsdatum ist nicht korrekt.';
$newreg_lang['fehlermsg17']='Es wurde kein Geschlecht ausgew&auml;hlt.';
$newreg_lang['fehlermsg18']='Fehler bei der Rassenauswahl.';
$newreg_lang['fehlermsg19']='Bitte akzeptiere die Nutzungsbedingungen/Spielregeln.';
$newreg_lang['fehlermsg20']='Es wurden nicht alle notwendigen Felder ausgef&uuml;llt.';


$newreg_lang['accountregistrierung']='Registrierung';
$newreg_lang['bittewaehlen']='Bitte w&auml;hlen';
$newreg_lang['jahr']='Jahr';

$newreg_lang['maennlich']='m&auml;nnlich';
$newreg_lang['monat']='Monat';
$newreg_lang['neuenaccountanlegen']='Neuen Account anlegen';
$newreg_lang['regmailbetreff']='Willkommen bei Die Ewigen: Deine Zugangsdaten';

$newreg_lang['servervoll']='Das Userlimit f&uuml;r den Server wurde erreicht. Es werden jedoch inaktive User gel&ouml;scht und so k&ouml;nnen wieder freie Pl&auml;tze entstehen. Versuchen Sie es bitte daher sp&auml;ter nocheinmal.';
$newreg_lang['spaet1']='Da die aktuelle Runde bereits seit ';
$newreg_lang['spaet2']=' Wirtschaftsticks l&auml;uft erhalten Sie ';
$newreg_lang['spaet3']=' Multiplex und ';
$newreg_lang['spaet4']=' Dyharra als Sp&auml;teinsteigerbonus.';
$newreg_lang['tag']='Tag';
$newreg_lang['weiblich']='weiblich';


$newreg_lang['spielername']='Spielername';
$newreg_lang['spielername1']='(Der Name wird im Spiel angezeigt)';
$newreg_lang['loginname']='Loginname';
$newreg_lang['loginname1']='(Darf nicht der Spielername sein)';
$newreg_lang['email']='E-Mail';
$newreg_lang['email1']='E-Mail best&auml;tigen';
$newreg_lang['vorname']='Vorname';
$newreg_lang['nachname']='Nachname';
$newreg_lang['strasse']='Stra&szlig;e / Hausnummer';
$newreg_lang['plz']='Postleitzahl';
$newreg_lang['ort']='Ort';
$newreg_lang['land']='Land';
$newreg_lang['optional']='optional';
$newreg_lang['telefonnummer']='Telefonnummer';
$newreg_lang['geburtsdatum']='Geburtsdatum';
$newreg_lang['geschlecht']='Geschlecht';
$newreg_lang['auswaehlen']='Ausw&auml;hlen';
$newreg_lang['rasse']='Rasse';
$newreg_lang['agb1']='Mit der Anmeldung akzeptiere ich die';
$newreg_lang['agb2']='Nutzungsbedingungen';
$newreg_lang['agb3']='Stolen Empires AGB (Spielregeln)';
$newreg_lang['agb4']='Alusania AGB (Spielregeln)';
$newreg_lang['agb5']='Ablyon AGB (Spielregeln)';
$newreg_lang['agb6']='Andalur AGB (Spielregeln)';
$newreg_lang['registrieren']='Registrieren';
$newreg_lang['hinweis']='Mehrfachanmeldungen sind nicht erlaubt, pro Person ist nur ein Account zul&auml;ssig.';
$newreg_lang['werberid']='Werber-ID';
$newreg_lang['newsletter_accept']='Ich stimme zu, dass mir der Betreiber E-Mails mit projektbezogenen Informationen zusendet.';
?>
