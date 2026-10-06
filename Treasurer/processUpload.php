<?php

	require_once('../config/classes/DB.php');
	require_once('../config/classes/User.php');
	require_once('../config/classes/Payslip.php');
	$db = new DB();
	$user = new User();
	$payslip = new Payslip();
	$con = $db->getConnection();

	$directory = dirname(__FILE__) . '/classes/pdfparser/';
	include $directory . 'autoload.php';
	$parser = new \Smalot\PdfParser\Parser();

$success = 0;
		$unsuccessful = 0;$not_exist=0;
		
	if (isset($_FILES['file'])) {
		// $file = $_FILES['file'];
		$source_file = $_FILES['file']['tmp_name'];
		 $dest_file = "./".$_FILES['file']['name'];
		move_uploaded_file( $source_file, $dest_file )
			or die ("Error!!");
		$file = dirname(__FILE__) . '/'.$dest_file;
		$pathInfo = pathinfo($file);
		$extension = $pathInfo['extension'];

		// echo $extension;
		$success = 0;
		$unsuccessful = 0;$not_exist=0;
		if ($extension =='pdf') {
			try {
				$test_encoding0 = 'test encoding: ' . mb_convert_encoding('hello world', 'UTF-8', 'Windows-1252');
			}
			catch (Exception $e) {
				echo 'You need to install php-mbstring to use the parser.';
				exit();
			}
			//$pdf    = $parser->parseFile($file);
			//$text = $pdf->getText();
	
			//$mycontent = fread($myfile,filesize("newfile.txt"));
			//try {
				//code...
				$document = $parser->parseFile($file);
				$pages    = $document->getPages();
				// $page     = $pages[4];
				$content  = $document->getText();
				$out      = $content;
				//echo nl2br($out);
				$yeee = '<pre>' . $out . '</pre>';
				//echo trim(substr_count( $yeee, "________________________________________________" );
		
				$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
				fwrite($myfile, $yeee);
				fclose($myfile);
		

				 $txt_file    = file_get_contents('newfile.txt');
				// echo strpos($txt_file,'FED UNI DUTSE MUSLIM FORUM N  500 ')."<br>";
				$txt_file = str_replace('FORUM N  100 ', 'FORUM N  100.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  150 ', 'FORUM N  150.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  200 ', 'FORUM N  200.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  250 ', 'FORUM N  250.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  300 ', 'FORUM N  300.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  350 ', 'FORUM N  350.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  400 ', 'FORUM N  400.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  450 ', 'FORUM N  450.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  500 ', 'FORUM N  500.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  550 ', 'FORUM N  550.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  600 ', 'FORUM N  600.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  650 ', 'FORUM N  650.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  700 ', 'FORUM N  700.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  750 ', 'FORUM N  750.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  800 ', 'FORUM N  800.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  850 ', 'FORUM N  850.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  900 ', 'FORUM N  900.00 ',$txt_file);
				$txt_file = str_replace('FORUM N  950 ', 'FORUM N  950.00 ',$txt_file);
				// echo $txt_file;
				// $txt_file    = $content;
				// echo $txt_file;
				// die();
				$data=explode("IPPIS TERTIARY INSTITUTIONS",$txt_file);
				// $db = new DB();
				$r = '';
				
				for ($i=1; $i < count($data); $i++) { 
					$data[$i] = str_replace('_'," ",$data[$i]);
		
						// echo $data[$i]."<br><br>";
						
						$month_year = trim(substr($data[$i],(strlen('EMPLOYEE PAYSLIP')+1),(strpos($data[$i], 'Employee Name')-(strlen('EMPLOYEE PAYSLIP')+1))));
						list($month,$year) = explode(" ",$month_year);
						$month_id = $payslip->getMonthId($month);
						
						// get name
						$start1 = strpos($data[$i], 'Employee Name:')+strlen('Employee Name:'); 
						$stop1 = strpos($data[$i], 'Grade:') - (strpos($data[$i], 'Employee Name:')+strlen('Employee Name:'));
						$name = trim(substr($data[$i],$start1,$stop1));
		
						// get grade level
						$start2 = strpos($data[$i], 'Grade:')+strlen('Grade:'); 
						$stop2 = strpos($data[$i], 'IPPIS Number:') - (strpos($data[$i], 'Grade:')+strlen('Grade:'));
						$level = trim(substr($data[$i],$start2,$stop2));
		
						// get IPPIS NUMBER
						$start3 = strpos($data[$i], 'IPPIS Number:')+strlen('IPPIS Number:'); 
						$stop3 = strpos($data[$i], 'Step:') - (strpos($data[$i], 'IPPIS Number:')+strlen('IPPIS Number:'));
						$ippis = trim(substr($data[$i],$start3,$stop3));
		
						// get GL STEP
						$start4 = strpos($data[$i], 'Step:')+strlen('Step:'); 
						$stop4 = strpos($data[$i], 'Legacy ID:') - (strpos($data[$i], 'Step:')+strlen('Step:'));
						$step = trim(substr($data[$i],$start4,$stop4));
		
						// get LEGACY ID
						$start5 = strpos($data[$i], 'Legacy ID:')+strlen('Legacy ID:'); 
						$stop5 = strpos($data[$i], 'Gender:') - (strpos($data[$i], 'Legacy ID:')+strlen('Legacy ID:'));
						$legacy = trim(substr($data[$i],$start5,$stop5));
		
						// echo ($legacy);
						
						// echo "<br/>";
						// get GENDER
						$start = strpos($data[$i], 'Gender:')+strlen('Gender:'); 
						$stop = strpos($data[$i], 'MDA/School/Command:') - (strpos($data[$i], 'Gender:')+strlen('Gender:'));
						$gender = trim(substr($data[$i],$start,$stop));
		
						// get ORGANIZATION
						$start = strpos($data[$i], 'MDA/School/Command:')+strlen('MDA/School/Command:'); 
						$stop = strpos($data[$i], 'Tax State:') - (strpos($data[$i], 'MDA/School/Command:')+strlen('MDA/School/Command:'));
						$org = trim(substr($data[$i],$start,$stop));
		
						// get tax state
						$start = strpos($data[$i], 'Tax State:')+strlen('Tax State:'); 
						$stop = strpos($data[$i], 'Department:') - (strpos($data[$i], 'Tax State:')+strlen('Tax State:'));
						$state = trim(substr($data[$i],$start,$stop));
		
						// get dept
						$start = strpos($data[$i], 'Department:')+strlen('Department:'); 
						$stop = strpos($data[$i], 'TIN:') - (strpos($data[$i], 'Department:')+strlen('Department:'));
						$dept = trim(substr($data[$i],$start,$stop));
		
						// get tin
						$start = strpos($data[$i], 'TIN:')+strlen('TIN:'); 
						$stop = strpos($data[$i], 'Location:') - (strpos($data[$i], 'TIN:')+strlen('TIN:'));
						$tin = trim(substr($data[$i],$start,$stop));
		
						// get location
						$start = strpos($data[$i], 'Location:')+strlen('Location:'); 
						$stop = strpos($data[$i], 'Date of Appointment:') - (strpos($data[$i], 'Location:')+strlen('Location:'));
						$location = trim(substr($data[$i],$start,$stop));
		
						// get date of apptmnt
						$start = strpos($data[$i], 'Date of Appointment:')+strlen('Date of Appointment:'); 
						$stop = strpos($data[$i], 'Job:') - (strpos($data[$i], 'Date of Appointment:')+strlen('Date of Appointment:'));
						$doa = trim(substr($data[$i],$start,$stop));
		
						$start = strpos($data[$i], 'Job:')+strlen('Job:'); 
						$stop = strpos($data[$i], 'Date of Birth:') - (strpos($data[$i], 'Job:')+strlen('Job:'));
						$job = trim(substr($data[$i],$start,$stop));
		
						// get date of birth
						$start = strpos($data[$i], 'Date of Birth:')+strlen('Date of Birth:'); 
						$stop = strpos($data[$i], 'Bank Information:') - (strpos($data[$i], 'Date of Birth:')+strlen('Date of Birth:'));
						$dob = trim(substr($data[$i],$start,$stop));
						
						// get union name
						//$start = strpos($data[$i], 'Union Name:')+strlen('Union Name:'); 
						//$stop = strpos($data[$i], 'Bank Information:') - (strpos($data[$i], 'Union Name:')+strlen('Union Name:'));
						//$union = trim(substr($data[$i],$start,$stop));
		
						// bank name
						$start = strpos($data[$i], 'Bank Name:')+strlen('Bank Name:'); 
						$stop = strpos($data[$i], 'PFA Name') - (strpos($data[$i], 'Bank Name:')+strlen('Bank Name:'));
						$bank = trim(substr($data[$i],$start,$stop));
		
						// pfa name
						$start = strpos($data[$i], 'PFA Name:')+strlen('PFA Name:'); 
						$stop = strpos($data[$i], 'Account Number') - (strpos($data[$i], 'PFA Name:')+strlen('PFA Name:'));
						$pfa = trim(substr($data[$i],$start,$stop));
		
						// account number
						$start = strpos($data[$i], 'Account Number:')+strlen('Account Number:'); 
						$stop = strpos($data[$i], 'Pension PIN') - (strpos($data[$i], 'Account Number:')+strlen('Account Number:'));
						$ac_no = trim(substr($data[$i],$start,$stop));
		
						// echo $legacy." ".$ac_no."<br/>";
						// pension pin
						$start = strpos($data[$i], 'Pension PIN:')+strlen('Pension PIN:'); 
						$stop = strpos($data[$i], 'Gross Earnings') - (strpos($data[$i], 'Pension PIN:')+strlen('Pension PIN:'));
						$pension_pin = trim(substr($data[$i],$start,$stop));
		
						//#################### UPLOAD TO DB #######################################
						$sp = trim($payslip->convertLegacy($legacy));
						$legacy = trim($payslip->cleanLegacy($legacy));
						
				// 		echo $legacy." ".$pfa."<br/>";
						
						if ($sp != -1) {
							$staffId =  $user->staffId($sp);
								
							// check if staff exist on employee table
							if ($staffId !=0) {
								
								// get static id
								$static_id = $payslip->isStaticExist($ippis);
								//check if fresh uploading for the first time
								if ($static_id == 0) {
									// echo "<h4>FRESH</H4>";
									# code...
									$q = "INSERT INTO ippis_static_record (legacy,ippis_no,tin,school,tax_state,employee_id,doa,dob_static,fullname)
										VALUES (:legacy,:ippis,:tin,:school,:tax,:employee,:doa,:dob,:name)
									";
									$stm = $con->prepare($q);
									$stm->bindParam(':legacy', $legacy, PDO::PARAM_STR);
									$stm->bindParam(':ippis', $ippis, PDO::PARAM_STR);
									$stm->bindParam(':tin', $tin, PDO::PARAM_STR);
									$stm->bindParam(':school', $org, PDO::PARAM_STR);
									$stm->bindParam(':employee', $staffId, PDO::PARAM_INT);
									$stm->bindParam(':tax', $state, PDO::PARAM_STR);
									$stm->bindParam(':doa', $doa, PDO::PARAM_STR);
									$stm->bindParam(':dob', $dob, PDO::PARAM_STR);
									$stm->bindParam(':name', $name, PDO::PARAM_STR);
									$stm->execute();
									if ($stm) {
										# code...
										$static_id = $con->lastInsertId();
										//insert non-static
										$q = "INSERT INTO ippis_nonstatic_record 
											(grade,step,job,bank_name,acctno,pfa,pfa_pin,dept,month,year,ippis_static_id)
											VALUES (:grade,:step,:job,:bank,:acct,:pfa,:pfa_pin,:dept,:month,:year,:ippis_id)
											";
											$stm = $con->prepare($q);
											$stm->bindParam(':grade', $level, PDO::PARAM_STR);
											$stm->bindParam(':step', $step, PDO::PARAM_STR);
											$stm->bindParam(':job', $job, PDO::PARAM_STR);
											$stm->bindParam(':bank', $bank, PDO::PARAM_STR);
											$stm->bindParam(':acct', $ac_no, PDO::PARAM_STR);
											$stm->bindParam(':pfa', $pfa, PDO::PARAM_STR);
											$stm->bindParam(':pfa_pin', $pension_pin, PDO::PARAM_STR);
											$stm->bindParam(':dept', $dept, PDO::PARAM_STR);
											$stm->bindParam(':month', $month_id, PDO::PARAM_STR);
											$stm->bindParam(':year', $year, PDO::PARAM_STR);
											//$stm->bindParam(':union', $union, PDO::PARAM_STR);
											$stm->bindParam(':ippis_id', $static_id, PDO::PARAM_STR);
											$stm->execute();
											$nonstatic_id = $con->lastInsertId();

											// Earnings
											$start = strpos($data[$i], 'Earnings Amount')+strlen('Earnings Amount')-1; 
											$stop = strpos($data[$i], 'Gross Deduction Information') - (strpos($data[$i], 'Earnings Amount')+strlen('Earnings Amount:'));
											$earnings = trim(substr($data[$i],$start,$stop));
											$list = explode(".",$earnings);
							
											for($k = 0;$k<count($list)-1; $k++){
												if ($i == 0) {
													$earning1 =$list[$k].".".trim(substr($list[$k+1],0,2));
													list($caption,$amount) = explode(" N ",$earning1);	
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);


													$qE = "INSERT INTO earning (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)
													";
													$stmE = $con->prepare($qE);
													$stmE->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmE->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmE->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmE->execute();							
												}else{
													$earning1 = trim(substr($list[$k],2).".".trim(substr($list[$k+1],0,2)));
													list($caption,$amount) = explode(" N ",$earning1);	
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);

													$qE = "INSERT INTO earning (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)
													";
													// echo $amount."<br/>";
													$stmE = $con->prepare($qE);
													$stmE->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmE->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmE->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmE->execute();							
												}
												
											}//end for k
									
											// DEDUCTIONS
											$start = strpos($data[$i], 'Deductions Amount')+strlen('Deductions Amount')-1; 
											$stop = strpos($data[$i], 'Summary') - (strpos($data[$i], 'Deductions Amount')+strlen('Deductions Amount:'));
											$deductions = trim(substr($data[$i],$start,$stop));
											$deductions_array = explode(".",$deductions);
											for($k = 0;$k<count($deductions_array)-1; $k++){
												if ($i == 0) {
													$deduction1 =$deductions_array[$k].".".trim(substr($deductions_array[$k+1],0,2));
													list($caption,$amount) = explode(" N ",$deduction1);
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);
													
													$qD = "INSERT INTO deduction (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)";
													$stmD = $con->prepare($qD);
													$stmD->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmD->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmD->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmD->execute();								
												}else{
													$deduction1 = trim(substr($deductions_array[$k],2).".".trim(substr($deductions_array[$k+1],0,2)));
													list($caption,$amount) = explode(" N ",$deduction1);
													$qD = "INSERT INTO deduction (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)";
													
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);
													$stmD = $con->prepare($qD);
													$stmD->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmD->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmD->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
												// 	echo $legacy." ".$amount." </br/>-<br/>";
													$stmD->execute();								
												}					
											}
											// INCOME TAX
											$start = strpos($data[$i], 'Income Tax')+strlen('Income Tax'); 
											$stop = strpos($data[$i], 'Total Gross Deductions') - (strpos($data[$i], 'Income Tax')+strlen('Income Tax:'));
											$income_tax = trim(substr($data[$i],$start,$stop));
											$income_tax = trim(str_replace("N","",$income_tax));
											$income_tax = trim(str_replace(" ","",$income_tax));
											$income_tax = trim(str_replace(",","",$income_tax));
											$income_tax = trim(str_replace("PoweredByIPPIS-SoftSuiteCOFIDETIAL","",$income_tax));
											// echo $income_tax."<br/>";

											$qI = "INSERT INTO ippis_income_tax (amount,row_nonstatic_id)
												VALUES (:amount,:nonId)
													";
													$stmI = $con->prepare($qI);
													$stmI->bindParam(':amount', $income_tax, PDO::PARAM_STR);
													$stmI->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													$stmI->execute();
									
													$success++;
									}
								}
								else{
									// echo "<h3>EXISTING</h3>";
									$nonstatic_id = $payslip->isNonStaticExist($static_id,$month,$year);
									//check if this month's payslip already exist
									if ($nonstatic_id ==0){
										//insert non-static
										$q = "INSERT INTO ippis_nonstatic_record 
											(grade,step,job,bank_name,acctno,pfa,pfa_pin,dept,month,year,ippis_static_id)
											VALUES (:grade,:step,:job,:bank,:acct,:pfa,:pfa_pin,:dept,:month,:year,:ippis_id)
											";
											$stm = $con->prepare($q);
											$stm->bindParam(':grade', $level, PDO::PARAM_STR);
											$stm->bindParam(':step', $step, PDO::PARAM_STR);
											$stm->bindParam(':job', $job, PDO::PARAM_STR);
											$stm->bindParam(':bank', $bank, PDO::PARAM_STR);
											$stm->bindParam(':acct', $ac_no, PDO::PARAM_STR);
											$stm->bindParam(':pfa', $pfa, PDO::PARAM_STR);
											$stm->bindParam(':pfa_pin', $pension_pin, PDO::PARAM_STR);
											$stm->bindParam(':dept', $dept, PDO::PARAM_STR);
											$stm->bindParam(':month', $month_id, PDO::PARAM_STR);
											$stm->bindParam(':year', $year, PDO::PARAM_STR);
											//$stm->bindParam(':union', $union, PDO::PARAM_STR);
											$stm->bindParam(':ippis_id', $static_id, PDO::PARAM_STR);
											$stm->execute();
											$nonstatic_id = $con->lastInsertId();

											// Earnings
											$start = strpos($data[$i], 'Earnings Amount')+strlen('Earnings Amount')-1; 
											$stop = strpos($data[$i], 'Gross Deduction Information') - (strpos($data[$i], 'Earnings Amount')+strlen('Earnings Amount:'));
											$earnings = trim(substr($data[$i],$start,$stop));
											$list = explode(".",$earnings);
							
											for($k = 0;$k<count($list)-1; $k++){
												if ($i == 0) {
													$earning1 =$list[$k].".".trim(substr($list[$k+1],0,2));
													list($caption,$amount) = explode(" N ",$earning1);	
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);


													$qE = "INSERT INTO earning (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)
													";
													$stmE = $con->prepare($qE);
													$stmE->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmE->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmE->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmE->execute();							
												}else{
													$earning1 = trim(substr($list[$k],2).".".trim(substr($list[$k+1],0,2)));
													list($caption,$amount) = explode(" N ",$earning1);	
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);

													$qE = "INSERT INTO earning (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)
													";
													// echo $amount."<br/>";
													$stmE = $con->prepare($qE);
													$stmE->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmE->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmE->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmE->execute();							
												}
												
											}//end for k
									
											// DEDUCTIONS
											$start = strpos($data[$i], 'Deductions Amount')+strlen('Deductions Amount')-1; 
											$stop = strpos($data[$i], 'Summary') - (strpos($data[$i], 'Deductions Amount')+strlen('Deductions Amount:'));
											$deductions = trim(substr($data[$i],$start,$stop));
											$deductions_array = explode(".",$deductions);
											for($k = 0;$k<count($deductions_array)-1; $k++){
												if ($i == 0) {
													$deduction1 =$deductions_array[$k].".".trim(substr($deductions_array[$k+1],0,2));
													list($caption,$amount) = explode(" N ",$deduction1);
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);
													
													$qD = "INSERT INTO deduction (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)";
													$stmD = $con->prepare($qD);
													$stmD->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmD->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmD->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													
													$stmD->execute();								
												}else{
													$deduction1 = trim(substr($deductions_array[$k],2).".".trim(substr($deductions_array[$k+1],0,2)));
													list($caption,$amount) = explode(" N ",$deduction1);
													$qD = "INSERT INTO deduction (caption,amount,row_nonstatic_id)
													VALUES (:name,:amount,:nonId)";
													$amount = str_replace(",","",trim($amount));
													$amount = number_format($amount,2);
													$stmD = $con->prepare($qD);
													$stmD->bindParam(':name', $caption, PDO::PARAM_STR);
													$stmD->bindParam(':amount', $amount, PDO::PARAM_STR);
													$stmD->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
												// 	echo $legacy." ".$amount." </br/>-<br/>";
													$stmD->execute();								
												}					
											}
											// INCOME TAX
											$start = strpos($data[$i], 'Income Tax')+strlen('Income Tax'); 
											$stop = strpos($data[$i], 'Total Gross Deductions') - (strpos($data[$i], 'Income Tax')+strlen('Income Tax:'));
											$income_tax = trim(substr($data[$i],$start,$stop));
											$income_tax = trim(str_replace("N","",$income_tax));
											$income_tax = trim(str_replace(" ","",$income_tax));
											$income_tax = trim(str_replace(",","",$income_tax));
											$income_tax = trim(str_replace("PoweredByIPPIS-SoftSuiteCOFIDETIAL","",$income_tax));
											// echo $income_tax."<br/>";

											$qI = "INSERT INTO ippis_income_tax (amount,row_nonstatic_id)
												VALUES (:amount,:nonId)
													";
													$stmI = $con->prepare($qI);
													$stmI->bindParam(':amount', $income_tax, PDO::PARAM_STR);
													$stmI->bindParam(':nonId', $nonstatic_id, PDO::PARAM_INT);
													$stmI->execute();
									
													$success++;
									}
									else{
										echo "Records exist for ".$month." ".$year.", Legacy ID: ".$legacy;
										echo "<br/>";
									}
									
								}
							}else{
							    $not_exist++;
								echo "<h4>Staff ".$sp." doesn't exist in our database. LegacyID: ".$legacy." </h4>";
							}
						}else{
							$unsuccessful++;
						}
						
						// echo $name;
				}
			// } catch (\Throwable $th) {
			// 	echo 'file format error';
			// }
			// $data=explode("IPPIS TERTIARY INSTITUTIONS",$txt_file);
			//$rows = explode('\n',$data[$i]);
			
	
			//echo $data[$i];
			
			echo '<br>';
			unlink($dest_file);
	
		}else{
			echo 'file not a pdf';
		}
		// echo file_exists(dirname(__FILE__) . '/payslip.pdf') ? '' : 'File: test.pdf was not found.';
		echo '<br/>';
	}else{
		echo 'pls select a pdf file and try again';
	}
	echo "<h3>
		".$success." record(s) uploaded.
	</h3>";
	echo "<h3>
		Could not process ".$unsuccessful." record(s) due to blank legacy ID
	</h3>";
	echo "<h3>
		".$not_exist." record(s) doesn't exist in our database
	</h3>";
?>