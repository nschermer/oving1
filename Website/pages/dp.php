<?php
include ('../config.php');

function printItem($label, $id) {
	print('<li class="item-content item-input">');
		print('<div class="item-inner">');
			print('<div class="item-title item-label">'.$label.'</div>');
			print('<div class="item-input-wrap">');
				print('<input type="text" id="'.$id.'" readonly>');
			print('</div>');
		print('</div>');
	print('</li>');
}

$col1 = [
	'gps-lat' => 'Latitude',
	'gps-epy' => 'Latitude Afwijking',
	'gps-speed' => 'Snelheid',
	'gps-eps' => 'Snelheid Afwijking',
];

$col2 = [
	'gps-lon' => 'Longitude',
	'gps-epx' => 'Longitude Afwijking',
	'gps-mode' => 'Modus',
	'gps-track' => 'Richting'
];

$dbmodes = [
	'off' => 'Uitgeschakeld',
	'fix' => 'Positie en Richting',
	'position' => 'Positie'
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
			<div class="title sliding">Dynamic Position</div>
			<div class="right">
				<a href="#" class="alarmpopup link icon-only">
					<i class="f7-icons icon">bell<span id="alarms" class="badge color-red">32</span></i>
				</a>
			</div>
		</div>
	</div>

	<div class="page-content">
		<div class="row">
			<div class="col-100 tablet-50">
				<div class="card">
					<div class="card-header">DP Mode</div>
					<div class="card-content card-content-padding">
						<p class="segmented segmented-raised">
<?php foreach ($dbmodes as $m => $t) { ?>
							<button data-mode="<?php print($m) ?>" class="dpmode button"><?php print($t) ?></button>
<?php } ?>
						</p>
						<div class="block">
							<br>Bij <i>Positie en Richting</i> zullen zowel de motor als het roer gebruikt worden
							om de positie en koers vast te houden. Bij <i>Positie</i> zal voornameijk de boegschroef
							gebruikt worden en gaat de boot in de wind liggen.
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header">GPS</div>
					<div class="card-content">
						<div class="row">
							<div class="col-50">
								<div class="list no-hairlines-md">
									<ul>
<?php
	foreach ($col1 as $k => $l) {
		printItem ($l, $k);
	}
?>
									</ul>
								</div>
							</div>
							<div class="col-50">
								<div class="list no-hairlines-md">
									<ul>
<?php
	foreach ($col2 as $k => $l) {
		printItem ($l, $k);
	}
?>
									</ul>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
			<div class="col-100 tablet-50">
				<div class="card">
					<div class="card-header">Tracking</div>
					<div class="card-content card-content-padding">
						<div id="gpstrack" style="max-width: 800px"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>