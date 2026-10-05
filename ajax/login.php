<?php
    if(isset($_SESSION))
        session_destroy();
        require_once '../config/classes/DB.php';
        require_once '../config/classes/User.php';
        $db = new DB();
        $user = new User();
        $password = $db->cleanData($_POST['password']);
        $username = $db->cleanData($_POST['username']);

        $login = $user->login($username, $password);
        if($login >= 0){
            $_SESSION['username'] = $username;
            echo $login;
        }else
            echo -1;
            
        //var_dump($login);
    
?>
        