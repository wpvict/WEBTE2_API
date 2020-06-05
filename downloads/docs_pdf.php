<?php
include_once '../include/PDF.php';

$pdf = new PDF();
// Column headings
$header = array("HTTP method", "API method", "Parameters", "Response", "Description");

include_once "../config/db_config.php";
include_once "../include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
$data = $db->get_docs();

// Data loading
$pdf->SetFont('Arial', '', 8);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();
$pdf->DocsTable($header, $data);

$pdf->Output();

 ?>
