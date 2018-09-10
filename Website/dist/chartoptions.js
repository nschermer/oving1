function formatDegrees() {
	var val = Math.abs(this.y);
	if (val > 180) val = 360 - (val % 360);
	
	return ('00' + val).slice(-3) + '°';
}

function formatPercent() {
	return ('00' + Math.abs(this.y)).slice(-3) + '%';
}

/* https://www.deif.com/products/xdi#documentation */

Highcharts.setOptions({
	chart: {
		backgroundColor: 'rgba(255, 255, 255, 0.0)',
		animation: false
	},
	credits: {
		enabled: false
	},
	tooltip: {
		enabled: false
	},
	legend: {
		enabled: false
	},
	global: {
		useUTC: false
	}
});

var lightColors = {
	plotOptions: {
		gauge: {
			pivot: {
				backgroundColor: '#000'
			},
			dataLabels: {
				color: '#fff'
			},
			dial: {
				backgroundColor: '#000'
			}
		},
		bullet: {
			color: '#000'
		}
	},
	yAxis: {
		tickColor: '#000',
		minorTickColor: '#000',
		lineColor: '#000',
		gridLineColor: '#ccc',
		minorGridLineColor: '#eee',
		labels: {
			style: {
				color: '#000'
			}
		}
	},
	xAxis: {
		gridLineColor: '#ccc',
		minorGridLineColor: '#eee',
		labels: {
			style: {
				color: '#000'
			}
		}
	},
	title: {
		style: {
			color: '#000'
		}
	}
};

var darkColors = {
	plotOptions: {
		gauge: {
			pivot: {
				backgroundColor: '#9e9e9e'
			},
			dataLabels: {
				color: '#000'
			},
			dial: {
				backgroundColor: '#9e9e9e'
			}
		},
		bullet: {
			color: '#fff'
		}
	},
	yAxis: {
		tickColor: '#fff',
		minorTickColor: '#fff',
		lineColor: '#fff',
		gridLineColor: '#666',
		minorGridLineColor: '#333',
		labels: {
			style: {
				color: '#fff'
			}
		}
	},
	xAxis: {
		gridLineColor: '#666',
		minorGridLineColor: '#333',
		labels: {
			style: {
				color: '#fff'
			}
		}
	},
	title: {
		style: {
			color: 'rgba(255,255,255,.87)'
		}
	}
};

var rudderOptions = {
	chart: {
		type: 'gauge',
		width: 300,
		height: 200
	},
	title: {
		text: "Roerstand"
	},
	plotOptions: {
		gauge: {
			pivot: {
				radius: '25px'
			},
			dataLabels: {
				borderWidth: 0,
				zIndex: 3,
				formatter: formatDegrees,
				allowOverlap: true,
				style: {
					fontSize: '14px',
					textOutline: 'none'
				},
				y: -20
			}
		}
	},
	yAxis: {
		min: -60,
		max: 60,
		tickInterval: 10,
		tickLength: 10,
		minorTickLength: 5,
		labels: {
			enabled: true,
			distance: 20,
			formatter: function () {
				if (this.isFirst)
					return "STBD";
				else if (this.isLast)
					return "PORT";
				else
					return Math.abs(this.value);
			}
		},
		plotBands: [{
			from: -100,
			to: 0,
			color: '#4caf50',
			thickness: '10'
		}, {
			from: 0,
			to: 100,
			color: '#f44336',
			thickness: '10'
		}]
	},
	pane: {
		startAngle: 100,
		endAngle: 260,
		background: null,
		center: ['50%', '30px'],
		size: '130%'
	},
	series: [{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '0',
			baseWidth: 25,
			radius: '85%',
			rearLength: '0',
			topWidth: 1
		}
	},
	{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '93%',
			baseWidth: 0.00001,
			radius: '107%',
			rearLength: '0%',
			topWidth: 10
		},
		dataLabels: {
			style: {
				fontSize: '10px'
			},
			y: -0
		},
	}]
};

var azimuthOptions = {
	chart: {
		type: 'gauge',
		width: 225,
		height: 200
	},
	title: {
		text: "Boegschroef",
		x: -37
	},
	plotOptions: {
		gauge: {
			pivot: {
				radius: '25px'
			},
			dataLabels: {
				borderWidth: 0,
				zIndex: 3,
				formatter: formatDegrees,
				allowOverlap: true,
				style: {
					fontSize: '14px',
					textOutline: 'none'
				},
				y: -20
			}
		}
	},
	yAxis: {
		min: 0,
		max: 359,
		tickInterval: 30,
		tickLength: 10,
		minorTickLength: 5,
		labels: {
			enabled: true,
			distance: 15,
			formatter: function () {
				if (this.value > 180)
					return 360 - this.value;
				return this.value;
			}
		},
		plotBands: [{
			from: 0,
			to: 180,
			color: '#4caf50',
			thickness: '10'
		}, {
			from: 180,
			to: 360,
			color: '#f44336',
			thickness: '10'
		}]
	},
	pane: {
		background: null,
		startAngle: 0,
		size: '70%'
	},
	series: [{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '0',
			baseWidth: 25,
			radius: '70%',
			rearLength: '0',
			topWidth: 1
		}
	},
	{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '90%',
			baseWidth: 0.00001,
			radius: '110%',
			rearLength: '0%',
			topWidth: 10
		},
		dataLabels: {
			style: {
				fontSize: '10px'
			},
			y: -0
		},
	}]
};

