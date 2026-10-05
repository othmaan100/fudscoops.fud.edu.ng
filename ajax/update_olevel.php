<?php

require_once '../classes/DB.php';
require_once '../classes/Putme.php';
        $db = new DB();
        $putme = new Putme();
        $sitting = $db->cleanData($_POST['sitting']);
        $type = $db->cleanData($_POST['type']);
        $centerNo = $db->cleanData($_POST['centerno']);
        $centerName = $db->cleanData($_POST['centername']);
        $examNo = $db->cleanData($_POST['examno']);
        $year = $db->cleanData($_POST['year']);

        $sub1 = $db->cleanData($_POST['subject1']);
        $grade1 = $db->cleanData($_POST['grade1']);
        $sub2 = $db->cleanData($_POST['subject2']);
        $grade2 = $db->cleanData($_POST['grade2']);
        $sub3 = $db->cleanData($_POST['subject3']);
        $grade3 = $db->cleanData($_POST['grade3']);
        $sub4 = $db->cleanData($_POST['subject4']);
        $grade4 = $db->cleanData($_POST['grade4']);
        $sub5 = $db->cleanData($_POST['subject5']);
        $grade5 = $db->cleanData($_POST['grade5']);
        $sub6 = $db->cleanData($_POST['subject6']);
        $grade6 = $db->cleanData($_POST['grade6']);
        $sub7 = $db->cleanData($_POST['subject7']);
        $grade7 = $db->cleanData($_POST['grade7']);
        $sub8 = $db->cleanData($_POST['subject8']);
        $grade8 = $db->cleanData($_POST['grade8']);
        $sub9 = $db->cleanData($_POST['subject9']);
        $grade9 = $db->cleanData($_POST['grade9']);

        $jambNo = $db->cleanData($_POST['jamb']);
        echo $putme-> updateOLevel($jambNo,$sitting,$type,$centerNo,$centerName,$examNo,$year,$sub1,$grade1,$sub2,$grade2,$sub3,$grade3,$sub4,$grade4,$sub5,$grade5,$sub6,$grade6,$sub7,$grade7,$sub8,$grade8,$sub9,$grade9)
?>
        