 <?php
 
            $host = 'localhost';
            $dbname = 'fuded535_hrms';
            $username = 'fuded535_hrms_user';
            $password = 'hrms@fud';
         
        try {
            $con = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected to $dbname at $host successfully.";
            return $con;
        } catch (PDOException $pe) {
            die("Could not connect to the database :" . $pe->getMessage());
        }