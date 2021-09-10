<?php
require_once 'function.inc.php';

$recordingsNotFound =[];
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
		//$tempRecordings = getCompanyList('crm.company.list', $recordings);
		$tempRecordings = getBigData('crm.company.list');
		
		$order = array("\n\t", "\t", "  ", "   ");
		$replace = ' ';

		foreach ($recordings as &$row) {
			foreach ($tempRecordings as $rowtest) {
				// if (strcasecmp($row[3], $rowtest['UF_CRM_1594794891']) == 0) {
				$strTemp = str_replace($order, $replace, $rowtest['UF_CRM_1594794891']);
				$strTemp = trim($strTemp);
				if ($row[3] == (int)$strTemp) {	
					$row[2] = $rowtest['ID'];
				}				
			}
		}

		checkArr(2, $recordings, $recordingsNotFound);			// проверяем найден или нет ID магазина 

		if (count($recordingsNotFound) == 0) {
			$params = array('recordings' => $recordings);
		}else{
			$params = array('recordings' => $recordings, 'recordingsNotFound' => $recordingsNotFound, 'tempRecordings' => $tempRecordings);
		}
		
		echo(json_encode($params, JSON_UNESCAPED_UNICODE));
		
		// echo '<pre>';
		// 	echo 'Step 2';
		// echo '</pre>';
		// echo '<pre>';
		// 	// print_r(var_dump($recordingsNotFound));
		// 	print_r(var_dump($tempRecordings));
		// echo '</pre>';	
		break;
	case '3':
		$recordings = json_decode($_POST['recordings'], true);
		$first_day = json_decode($_POST['first_day'], true);
		$last_day = json_decode($_POST['last_day'], true);

		// updateCompanyFiled($recordings, $first_day, $last_day);

		$params = array('Step3' => 'finish');
		// echo(json_encode($params, JSON_UNESCAPED_UNICODE));

		// echo '<pre>';
		// 	echo 'Step 3';
		// echo '</pre>';
		// echo '<pre>';
		// 	// print_r($_POST);
		// 	print_r($first_day);
		// 	print_r($last_day);
		// 	// print_r($recordings);
		// echo '</pre>';
		break;
	case '4':
		$recordings = json_decode($_POST['recordings'], true);
		
		$dealList = getDealList('crm.deal.list', $recordings);
		
		foreach ($recordings as &$row) {
			foreach ($dealList as $rowtest) {
				if (strcasecmp($row[2], $rowtest['COMPANY_ID']) == 0) {
					$row[1] = $rowtest['ID'];
					$row[0] = $rowtest['STAGE_ID'];
				}				
			}
		}

		checkArr(1, $recordings, $recordingsNotFound);					// проверяем найдена или нет активная сделка в направлении Оплата за КГ по ID магазина

		if (count($recordingsNotFound) == 0) {
			$params = array('recordings' => $recordings);
		}else{
			$params = array('recordings' => $recordings, 'recordingsNotFound' => $recordingsNotFound);
		}
		
		echo(json_encode($params, JSON_UNESCAPED_UNICODE));

		// echo '<pre>';
		// 	echo 'Step 4';
		// echo '</pre>';
		// echo '<pre>';
		// 	// print_r($_POST);
		// 	print_r($recordings);
		// 	print_r($recordingsNotFound);
		// echo '</pre>';
		break;
	case '5':
		$temp = getAllDeals('crm.deal.list');

		echo '<pre>';
			echo 'Step 5';
			print_r($temp);
		echo '</pre>';
		break;			
	case '6':
		echo '<pre>';
			echo 'Step 6';
		echo '</pre>';
		break;	
	default:
		echo '<pre>';
			echo 'default';
		echo '</pre>';
		break;
}
?>