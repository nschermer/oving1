<?php

$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->select(1);

$sensor_cat = [ 
	'azimuth' => 'Boegschroef',
	'prop' => 'Schroef',
	'fuel' => 'Brandstof',
	'engine' => 'Motor',
	'gen' => 'Generator'
];
$sensors = [
	'azimuth_vfd_temp' => 'VFD temperatuur',
	'azimuth_vfd_power' => 'VFD vermogen',
	'azimuth_temp' => 'motor temperatuur',
	'prop_vfd_temp' => 'VFD temperatuur',
	'prop_vfd_power' => 'VFD vermogen',
	'prop_temp' => 'motor temperatuur',
	'fuel_level' => 'niveau',
	'engine_oil_temp' => 'olietemperatuur',
	'engine_oil_pressure' => 'oliedruk',
	'engine_water_temp' => 'koelwater temperatuur',
	'engine_water_level' => 'koelwater niveau',
	'engine_exhaust_temp' => 'uitlaat temperatuur',
	'gen_temp' => 'temperatuur',
	'gen_voltage' => 'voltage',
	'gen_power' => 'vermogen',
	'gen_rpm' => 'toerental'
];


function redis_get($key, $default = false)
{
	global $redis;

	$v = $redis->get($key);
	if ($v === false)
		$v = $default;
	return $v;
}

function time_elapsed_string($datetime, $full = false) {
	try {
		$ago = new DateTime($datetime);
	} catch (Exception $e) {
		return 'Nooit';
	}
	$now = new DateTime;
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array(
		'y' => [ 'jaar', 'jaren' ],
		'm' => [ 'maand', 'maanden' ],
		'w' => [ 'week', 'weken' ],
		'd' => [ 'dag', 'dagen' ],
		'h' => [ 'uur', 'uur' ],
		'i' => [ 'minuut', 'minuten' ],
		's' => [ 'seconden', 'seconden' ],
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . ($diff->$k > 1 ? $v[1] : $v[0]);
		} else {
			unset ($string[$k]);
		}
	}

	if (!$full)
		$string = array_slice($string, 0, 1);

	return $string ? implode(', ', $string) . ' geleden' : 'zojuist';
}

function str_startwith($haystack, $query)
{
	return substr($haystack, 0, strlen($query)) === $query;
}

function array_prepend_str($arr, $str)
{
	$ret = [];
	foreach ($arr as $i)
		$ret[] = "$str$i";
	return $ret;
}

function str_endswith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}


?>