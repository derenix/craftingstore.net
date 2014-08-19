<?php
defined('_LOGIN') or die("Security block!");

?>
	<section class="content-header">
		<h1>
			Übersetzungen
		</h1>
		<ol class="breadcrumb">
			<li><a href="index.php?p=1"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Übersetzungen</li>
		</ol>
	</section>
	<section class="content">
<?php
$language = isset($_POST['selectLang']) ? $_POST['selectLang'] : 1;

#region Sprache auswählen
if ($db->fetchOne("SELECT COUNT(*) FROM mc_languages WHERE Id='" . htmlspecialchars($language) . "'") == 1) {
	$_SESSION['currentLang'] = $language;
}

$result = $db->query("SELECT Id,Language FROM mc_languages");

echo '
<div class="row">
<form method="post" action="?p=' . $_GET['p'] . '">
<div class="col-lg-4">
<select class="form-control" name="selectLang">';
foreach ($result as $row) {
	echo '<option value="'.$row->Id.'"'.($_SESSION['currentLang'] == $row->Id?' selected="selected"':'').'>'.htmlspecialchars($row->Language).'</option>';
}
echo '</select></div>
<input type="submit" class="btn btn-primary" value="Wählen" />
</form></div>';
#end


if ($_SESSION['currentLang'] > 0) {


#region Seitenzahlen
	$zeilenProSeite = 50;
	$seiten = ceil(($countOrigin = $db->fetchOne("SELECT COUNT(*) FROM mc_translations WHERE LanguagesId='1'")) / $zeilenProSeite);
	$seite = 0;
	if (isset($_GET['s']) && isNumber($_GET['s'], 1) && $_GET['s'] < $seiten) {
		$seite = $_GET['s'];
	}

	#region Prozentsatz ermitteln
//Gesamtzahl Einträge der aktuellen Sprache
	$translationsDone = $db->fetchOne("SELECT COUNT(*) FROM mc_translations WHERE LanguagesId= :languageId AND translation <> ''", array("languageId" => $_SESSION['currentLang']));
	echo '<div>Übersetzung abgeschlossen zu <strong>' . floor($translationsDone / $countOrigin * 100) . '%.</strong></div>';
#endregion

	echo '<ul class="pagination">';
	for ($i = 0; $i < $seiten; $i++) {
		if ($seite == $i)
			echo '<li><a href="?p=' . $_GET['p'] . '&amp;s=' . $i . '"><b>' . ($i + 1) . '</b></a></li>';
		else
			echo '<li><a href="?p=' . $_GET['p'] . '&amp;s=' . $i . '">' . ($i + 1) . '</a></li>';
	}
	echo '</ul>';

	$limit = ($seite * $zeilenProSeite) . ',' . $zeilenProSeite;
#end

	if (isset($_POST['translation'])) {
		$result = $db->query("
SELECT
	org.Label,
	org.Translation as org,
	trans.Translation AS translation

FROM mc_translations AS org
LEFT JOIN mc_translations AS trans
ON trans.Label=org.Label AND trans.LanguagesId='{$_SESSION['currentLang']}'
WHERE org.LanguagesId='1' LIMIT " . $limit);

#region Speichern
		foreach ($result as $row) {
			$oldBb = ($_POST['oldBb'][$row['Label']] ? true : false);
			$newBb = ($_POST['newBb'][$row['Label']] ? true : false);

			if ($_POST['translation'][$row['Label']] && ($_POST['translation'][$row['Label']] != $_POST['original'][$row['Label']]) || ($oldBb != $newBb)) {
				$translation = mysql_real_escape_string($_POST['translation'][$row['Label']]);
				$db->query("INSERT INTO mc_translations (Label,LanguagesId,Translation,parseBBCode) VALUES ('{$row['Label']}','{$_SESSION['currentLang']}','$translation','$bbcode')
			ON DUPLICATE KEY UPDATE Translation='$translation',parseBBCode='$newBb'");
			}
		}
	}

#end

	$page = isset($_GET['s']) ? $_GET['s'] : 1;

	$translations = $db->query("
SELECT
	org.Label,
	org.Translation AS org,
	trans.Translation AS translation,
	trans.parseBBCode AS oldBb

FROM mc_translations AS org
LEFT JOIN mc_translations AS trans
ON trans.Label=org.Label AND trans.LanguagesId='{$_SESSION['currentLang']}'
WHERE org.LanguagesId='1' LIMIT " . $limit);

	echo '<br>
<form method="post" action="?p=' . $_GET['p'] . '&amp;s=' . $page . '">
<table class="table table-striped table-hover">
<tr>
	<th>Label</th>
	<th>Original-Text</th>
	<th >Übersetzung</th>
	<th>BBCode verwenden</th>
</tr>';

	foreach ($translations as $row) {
		if (strpos($row->org, "\r") !== false || strpos($row->org, "\n") !== false) {
			echo '
<tr'.($row->translation ? '' : ' class="empty"').'>
	<td>'.$row->Label.'</td>
	<td><textarea readonly>'.htmlspecialchars($row->org).'</textarea></td>
	<td>
		<input type="hidden" name="original['.$row->Label.']" value="'.htmlspecialchars($row->translation).'" />
		<textarea name="translation['.$row->Label.']">'.htmlspecialchars($row->translation).'</textarea>
	</td>
	<td>
		<input type="hidden" name="oldBb['.$row->Label.']" value="'.$row->oldBb.'" />
		<input type="checkbox" name="newBb['.$row->Label.']" value="1"'.($row->oldBb?' checked="checked"':'').' />
	</td>
</tr>';
		} else {
			echo '
<tr'.($row->translation ? '' : ' class="empty"').'>
	<td>'.$row->Label.'</td>
	<td><input type="text" readonly value="'.htmlspecialchars($row->org).'" /></td>
	<td>
		<input type="hidden" name="original['.$row->Label.']" value="'.htmlspecialchars($row->translation).'" />
		<input type="text" name="translation['.$row->Label.']" value="'.htmlspecialchars($row->translation).'" />
	</td>
	<td>
		<input type="hidden" name="oldBb['.$row->Label.']" value="'.$row->oldBb.'" />
		<input type="checkbox" name="newBb['.$row->Label.']" value="1"'.($row->oldBb?' checked="checked"':'').' />
	</td>
</tr>';
		}
	}
	echo '</table><br>
<input type="submit" class="btn btn-primary pull-right" value="Speichern" />
</form>';
#end
}