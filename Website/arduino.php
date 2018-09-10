<?php

include ('config.php');

if (!isset ($_GET['src']))
	die ('No src');

$src = $_GET['src'];

switch ($src)
{
	/* Update process value in redis */
	case 'azimuth':
	case 'rudder':
		if (isset ($_GET['pv'])) {
			$val = $_GET['pv'];
			if (is_numeric ($val)) {
				/* store new process value */
				$redis->set("$src/pv", floatval($val));
			} else {
				/* arduino asked for setpoint */
				$sp = $redis->get("$src/sp");
				file_get_contents("http://192.168.0.30/?sp=$sp");
			}
		}
		if (isset ($_GET['sp'])) {
			$val = $_GET['sp'];
			if (is_numeric ($val)) {
				$redis->set("$src/sp", floatval($val));
				
				/* testing */
				file_get_contents("http://192.168.0.30/?sp=$val");
			}
		}
		break;
	
	case 'lights':
		$name = $_GET['name'];
		$is_on = $_GET['val'] == "on";
		$redis->set("lights/$name/pv", $is_on ? "on" : "off");
		break;

	case 'sensors':
		$name = $_GET['name'];
		$val = $_GET['val'];
		if (is_numeric ($val) && array_key_exists ($name, $sensors)) {
			$val = floatval($val);
			
			$set = [];
			$set["sensors/$name/pv"] = $val;
			$set["sensors/$name/stamp"] = time();

			$getkeys = [ 'all', 'al', 'ah', 'ahh', 'hyst', 'act' ];
			$vals = $redis->mGet(array_prepend_str ($getkeys, "sensors/$name/"));
			$param = array_combine ($getkeys, $vals);

			$hyst = $param['hyst'];
			if (!is_numeric ($hyst))
				$hyst = 0;
			$oldact = $param['act'];

			/* Check new alarm or within hysterese */
			if ($param['all']
				&& ($val <= $param['all']
					|| ($oldact == "all" && $val - $hyst <= $param['all'])))
				$newact = "all";
			elseif ($param['al']
					&& ($val <= $param['al']
						|| ($oldact == "al" && $val - $hyst <= $param['al'])))
				$newact = "al";
			elseif ($param['ahh']
					&& ($val >= $param['ahh']
						|| ($oldact == "ahh" && $val + $hyst >= $param['ahh'])))
				$newact = "ahh";
			elseif ($param['ah']
					&& ($val >= $param['ah']
						|| ($oldact == "ah" && $val + $hyst >= $param['ah'])))
				$newact = "ah";
			else
				$newact = "";

			$set["sensors/$name/act"] = $newact;

			var_dump($newact);
			
			$redis->mSet($set);
		}
		break;

	default:
		die ('Unknown src');
}

?>