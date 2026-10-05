<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";

$db = new DB();
$user = new User();

if (isset($_FILES['uploadFile'])) {
    if (isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
        $allowedExtensions = array("csv");
        $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
        if (in_array($ext, $allowedExtensions)) {
            $file_size = $_FILES['uploadFile']['size'] / 1024;
            if ($file_size < 500) {
                $file = "../resources/uploads/" . $_FILES['uploadFile']['name'];
                $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                if ($isUploaded) {
                    $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                    $i = 0;
                    $inserted = 0;
                    $updated = 0;
                    $invalidSPs = [];

                    $r = '<table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Staff No.</th>
                                    <th>Shares Amount</th>
                                    <th>Unit Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>';

                    $con = $db->getConnection();

                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if ($i > 0) {
                            $spNo = trim(DB::cleanData($data[0]));
                            $employeeId = $user->getEmployeeId($spNo);

                            if (!$employeeId) {
                                $invalidSPs[] = $spNo;
                                $i++;
                                continue;
                            }

                            $memberId = $user->getMemberId($employeeId);
                            if (!$memberId) {
                                $invalidSPs[] = $spNo . ' (no member ID)';
                                $i++;
                                continue;
                            }

                            $amountPaid = DB::cleanData($data[1]);
                            $amountWord = $user->numberToWords($amountPaid);
                            $today = date('Y-m-d');

                            $shares_status = 1;
                            $secretary_comment = 'Approved';
                            $chairman_comment = 'Approved';
                            $secretary_endos = 1;
                            $chairman_approval = 1;

                            $date = $today;
                            $secretary_endo_date = $today;
                            $chairman_app_date = $today;
                            $update_at = $today;

                            // Check if member already exists
                            $q = "SELECT * FROM fudscoops_shares WHERE member_id = :member_id";
                            $stm = $con->prepare($q);
                            $stm->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                            $stm->execute();

                            if ($stm->rowCount() > 0) {
                                // Update
                                $q = "UPDATE fudscoops_shares 
                                      SET unit_price = :unit_price, 
                                          amount_paid = :amount_paid, 
                                          shares_amount_word = :amount_word, 
                                          date = :date, 
                                          secretary_endorse_date = :sec_endo_date,
                                          chairman_approval_date = :chai_appro_date,
                                          update_at = :update_at 
                                      WHERE member_id = :member_id";
                                $stmt = $con->prepare($q);
                                $stmt->bindParam(':unit_price', $amountPaid, PDO::PARAM_INT);
                                $stmt->bindParam(':amount_paid', $amountPaid, PDO::PARAM_INT);
                                $stmt->bindParam(':amount_word', $amountWord, PDO::PARAM_STR);
                                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                                $stmt->bindParam(':sec_endo_date', $secretary_endo_date, PDO::PARAM_STR);
                                $stmt->bindParam(':chai_appro_date', $chairman_app_date, PDO::PARAM_STR);
                                $stmt->bindParam(':update_at', $update_at, PDO::PARAM_STR);
                                $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                                $stmt->execute();

                                if ($stmt) {
                                    $updated++;
                                    $r .= "<tr>
                                            <td>{$i}</td>
                                            <td>{$spNo}</td>
                                            <td>{$amountPaid}</td>
                                            <td>{$amountPaid}</td>
                                            <td><span class='text-primary'>Updated</span></td>
                                           </tr>";
                                }
                            } else {
                                // Insert
                                $query = "INSERT INTO fudscoops_shares (
                                            member_id, unit_price, amount_paid, shares_amount_word, date,
                                            share_status, secretary_comment, secretary_endorsement, chairman_comment, chairman_approval,
                                            secretary_endorse_date, chairman_approval_date, update_at) 
                                          VALUES (
                                            :member_id, :unit_price, :amount_paid, :amount_word, :date,
                                            :share_status, :sec_comment, :sec_endos, :chair_comment, :chair_appro,
                                            :sec_endo_date, :chai_appro_date, :update_at)";
                                $stm = $con->prepare($query);
                                $stm->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                                $stm->bindParam(':unit_price', $amountPaid, PDO::PARAM_INT);
                                $stm->bindParam(':amount_paid', $amountPaid, PDO::PARAM_INT);
                                $stm->bindParam(':amount_word', $amountWord, PDO::PARAM_STR);
                                $stm->bindParam(':date', $date, PDO::PARAM_STR);
                                $stm->bindParam(':share_status', $shares_status, PDO::PARAM_INT);
                                $stm->bindParam(':sec_comment', $secretary_comment, PDO::PARAM_STR);
                                $stm->bindParam(':sec_endos', $secretary_endos, PDO::PARAM_INT);
                                $stm->bindParam(':chair_comment', $chairman_comment, PDO::PARAM_STR);
                                $stm->bindParam(':chair_appro', $chairman_approval, PDO::PARAM_INT);
                                $stm->bindParam(':sec_endo_date', $secretary_endo_date, PDO::PARAM_STR);
                                $stm->bindParam(':chai_appro_date', $chairman_app_date, PDO::PARAM_STR);
                                $stm->bindParam(':update_at', $update_at, PDO::PARAM_STR);
                                $stm->execute();

                                if ($stm) {
                                    $inserted++;
                                    $r .= "<tr>
                                            <td>{$i}</td>
                                            <td>{$spNo}</td>
                                            <td>{$amountPaid}</td>
                                            <td>{$amountPaid}</td>
                                            <td><span class='text-success'>Inserted</span></td>
                                           </tr>";
                                }
                            }
                        }
                        $i++;
                    }

                    fclose($handle);

                    $r .= '<tfoot>
                            <tr>
                                <th>S/N</th>
                                <th>Staff No.</th>
                                <th>Shares Amount</th>
                                <th>Unit Price</th>
                                <th>Status</th>
                            </tr>
                          </tfoot>';

                    // Show invalid SP numbers
                    if (!empty($invalidSPs)) {
                        $r .= "<h4 class='text-danger mt-3'>Invalid Users (SP No. not found):</h4><ul>";
                        foreach ($invalidSPs as $badSp) {
                            $r .= "<li>{$badSp}</li>";
                        }
                        $r .= "</ul>";
                    }

                    if ($inserted == 0 && $updated == 0) {
                        echo "<h3 class='text-warning'>No record was uploaded or updated</h3>" . $r;
                    } else {
                        echo "<h3 class='text-success text-center'>{$inserted} Record(s) Inserted, {$updated} Updated</h3>" . $r;
                    }
                } else {
                    echo '<h3 class="text-danger">Error!!! File not uploaded!</h3>';
                }
            } else {
                echo '<h3 class="text-danger">Error!!! Maximum file size should not cross 200 KB!</h3>';
            }
        } else {
            echo '<h3 class="text-danger">Error!!! Only CSV files are allowed!</h3>';
        }
    } else {
        echo '<h3 class="text-danger">Error!!! Please select a file to upload!</h3>';
    }
} else {
    echo "<h3 class='text-danger'>Error!!! Please select a CSV file and try again!</h3>";
}
?>
