<?php

include ('config.php');

function isset_arr($arr)
{
	foreach ($arr as $a) {
		if (!isset ($_POST[$a]))
			return false;
	}
	return true;
}

if (!isset ($_POST['type']))
	die ('Unknown');

switch ($_POST['type']) {
	case 'encoder':
		if (isset_arr(['encoder', 'value'])
			&& is_numeric ($_POST['value'])
			&& str_endswith($_POST['encoder'], '_offset')) {
			$redis->set($_POST['encoder'], $_POST['value']);
		}
		break;

	case 'dpmode':
		if (isset_arr(['mode']))
			$redis->set('dp/mode', $_POST['mode']);
		break;

	case 'lights':
		if (isset_arr(['name', 'sp'])) {
			$redis->set('lights/'.$_POST['name'].'/sp', $_POST['sp']);
		}
		break;

	case 'sensor':
		$arr_vals = [ 'al', 'all', 'ah', 'ahh', 'hyst' ];
		if (isset_arr($arr_vals) && isset ($_POST['name'])) {
			$name = $_POST['name'];
			$vals = [];
			foreach ($arr_vals as $a) {
				$v = $_POST[$a];
				$v = is_numeric ($v) ? floatval($v) : '';
				$vals["sensors/$name/$a"] = $v;
			}
			$redis->mSet($vals);
		}
		break;

	default:
		die ('Unknown type');
}

?>