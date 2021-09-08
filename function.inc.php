<?php
require_once 'crest.php';


/**
* Get first and last day week
* @param $week_number - week number
* @param $year - year
* @return array
*/
function weekToDay(int $week_number = 1, int $year = 2011){	

	$first_day = date('d/m/Y', $week_number * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400);
	$last_day = date('d/m/Y', ($week_number + 1) * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400);

	for ($i=1;$i<8;$i++){
  		$day[$i]=date('d/m/Y', $week_number * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400*$i);
  	}

  	return array('first_day' => $first_day, 'last_day' => $last_day, 'days' => $day);
}

/**
* Reading file data
* @param $file - array file from a web form
* @return array
*/
function myReadFile(array $file){	// функция чтения файла
	$recordings = [];
	$i = 0;
	$uploadfile = basename($file['uploaded_file']['name']);
	
	if (move_uploaded_file($file['uploaded_file']['tmp_name'], $uploadfile)) {
	    $result = "Файл был успешно загружен на сервер!";
	} else {
	    $result = "Возможная атака с помощью файловой загрузки!";
	    return array('Error' => $result);
	}

	if (($handle = fopen($uploadfile, "r")) !== FALSE) {
		while (($row = stream_get_line($handle, 1024 * 1024, "\n")) !== false) {
			$columns = explode(';', $row);                // разбиваем строку на две части по символу ;
            if ($i == 0) {
                $i++; 
                continue;
            }
			$recordings[$i-1][0] = $columns[0];
			$recordings[$i-1][1] = $columns[1];
			$recordings[$i-1][2] = $columns[2];
			$recordings[$i-1][3] = $columns[3];
			$recordings[$i-1][4] = $columns[4];
			$i++;
		}

		fclose($handle);
	}
	unlink($uploadfile);

	return array('recordings' => $recordings);
}

/**
* Get id company by an internal number
* @param $method - Rest API request method 
* @param $arInnerNumber - array of internal company numbers
* @return array
*/
function getCompanyList(string $method = 'crm.company.list', array $arInnerNumber) {			// функция получения ID магазина по его внутреннему номеру.
			
    $total = count($arInnerNumber);   // Всего записей в выборке
    $calls = $total;       			  // Сколько запросов надо сделать
    $current_call = 0;                // Номер текущего запроса
    $call_count = 0;                  // Счетчик вызовов для соблюдения условия не больше 2-х запросов в секунду

    sleep(1);                         // Делаем паузу перед основной работай  

    $arData = array();                // Массив для вызова callBatch
    $result = array();                // Массив для результатов вызова callBatch
    $totalResultCompany = array();    // Массив всех выбранных магазинов

    /***********Цыкл формирования пакета запросов и выполнение их *********/
    do {
        $current_call++;

        $temp = [                                   // Собираем запрос
            'method' => $method,
            'params' => [ 
                'filter' => [
                    'UF_CRM_1594794891' => $arInnerNumber[$current_call-1][3]    // Внутренний номер магазина
                ],
                'select' => [
                    'ID',                              // ID магазина
                    'UF_CRM_1594794891'                // Внутренний номер магазина
                ]
            ]
        ];

        array_push($arData, $temp);                 // Сохраняем собранный запрос в массив параметров arData для передачи его в callBatch

        if ((count($arData) == 50) || ($current_call == $calls)) {  // Если в массиве параметров arData 50 запросов или это последний запрос
            
            $call_count++;                                      // При каждом вызове увеличиваем счетчик
            if ($call_count == 2) {                             // Проверяем счетчик вызовов call_count
                sleep(1);                                       // Если да то делаем паузу 1 сек
                $call_count = 0;                                // Сбрасываем счетчик
            }


            $result = CRest::callBatch($arData);                // Вызываем callBatch
            
            while($result['error']=="QUERY_LIMIT_EXCEEDED"){
				sleep(1);
				$result = CRest::callBatch($arData);
				if ($result['error']<>"QUERY_LIMIT_EXCEEDED"){break;}
			}

            $resultTemp = $result['result']['result'];          // Убираем лишнее вложение в массиве

            foreach ($resultTemp as $company){                  // Перебираем массив что бы 
                foreach ($company as $value) {                  // удобно было с ним работать в дальнейшем
                    array_push($totalResultCompany, $value);    // и сохраняем каждый елемент в totalResult
                }            
            }
            $arData = [];                                       // Очишаем массив параметров arData для callBatch
        }
    } while ($current_call < $calls);                           // Проверяем условие что текущих вызовов меньще чем надо сделать всего


    return $totalResultCompany;
}
?>