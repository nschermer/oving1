<?php

include('config.php');

if (!isset ($_GET['t']))
	die ('No type');

function toMode($val)
{
	switch($val) {
		case 0: return 'Inactief';
		case 1: return 'Geen fix';
		case 2: return '2D fix';
		case 3: return '3D fix';
		default: return '-';
	}
}

function gpsHistory($val)
{
	global $redis;

	$keys = $redis->keys('gps/history/*');
	$data = [];

	foreach ($keys as $k) {
		$val = $redis->get($k);
		$stamp = substr ($k, 12);
		$data[$stamp] = json_decode($val);
	}

	return $data;
}

function sensorData($val)
{
	global $redis, $sensors;

	$name = $_GET['name'];
	if (!array_key_exists ($name, $sensors))
		die ('Unknown sensor');

	$subkeys = [ 'all', 'al', 'ah', 'ahh', 'hyst' ];
	$keys = array_prepend_str($subkeys, "sensors/$name/");
	$vals = $redis->mGet($keys);

	foreach ($vals as &$v)
		if (!is_numeric ($v))
			$v = '';

	$subkeys[] = 'name';
	$vals[] = $name;
	$subkeys[] = 'title';
	$vals[] = $sensors[$name];

	return array_combine($subkeys, $vals);
}


switch ($_GET['t'])
{
	case 'dashboard':
		$sppv = [ 'sp' => null, 'pv' => null ];
		$keys = [
			'azimuthrpm' => $sppv ,
			'azimuth' => $sppv ,
			'rudder' => $sppv ,
			'motorrpm' => 
				[ 'pv' => null ],
			'motor' => $sppv ,
			'lights' => 
				[ 'ps' => $sppv ,
				  'stb' => $sppv ,
				  'masthead' => $sppv ,
				  'stern' => $sppv ,
				  'anchor' => $sppv ]
		];
		break;
	
	case 'sensor':
		$keys = [
			'sensor' => 'sensorData'
		];
		break;

	case 'gps':
		$keys = [
			'gps' =>
				[ 'lat' => null,
				  'lon' => null,
				  'mode' => 'toMode',
				  'speed' => null,
				  'epx' => null,
				  'epy' => null,
				  'eps' => null,
				  'track' => null,
				  'history' => 'gpsHistory' ],
			'dp' =>
				[ 'mode' => null,
				  'heading' => null, /* compass */
				  'target' => null, /* compass target */
				  'dy' => null,
				  'dx' => null ]
		];
		break;
	
	default:
		die ('Unknown type');
}

function fillValues(&$arr, $base=null)
{
	global $redis;

	foreach ($arr as $k => &$v) {
		if ($base == null) {
			$prop = $k;
		} else {
			$prop = "$base/$k";
		}

		if (is_array ($arr[$k])) {
			fillValues($arr[$k], $prop);
		} elseif ($v) {
			$v = $v($redis->get($prop));
		} else {
			$v = redis_get($prop);
			$offset = redis_get($prop.'_offset');
			if ($offset)
				$v = ($v + $offset) % 360;
		}
	}
}

fillValues ($keys);

header('Content-Type: application/json');
print(json_encode($keys, JSON_NUMERIC_CHECK));

?>