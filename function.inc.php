<?php
require_once 'crest.php';


/**
* Get first and last day week
* @param $week_number - week number
* @param $year - year
* @return array
*/
function weekToDay(int $week_number = 1, int $year = 2011){	

	$first_day = date('d.m.Y', $week_number * 7 * 86400 + strtotime('1.1.' . $year) - date('w', strtotime('1.1.' . $year)) * 86400 + 86400);
	$last_day = date('d.m.Y', ($week_number + 1) * 7 * 86400 + strtotime('1.1.' . $year) - date('w', strtotime('1.1.' . $year)) * 86400);

	for ($i=1;$i<8;$i++){
  		$day[$i]=date('d.m.Y', $week_number * 7 * 86400 + strtotime('1.1.' . $year) - date('w', strtotime('1.1.' . $year)) * 86400 + 86400*$i);
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
			$recordings[$i-1][0] = 0;
			$recordings[$i-1][1] = 0;
			$recordings[$i-1][2] = 0;
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

/**
* searches for 0 in an array string and moves the found string to a new array before deleting it from the search array
* @param $number - number key in row array
* @param $auto - the array in which we will search
* @param $manual - the array to which we will transfer the found
* @return 0
*/
function checkArr(int $number, array &$auto, array &$manual){      // функция для проверки массива
    $i = 0;
    $arrSplice = [];
    foreach ($auto as &$row){             // перебираем массив что бы убедиться что все ID магазина найдены
        if ($row[$number] == 0) {
            array_push($manual, $row);    // если ID не найден то переносим магазин в ручной разбор
            array_push($arrSplice, $i);         // записываем индекс документа в котором нет ID  магазина   
        }
        $i++;
    }

    for ($i=0; $i < count($arrSplice); $i++) {  // в цикле перебираем и удаляем те документы по котором не смогли найти ID  магазина
        unset($auto[$arrSplice[$i]]);
    }
        
    $auto = array_values($auto);                // переиндексируем массив что бы индексы были по парядку без дыр на тот случай если захотим идти циклом по индексам 
}

/**
* Get id everyone company
* @param $method - Rest API request method 
* @return array
*/
function getBigData(string $method = 'crm.company.list'){

    /***********************************************/
    $params = [
        'filter' => [
            '>UF_CRM_1594794891' => 0    // Внутренний номер магазина боьше нуля
        ],
        'select' => [
            'ID',                              // ID магазина
            'UF_CRM_1594794891'                // Внутренний номер магазина
        ],  
        'start' => 0
    ];

    $result = CRest::call($method, $params); // Делаем запрос что бы понять сколько записей нам надо будет вытянуть
    while($result['error']=="QUERY_LIMIT_EXCEEDED"){
        sleep(1);
        $result = CRest::callBatch($arData);
        if ($result['error']<>"QUERY_LIMIT_EXCEEDED"){break;}
    } 
    $total = $result['total'];        // Всего записей в выборке
    $calls = ceil($total / 50);       // Сколько запросов надо сделать
    $current_call = 0;                // Номер текущего запроса
    $call_count = 0;                  // Счетчик вызовов для соблюдения условия не больше 2-х запросов в секунду

    sleep(1);                         // Делаем паузу перед основной работай  

    $arData = array();                // Массив для вызова callBatch
    $result = array();                // Массив для результатов вызова callBatch
    $totalResult = array();           // Массив с финальными данными

    /***********Цыкл формирования пакета запросов и выполнение их *********/
    do {
        $current_call++;

        $temp = [                                   // Собираем запрос
            'method' => $method,
            'params' => [ 
                'filter' => [
                    '>UF_CRM_1594794891' => 0    // Внутренний номер магазина боьше нуля
                ],
                'select' => [
                    'ID',                              // ID магазина
                    'UF_CRM_1594794891'                // Внутренний номер магазина
                ],  
                'start' => ($current_call - 1) * 50
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
                    array_push($totalResult, $value);           // и сохраняем каждый елемент в totalResult
                }            
            }
            $arData = [];                                       // Очишаем массив параметров arData для callBatch
        }
    } while ($current_call < $calls);                           // Проверяем условие что текущих вызовов меньще чем надо сделать всего


    return $totalResult;
}

/**
* Update company field mass and priod
* @param $update - array mass and ID company
* @param $first_day - first day of the period
* @param $last_day - last day of the period
* @return 0
*/
function updateCompanyFiled(array $update, string $first_day = '01.01.1900', string $last_day = '07.01.1900'){
    $total = count($update);            // Всего записей в файле
    $calls = $total;                    // Сколько запросов надо сделать
    $current_call = 0;                  // Номер текущего запроса
    $call_count = 0;                    // Счетчик вызовов для соблюдения условия не больше 2-х запросов в секунду

    $arData = array();                  // Массив для вызова callBatch
    $result = array();                  // Массив для результатов вызова callBatch

    /***********Цыкл формирования пакета запросов и выполнение их *********/
    do {
        $current_call++;

        $temp = [                                   // Собираем запрос
            'method' => 'crm.company.update',
            'params' => [ 
                'ID' => $update[$current_call-1][2],
                'fields' => [
                    'UF_CRM_1614603075' => $update[$current_call-1][4], //Перевес в Кг
                    'UF_CRM_1619766058' => ' c '.$first_day.' по '.$last_day, //Период за который перевес
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
            $arData = [];                                       // Очишаем массив параметров arData для callBatch
        }

    } while ($current_call < $calls);                           // Проверяем условие что текущих вызовов меньще чем надо сделать всего
}

/**
* Get a list of deals by company id
* @param $method - Rest API request method 
* @param $arCompanyId - array company id
* @return 0
*/
function getDealList(string $method = 'crm.deal.list', array $arCompanyId){            // функция получения ID седлок по ID магазина и сумме поступишвих денег
    $total = count($arCompanyId);     // Всего записей в выборке
    $calls = $total;                  // Сколько запросов надо сделать
    $current_call = 0;                // Номер текущего запроса
    $call_count = 0;                  // Счетчик вызовов для соблюдения условия не больше 2-х запросов в секунду

    sleep(1);                         // Делаем паузу перед основной работай  

    $arData = array();                // Массив для вызова callBatch
    $result = array();                // Массив для результатов вызова callBatch
    $totalResultDeal = array();     // Массив всех выбранных магазинов

    /***********Цыкл формирования пакета запросов и выполнение их *********/
    do {
        $current_call++;
        $temp = [                                   // Собираем запрос
            'method' => $method,
            'params' => [ 
                'filter' => [
                    'COMPANY_ID' => $arCompanyId[$current_call-1][2],     // ID магазина
                    'CLOSED' => 'N',                                       // Сделка не закрыта
                    'CATEGORY_ID' => 12,
                    '!STAGE_ID' => 'C12:6',                                   
                    'STAGE_SEMANTIC_ID' => 'P'  //   P - промежуточная стадия, S - успешная стадия, F - провальная стадия (стадии).
                ],
                'select' => [
                    'ID',                              // ID сделки
                    'STAGE_ID',                        // Стадия сделки
                    'COMPANY_ID',                      // ID магазина
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
            
            foreach ($resultTemp as $deal){                  // Перебираем массив что бы 
                foreach ($deal as $value) {                  // удобно было с ним работать в дальнейшем
                    array_push($totalResultDeal, $value);    // и сохраняем каждый елемент в totalResult
                }            
            }
            $arData = [];                                       // Очишаем массив параметров arData для callBatch
        }
    } while ($current_call < $calls);                           // Проверяем условие что текущих вызовов меньще чем надо сделать всего


    return $totalResultDeal;
}

/**
* Get id everyone deal
* @param $method - Rest API request method 
* @return 0
*/
function issueAnInvoice(){

}

/**
* Get list everyone deal
* @param $method - Rest API request method 
* @return array
*/
function getAllDeals(string $method = 'crm.deal.list'){

    /***********************************************/
    $params = [
        'filter' => [
            'CLOSED' => 'N',                                       // Сделка не закрыта
            'CATEGORY_ID' => 12,
            '!STAGE_ID' => 'C12:6',                                   
            'STAGE_SEMANTIC_ID' => 'P'  //   P - промежуточная стадия, S - успешная стадия, F - провальная стадия (стадии).
        ],
        'select' => [
            'ID',                              // ID сделки
            'STAGE_ID',
            'COMPANY_ID',                      // ID магазина                
        ],  
        'start' => 0
    ];

    $result = CRest::call($method, $params); // Делаем запрос что бы понять сколько записей нам надо будет вытянуть
    while($result['error']=="QUERY_LIMIT_EXCEEDED"){
        sleep(1);
        $result = CRest::callBatch($arData);
        if ($result['error']<>"QUERY_LIMIT_EXCEEDED"){break;}
    } 
    $total = $result['total'];        // Всего записей в выборке
    $calls = ceil($total / 50);       // Сколько запросов надо сделать
    $current_call = 0;                // Номер текущего запроса
    $call_count = 0;                  // Счетчик вызовов для соблюдения условия не больше 2-х запросов в секунду

    sleep(1);                         // Делаем паузу перед основной работай  

    $arData = array();                // Массив для вызова callBatch
    $result = array();                // Массив для результатов вызова callBatch
    $totalResult = array();           // Массив с финальными данными

    /***********Цыкл формирования пакета запросов и выполнение их *********/
    do {
        $current_call++;

        $temp = [                                   // Собираем запрос
            'method' => $method,
            'params' => [ 
                'filter' => [
                    'CLOSED' => 'N',                                       // Сделка не закрыта
                    'CATEGORY_ID' => 12,
                    '!STAGE_ID' => 'C12:6',                                   
                    'STAGE_SEMANTIC_ID' => 'P'  //   P - промежуточная стадия, S - успешная стадия, F - провальная стадия (стадии).
                ],
                'select' => [
                    'ID',                              // ID сделки
                    'STAGE_ID',
                    'COMPANY_ID',                      // ID магазина                       
                ],  
                'start' => ($current_call - 1) * 50
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
                    array_push($totalResult, $value);           // и сохраняем каждый елемент в totalResult
                }            
            }
            $arData = [];                                       // Очишаем массив параметров arData для callBatch
        }
    } while ($current_call < $calls);                           // Проверяем условие что текущих вызовов меньще чем надо сделать всего


    return $totalResult;
}

/**
* searches for 1 in an array string and moves the found string to a new array before deleting it from the search array
* @param $number - number key in row array
* @param $auto - the array in which we will search
* @param $manual - the array to which we will transfer the found
* @return 0
*/
function checkArr2(int $number, array &$auto, array &$manual){      // функция для проверки массива
    $i = 0;
    $arrSplice = [];
    foreach ($auto as &$row){             // перебираем массив 
        if ($row[$number] != 0) {
            array_push($manual, $row);    
            array_push($arrSplice, $i);         // записываем индекс документа 
        }
        $i++;
    }

    for ($i=0; $i < count($arrSplice); $i++) {  // в цикле перебираем и удаляем  документы 
        unset($auto[$arrSplice[$i]]);
    }
        
    $auto = array_values($auto);                // переиндексируем массив что бы индексы были по парядку без дыр на тот случай если захотим идти циклом по индексам 
}


/**
* Write CSV file
* @param $method - Rest API request method 
* @return 0
*/
function getCSV(array $data, string $name = '', &$output, string $pattern = '1'){
    $temp = [];
    $temp2 = [];
    if ((int)$pattern == 1) {
        fputcsv($output, array('Внутренний номер', 'Вес за период'), ';');

        foreach ($data as $value) {
            $temp2[0] = $value[3];
            $temp2[1] = $value[4];
            array_push($temp, $temp2);
        }
    }
    
    if ((int)$pattern == 2) {
        fputcsv($output, array('ID Сделки', 'ID магазина', 'Внутренний номер', 'Вес за период'), ';');
        
        foreach ($data as $value) {
            $temp2[0] = $value[1];
            $temp2[1] = $value[2];
            $temp2[2] = $value[3];
            $temp2[3] = $value[4];
            array_push($temp, $temp2);
        }
    }
    
    if ((int)$pattern == 3) {
        fputcsv($output, array('ID Сделки', 'Стадия', 'ID магазина',), ';');
        
        $temp = $data;
    }

    foreach ($temp as $value) {
        fputcsv($output, $value, ";");
    }
}
?>