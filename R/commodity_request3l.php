<?php
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        require_once '../config/classes/Commodity.php';

        $view = new View();
        $user = new User();
        $commodity = new Commodity();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
        $employeeid = $user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($employeeid);
        $share_info = $user->getMemberShareInformation($memberid);
        $share_unit = $user->getShareUnitInfoArray();
        $date = date('Y-m-d', strtotime($share_info['date']));
        
        $timeout = $commodity->getSchedulesTimeOut();
        $timeoutid = $commodity->getSchedulesTimeOutId();
        $items = Commodity::getCommoditySupplyItems($timeoutid);
        
        //var_dump($items);
                                                
         // Check if member already has a pending application
         $pendingcheck =Commodity::hasPendingApplication($memberid, $timeoutid);
       
    // if ($pendingcheck==true) {
    //     $_SESSION['error'] = 'You already have a pending application for this commodity supply.';
    //   // header('Location: commodity_request.php');
    //   //  exit();
    // }
    
    
    
    
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->staffSideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex2 justify-content-between flex-wrap2">
                <div class="d-flex2 align-items-end card flex-wrap2">
                  
                  
                 
                      <div class="container mt-5">
                            <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                    <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                    <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                    <h5 class="text-center mb-4" style="text-decoration: underline;">COMMODITY LOAN APPLICATION FORM</h5>
                                    
                                      <?php
                                    
                                     var_dump($pendingcheck);
                                     var_dump($commodity->checkSchedule());
                                    
                                    if ($commodity->checkSchedule() && !$pendingcheck) {  
                                 
                                 // Show the form ONLY if schedule is active AND NO pending application exists
                                        echo "
                                        <h3>Closes in <span> <input type='hidden' name='time' id='time' value='$timeout'>  </span></h3>
                                        <h1 class='row alert alert-danger'>
                                          <i class='fa fa-clock-o' aria-hidden='true'></i> <span id='demo'></span>
                                        </h1>
                                        <div class='container2 py-4'>
                                            <div class='card shadow'>
                                                <div class='card-body'>
                                                    <form id='commodity-loan-form' method='post' action='process_commodity_loan.php'>
                                                        <input type='hidden' name='commodity_supply_id' value='{$timeoutid}'>
                                                        
                                                        <!-- Available Items Table -->
                                                        <div class='mb-4'>
                                                            <h4 class='text-success mb-3'>
                                                                <i class='fa fa-list'></i> List of Available Items
                                                            </h4>
                                                            
                                                            <div class='table-responsive'>
                                                                <table class='table table-bordered table-hover' id='itemsTable'>
                                                                    <thead class='table-dark'>
                                                                        <tr>
                                                                            <th width='5%'>S/N</th>
                                                                            <th width='5%'><i class='fa fa-check'></i></th>
                                                                            <th width='30%'>Item</th>
                                                                            <th width='15%'>Unit Price (₦)</th>
                                                                            <th width='15%'>Available Qty</th>
                                                                            <th width='15%'>Quantity Request</th>
                                                                            <th width='15%'>Subtotal (₦)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id='itemsTableBody'>
                                                                        {$items}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                    
                                                        <!-- Preview Section -->
                                                        <div class='preview-section bg-light p-3 rounded'>
                                                            <h4 class='text-primary mb-3'>
                                                                <i class='fa fa-eye'></i> Selected Items Preview
                                                            </h4>
                                                            
                                                            <div id='noItemsMessage' class='text-center text-muted py-4'>
                                                                <i class='fa fa-info-circle fa-2x mb-2'></i>
                                                                <p>No items selected. Please select items above to see preview.</p>
                                                            </div>
                                                            
                                                            <div id='previewTable' style='display: none;'>
                                                                <div class='table-responsive'>
                                                                    <table class='table table-sm table-bordered'>
                                                                        <thead class='table-success'>
                                                                            <tr>
                                                                                <th>S/N</th>
                                                                                <th>Item</th>
                                                                                <th>Unit Price (₦)</th>
                                                                                <th>Quantity</th>
                                                                                <th>Subtotal (₦)</th>
                                                                                <th>Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id='previewTableBody'>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                
                                                                <div class='row mt-3'>
                                                                    <div class='col-md-6 offset-md-6'>
                                                                        <div class='card bg-success text-white'>
                                                                            <div class='card-body text-center'>
                                                                                <h5 class='card-title'>Total Amount</h5>
                                                                                <h3 id='totalAmount'>₦0.00</h3>
                                                                                <input type='hidden' name='total_amount' id='hiddenTotalAmount' value='0'>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                    
                                                        <!-- Submit Button -->
                                                        <div class='text-center mt-4'>
                                                            <button type='submit' class='btn btn-primary btn-lg' id='submitBtn' disabled>
                                                                <img src='../img/loading.gif' style='display: none;' id='loader' alt=''> 
                                                                <span id='btxt'><i class='fa fa-save'></i> Save & Print Application</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>";
                                    } else {
                                        // Either schedule is not active OR there IS a pending application
                                        if ($pendingcheck) {
                                            $_SESSION['error'] = 'You already have a pending application for this commodity supply.';
                                        } else {
                                            $_SESSION['error'] = 'Commodity loan application is currently not available.';
                                        }
                                        
                                        echo "
                                        <h3>Closes in <span><input type='hidden' name='time' id='time' value='$timeout'></span></h3>
                                        <h1 class='row alert alert-danger'>
                                            <i class='fa fa-clock-o' aria-hidden='true'></i> <span id='demo'></span>
                                        </h1>
                                        <div class='container2 py-4'>
                                            <div class='card shadow'>
                                                <div class='card-body text-danger'>
                                                    " . htmlspecialchars($_SESSION['error']) . "
                                                </div>
                                            </div>
                                        </div>";
                                    }

                                      ?>
                                
                      </div>
                </div>
              </div>
            </div>    
          </div>
        </div>
        <!-- content-wrapper ends -->
        
        <!-- FOOTER PLACED HERE -->
        <?php 
          echo $view->subFooter('../');
        ?>
        
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
        
        <script>
    // Set the date we're counting down to
    var myTime = $('#time').val();
    var countDownDate = new Date(myTime+'').getTime();
    // alert(myTime)      
    // Update the count down every 1 second
          var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="demo"
            document.getElementById("demo").innerHTML = days + " Day(s):" + hours + "Hour(s):"
            + minutes + " Minute(s):" + seconds + " Seconds ";

            // If the count down is finished, write some text
            if (distance < 0) {
              clearInterval(x);
              document.getElementById("demo").innerHTML = "CLOSED";
            }
          }, 1000);
          
          $(document).ready(function() {
    var selectedItems = {};
    var totalAmount = 0;

    //========== Enable/disable quantity input based on checkbox state ==================================
    $(document).on('change', '.item-checkbox', function() {
        var checkbox = $(this);
        var itemId = checkbox.data('item-id');
        var quantityInput = $('.quantity-input[data-item-id="' + itemId + '"]');
        var row = checkbox.closest('.item-row');
        
        if (checkbox.is(':checked')) {
            quantityInput.prop('disabled', false).val(1);
            row.addClass('selected');
        } else {
            quantityInput.prop('disabled', true).val(0);
            row.removeClass('selected');
        }
        
        // Update preview after changing the state
        updatePreview();
    });

    //========== Handle quantity input changes ==================================
    $(document).on('input', '.quantity-input', function() {
        updatePreview();
    });

    //========== Update preview function ==================================
    function updatePreview() {
        selectedItems = {};
        totalAmount = 0;
        
        var previewTableBody = $('#previewTableBody');
        var noItemsMessage = $('#noItemsMessage');
        var previewTable = $('#previewTable');
        var submitBtn = $('#submitBtn');

        previewTableBody.html(''); // Clear previous preview

        var sn = 1;
        // Loop through only checked checkboxes
        $('.item-checkbox:checked').each(function() {
            var checkbox = $(this);
            var itemId = checkbox.data('item-id');
            var itemName = checkbox.data('item-name');
            var price = parseFloat(checkbox.data('price'));
            var quantityInput = $('.quantity-input[data-item-id="' + itemId + '"]');
            var quantity = parseInt(quantityInput.val()) || 0;

            if (quantity > 0) {
                var subtotal = quantity * price;
                totalAmount += subtotal;

                selectedItems[itemId] = {
                    name: itemName,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                };

                // Update main table subtotal
                var mainTableRow = $('.item-row[data-item-id="' + itemId + '"]');
                mainTableRow.find('.subtotal').text(subtotal.toFixed(2));

                var row = '<tr>' +
                    '<td>' + sn + '</td>' +
                    '<td>' + itemName + '</td>' +
                    '<td>' + price.toFixed(2) + '</td>' +
                    '<td>' + quantity + '</td>' +
                    '<td>' + subtotal.toFixed(2) + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-danger remove-item" data-item-id="' + itemId + '"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>';
                
                previewTableBody.append(row);
                sn++;
            } else {
                // Reset main table subtotal if quantity is 0
                var mainTableRow = $('.item-row[data-item-id="' + itemId + '"]');
                mainTableRow.find('.subtotal').text('0.00');
            }
        });

        // Reset subtotals for unchecked items
        $('.item-checkbox').not(':checked').each(function() {
            var itemId = $(this).data('item-id');
            var mainTableRow = $('.item-row[data-item-id="' + itemId + '"]');
            mainTableRow.find('.subtotal').text('0.00');
        });

        // Toggle visibility of preview table and "no items" message
        if (Object.keys(selectedItems).length === 0) {
            noItemsMessage.show();
            previewTable.hide();
            submitBtn.prop('disabled', true);
        } else {
            noItemsMessage.hide();
            previewTable.show();
            submitBtn.prop('disabled', false);
        }

        $('#totalAmount').text('₦' + totalAmount.toFixed(2));
        $('#hiddenTotalAmount').val(totalAmount.toFixed(2));
    }

    //========== Handle remove item from preview ==================================
    $(document).on('click', '.remove-item', function() {
        var itemId = $(this).data('item-id');
        var checkbox = $('.item-checkbox[data-item-id="' + itemId + '"]');
        var quantityInput = $('.quantity-input[data-item-id="' + itemId + '"]');
        var row = checkbox.closest('.item-row');
        
        checkbox.prop('checked', false);
        quantityInput.val(0).prop('disabled', true);
        row.removeClass('selected');
        
        updatePreview();
    });

    //==========Ask the user if he wanted to proceed with the commodity loan application before it gets submitted==================================
    $(document).on('click', '#submitBtn', function (e) {
        e.preventDefault(); // Prevent the default form submission
        
        if (Object.keys(selectedItems).length === 0) {
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"><i class="fa fa-exclamation-triangle"></i> Please select at least one item.</div>');
            return;
        }

        var totalAmountFormatted = '₦' + totalAmount.toFixed(2);
        var itemCount = Object.keys(selectedItems).length;

        if (confirm('Are you sure you want to submit this commodity loan application?\n\nTotal Items: ' + itemCount + '\nTotal Amount: ' + totalAmountFormatted)) {
            
            // Prepare form data
            var formData = {
                commodity_supply_id: commoditySupplyId, // This should be set from PHP
                total_amount: totalAmount.toFixed(2),
                selected_items: JSON.stringify(Object.keys(selectedItems)),
                quantities: JSON.stringify(Object.keys(selectedItems).reduce(function(acc, itemId) {
                    acc[itemId] = selectedItems[itemId].quantity;
                    return acc;
                }, {})),
                selected_items_data: JSON.stringify(selectedItems)
            };
            
            $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i class="fa fa-spinner fa-spin"></i> Processing application, please wait...</div>');
            
            // Show loading state on button
            $('#loader').show();
            $('#btxt').html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            $('#submitBtn').prop('disabled', true);

            $.ajax({
                url: "process_commodity_loan.php",
                data: formData,
                type: "POST",
                dataType: "json",
                
                success: function (response) {
                    // Hide loading state
                    $('#loader').hide();
                    $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
                    
                    if (response.success) {
                        $('.msg').html('<div class="alert offset-md-2 col-6 alert-success text-primary"><i class="fa fa-check-circle"></i> ' + response.message + '</div>');
                        
                        // Reset form
                        resetForm();
                        
                        // Delay redirection to allow the success message to be seen
                        setTimeout(function () {
                            if (response.application_id) {
                                window.location.href = 'application_success.php?id=' + response.application_id;
                            } else {
                                window.location.href = '../'; // Redirects to one folder up
                            }
                        }, 2000); // 2000 milliseconds = 2 seconds
                        
                    } else {
                        $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary"><i class="fa fa-exclamation-triangle"></i> ' + (response.message || 'Error! Contact system admin') + '</div>');
                        $('#submitBtn').prop('disabled', Object.keys(selectedItems).length === 0);
                    }
                },
                
                error: function(xhr, status, error) {
                    // Hide loading state
                    $('#loader').hide();
                    $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
                    $('#submitBtn').prop('disabled', Object.keys(selectedItems).length === 0);
                    
                    $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary"><i class="fa fa-exclamation-triangle"></i> Network error! Please check your connection and try again.</div>');
                    console.error('AJAX Error:', error);
                }
            });
        } else {
            // User clicked Cancel, do nothing
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"><i class="fa fa-times-circle"></i> Commodity loan application cancelled.</div>');
        }
    });

    //========== Function to reset form ==================================
    function resetForm() {
        // Reset all checkboxes and quantities
        $('.item-checkbox').prop('checked', false);
        $('.quantity-input').val(0).prop('disabled', true);
        $('.subtotal').text('0.00');
        $('.item-row').removeClass('selected');
        
        // Clear selected items
        selectedItems = {};
        totalAmount = 0;
        
        // Update preview
        updatePreview();
    }

    // Initial call to set proper state
    updatePreview();
});
    </script>
    
    
 
