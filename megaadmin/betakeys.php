<?php

function sendMail(MySqlDatabase $db, $mail)
{
	try {
		$res = $db->update("UPDATE betamails SET invited='1' WHERE mail= :mail LIMIT 1", array("mail" => $mail));

		if ($res === 1) {
			$mailSent = mail('', 'Minecraftshop/Craftingstore.net - Beta starting',
				'<html>
				<head>
				<title>Craftingstore.net - beta starting</title>
				<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
				</head>
				<body>
				<h3>Hello!</h3>
				<p>After two years of development we now start the official public beta!<br />
				You have subscribed for information when it will start. So here it is!</p>
				<p>If you are a user, please inform your minecraft server administrator about this shop.<br />Also you can <a href="https://secure.craftingstore.net?show=Register">register here</a></p>
				<p>As a server administrator you can now register <a href="https://secure.craftingstore.net?show=RegisterAdmin">here</a> and set up your own shop.</p>
				<p>For help visit the <a href="http://support.craftingstore.net/">support page</a>.</p>
				<p>If you want to learn about, what we changed or what it is all about, you can read the about the project in <a href="http://craftingstore.net/public-beta/">our blog</a></p>
				<p><strong>Enjoy!</strong></p>
				<br /><hr><br />
				<h3>Hallo!</h3>
				<p>Nach zwei Jahren der Entwicklung startet nun die ofizielle Beta!<br />
				Du hast Dich damals angemeldet, über die Beta informiert zu werden, und hier ist sie!</p>
				<p>Wenn Du ein User bist, informiere bitte Deinen Minecraft Server Administrator über diesen Shop. Außerdem kannst Du Dich jetzt <a href="https://secure.craftingstore.net?show=Register">hier registrieren</a></p>
				<p>Als Server Administrator kannst Du Dich <a href="https://secure.craftingstore.net?show=LoginServer">hier registrieren</a> und Deinen eigenen Shop einrichten.</p>
				<p>Wenn Du Hilfe benötigst, besuche bitte unsere <a href="http://support.craftingstore.net/">Support-Seite</a>.</p>
				<p><strong>Viel Spaß!</strong></p>
				</body>
				</html>',
				"MIME-Version: 1.0\n"
				. "To: {$mail}\n"
				. "From: Craftingstore.net <beta@" . BASE_DOMAIN . ">\n"
				. "Content-type: text/html; charset=utf-8\n");

			if ($mailSent) {
				return 'OK: ' . $mail;
			} else {
				$db->update("UPDATE betamails SET invited='2' WHERE mail= :mail LIMIT 1", array("mail" => $mail));

				return 'FEHLER: ' . $mail;
			}
		}
	} catch (Exception $e) {
		return 'FEHLER: ' . $mail . ', ' . $e;
	}
}

$anzahlMails = isset($_POST['anzahlMails']) ? $_POST['anzahlMails'] : 0;

echo '
<form action="?p=' . $_GET['p'] . '" method="post">
<h3>Betamails senden</h3>
Anzahl der Mails, die gesendet werden sollen<br/>
<input type="text" name="anzahlMails" value="' . htmlspecialchars($anzahlMails) . '" /><br/>
<input type="submit" />
</form>';

if (preg_match('/^([0]*)([1-9]{1}\d*)$/', $anzahlMails)) {
	$mails = $db->query("SELECT mail FROM betamails WHERE invited='0' ORDER BY id DESC LIMIT :amountMails", array("amountMails" => (int) $anzahlMails));

	foreach ($mails as $row) {
		echo '<br />' . sendMail($db, $row->mail);
	}
}