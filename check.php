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
		// 	echo 'Step 1';
		// echo '</pre>';
		break;
	case '2':
		$recordings = json_decode($_POST['recordings'], true);

		// $test = getCompanyList('crm.company.list', $recordings);
		
		echo '<pre>';
			print_r(count($recordings));
		echo '</pre>';


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