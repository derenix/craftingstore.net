<?php
defined('_LOGIN') or die("Security block!");


echo '<div style="width:400px;margin:auto;">';

#region Tabellenoptimierung
echo '<p>
<a href="?p=' . $_GET['p'] . '&amp;optimize=1">Tabellen optimieren</a><br/>
Führt auf alle Tabellen der Datenbank den optimize-Befehl aus.';
if (isset($_GET['optimize'])) {
	$optimize = '';
	$tables = $db->query("SHOW TABLES", array());

	$key = "Tables_in_" . SQL_DB;
	foreach ($tables as $table) {
		if ($optimize) {
			$optimize .= ',' . $table->{$key};
		} else {
			$optimize = $table->{$key};
		}
	}
	$optimize = 'optimize tables ' . $optimize;

	echo '<table>
	<tr>
		<th>Table</th>
		<th>Op</th>
		<th>Msg_type</th>
		<th>Msg_text</th>
	</tr>';
	$i = 0;
	$optimizeResult = $db->query($optimize, array());
	foreach ($optimizeResult as $row) {
		echo '
	<tr style="background-color:#' . ($i ? 'eef' : 'efe') . '">
		<td>' . $row->Table . '</td>
		<td>' . $row->Op . '</td>
		<td>' . $row->Msg_type . '</td>
		<td>' . $row->Msg_text . '</td>
	</tr>';
		$i++;
		$i = $i % 2;
	}

	echo '</table>
</p>';
}
#endregion

#region Unnötige Item-Bilder löschen
echo '
<p><a href="?p=' . $_GET['p'] . '&amp;cleanup=1">Bilder bereinigen</a><br />
Löscht alle Item-Bilder, die nicht mehr mit einem Item in der Datenbank verknüpft sind.';
if (isset($_GET['cleanup'])) {
	$folders = array($_SERVER['DOCUMENT_ROOT'] . '/images/items/', $_SERVER['DOCUMENT_ROOT'] . '/images/items/preview/');

	$removedImages = '';
	$images = array();
	$results = $db->query("SELECT DISTINCT image FROM mc_products");
	foreach ($results as $row) {
		$images[] = $row->image;
	}
	if (count($images) > 0) //nur falls Bilder von der Datenbank zurückgegeben wurden
	{
		foreach ($folders as $folder) {
			if ($handle = opendir($folder)) {
				while (false !== ($file = readdir($handle))) {
					if (!is_dir($file) && $file != "." && $file != ".." && !in_array($file, $images) && isNumber(substr($file, 0, 28))) {
						$removedImages .= '<br/>' . $folder . $file;
						unlink($folder . $file);
					}
				}
				closedir($handle);
			}
		}
	}
}

if (isset($removedImages) && strlen($removedImages) > 0)
	echo '<br />Folgende Bilder wurden gelöscht:' . $removedImages;
elseif (isset($_GET['cleanup']))
	echo '<br/>Es wurden keine zu löschenden Bilder gefunden.';

echo '</p>';
#endregion

echo '</div>';