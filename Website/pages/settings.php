<?php

include ('../config.php');

$encoders = [
	'azimuth/pv_offset' => 'Boegschroef',
	'azimuth/sp_offset' => 'Boegschroef Bediening',
	'rudder/pv_offset' => 'Roer',
	'rudder/sp_offset' => 'Roer Bediening'
];


?>
<div class="page">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="left">
				<a href="#" class="link icon-only panel-open" data-panel="left">
					<i class="icon f7-icons">menu</i>
				</a>
			</div>
			<div class="title sliding">Instellingen</div>
		</div>
	</div>

	<div class="toolbar tabbar">
		<div class="toolbar-inner">
			<a href="#tab-1" class="tab-link tab-link-active">Sensoren</a>
			<a href="#tab-2" class="tab-link">Encoders</a>
		</div>
	</div>
	
	<div class="tabs">
		<div id="tab-1" class="page-content tab tab-active">
			<div class="data-table">
				<table>
					<thead>
						<tr>
							<th width="16%" class="label-cell">Categorie</th>
							<th width="16%" class="label-cell">Label</th>
							<th width="8%" class="numeric-cell text-color-red">ALL</th>
							<th width="8%" class="numeric-cell text-color-blue">AL</th>
							<th width="8%" class="numeric-cell">Waarde</th>
							<th width="8%" class="numeric-cell text-color-blue">AH</th>
							<th width="8%" class="numeric-cell text-color-red">AHH</th>
							<th width="8%" class="numeric-cell">Hysteresis</th>
							<th width="16%" class="numeric-cell">Laatste update</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php

$redis->set('sensors/azimuth_vfd_temp/ah', 50);

$subkeys = [ 'all', 'al', 'pv', 'ah', 'ahh', 'hyst', 'stamp' ];

foreach ($sensors as $s => $t) {

	$keys = array_prepend_str($subkeys, "sensors/$s/");
	$vals = $redis->mGet($keys);

	foreach ($vals as &$i) {
		if (!is_numeric ($i))
			$i = '-';
	}
?>
						<tr>
							<td class="label-cell"><?php print($sensor_cat[strstr($s, '_', TRUE)]) ?></td>
							<td class="label-cell"><?php print($t) ?></td>
							<td class="numeric-cell"><?php print($vals[0]) ?></td>
							<td class="numeric-cell"><?php print($vals[1]) ?></td>
							<td class="numeric-cell"><?php print($vals[2]) ?></td>
							<td class="numeric-cell"><?php print($vals[3]) ?></td>
							<td class="numeric-cell"><?php print($vals[4]) ?></td>
							<td class="numeric-cell"><?php print($vals[5]) ?></td>
							<td class="numeric-cell"><?php print(is_numeric($vals[6]) ? time_elapsed_string("@$vals[6]") : $vals[6]) ?></td>
							<td class="actions-cell">
								<a class="link icon-only sensoredit" data-sensor="<?php print($s) ?>" title="Wijzigen">
									<i class="icon f7-icons">compose</i>
								</a>
							</td>
						</tr>
<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		<div id="tab-2" class="page-content tab">
			<div class="list no-hairlines-md">
				<ul>
<?php foreach ($encoders as $k => $t) { ?>
					<li class="item-content item-input">
						<div class="item-inner">
							<div class="item-title item-label"><?php print($t) ?></div>
							<div class="item-input-wrap">
								<input type="number" data-encoder="<?php print($k) ?>" class="encoder" value="<?php print (redis_get($k, 0)) ?>">
							</div>
						</div>
					</li>
<?php } ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="popup sensors-popup">
		<div class="view">
			<div class="page">
				<div class="navbar">
					<div class="navbar-inner">
						<div class="title">Sensor</div>
						<div class="right">
							<a class="link popup-close">Sluiten</a>
						</div>
					</div>
				</div>
				<div class="page-content">
					<form class="list no-hairlines-md" id="sensor-form">
						<input type="hidden" name="name">
						<ul>
							<li>
								<div class="item-content item-input">
									<div class="item-inner">
										<div class="item-title item-label">Naam</div>
										<div class="item-input-wrap">
											<input type="text" name="title" readonly>
										</div>
									</div>
								</div>
							</li>
<?php
$arr = [
	'all' => 'alarm laag',
	'al' => 'waarschuwing laag',
	'ah' => 'waarschuwing hoog',
	'ahh' => 'alarm hoog',
	'hyst' => 'hysteresis'
];

foreach ($arr as $a => $t) {
?>
							<li>
								<div class="item-content item-input">
									<div class="item-inner">
										<div class="item-title item-label"><?php print(strtoupper($a)) ?> (<?php print($t) ?>)</div>
										<div class="item-input-wrap">
											<input type="text" name="<?php print($a) ?>" placeholder="-" validate pattern="|[0-9]*(\.[0-9]*)?">
										</div>
									</div>
								</div>
							</li>
<?php } ?>
						</ul>
						<div class="block">
							<a class="button button-fill sensorsave">Opslaan</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>