<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename = '.$_POST['name'].' c '.$_POST['first_day'].'po'.$_POST['last_day'].'.csv');
require_once 'function.inc.php';

$output = fopen("php://output", "w");

$pattern = $_POST['pattern'];
$name = $_POST['name'];
$name = $name.'_data';
$data = json_decode($_POST['data'], true);

getCSV($data, $name, $output, $pattern);

fclose($output);
?>