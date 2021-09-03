<?php
require_once 'crest.php';


function weeakToDay(int $week_number = 1, int $year = 2011){
	$week_number = 18;
	$year = 2011;

	$first_day = date('d/m/Y', $week_number * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400);
	$last_day = date('d/m/Y', ($week_number + 1) * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400);

	// если необходимо вернуть все даты недели
	for ($i=1;$i<8;$i++){
  		$day[$i]=date('d/m/Y', $week_number * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400*$i);
  	}

  	return $first_day, $last_day;
}
?>