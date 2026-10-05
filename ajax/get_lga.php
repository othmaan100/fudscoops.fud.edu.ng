<?php
    
        require_once '../classes/DB.php';
        require_once '../classes/View.php';
        $db = new DB();
        $view = new View();
        $state = $db->cleanData($_POST['state']);

        echo $view->loadlga($state);
        
    
?>
        