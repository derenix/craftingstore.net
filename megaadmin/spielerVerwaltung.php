<?php
defined('_LOGIN') or die("Security block!");

$allowedSortColumns = array('Nickname', 'Email', 'MinecraftName', 'RegTime', 'Validated');

$sort = ((isset($_GET['sort'])) && ($_GET['sort'] == 'desc') ? 'desc' : 'asc');
$sortReverse = ($sort == 'asc' ? 'desc' : 'asc');

$order = "ORDER BY Id " . $sort;
$orderBy = "Id";
if (isset($_GET['order']) && in_array($_GET['order'], $allowedSortColumns)) {
	$orderBy = $_GET['order'];
	$order = " ORDER BY " . $orderBy . ' ' . $sort;
}

echo '<table>
<tr>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'Nickname' ? $sortReverse : $sort) . '&order=Nickname">Nickname</a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy === 'Email' ? $sortReverse : $sort) . '&order=Email">Email</a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'MinecraftName' ? $sortReverse : $sort) . '&order=MinecraftName">MinecraftName</a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'RegTime' ? $sortReverse : $sort) . '&order=RegTime">RegTime</a></th>
	<th><a href="?p=' . $_GET['p'] . '&sort=' . ($orderBy == 'Validated' ? $sortReverse : $sort) . '&order=Validated">Validated</a></th>
</tr>';
$query = "SELECT Id,Nickname,Email,MinecraftName,RegTime,Validated FROM mc_gamer " . $order;

$result = $db->query($query, array());
foreach ($result as $row) {
	echo "
<tr>
	<td>{$row->Nickname}</td>
	<td>{$row->Email}</td>
	<td>{$row->MinecraftName}</td>
	<td>".date('d.m.Y h:s:i',$row->RegTime)."</td>
	<td>{$row->Validated}</td>
</tr>";
}
echo '</table>';