var rpmOptions = {
	chart: {
		type: 'bullet',
		height: 200,
		width: 75,
		spacingTop: 50
	},
	plotOptions: {
		series: {
			pointPadding: 0.25,
			borderWidth: 0,
			targetOptions: {
				width: '200%'
			}
		},
		bullet: {
			dataLabels: {
				enabled: true,
			}
		}
	},
	title: {
		text: null,
	},
	xAxis: {
		categories: ['rpm'],
		tickLength: 0,
		lineWidth: 0,
		labels: {
			autoRotation: 0
		},
	},
	yAxis: {
		title: null,
		gridLineWidth: 0,
		min: 0,
		max: 1800,
		tickInterval: 100,
		endOnTick: false,
		plotBands: [{
			from: 0,
			to: 1200,
			color: '#4caf50'
		}, {
			from: 1200,
			to: 1800,
			color: '#f44336'
		}],
	},
	series: [{
		animation: false,
		data: [{
			y: 0
		}]
	}]
};

var motorOptions = {
	chart: {
		type: 'gauge',
		width: 225,
		height: 200
	},
	title: {
		text: "Motorvermogen",
		x: -37
	},
	plotOptions: {
		gauge: {
			pivot: {
				radius: '25px'
			},
			dataLabels: {
				borderWidth: 0,
				formatter: formatPercent,
				zIndex: 3,
				allowOverlap: true,
				style: {
					fontSize: '14px',
					textOutline: 'none'
				},
				y: -20
			}
		}
	},
	yAxis: {
		min: -125,
		max: 125,
		tickInterval: 25,
		tickLength: 10,
		minorTickLength: 5,
		minorTickInterval: 5,
		labels: {
			enabled: true,
			distance: 20,
			formatter: function () {
				if (this.isFirst)
					return "REV";
				else if (this.isLast)
					return "FWD";
				else
					return Math.abs(this.value);
			}
		},
		plotBands: [{
			from: -200,
			to: -100,
			color: '#f44336',
			thickness: '10'
		}, {
			from: 100,
			to: 200,
			color: '#f44336',
			thickness: '10'
		}]
	},
	pane: {
		startAngle: -80,
		endAngle: 80,
		background: null,
		center: ['50%', '80%'],
		size: '100%'
	},
	series: [{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '0',
			baseWidth: 25,
			radius: '85%',
			rearLength: '0',
			topWidth: 1
		}
	},
	{
		animation: false,
		data: [{
			y: 0
		}],
		dial: {
			baseLength: '93%',
			baseWidth: 0.00001,
			radius: '107%',
			rearLength: '0%',
			topWidth: 10
		},
		dataLabels: {
			style: {
				fontSize: '10px'
			},
			y: -0
		},
	}]
};

/* Boat shape */
var ovingPoints = [
	[0, 12 ],
	[ 4, 6 ],
	[ 4, -12 ],
	[ -4, -12 ],
	[ -4, 6 ],
	[ 0, 12]
];

/* Arrow shape */
var arrowPoints = [
	[ 0, 10 ],
	[ 2, 8 ],
	[ 1, 8 ],
	[ 1, 0 ],
	[ -1, 0 ],
	[ -1, 8 ],
	[ -2, 8 ],
	[ 0, 10 ]
];

var gpsOptions = {
	chart: {
		height: '100%',
		zoomType: 'xy'
	},
	title: {
		text: null
	},
	xAxis: {
		min: -30,
		max: 30,
		tickInterval: 10,
		tickLength: 0,
		minorTickInterval: 1,
		title: null,
		gridLineWidth: 1,
		labels: {
			format: '{value}m'
		},
		opposite: true
	},
	yAxis: {
		min: -30,
		max: 30,
		tickInterval: 10,
		minorTickInterval: 1,
		title: null,
		labels: {
			format: '{value}m',
		}
	},
	series: [
		{
			name: 'GPS track',
			type: 'scatter',
			lineWidth: 2,
			color: 'rgba(33, 150, 243, 0.6)',
			connectEnds: false,
			animation: false,
			marker: {
				enabled: false
			},
			enableMouseTracking: false,
			data: []
		},
		{
			name: 'Oving',
			type: 'polygon',
			animation: false,
			marker: {
				enabled: false
			},
			color: 'rgba(244, 67, 54, 0.6)',
			lineWidth: 2,
			enableMouseTracking: false,
			zIndex: 2,
			data: []
		},
		{
			name: "Target Heading",
			type: 'polygon',
			animation: false,
			marker: {
				enabled: false
			},
			lineWidth: 2,
			color: 'rgba(76, 175, 80, 0.6)',
			enableMouseTracking: false,
			zIndex: 1,
		}
	]
};