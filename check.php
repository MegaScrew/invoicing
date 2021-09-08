<?php
require_once 'function.inc.php';

switch ($_POST['Step']) {
	case '1':
		$yearWeek = explode("-W", $_POST['week']);
		$startEndWeek = weekToDay((int)$yearWeek[1], (int)$yearWeek[0]);
		$params = $startEndWeek;
		$file = myReadFile($_FILES);
		$params += $file;
		echo(json_encode($params, JSON_UNESCAPED_UNICODE));

		// echo '<pre>';
		// 	print_r($startEndWeek);
		// echo '</pre>';

		// echo '<pre>';
		// 	print_r($file);
		// echo '</pre>';

		break;
	case '2':
		$recordings = json_decode($_POST['recordings'], true);
		unset($recordings[0]);
		$recordings = array_values($recordings);

		$test = getCompanyList('crm.company.list', $recordings);
		// echo '<pre>';
		// 	print_r($test);
		// echo '</pre>';


		// echo '<pre>';
		// 	//echo count($_POST['recordings']);
		// 	print_r(json_decode($_POST['recordings'], true));

		// echo '</pre>';
		// $params = 'count '.count($_POST['recordings']);
		// $params += $_POST;
		// echo 'count '.count($_POST['recordings']);
		//echo(json_encode($params, JSON_UNESCAPED_UNICODE));
		echo '<pre>';
			echo 'Step 2';
		echo '</pre>';
		break;
	case '3':
		echo '<pre>';
			echo 'Step 3';
		echo '</pre>';
		break;
	case '4':
		echo '<pre>';
			echo 'Step 4';
		echo '</pre>';
		break;
	case '5':
		echo '<pre>';
			echo 'Step 5';
		echo '</pre>';
		break;			
	default:
		echo '<pre>';
			echo 'default';
		echo '</pre>';
		break;
}
?>