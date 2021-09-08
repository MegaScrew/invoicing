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
		// 	print_r($params);
		// echo '</pre>';
		break;
	case '2':
		$recordings = json_decode($_POST['recordings'], true);

		$test = getCompanyList('crm.company.list', $recordings);

		$order = array("\n\t", "\t", "  ", "   ");
		$replace = '';
		
		foreach ($recordings as &$row) {
			foreach ($test as $rowtest) {
				if (str_replace($order, $replace, $row[3]) == $rowtest['UF_CRM_1594794891']) {
					// echo 'here';
					$row[2] = $rowtest['ID'];
				}else{
					$row[2] = 0;
				}				
			}
		}

		//echo(json_encode($params, JSON_UNESCAPED_UNICODE));
		echo '<pre>';
			echo 'Step 2';
		echo '</pre>';
		echo '<pre>';
			print_r($test);
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