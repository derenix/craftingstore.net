<?php
defined('_LOGIN') or die("Security block!");

?>
	<section class="content-header">
		<h1>
			Spielerverwaltung
		</h1>
		<ol class="breadcrumb">
			<li><a href="index.php?p=1"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Spielerverwaltung</li>
		</ol>
	</section>
	<section class="content">
<?php

$allowedSortColumns = array('Nickname', 'Email', 'MinecraftName', 'RegTime', 'Validated');

$sort = ((isset($_GET['sort'])) && ($_GET['sort'] == 'desc') ? 'desc' : 'asc');
$sortReverse = ($sort == 'asc' ? 'desc' : 'asc');

$order = "ORDER BY Id " . $sort;
$orderBy = "Id";
if (isset($_GET['order']) && in_array($_GET['order'], $allowedSortColumns)) {
	$orderBy = $_GET['order'];
	$order = " ORDER BY " . $orderBy . ' ' . $sort;
}

echo '<table class="table table-hover">
<thead>
<tr>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'Nickname' ? $sortReverse : $sort) . '&order=Nickname">Nickname <i class="fa fa-sort"></i></a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy === 'Email' ? $sortReverse : $sort) . '&order=Email">Email <i class="fa fa-sort"></i></a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'MinecraftName' ? $sortReverse : $sort) . '&order=MinecraftName">MinecraftName <i class="fa fa-sort"></i></a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'RegTime' ? $sortReverse : $sort) . '&order=RegTime">RegTime <i class="fa fa-sort"></i></a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'Validated' ? $sortReverse : $sort) . '&order=Validated">Validated <i class="fa fa-sort"></i></a></th>
</tr>
</thead>';
$query = "SELECT Id,Nickname,Email,MinecraftName,RegTime,Validated FROM mc_gamer " . $order;

$result = $db->query($query, array());
foreach ($result as $row) {
	$validateIcon = ($row->Validated == 1) ? 'check-square' : 'times';
	echo "
<tr>
	<td>{$row->Nickname}</td>
	<td>{$row->Email}</td>
	<td>{$row->MinecraftName}</td>
	<td>".date('d.m.Y h:s:i',$row->RegTime)."</td>
	<td> <i class='fa fa-"  . $validateIcon . "'></i></td>
</tr>";
}
echo '</table>';