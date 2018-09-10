var $$ = Dom7;

var app = new Framework7({
	root: '#app',
	id: 'nl.oving1',
	name: 'Oving1',
	theme: 'md',
	view: {
		xhrCache: false
	},
	touch: {
		disableContextMenu: false,
	},
	dialog: {
		buttonOk: 'Ja',
		buttonCancel: 'Nee'
	},
	smartSelect: {
		searchbarPlaceholder: 'Zoeken',
		searchbarDisableText: 'Annuleer',
		pageBackLinkText: 'Terug',
	}
});

window.setCookie = function(name, value) {
	var date = new Date();
	date.setTime(date.getTime() + (265*24*3600*1000));
	expires = "; expires=" + date.toUTCString();
	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
window.getCookie = function(name) {
	var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
	if (match) return match[2];
}


var timerIds = [];

function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function stopTimer(name) {
	if (timerIds[name] > 0) {
		clearInterval(timerIds[name]);
		timerIds[name] = 0;
	}
}

function startTimer(name, func, timeout) {
	stopTimer(name);
	timerIds[name] = setInterval(func, timeout);
}

function initDashboard() {
	let charts = [
		['rudder', rudderOptions],
		['azimuth', azimuthOptions],
		['azimuthrpm', rpmOptions],
		['motor', motorOptions],
		['motorrpm', rpmOptions],
	];

	/* Hookup all charts to window for update */
	window.charts = [];
	charts.forEach(function (e) {
		window.charts[e[0]] = Highcharts.chart(e[0], e[1]);
	});
}

var dashboardCharts = ['rudder', 'azimuth', 'motor', 'motorrpm', 'azimuthrpm'];
var lightsBtns = [ 'ps', 'stb', 'masthead', 'stern', 'anchor' ];

function updateDashboard() {
	app.request.json("/json.php?t=dashboard", {},
		function (data, status, xhr) {
			dashboardCharts.forEach(function (e) {
				if (e in data) {
					let d = data[e];
					let chart = window.charts[e];

					if ('pv' in d) {
						if (chart.series[0].data[0].y != d['pv'])
							chart.series[0].setData([d['pv']]);
					}
					if ('sp' in d) {
						if (chart.series[0].targetData) {
							if (d['sp']) {
								chart.series[0].data[0].update({ target: d['sp'] });
							}
						} else if (chart.series[1].data[0].y != d['sp']) {
							chart.series[1].setData([d['sp']]);
						}
					}
				}
				if ('lights' in data) {
					var d = data['lights'];
					var cnt = 0;
					lightsBtns.forEach(function (e) {
						let on = d[e]['sp'] == "on";
						$$('#'+e+'-sp').prop('checked', on);
						if (e != 'anchor' && on)
							cnt++;
						
						let state_icon = $$('#'+e+'-pv');
						if (state_icon.data('pv') != d[e]['pv']) {
							let icon = 'bolt_round';
							if (d[e]['pv'] == "on")
								icon += '_fill';
							state_icon.text(icon);
							state_icon.data('pv', d[e]['pv']);
						}
					});
					$$('.lightsall').prop('checked', cnt == 4);
				}
			});
		},
		function (status, xhr) {
			dashboardCharts.forEach(function (e) {
				// disable
			});
		}
	);
}

var gpsFields = [ 'lat', 'lon', 'mode', 'speed', 'epx', 'epy', 'eps', 'track' ];

function updateGps() {
	app.request.json("/json.php?t=gps", {},
		function (data, status, xhr) {
			var chart = window.charts['track'];

			if ('gps' in data) {
				var d = data['gps'];
				/* Update card fields */
				gpsFields.forEach(function (e) {
					if (e in d) {
						$$('#gps-' + e).val(d[e]);
					}
				});

				if ('history' in d) {
					var a = d['history'];

					/* Push values to sortable array */
					var hist = [];
					for (var key in a) {
						var obj = a[key];
						obj['t'] = Number(key);
						hist.push(obj);
					}
					hist.sort(function (a, b) { return b.t - a.t });

					/* Data for plot */
					var plotData = [];

					/* First value is reference */
					var recent = hist.shift();
					var lat = recent.lat;
					var lon  = recent.lon;
					var latrad = Math.cos(Math.PI / 180 * lat);
					var magic = 111319; // For Alkmaar, origional is 111111

					hist.forEach(function (e) {
						dy = (e.lat - lat) * magic;
						dx = latrad * (e.lon - lon) * magic;
						plotData.unshift([ dx, dy ]);
					});

					/* Last point is always the center */
					plotData.push({
						x: 0,
						y: 0,
						selected: true,
						marker: {
							enabled: true
						}
					});

					chart.series[0].setData(plotData, false, null, false);
				}
			}

			if ('dp' in data) {
				var d = data['dp'];

				/* Compass heading */
				var ovingData = [];
				if (isNumber(d['heading'])) {
					var rad = 2 * Math.PI * (d['heading'] / 360);
					ovingPoints.forEach(function (p, i) {
						ovingData[i] = transformPoint (p, rad, d['dx'] || 0, d['dy'] || 0);
					});
				}
				chart.series[1].setData(ovingData, false, null, false);

				/* Target heading */
				var arrowData = [];
				if (isNumber(d['target'])) {
					var rad = 2 * Math.PI * (d['target'] / 360);
					arrowPoints.forEach(function (p, i) {
						arrowData[i] = transformPoint (p, rad);
					});
				}
				chart.series[2].setData(arrowData, false, null, false);

				/* Active mode */
				var dpmode = d['mode'] || 'off';
				var activebtn = $$('button[data-mode='+dpmode+']');
				if (!activebtn.hasClass('button-active')) {
					$$('button.dpmode').removeClass('button-active');
					activebtn.addClass('button-active');
				}
			}

			/* Final redraw */
			chart.redraw();
		},
		function (status, xhr) {

		}
	);
}

function transformPoint(point, angle, xo = 0, yo = 0)
{
	var cos = Math.cos(angle);
	var sin = Math.sin(angle);
	var x2 = xo + point[0] * cos + point[1] * sin;
	var y2 = yo + point[1] * cos - point[0] * sin;

	return [ x2, y2 ];
}

var routes = [
	{
		path: '/',
		url: './pages/dashboard.php',
		on: {
			pageInit: function () {
				$$('.lights').change(function () {
					var n = $$(this).attr('id').replace('-sp','');;
					var e = $$(this).prop('checked') ? "on" : "off";
					app.request.post('/post.php',
						{
							type: 'lights',
							name: n,
							sp: e
						}, updateDashboard
					);
				});

				$$('.lightsall').change(function () {
					var on = $$(this).prop('checked');
					lightsBtns.forEach(function (e) {
						if (e != 'anchor') {
							$$('#'+e+'-sp').prop('checked', on).change();
						}
					});
				});

				/* Build all the plots */
				initDashboard();

				/* Initial update */
				updateDashboard();
			},
			pageAfterIn: function (page) {
				startTimer('dashboard', updateDashboard, 100);
			},
			pageBeforeOut: function (page) {
				stopTimer('dashboard');
			}
		}
	},
	{
		path: '/power',
		url: './pages/power.php',
	},
	{
		path: '/log',
		url: './pages/log.php',
	},
	{
		path: '/trends',
		url: './pages/trends.php',
	},
	{
		path: '/dp',
		url: './pages/dp.php',
		on: {
			pageInit: function () {
				window.charts = [];
				window.charts['track'] = Highcharts.chart('gpstrack', gpsOptions);

				$$('button.dpmode').click(function () {
					var m = $$(this).data('mode');
					app.request.post('/post.php',
						{
							type: 'dpmode',
							mode: m
						},
						updateGps
					);
				});

				updateGps();
			},
			pageAfterIn: function (page) {
				startTimer('gps', updateGps, 5000);
			},
			pageBeforeOut: function (page) {
				stopTimer('gps');
			}
		}
	},
	{
		path: '/settings',
		url: './pages/settings.php',
		on: {
			pageInit: function () {
				$$('.encoder').on('change', function () {
					var e = $$(this).data('encoder');
					app.request.post('/post.php',
						{
							type: 'encoder',
							encoder: e,
							value: this.value
						},
						function (data) {

						}
					);
				});
				$$('a.sensorsave').click(function () {
					var data = app.form.convertToData('#sensor-form');
					data['type'] = 'sensor';

					app.request.post('/post.php', data,
						function (data) {
							app.popup.close('.sensors-popup');
							var err = app.toast.create({
								text: 'Sensor gegevens opgeslagen',
								position: 'center',
								closeTimeout: 2000,
							});
							err.open();
							mainView.router.refreshPage();
						},
						function (data) {
							var err = app.toast.create({
								text: 'Sensor gegevens zijn niet opgeslagen',
								position: 'center',
								closeTimeout: 2000,
							});
							err.open();
						}
					);
				});
				$$('a.sensoredit').click(function () {
					var n = $$(this).data('sensor');
					app.request.json('/json.php?t=sensor', { name: n },
						function (data, status, xhr) {
							app.form.fillFromData('#sensor-form', data['sensor']);
							app.popup.open('.sensors-popup');
						},
						function (status, xhr) {
							var err = app.toast.create({
								text: 'Fout tijdens het laden van de sensor gegevens',
								position: 'center',
								closeTimeout: 2000,
							});
							err.open();
						}
					);
				});
			}
		}
	},
	{
		path: '(.*)',
		url: './pages/404.php',
	}
];

var mainView = app.views.create('.view-main', {
	url: '/',
	routes: routes,
	main: true,
	pushState: true,
});

/* Chart themes */
function HighchartDarkLight() {
	if ($$('body').hasClass('theme-dark')) {
		return darkColors;
	} else {
		return lightColors;
	}
}
$$('#darkbtn').click(function () {
	$$('body').toggleClass('theme-dark');

	var is_dark = $$('body').hasClass('theme-dark');
	window.setCookie('dark', is_dark);

	var colors = HighchartDarkLight();
	Highcharts.setOptions(colors);

	Highcharts.each(Highcharts.charts, function (chart) {
		chart.update(colors);
	});
});
/* Init */
if (window.getCookie ('dark'))
	$$('body').addClass('theme-dark');
Highcharts.setOptions(HighchartDarkLight());