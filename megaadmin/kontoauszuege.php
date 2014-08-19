<?php
defined('_LOGIN') or die("Security block!");

?>
	<section class="content-header">
		<h1>
			Konto
		</h1>
		<ol class="breadcrumb">
			<li><a href="index.php?p=1"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Konto</li>
		</ol>
	</section>
<section class="content">
<?php

#region Für Diese Woche noch extras berechen
$w = date('w'); #0 (für Sonntag) bis 6 (für Samstag)
$dEnde = ($w == 0 ? 0 : 7 - $w);
$dStart = -6;
#endregion

#region Zeiträume
$zeiten = array(
	'Heute' => array(
		'start' => mktime(0, 0, 0),
		'end' => mktime(24, 0, 0)),
	'Gestern' => array(
		'start' => mktime(-24, 0, 0),
		'end' => mktime(0, 0, 0)),
	'Diese Woche' => array(
		'start' => mktime(0, 0, 0, date('n'), date('j') + $dStart),
		'end' => mktime(24, 0, 0, date('n'), date('j') + $dEnde)),
	'Dieser Monat' => array(
		'start' => mktime(0, 0, 0, date('n'), 1),
		'end' => mktime(24, 0, 0, date('n'), date('t'))),
	'Gesamt' => array(
		'start' => 0,
		'end' => time() + 1,
	));
#endregion

#region Funktionen zum Daten ermitteln
function alsEuro($cents)
{
	return str_replace('.', ',', sprintf('%01.2f', $cents / 100)) . ' €';
}

function EigenerUmsatz($time, MySqlDatabase $db)
{
	// include the last day
	$time['end'] += (3600 * 24);

	return $db->fetchOne("SELECT Sum(Difference) FROM mc_ouraccount WHERE Time>= :start AND Time< :endTime", array("start" => $time['start'], "endTime" => $time['end']));
}

function EinzahlungenSpieler($time, MySqlDatabase $db)
{
	// include the last day
	$time['end'] += (3600 * 24);

	return $db->fetchOne("SELECT SUM(Revenue) FROM mc_gameraccounts WHERE Action='INPAYMENT' AND Time >= :start AND Time < :endTime", array("start" => $time['start'], "endTime" => $time['end']));
}

function AusgabenSpieler($time, MySqlDatabase $db)
{
	// include the last day
	$time['end'] += (3600 * 24);

	return $db->fetchOne("SELECT SUM(Revenue) FROM mc_gameraccounts WHERE Action='BOUGHT_ITEM' AND Time>= :start AND Time< :endTime", array("start" => $time['start'], "endTime" => $time['end']));
}

function AuszahlungenAnShopbetreiber($time, MySqlDatabase $db)
{
	// include the last day
	$time['end'] += (3600 * 24);

	return $db->fetchOne("SELECT SUM(Difference) FROM mc_customeraccounts WHERE PayoutStatus>0 AND Time>= :start AND Time < :endTime", array("start" => $time['start'], "endTime" => $time['end']));
}

#end

#region Benutzer-Zeiten bearbeiten
if (!isset($_SESSION['customTimes'])) {
	$_SESSION['customTimes'] = array();
}

if (isset($_GET['del']) && array_key_exists($_GET['del'], $_SESSION['customTimes'])) {
	unset($_SESSION['customTimes'][$_GET['del']]);
}

$custom = array();
$start = date("d.m.Y", time() - (3600 * 24));
$end = date("d.m.Y");

if (isset($_POST['customStart']) && isset($_POST['customEnd'])) {
	$start = strtotime($_POST['customStart']);
	$end = strtotime($_POST['customEnd']);
	if ($start > 0 && $end > $start) {
		$_SESSION['customTimes'][] = array('start' => $start, 'end' => $end);
	}
}
#endregion

#region style+sciprt
?>
	<script type='text/javascript'>

		function submitenter(myfield, e) {
			var keycode;
			if (window.event) keycode = window.event.keyCode;
			else if (e) keycode = e.which;
			else return true;

			if (keycode == 13) {
				if (navigator.userAgent.search(/Firefox/) > 0) return true;
				else {
					myfield.form.submit();
					return false;
				}
			}
			else return true;
		}

		jQuery(function ($) {
			$('#timerange').daterangepicker({
				format: 'DD.MM.YYYY',
				startDate: new Date(<?php echo strtotime($start) * 1000 ?>),
				endDate: new Date(<?php echo strtotime($end) * 1000 ?>)
			}, function (start, end, label) {
				$('#start').val(start.format('DD.MM.YYYY'));
				$('#end').val(end.format('DD.MM.YYYY'));

				$('#daterange-form').submit();
			});
		});

	</script>
<div>
	<div class="box">
		<div class="box-header">
			<h3 class="box-title">Zeitraum hinzufügen</h3>
		</div>
		<div class="box-body">
			<form action="?p=<?php echo $_GET['p'] ?>" method="post" id="daterange-form">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">
							<label for="timerange">
								<i class="fa fa-calendar"></i>
							</label>
						</div>
						<input type="text" class="form-control pull-right" id="timerange"/>
					</div>
				</div>
				<input type="hidden" name="customStart" id="start"/>
				<input type="hidden" name="customEnd" id="end"/>
			</form>
		</div>
	</div>

	<table class="table table-hover table-striped">
	<thead>
