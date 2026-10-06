<?php
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/SavingsG1.php');

$view = new View();
$user = new User();
$savings = new SavingsG1();

$user->is_authenticated('auth/');
$user->isBur('../auth/logout.php'); // Ensure you have this method

$approvedRequests = $savings->getChairmanApprovedSavingsUpdates();

echo $view->subHeader('../');
?>

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<div class="container-fluid page-body-wrapper">
  <?php echo $view->treasurerSideNav()?>
  
          <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                <div class="col-md-12">
                  <div class="justify-content-between ">
                    <div class=" card ">
                      <div class="mr-md-10 card-body mr-xl-10">
                            <div class="card-body">
                
              <h2>TREASURER: Approved Savings Updates</h2>
              
              <!-- Download Button -->
              <div class="mb-3">
                <a href="download_approved_savings_csv.php" class="btn btn-success">
                  <i class="fa fa-download"></i> Download as CSV
                </a>
                <a href="upload_approved_savings_csv.php" class="btn btn-primary">
                  <i class="fa fa-download"></i> Upload Approved Amount
                </a>
              </div>
        
              <table id="savingsTable" class="table table-bordered">
                <thead>
                  <tr>
                    <th>Staff No</th>
                    <th>Name</th>
                    <th>Proposed Amount</th>
                    <th>Final Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($approvedRequests as $req): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($req['sp_no']); ?></td>
                    <td><?php echo htmlspecialchars($req['fname'].' '.$req['lname']); ?></td>
                    <td>₦<?php echo number_format($req['savings_update_amount'], 2); ?></td>
                    <td>
                      <input type="number" class="form-control final-amount" 
                             value="<?php echo $req['savings_update_amount']; ?>"
                             data-update-id="<?php echo $req['id']; ?>">
                    </td>
                    <td>
                      <button class="btn btn-sm btn-primary save-amount" 
                              data-update-id="<?php echo $req['id']; ?>">
                        Save
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
         </div>
        </div>
      </div>
              
    </div>
    
    </div>
    </div>
 <?php 
          echo $view->subFooter('../');
        ?>
<script>
$(document).ready(function() {
    $('.save-amount').on('click', function() {
        const updateId = $(this).data('update-id');
        const amount = $(this).closest('tr').find('.final-amount').val();
        
        if (amount <= 0) {
            alert('Amount must be greater than zero.');
            return;
        }

        $.post('../ajax/save_treasurer_savings_amount.php', {
            update_id: updateId,
            amount: amount
        }, function(res) {
            if (res.success) {
                alert('Amount saved successfully!');
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        }, 'json');
    });
});
</script>