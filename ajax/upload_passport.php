<?php

require_once '../classes/DB.php';
require_once '../classes/Putme.php';
        $db = new DB();
        $putme = new Putme();
        if (isset($_FILES['file'])) {
                $file = $db->cleanData($_FILES['file']) ;
                $jambNo = $db->cleanData($_POST['jamb']);
                
                
                echo $putme->uploadPassport($jambNo);
        }else{
                echo -1;
        }
        
?>
        