<?php
    
        require_once '../classes/DB.php';
        require_once '../classes/View.php';
        $db = new DB();
        $view = new View();
        $country = $db->cleanData($_POST['country']);

        echo $view->loadStates($country);
        
    
?>
        