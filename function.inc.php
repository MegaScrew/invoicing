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
?>