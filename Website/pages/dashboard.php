<?php
include ('../config.php');

$lights = [
	'ps' => 'Bakboord',
	'stb' => 'Stuurboord',
	'masthead' => 'Toplicht',
	'stern' => 'Heklicht',
	'anchor' => 'Ankerlicht'
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
			<div class="title sliding">Dashboard</div>
			<div class="right">
				<a href="#" class="alarmpopup link icon-only">
					<i class="f7-icons icon">bell<span id="alarms" class="badge color-red">32</span></i>
				</a>
			</div>
		</div>
	</div>

	<div class="page-content">
		<div class="row block">
			<div class="col-100 tablet-80">
				<div class="card card-outline">
					<div class="card-header">Koers</div>
					<div class="card-content card-content-padding">
						<div class="display-inline-block elevation-1">
							<div id="azimuthrpm" class="float-left"></div>
							<div id="azimuth" class="float-left"></div>
						</div>
						<div id="rudder" class="display-inline-block elevation-1"></div>
						<div class="display-inline-block elevation-1">
							<div id="motorrpm" class="float-left"></div>
							<div id="motor" class="float-left"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-100 tablet-20">
				<div class="card card-outline">
					<div class="card-header">
						<span>Verlichting</span>
						<label class="toggle toggle-init">
							<input type="checkbox" class="lightsall">
							<span class="toggle-icon"></span>
						</label>
					</div>
					<div class="card-content card-content-padding">
						<div class="list">
							<ul>
<?php foreach ($lights as $n => $t) { ?>
								<li>
									<div class="item-content">
										<div class="item-media" style="min-width: 25px">
											<i class="f7-icons text-color-orange" id="<?php print($n) ?>-pv">bolt_round</i>
										</div>
										<div class="item-inner">
											<div class="item-title"><?php print($t) ?></div>
											<div class="item-after">
												<label class="toggle toggle-init">
													<input type="checkbox" class="lights" id="<?php print($n) ?>-sp">
													<span class="toggle-icon"></span>
												</label>
											</div>
										</div>
									</div>
								</li>
<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>