<?php
require_once 'function.inc.php';

echo '<pre>';
	print_r($_FILES);
echo '</pre>';

$yearWeek = explode("-W", $_POST['week']);
$startEndWeek = weekToDay((int)$yearWeek[1], (int)$yearWeek[0]);

$file = myReadFile($_FILES);

echo '<pre>';
	print_r($startEndWeek);
echo '</pre>';

echo '<pre>';
	print_r($file);
echo '</pre>';

?>