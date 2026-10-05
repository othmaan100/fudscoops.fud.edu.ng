<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once('./classes/Payslip.php');

$payslip = new Payslip();
$mpdf = new \Mpdf\Mpdf();
$font = file_get_contents('font-style.css');
$stylesheet = file_get_contents('style.css');

$mpdf->WriteHTML($font,\Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($payslip->getPayslip('SPR2918','DECEMBER',2020));
$mpdf->Output();