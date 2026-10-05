<?PHP
//Set the Content-Type and Content-Disposition headers to force the download.
	header('Content-Type: application/excel');
	header('Content-Disposition: attachment; filename="share_staff_list.csv"');
	
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/DB.php');
	
	 $cols	= array("Staff No","Shares Amount");
	 $fileName = 'share_staff_list.csv';

	
	  

        

  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.

  function cleanData2(&$str)
  {
    // escape tab characters
    $str = preg_replace("/\t/", "\\t", $str);

    // escape new lines
    $str = preg_replace("/\r?\n/", "\\n", $str);

    // convert 't' and 'f' to boolean values
    if($str == 't') $str = 'TRUE';
    if($str == 'f') $str = 'FALSE';

    // force certain number/date formats to be imported as strings
    if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
    }

    // escape fields that include double quotes
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

 	$db = new DB(); 	


	$con = $db->getConnection();
	$q = "
	    SELECT member_id, proposed_monthly_savings 
	    FROM fudscoops_member
        ORDER BY member_id
";
	$sn=0;
	$stm = $con->prepare($q);
	$stm->execute();
	
	//Open up a file pointer
	$fp = fopen('php://output', 'w');

	//Start off by writing the column names to the file.
	fputcsv($fp, $cols);

	//Then, loop through the rows and write them to the CSV file.
	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		fputcsv($fp, $row);
	}

//Close the file pointer.
fclose($fp);
  exit;
?>