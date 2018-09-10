<?php

function path_time($path) {
	print ("/$path?" . filemtime ($path));
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Security-Policy" content="default-src * 'self' 'unsafe-inline' 'unsafe-eval' data: gap: content:">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui, viewport-fit=cover">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="theme-color" content="#4caf50">
	<meta name="format-detection" content="telephone=no">
	<meta name="msapplication-tap-highlight" content="no">
	<title>Oving1</title>
	<!--
	<link rel="shortcut icon" sizes="196x196" href="/css/icons/192.png">
	<link rel="shortcut icon" sizes="128x128" href="/css/icons/128.png">
	<link rel="apple-touch-icon" href="/css/icons/57.png">`
	<link rel="apple-touch-icon" sizes="72x72" href="/css/icons/72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/css/icons/114.png">
	-->
	<link rel="stylesheet" href="<?php path_time('dist/fw7/framework7.md.min.css') ?>">
	<link rel="stylesheet" href="<?php path_time('dist/fw7/framework7-icons.css') ?>">
	<link rel="stylesheet" href="<?php path_time('dist/app.css') ?>">
</head>

<body class="color-theme-green" lang="<?php print($locale) ?>">
	<div id="app">
		<div class="statusbar"></div>

		<?php include('pages/panel.php') ?>

		<div class="view view-main">
			<!-- router content goes here -->
		</div>

		 <div class="popup" id="genaralpopup">
			<div class="view view-init popup-view">
				<!-- popup content goes here -->
			</div>
		</div>
	</div>

	<script src="<?php path_time('dist/fw7/framework7.min.js') ?>"></script>
	<script src="<?php path_time('dist/highcharts/highcharts.js') ?>"></script>
	<script src="<?php path_time('dist/highcharts/highcharts-more.js') ?>"></script>
	<script src="<?php path_time('dist/highcharts/bullet.js') ?>"></script>
	<script src="<?php path_time('dist/chartoptions.js') ?>"></script>
	<script src="<?php path_time('dist/app.js') ?>"></script>

</body>

</html>
