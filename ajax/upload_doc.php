<?php

require_once '../classes/DB.php';
require_once '../classes/Putme.php';
        $db = new DB();
        $putme = new Putme();
        if (isset($_FILES['file'])) {
                $file = $db->cleanData($_FILES['file']) ;
                $jambNo = $db->cleanData($_POST['jamb']);
                $type = $db->cleanData($_POST['type']);
                $role = $db->cleanData($_POST['role']);
                if($type == 'docO1')
                        $sitting = 1;
                elseif($type == 'docO2')
                        $sitting = 2;
                else {
                        $sitting = 0;
                }
                
                echo $putme->uploadDoc($jambNo,$role,$sitting);
        }else{
                echo -1;
        }
        
?>
        