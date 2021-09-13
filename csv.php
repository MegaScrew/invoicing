<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename = '.$_POST['year'].'-'.date('y-m-d').'.csv');
require_once 'function.inc.php';

$output = fopen("php://output", "w");

$name = $_POST['name'];
$name = $name.'_data';
$data = json_decode($_POST['check'], true)[$name];

getCSV($data, $name, $output);

fclose($output);
?>