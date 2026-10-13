<?php
/**
 * Created by PhpStorm.
 * User: freshsalis
 * Date: 9/16/2017
 * Time: 2:14 PM
 */
class DB{

        public $con;

        // Database logins tried in order; the first that connects is used. The same file therefore
        // works on the live server and on a local XAMPP copy, and can be deployed safely.
        // If the live login ever differs, put it in config/db_config.php (not in git, not deployed):
        //   <?php return ['host' => 'localhost', 'dbname' => '...', 'username' => '...', 'password' => '...'];
        private static function credentials()
        {
            $list = [];
            $override = __DIR__ . '/../db_config.php';
            if (is_file($override)) {
                $custom = include $override;
                if (is_array($custom)) {
                    $list[] = $custom;
                }
            }
            // Live server
            $list[] = ['host' => 'localhost', 'dbname' => 'fuded535_fudscoops', 'username' => 'fuded535_hrms', 'password' => 'hrms@fud'];
            // Local XAMPP
            $list[] = ['host' => 'localhost', 'dbname' => 'fuded535_fudscoops', 'username' => 'root', 'password' => ''];
            return $list;
        }

        // Index of the login that worked, so later connections in the same request do not retry failed ones
        private static $working = null;

       public  function getConnection()
        {
            $list = self::credentials();
            $order = self::$working === null ? array_keys($list) : array_merge([self::$working], array_keys($list));
            $lastError = '';

            foreach (array_unique($order) as $i) {
                $c = $list[$i];
                try {
                    $this->con = new PDO("mysql:host={$c['host']};dbname={$c['dbname']}", $c['username'], $c['password']);
                    // set the PDO error mode to exception
                    $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$working = $i;
                    return $this->con;
                } catch (PDOException $e) {
                    $lastError = $e->getMessage();
                }
            }

            // No login worked: details go to the error log, not to the page
            error_log("DB connection failed (all logins tried): " . $lastError);
            echo "Connection failed. Please contact the system administrator.";
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


