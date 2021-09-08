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
function readFile(array $file){
	$recordings = [];
	$i = 0;
	$uploadfile = basename($file[0]['name']);
	
	if (($handle = fopen($uploadfile, "r")) !== FALSE) {
		while (($row = stream_get_line($handle, 1024 * 1024, "\n")) !== false) {
			$columns = explode(';', $row);                // разбиваем строку на две части по символу ;

			$recordings[$i][0] = $columns[0];
			$recordings[$i][1] = $columns[1];
			$recordings[$i][2] = $columns[2];
			$recordings[$i][3] = $columns[3];
			$recordings[$i][4] = $columns[4];
			$i++;
		}

		fclose($handle);
	}
	unlink($uploadfile);

	return array('recordings' => $recordings);
}
?>