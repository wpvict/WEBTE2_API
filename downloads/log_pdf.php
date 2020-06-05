<?php
include_once '../include/PDF.php';

$pdf = new PDF();
// Column headings
$header = array('ID', 'Date', 'Success', 'Error', 'Commands');

include_once "../config/db_config.php";
include_once "../include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
$data = $db->get_log();

// Data loading
$pdf->SetFont('Arial', '', 8);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();
$pdf->Table($header,$data);

$pdf->Output();

 ?>
