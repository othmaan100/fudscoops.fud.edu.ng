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
       
    if ($pendingcheck==true) {
        $_SESSION['error'] = 'You already have a pending application for this commodity supply.';
       // header('Location: commodity_request.php');
      //  exit();
    }
    
    
    
    
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
                                      
                                    //      var_dump($pendingcheck);
                                    //  var_dump($commodity->checkSchedule());
                                    
                                      if ($commodity->checkSchedule() && !($pendingcheck)) { 
                                   // if (1>=1) { 
                                                // $timeout = $commodity->getSchedulesTimeOut();
                                                // $timeoutid = $commodity->getSchedulesTimeOutId();
                                                // $items = Commodity::getCommoditySupplyItems($timeoutid);
                                        
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
                                                                            <span id='btxt'><i class='fa fa-save'></i> Save  Application</span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                  
                                                    ";
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
    </script>
    
    <script>
    //==========Ask the user if he wanted to proceed with the commodity loan application before it gets submitted==================================
// $(document).on('submit', '#commodity-loan-form', function (e) {
//     e.preventDefault(); // Prevent the default form submission
    
//     var totalAmount = $('#hiddenTotalAmount').val();
//     var selectedItemsCount = $('.item-checkbox:checked').length;
    
//     // Validate that items are selected
//     if (selectedItemsCount === 0) {
//         $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"> Please select at least one item before submitting.</div>');
//         return;
//     }
    
//     // Validate that all selected items have quantities > 0
//     var hasValidQuantities = true;
//     $('.item-checkbox:checked').each(function() {
//         var itemId = $(this).data('item-id');
//         var quantity = parseInt($('input[data-item-id="' + itemId + '"].quantity-input').val()) || 0;
//         if (quantity <= 0) {
//             hasValidQuantities = false;
//             return false; // Break the loop
//         }
//     });
    
//     if (!hasValidQuantities) {
//         $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"> Please enter valid quantities for all selected items.</div>');
//         return;
//     }
    
//     if (confirm(`Are you sure you want to submit this commodity loan application for ₦${totalAmount}?`)) {
        
//         var data = $(this).serialize();
        
//         // Show loading message and disable submit button
//         $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Saving please wait...</i></div>');
//         $('#submitBtn').prop('disabled', true);
//         $('#loader').show();
//         $('#btxt').html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
//         console.log("Serialized data:", data);
        
//         $.ajax({
//             url: "../../ajax/commodity_ajax.php",
//             data: {
//                 "data": data, 
//                 "action": 'add_items_request'
//             },
//             type: "POST",
            
//             success: function (response) {
//                 console.log("Server response:", response);
                
//                 // // Try to parse JSON response first
//                 // try {
//                 //     var jsonResponse = JSON.parse(response);
//                 //     if (jsonResponse.success === false) {
//                 //         $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary"> Error! ' + jsonResponse.message + '</div>');
                        
//                 //         // Re-enable the submit button on error
//                 //         $('#submitBtn').prop('disabled', false);
//                 //         $('#loader').hide();
//                 //         $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
//                 //         return;
//                 //     }
//                 // } catch (e) {
//                 //     // Not JSON, check for simple success response
//                 // }
                
//                 // // Check for simple success response
//                 // if (jsonResponse.success === true) {
//                 //     $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Commodity loan application submitted successfully.</div>');
//                 //      $('#btxt').html('<i class="fa fa-save"></i> Application Saved Successfully');
                    
//                 //     // Delay redirection to allow the success message to be seen
//                 //     setTimeout(function () { 
//                 //         window.location.href = '../'; // Redirects to one folder up
//                 //     }, 2000); // 2000 milliseconds = 2 seconds
//                 // } else {
//                 //     $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary"> Error! ' + response + '</div>');
                    
//                 //     // Re-enable the submit button on error
//                 //     $('#submitBtn').prop('disabled', false);
//                 //     $('#loader').hide();
//                 //     $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
//                 // }
                
                
//                 // Check if the 'success' property in the JSON response is true
//         if (response.success) {
//             // Display the success message from the server
//             $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">' + response.message + '</div>');
            
//             // Update the button text to show it's saved
//             $('#btxt').html('<i class="fa fa-check"></i> Saved Successfully');
            
//             // Wait for 2 seconds before reloading the page
//             setTimeout(function () {
//                 // Reload the current page
//                 location.reload(); 
//             }, 2000);

//         }
                
                
            
//             },
            
//             error: function(xhr, status, error) {
//                 console.log("AJAX Error:", xhr.responseText);
//                 $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary"> Network Error! Please try again or contact system admin.</div>');
                
//                 // Re-enable the submit button on error
//                 $('#submitBtn').prop('disabled', false);
//                 $('#loader').hide();
//                 $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
//             }
//         });
//     } else {
//         // User clicked Cancel, do nothing
//         $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"> Commodity loan application cancelled.</div>');
//     }
// });

// // Enhanced item selection and preview functionality (corrected version)
// document.addEventListener('DOMContentLoaded', function() {
//     let selectedItems = {};
//     let totalAmount = 0;

//     // Handle checkbox and quantity changes
//     document.addEventListener('change', function(e) {
//         if (e.target.classList.contains('item-checkbox') || e.target.classList.contains('quantity-input')) {
//             updatePreview();
//         }
//     });

//     document.addEventListener('input', function(e) {
//         if (e.target.classList.contains('quantity-input')) {
//             updatePreview();
//         }
//     });

//     function updatePreview() {
//         selectedItems = {};
//         totalAmount = 0;
        
//         const checkboxes = document.querySelectorAll('.item-checkbox:checked');
//         const previewTableBody = document.getElementById('previewTableBody');
//         const noItemsMessage = document.getElementById('noItemsMessage');
//         const previewTable = document.getElementById('previewTable');
//         const submitBtn = document.getElementById('submitBtn');

//         previewTableBody.innerHTML = '';

//         if (checkboxes.length === 0) {
//             noItemsMessage.style.display = 'block';
//             previewTable.style.display = 'none';
//             submitBtn.disabled = true;
//         } else {
//             noItemsMessage.style.display = 'none';
//             previewTable.style.display = 'block';
            
//             let hasValidItems = false;
//             let sn = 1;
            
//             checkboxes.forEach(function(checkbox) {
//                 const itemId = checkbox.dataset.itemId;
//                 const itemName = checkbox.dataset.itemName;
//                 const price = parseFloat(checkbox.dataset.price);
//                 const quantityInput = document.querySelector('input[data-item-id="' + itemId + '"].quantity-input');
//                 const quantity = parseInt(quantityInput.value) || 0;
//                 const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 0;
                
//                 if (quantity > 0 && quantity <= maxQuantity) {
//                     const subtotal = quantity * price;
//                     totalAmount += subtotal;
//                     hasValidItems = true;
                    
//                     selectedItems[itemId] = {
//                         name: itemName,
//                         price: price,
//                         quantity: quantity,
//                         subtotal: subtotal
//                     };

//                     const row = '<tr>' +
//                         '<td>' + sn + '</td>' +
//                         '<td>' + itemName + '</td>' +
//                         '<td>₦' + price.toFixed(2) + '</td>' +
//                         '<td>' + quantity + '</td>' +
//                         '<td>₦' + subtotal.toFixed(2) + '</td>' +
//                         '<td><button type="button" class="btn btn-sm btn-danger remove-item" data-item-id="' + itemId + '"><i class="fa fa-trash"></i></button></td>' +
//                         '</tr>';
                    
//                     previewTableBody.innerHTML += row;
//                     sn++;
//                 } else if (quantity > maxQuantity) {
//                     // Show warning for quantity exceeding available stock
//                     quantityInput.value = maxQuantity;
//                     alert('Quantity for ' + itemName + ' cannot exceed available stock (' + maxQuantity + ')');
//                 }
//             });

//             submitBtn.disabled = !hasValidItems;
//         }

//         document.getElementById('totalAmount').textContent = '₦' + totalAmount.toFixed(2);
//         document.getElementById('hiddenTotalAmount').value = totalAmount.toFixed(2);
//     }

//     // Handle remove item from preview
//     document.addEventListener('click', function(e) {
//         if (e.target.closest('.remove-item')) {
//             const itemId = e.target.closest('.remove-item').dataset.itemId;
//             const checkbox = document.querySelector('input[data-item-id="' + itemId + '"].item-checkbox');
//             const quantityInput = document.querySelector('input[data-item-id="' + itemId + '"].quantity-input');
            
//             checkbox.checked = false;
//             quantityInput.value = 0;
//             quantityInput.disabled = true;
            
//             updatePreview();
//         }
//     });

//     // Handle checkbox state changes
//     document.addEventListener('change', function(e) {
//         if (e.target.classList.contains('item-checkbox')) {
//             const checkbox = e.target;
//             const itemId = checkbox.dataset.itemId;
//             const quantityInput = document.querySelector('input[data-item-id="' + itemId + '"].quantity-input');
            
//             if (checkbox.checked) {
//                 quantityInput.disabled = false;
//                 quantityInput.value = 1;
//                 quantityInput.focus(); // Focus on quantity input when checkbox is checked
//             } else {
//                 quantityInput.disabled = true;
//                 quantityInput.value = 0;
//             }
//         }
//     });
    
//     // Add quantity validation
//     document.addEventListener('input', function(e) {
//         if (e.target.classList.contains('quantity-input')) {
//             const input = e.target;
//             const max = parseInt(input.getAttribute('max')) || 0;
//             const value = parseInt(input.value) || 0;
            
//             if (value > max) {
//                 input.value = max;
//                 alert('Quantity cannot exceed available stock (' + max + ')');
//             }
//             if (value < 0) {
//                 input.value = 0;
//             }
//         }
//     });
// });

// Complete Commodity Selection JavaScript (without timer)
$(document).ready(function() {
    let selectedItems = {};
    let totalAmount = 0;

    // Handle checkbox changes
    $(document).on('change', '.item-checkbox', function() {
        const checkbox = $(this);
        const itemId = checkbox.data('item-id');
        const quantityInput = $('input[data-item-id="' + itemId + '"].quantity-input');
        
        if (checkbox.is(':checked')) {
            // Enable quantity input and set default value
            quantityInput.prop('disabled', false);
            quantityInput.val(1);
            quantityInput.focus();
        } else {
            // Disable quantity input and reset value
            quantityInput.prop('disabled', true);
            quantityInput.val(0);
        }
        
        updatePreview();
    });

    // Handle quantity input changes
    $(document).on('input change', '.quantity-input', function() {
        const input = $(this);
        const max = parseInt(input.attr('max')) || 0;
        const min = parseInt(input.attr('min')) || 0;
        let value = parseInt(input.val()) || 0;
        
        // Validate quantity bounds
        if (value > max) {
            value = max;
            input.val(max);
            alert('Quantity cannot exceed available stock (' + max + ')');
        }
        if (value < min) {
            value = min;
            input.val(min);
        }
        
        updatePreview();
    });

    // Remove item from preview
    $(document).on('click', '.remove-item', function() {
        const itemId = $(this).data('item-id');
        const checkbox = $('.item-checkbox[data-item-id="' + itemId + '"]');
        const quantityInput = $('.quantity-input[data-item-id="' + itemId + '"]');
        
        checkbox.prop('checked', false);
        quantityInput.prop('disabled', true).val(0);
        
        updatePreview();
    });

    function updatePreview() {
        selectedItems = {};
        totalAmount = 0;
        
        const previewTableBody = $('#previewTableBody');
        const noItemsMessage = $('#noItemsMessage');
        const previewTable = $('#previewTable');
        const submitBtn = $('#submitBtn');

        previewTableBody.empty();

        const checkedBoxes = $('.item-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            noItemsMessage.show();
            previewTable.hide();
            submitBtn.prop('disabled', true);
            return;
        }

        noItemsMessage.hide();
        previewTable.show();
        
        let hasValidItems = false;
        let sn = 1;
        
        checkedBoxes.each(function() {
            const checkbox = $(this);
            const itemId = checkbox.data('item-id');
            const itemName = checkbox.data('item-name');
            const price = parseFloat(checkbox.data('price'));
            const quantityInput = $('.quantity-input[data-item-id="' + itemId + '"]');
            const quantity = parseInt(quantityInput.val()) || 0;
            const maxQuantity = parseInt(quantityInput.attr('max')) || 0;
            
            if (quantity > 0 && quantity <= maxQuantity) {
                const subtotal = quantity * price;
                totalAmount += subtotal;
                hasValidItems = true;
                
                selectedItems[itemId] = {
                    name: itemName,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                };

                const row = `
                    <tr>
                        <td>${sn}</td>
                        <td>${itemName}</td>
                        <td>₦${price.toFixed(2)}</td>
                        <td>${quantity}</td>
                        <td>₦${subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-item-id="${itemId}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                previewTableBody.append(row);
                sn++;
            }
        });

        submitBtn.prop('disabled', !hasValidItems);
        $('#totalAmount').text('₦' + totalAmount.toFixed(2));
        $('#hiddenTotalAmount').val(totalAmount.toFixed(2));
    }

    // Form submission handling
    $(document).on('submit', '#commodity-loan-form', function (e) {
        e.preventDefault();
        
        var totalAmount = $('#hiddenTotalAmount').val();
        var selectedItemsCount = $('.item-checkbox:checked').length;
        
        // Validate that items are selected
        if (selectedItemsCount === 0) {
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary">Please select at least one item before submitting.</div>');
            return;
        }
        
        // Validate that all selected items have quantities > 0
        var hasValidQuantities = true;
        $('.item-checkbox:checked').each(function() {
            var itemId = $(this).data('item-id');
            var quantity = parseInt($('.quantity-input[data-item-id="' + itemId + '"]').val()) || 0;
            if (quantity <= 0) {
                hasValidQuantities = false;
                return false;
            }
        });
        
        if (!hasValidQuantities) {
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary">Please enter valid quantities for all selected items.</div>');
            return;
        }
        
        if (confirm(`Are you sure you want to submit this commodity loan application for ₦${totalAmount}?`)) {
            
            var data = $(this).serialize();
            
            // Show loading message and disable submit button
            $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving please wait...</i></div>');
            $('#submitBtn').prop('disabled', true);
            $('#loader').show();
            $('#btxt').html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            console.log("Serialized data:", data);
            
            $.ajax({
                url: "../../ajax/commodity_ajax.php",
                data: {
                    "data": data, 
                    "action": 'add_items_request'
                },
                type: "POST",
                dataType: "json", // Expect JSON response
                
                success: function (response) {
                    console.log("Server response:", response);
                    
                    if (response.success) {
                        $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">' + response.message + '</div>');
                        $('#btxt').html('<i class="fa fa-check"></i> Saved Successfully');
                        
                        setTimeout(function () {
                            location.reload(); 
                        }, 2000);
                    } else {
                        $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! ' + (response.message || 'Unknown error occurred') + '</div>');
                        
                        $('#submitBtn').prop('disabled', false);
                        $('#loader').hide();
                        $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
                    }
                },
                
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", xhr.responseText);
                    $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Network Error! Please try again or contact system admin.</div>');
                    
                    $('#submitBtn').prop('disabled', false);
                    $('#loader').hide();
                    $('#btxt').html('<i class="fa fa-save"></i> Save & Print Application');
                }
            });
        } else {
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary">Commodity loan application cancelled.</div>');
        }
    });

    // Initialize the form state
    updatePreview();
});
</script>
