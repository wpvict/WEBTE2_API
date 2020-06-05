<?php

include_once "../config/db_config.php";
include_once "../include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);

$data = $db->get_log();
$headers = array('ID', 'Commands', 'Success', 'Error', 'Date');

$fh = fopen("log.csv", 'w');

$result = [];
$result[] = $headers;
foreach($data as $line){
  $line[1] = str_replace("\n", "", $line[1]);
  $line[1] = str_replace("  ", "", $line[1]);

  $result[] = array($line[0], "\"{$line[1]}\"", $line[2], $line[3], $line[4]);
}

foreach($result as $line){
  fputcsv($fh, $line);
}

if(fclose($fh)){
  header("Location: ./log.csv");
}

 ?>
