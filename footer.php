<?php
include 'content/de/lang/'.$_SESSION['ums_language'].'_footer.lang.php';

//Seitenrahmen schließen (geöffnet in content/index.inc.php)
echo '</main>';

echo '
<footer class="fuss">
	<div class="fuss-innen">
		<p>&copy; <a href="'.$GLOBALS['env_url_portal'].'" target="_blank">'.$footer_lang['dieewigen'].'</a></p>
		<ul class="fuss-links">
			<li><a href="'.$GLOBALS['env_url_impressum'].'" target="_blank">'.$footer_lang['impressum'].'</a></li>
			<li><a href="'.$GLOBALS['env_url_datenschutz'].'" target="_blank">Datenschutz</a></li>
		</ul>
	</div>
</footer>';