<tr>
<th></th>
<?php


#region Übersicht
foreach ($zeiten as $value => $row) {
	echo '<th>' . $value . '</th>';
}

foreach ($_SESSION['customTimes'] as $key => $value) {
	echo '<th><a href="?p=' . $_GET['p'] . '&amp;del=' . $key . '"><img src="delete.png" title="Diesen Bereich löschen" style="border:0;" /></a>  ' . date('d.m.Y', $value['start']) . '<br />bis ' . date('d.m.Y', $value['end']) . '</th>';
}

echo '
</thead>
<tr>
<td><strong>Einzahlungen der Spieler</strong></td>';
foreach ($zeiten as $row) {
	echo '<td>' . alsEuro(EinzahlungenSpieler($row, $db)) . '</td>';
}

foreach ($_SESSION['customTimes'] as $row) {
	$a = EinzahlungenSpieler($row, $db);
	echo '<td>' . alsEuro(EinzahlungenSpieler($row, $db)) . '</td>';
}
echo '
<td></td>
</tr>
<tr>
<td><strong>Ausgaben der Spieler</strong></td>';
foreach ($zeiten as $row) {
	echo '<td>' . alsEuro(AusgabenSpieler($row, $db)) . '</td>';
}
foreach ($_SESSION['customTimes'] as $row) {
	echo '<td>' . alsEuro(AusgabenSpieler($row, $db)) . '</td>';
}
echo '
<td></td>
</tr>
<tr>
<td><strong>Auszahlungen an Shopbetreiber</strong></td>';
foreach ($zeiten as $row) {
	echo '<td>' . alsEuro(AuszahlungenAnShopbetreiber($row, $db)) . '</td>';
}
foreach ($_SESSION['customTimes'] as $row) {
	echo '<td>' . alsEuro(AuszahlungenAnShopbetreiber($row, $db)) . '</td>';
}
echo '
<td></td>
</tr>
<tr>
<td><strong>Eigener Umsatz</strong></td>';
foreach ($zeiten as $row) {
	echo '<td>' . alsEuro(EigenerUmsatz($row, $db)) . '</td>';
}
foreach ($_SESSION['customTimes'] as $row) {
	echo '<td>' . alsEuro(EigenerUmsatz($row, $db)) . '</td>';
}
echo '
<td></td>
</tr>
</table>
</div>';
#end
$sum = 0;
$output = '';
$result = $db->query("SELECT CustomersId, Difference, Time, 'ca' AS 'Table', c.MinecraftName AS 'Name' FROM mc_customeraccounts AS ca
LEFT JOIN mc_customers AS c ON  c.Id=ca.CustomersId
WHERE PayoutStatus>0

UNION SELECT GamerId,Difference,Time, 'ga', g.Minecraftname FROM mc_gameraccounts AS ga
LEFT JOIN mc_gamer AS g ON g.Id=ga.GamerId
WHERE Action='1'

UNION SELECT '0',Difference,Time,'oa',oa.PayoutMail FROM mc_ouraccount AS oa
WHERE PayoutMail IS NOT NULL

ORDER BY Time DESC", array());
foreach ($result as $row) {
	if ($row->Table == 'ca') {
		$text = 'Auszahlung an ' . $row->Name;
		$row->Difference = -$row->Difference;
	} elseif ($row->Table == 'ga') {
		if ($row->Difference >= 0) {
			$text = 'Einzahlung von ' . $row->Name;
		} else {
			$text = 'Auszahlung an ' . $row->Name;
		}
	} elseif ($row->Table == 'oa') {
		$row->Difference = -$row->Difference;
		$text = 'Privatentnahme von ' . $row->Name;
	} else {
		$text = '';
	}
	$sum += $row->Difference;
	$output .= '
<tr>
	<td>' . date('d.m.Y', strtotime($row->Time)) . '</td>
	<td>' . $text . '</td>
	<td' . ($row->Difference < 0 ? ' class="text-danger"' : '') . '>' . alsEuro($row->Difference / POINTS_PER_EURO) . '</td>
</tr>';
}
echo "</table>";

$money = $sum / POINTS_PER_EURO;
$moneyFormatted = alsEuro($money);
$moneyBackground = "bg-green";

if ($money < 0) {
	$moneyBackground = "bg-red";
}

?>
<div class="small-box <?php echo $moneyBackground ?>">
	<div class="inner">
		<h3>
			<?php echo $moneyFormatted ?>
		</h3>

		<p>
			Saldo zum <strong><?php echo date('d.m.Y H:i:s') ?></strong>
		</p>
	</div>
	<div class="icon">
		<i class="ion ion-stats-bars"></i>
	</div>
	<div class="small-box-footer">
		<br/>
	</div>
</div>
<?php

echo <<<html
<table class="table table-hover table-striped">
<thead>
<tr>
	<th>Datum</th>
	<th>Beteiligter</th>
	<th>Betrag</th>
</tr>
</thead>
{$output}
</table>
html;
