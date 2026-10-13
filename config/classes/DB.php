<?php
/**
 * Created by PhpStorm.
 * User: freshsalis
 * Date: 9/16/2017
 * Time: 2:14 PM
 */
class DB{


        public $con;
       public  function getConnection()
        {
            $host = 'localhost';
            $dbname = 'fuded535_fudscoops';
            $username = 'root';
            $password = '';
         
            try {
                $this->con = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception
                $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // echo "Connected successfully";
                return $this->con;
              } catch(PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
              }
        }

        public static function cleanData($str) {
            $str = @trim($str);
            // get_magic_quotes_gpc() was removed in PHP 8 (it always returned false since PHP 5.4)
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $str = stripslashes($str);
            }
            return $str; //mysql_real_escape_string($str);
        }



    }


