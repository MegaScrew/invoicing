<?php
require_once 'function.inc.php';

echo '<pre>';
	print_r($_FILES);
echo '</pre>';

$yearWeek = explode("-W", $_POST['week']);
$startEndWeek = weekToDay((int)$yearWeek[1], (int)$yearWeek[0]);

echo '<pre>';
	print_r($startEndWeek);
echo '</pre>';

?>