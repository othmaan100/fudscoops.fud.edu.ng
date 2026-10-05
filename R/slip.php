<?php

require_once '../config/vendor/autoload.php';
require_once('../config/classes/Payslip.php');



$payslip = new Payslip();
$mpdf = new \Mpdf\Mpdf();

$stylesheet = file_get_contents('../config/style.css');


$month = $_POST['month'];
$year = $_POST['year'];
$legacy = $_POST['legacy'];
$month_name = $payslip->getMonthName($month);
$slip = trim($payslip->getPayslip($legacy,$month,$year));

if($slip != -1){
    $mpdf->SetTitle('IPPIS - FUD PAYSLIP');
    $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
    $slip = mb_convert_encoding($slip, 'UTF-8', 'UTF-8');
    $mpdf->WriteHTML($slip);
    $mpdf->Output($legacy.'_PAYSLIP_'.$month_name.'_'.$year.'.pdf', 'D');
}else{
    echo "PAYSLIP NOT AVAILABLE FOR THIS MONTH";
}