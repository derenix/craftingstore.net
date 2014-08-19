<?php
define("_MCSHOP", true);

$menu = array(
	0,
	array('Start', 'start.php'),
	array('Konto', 'kontoauszuege.php'),
	array('Auszahlungen', 'beantragteAuszahlungen.php'),
	array('Shop-<br />verwaltung', 'shopVerwaltung.php'),
	array('Kunden-<br />verwaltung', 'kundenVerwaltung.php'),
	array('Spieler-<br />verwaltung', 'spielerVerwaltung.php'),
	array('Beta', 'betakeys.php'),
	array('Übersetzung', 'translator.php')
);


require_once('../config/config.include.php');
require_once('../lib/general.functions.php');

require_once(DOC_ROOT . '/lib/class/mysqldatabase.class.php');
require_once(DOC_ROOT . '/lib/class/mysqlresultset.class.php');

session_start();

if(isset($_GET['logout'])) {
	unset($_SESSION['LoginDone']);
	header("Location: /megaadmin/");
}

$db = MySqlDatabase::getInstance();
if (!$db->isConnected()) {
	try {
		$db->connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB);
	} catch (Exception $e) {
		die($e->getMessage());
	}
}

if (!isset($_SESSION['LoginDone'])) { // changed for github
	if (isset($_POST['user']) && sha1($_POST['user']) == 'dea3e0200ce8afadd6cd4c00533b76c991be5e14' && sha1($_POST['pw']) == 'dea3e0200ce8afadd6cd4c00533b76c991be5e14') {
		$_SESSION['LoginDone'] = 1;

		header("Location: /megaadmin/?p={$_GET['p']}");
	} else {
		$output = -1;
	}
} else {
	define("_LOGIN", true);
	if (isNumber($_GET['p'])) {
		$output = $_GET['p'];
	} else {
		$output = 1;
	}
}

include("_header.html");

switch ($output) {
	case -1: //Login ist nicht erforderlich
		?>
		<div class="form-box" id="login-box">
			<div class="header bg-blue">Sign In</div>
			<form action="" method="post">
				<div class="body bg-gray">
					<div class="form-group">
						<input type="text" name="user" class="form-control" placeholder="User ID">
					</div>
					<div class="form-group">
						<input type="password" name="pw" class="form-control" placeholder="Password">
					</div>
				</div>
				<div class="footer">
					<button type="submit" class="btn bg-blue btn-block">Sign me in</button>
				</div>
			</form>
		</div>
		<?php
		break;
	case -2: //Login war erfolgreich, Link zum weiterleiten
		echo <<<html
Du wurdest erfolgreich eingeloggt.<br />
<a href="/megaadmin/?p={$_GET['p']}">weiter</a>
html;
		break;
	default:
		require_once($menu[$output][1]);
		break;
}

include("_footer.html");
?>