<?php

require_once '../classes/DB.php';
require_once '../classes/Putme.php';
        $db = new DB();
        $putme = new Putme();
        $school = $db->cleanData($_POST['institution']);
        $type = $db->cleanData($_POST['type']);
         $from = $db->cleanData($_POST['start_year']);
        $to = $db->cleanData($_POST['end_year']);
        $course = $db->cleanData($_POST['course']);
        $grade = $db->cleanData($_POST['grade']);
        $cgpa = $db->cleanData($_POST['cgpa']);
        $jambNo = $db->cleanData($_POST['jamb']);

        echo $putme-> updateALevel($jambNo,$school,$type,$from,$to,$course,$grade,$cgpa);
?>
        