<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');

$view = new View(); 
$user = new User();

$user->is_authenticated('../auth/'); 
$user->isBur('../auth/logout.php'); // 

echo $view->subHeader('../');
?>
<!-- Add this in <head> or just before </body> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <!-- partial:partials/_sidebar.html -->
  <?php echo $view->treasurerSideNav()?>
  
  <!-- partial -->
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end card stretch-card flex-wrap">
              <div class="mr-md-5 card-body mr-xl-5">
                <h2>VIEW WITHDRAWALS REPORT</h2>
              </div>                  
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-12 card">
                <form class="form-fetch-withdrawals" id="form-fetch-withdrawals">                                                 
                  <div class="row">
                    <div class="col-md-4 col-sm-3 col-xs-12">
                      <div class="form-group">
                        <label>FROM</label>
                        <input type="date" name="from" id="from" class="form-control text-dark form-control-sm" required>
                      </div>
                    </div>
                    
                    <div class="col-md-4 col-sm-3 col-xs-12">
                      <div class="form-group">
                        <label>TO</label>
                        <input type="date" name="to" id="to" class="form-control text-dark form-control-sm" required>
                      </div>
                    </div>
                      
                    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                      <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" id="fetch_withdrawals" class="form-control btn btn-sm btn-danger">
                          Fetch <i class="fa fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <div id="msg" class="row text-center msg">
                    <h3 id="loading" hidden>
                      <img src="../../img/loading.gif" alt=""> <i>Fetching withdrawals, please wait...</i>
                    </h3>
                  </div>
                </form>
                
                <!-- Results Table Container -->
                <div id="results-container" style="display:none; margin-top: 20px;">
                  <table id="withdrawalsTable" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                    <thead class="thead-dark">
                      <tr>
                        <th>S/N</th>
                        <th>Staff Number</th>
                        <th>Full Name</th>
                        <th>Amount (₦)</th>
                        <th>Bank</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Approved Date</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody id="withdrawals-table-body">
                      <!-- Filled by AJAX -->
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

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<!-- Footer -->
<?php echo $view->subFooter('../'); ?>

<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    let dataTable = null;

    $('#form-fetch-withdrawals').on('submit', function(e) {
        e.preventDefault();
        
        const from = $('#from').val();
        const to = $('#to').val();
        
        if (!from || !to) {
            alert('Please select both FROM and TO dates.');
            return;
        }
        
        if (from > to) {
            alert('FROM date cannot be later than TO date.');
            return;
        }

        $('#loading').show();
        $('#msg .alert').remove();

        $.ajax({
            url: '../ajax/fetch_withdrawals_ajax.php',
            type: 'POST',
             { from: from, to: to },
            dataType: 'json',
            success: function(response) {
                $('#loading').hide();
                
                if (response.success && response.data.length > 0) {
                    let rows = '';
                    response.data.forEach((item, index) => {
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.sp_no}</td>
                                <td>${item.fname} ${item.lname} ${item.oname}</td>
                                <td>₦${parseFloat(item.approved_withrawal_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                                <td>${item.bank_id}</td>
                                <td>${item.acount_number}</td>
                                <td>${item.account_name}</td>
                                <td>${item.approved_date}</td>
                                <td><span class="badge badge-success">Approved</span></td>
                            </tr>
                        `;
                    });
                    
                    $('#withdrawals-table-body').html(rows);
                    $('#results-container').show();
                    
                    // Destroy existing DataTable if exists
                    if (dataTable) {
                        dataTable.destroy();
                    }
                    
                    // Initialize DataTable
                    dataTable = $('#withdrawalsTable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        responsive: true,
                        pageLength: 10
                    });
                } else {
                    $('#results-container').hide();
                    $('#msg').prepend('<div class="alert alert-warning">No withdrawals found in this date range.</div>');
                }
            },
            error: function() {
                $('#loading').hide();
                $('#msg').prepend('<div class="alert alert-danger">Error fetching data. Please try again.</div>');
            }
        });
    });
});
</script>