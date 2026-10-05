function updateLoanStatus(loanId, status) {
    // Make an AJAX call to update the loan status
    fetch(`../../ajax/guarantor_decision.php?loan_id=${loanId}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Loan status updated successfully.');
                location.reload(); // Reload the page to see updated status
            } else {
                alert('Failed to update loan status: ' + data.error);
            }
        })
        .catch(error => console.error('Error updating loan status:', error));
}


var requestArray = [];
var stockArray = [];
var ItemsListArray = [];
var totalCost = 0;
var tableBody = ""


$(document).ready(function () {
    var isGuarantorVerified = false;// this is to track the guarantor verification status
    var isloanDeserving = false;// this is to track the loan amoun 
    var isBuyerVerified = false;// this is to track the guarantor verification status

    $('.fixed-table-loading').hide();

    // alert('ok')
    // login module
   
  
    
    $('#login').click(function (e) {
        e.preventDefault();
        // $('.msg').removeClass('hidden')
        $('.msg').html('<div class="alert alert-primary"><i>Please wait...</i></div>')

        form_data = $('#frm-login').serialize();
        // alert(form_data)
        var username = $('#username').val();
        var password = $('#password').val();
        // alert(username)
        $.ajax({
            type: "POST",
            url: "ajax/login.php",
            data: form_data,
            success: function(msg) {
                //console.log(msg);
                 //alert(msg)
                if (username == password && (msg==0 || msg==1 || msg==2 || msg==3 || msg==4 || msg==5)) {
                    setTimeout((function(){ window.location = 'change_password.php'  }), 100);                                            
                }
                else if(msg== 3){
                    $('.msg').html('<div class="alert msg alert-success text-primary"> Login in please wait...</div>')
                    setTimeout((function(){ window.location = './Treasurer /'  }), 100);                        
                }
                else if(msg==1){
                    $('.msg').html('<div class="alert alert-success text-primary"> Login in please wait...</div>')
                    setTimeout((function(){ window.location = './R/'  }), 100);                        
                }
                else if(msg==2){
                    $('.msg').html('<div class="alert alert-success text-primary"> Login in please wait...</div>')
                    setTimeout((function(){ window.location = './Secretary/'  }), 100);                        
                }
                else if(msg==4){
                    $('.msg').html('<div class="alert alert-success text-primary"> Login in please wait...</div>')
                    setTimeout((function(){ window.location = './Chairman/'  }), 100);                        
                }
                else if(msg==0){
                    $('.msg').html('<div class="alert alert-success text-primary"> Login in please wait...</div>')
                    setTimeout((function(){ window.location = './UR/'  }), 100);                        
                }
                // else if(msg==0){
                //     $('.msg').html('<div class="alert alert-success text-primary"><img height="25" width="25" src="img/loading.gif" alt=""> Login in please wait...</div>')
                //     setTimeout((function(){ window.location = 'dashboard/hod/'  }), 100);                        
                // }
                else{
                    $('.msg').html('<div class="alert alert-danger">Invalid login details</div>')

                }
            }
        })
    });
    
    //===================== Country combo ==================================
    $('#country').change(function(e){
        e.preventDefault();
        // var action = "get-lga";
        var country = $(this).val();
        // alert(country);
        $.ajax({
            url: "ajax/get_states.php",
            data: {'country':country},
            type: "POST",
            cache:false,
            success: function(msg){
                // alert(msg);
                $('#state').html(msg);
            }
        });
    });

     //===================== state combo ==================================
     $('#state').change(function(e){
        e.preventDefault();
        // var action = "get-lga";
        var state = $(this).val();
        // alert(country);
        $.ajax({
            url: "ajax/get_lga.php",
            data: {'state':state},
            type: "POST",
            cache:false,
            success: function(msg){
                // alert(msg);
                $('#lga').html(msg);
            }
        });
    });
    
    $('#type').change(function(e){
        e.preventDefault();
        // var action = "get-lga";
        var type = $(this).val();
        // alert(type)
        $('#form-profile'+type).removeAttr('hidden');
        $('#form-profile'+type).show();
        
        if(type == 2){
            $('#form-profile1').hide();
        }else{
            $('#form-profile2').hide();
        }
        
        
        
        
    });
// alert('ok')
    //===================== profile  ==================================
    $(document).on('submit','#form-profile1',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize()+"&type=1";
        
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        $.ajax({
            url: "../ajax/update_bio.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Profile successfuly updated.</div>');
                    // setTimeout((function(){ window.location = 'payment.php'  }), 100);              

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Cannot update profile. Contact system admin</div>');
                }

            }
        });
    });
    //===================== update Remapyment  ==================================
    $(document).on('submit','#frm-update-loan-tenor',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize();
        
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> updaing please wait...</i></div>')
        $.ajax({
            url: "../ajax/updateLoanTenor.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    $('.msg').html('<div class="alert offset-md-2 col-6  alert-success text-primary"> Loan Tenor successfuly updated.</div>');
                     setTimeout((function(){ location.reload()  }), 100);              

                }else{
                    $('.msg').html('<div class="alert  offset-md-2 col-6  alert-danger text-primary"> Error! Cannot update profile. Contact system admin</div>');
                }

            }
        });
    });
    
       //===================== MemberReg  ==================================
    $(document).on('submit','#registerMember',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize();
        //console.log(data)
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        $.ajax({
            url: "../ajax/register_member.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                
                 let result = JSON.parse(msg);
                  let status = result[0];
                  let spNo = result[1];
                let monthly_savings = result[2];

                // Optionally, use the values (e.g., printing them)
                console.log("status: " + status);
                console.log("SP No: " + spNo);
                console.log("Monthly Savings: " + monthly_savings);

                // Call the printReg function if needed
                //printReg(result);
                
                if(status ==1){
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Member Registeration successfuly.</div>');
                     setTimeout((function(){ window.location = '../auth/logout.php'  }), 100);   
                    //printReg(msg);

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Cannot Register a Member. Contact system admin</div>');
                }

            }
        });
    });
    
    
       //===================== MemberReg  Slip ==================================  
        $(document).on("click", ".printReg", function(e) {
        var status = $(this).data("status")
        var invoice = $(this).data("invoice")
        var date = $(this).data("date")

        // items = JSON.parse(items)
        $.ajax({
            url: "../ajax/register_slip.php",
            method: "POST",
            data: {"invoice":invoice},
            success: function (response,x) {
                console.log(response);
                if (x == 'success') {
                    $("#tb").html("")
                    printReg(response.items)
                    reset();
                    $("#quantity").val("")
                    $("#product_id").val("")


                    // location.reload()
                }
            }, error: function(xhr, status, error) {
                // Request encountered an error
                // console.log('Error');
                // console.log('Status Code: ' + xhr.status);
                // console.log('Error: ' + error);
            },
        })
        
    })
    
    function printReg( ) {
    
 // console.log(customer); 
    if (!items) {
        return;
    }

   // <img src="{{ asset('log1.png') }}" alt="Description of the image">
                        
   
    var printout = `<img src="" alt="Logo">;
   
                        `;
    
   

    var iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
  
    var iframeDoc = iframe.contentWindow.document;
  iframeDoc.open();
  iframeDoc.write('<html><head><title>Receipt</title><style>@page { size: auto; margin: 0mm; }</style></head><body><img src="public/log1.png" >' + receiptContent + '</body></html>');
  iframeDoc.close();

  iframe.contentWindow.print();
  
    // Remove the iframe after printing
    iframe.parentNode.removeChild(iframe);
  }
  
  
  
    
     //===================== Member Secretary Reg Approval  ==================================

    $(document).on("click", ".approve_reg", function(e) {
        var staff_id = $(this).data("staff");
        //alert(staff_id);
       //console.log(staff_id);

        // items = JSON.parse(items)
        $.ajax({
            url: "../ajax/secretary_member_endorse.php",
            method: "POST",
            data: {"staff_no":staff_id},
            success: function (response,x) {
                alert(response);
                console.log(response);
                if (x == 'success') {
                     $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Secretary Emdorsement of Member Registeration  submitted successfully.</div>');
                     setTimeout((function(){ window.location = '../index.php'  }), 100); 
                   


                    // location.reload()
                }
            }, error: function(xhr, status, error) {
                // Request encountered an error
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
            },
        })
        // alert(items)
    })
    
         //=====================  Chairman Withdrawal Endorsement   ==================================

    $(document).on("click", ".chairman_approve_withdrawal", function(e) {
        var withrawals_id = $(this).data("withrawal");
         var approveWithdrawal = $(this).data("approve");
        
        
       

        // items = JSON.parse(items)
        $.ajax({
            url: "../ajax/secretary_withdrawal_endorsement.php",
            method: "POST",
            data: {"withrawals_id":withrawals_id,"approve":approveWithdrawal},
            success: function (response,x) { 
                //alert(x);
                //console.log(response);
                console.log(response);
                console.log(x);
                if (x == 'success') {
                     $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Secretary Emdorsement of Withdrawal submitted successfully.</div>');
                     setTimeout((function(){ window.location = '/Chairman/withdrawal_endorsement.php'  }), 500); 
                   


                    // location.reload()
                }
            }, error: function(xhr, status, error) {
                // Request encountered an error
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
            },
        })
        // alert(items)
    });
    
       //=====================  Chairman Withdrawal Endorsement   ==================================

    $(document).on("click", ".chairman_approve_com_withdrawal", function(e) {
        var withrawals_id = $(this).data("withrawal");
         var approve = $(this).data("approve"); 
        var withdrawaltype = $(this).data("withdrawaltype");
        
       

        // items = JSON.parse(items)
        $.ajax({
            url: "../ajax/secretary_withdrawal_endorsement.php",
            method: "POST",
            data: {"withrawals_id":withrawals_id,"approve":approve,"withdrawaltype":withdrawaltype},
            success: function (response,x) { 
                //alert(x);
                //console.log(response);
                console.log(response);
                console.log(x);
                if (x == 'success') {
                     $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Secretary Emdorsement of Withdrawal submitted successfully.</div>');
                     setTimeout((function(){ window.location = '/Chairman/complete_withdrawals.php'  }), 100); 
                   


                    // location.reload()
                }
            }, error: function(xhr, status, error) {
                // Request encountered an error
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
            },
        })
        // alert(items)
    });
    
    
       // ++++++++++++++++ upload Members +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#upload_members', function (e) {
        e.preventDefault();
     
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Members List please wait...</i></h3>')

 
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
         //alert(file_data);
        form_data.append('uploadFile', file_data);
        
        
        
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadMembersExcel.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
     // ++++++++++++++++ upload Withdrawals +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#withdrawals_uploads', function (e) { 
        e.preventDefault();
     
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Withdrawal List please wait...</i></h3>')

 
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
         //alert(file_data);
        form_data.append('uploadFile', file_data);
        
        
        
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadWithdrawalCSV.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
    
       // ++++++++++++++++ upload Loans +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#upload_loans', function (e) {
        e.preventDefault();
            // Confirm the action with the user
            if (!confirm('Are you sure you want to Upload the loan?')) {
                return false; // Exit if not confirmed
            }
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Members Loan please wait...</i></h3>')

        var file_type = $('#loanId').val();
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
        // alert(file_type);
        form_data.append('uploadFile', file_data);
        form_data.append('file_type', file_type);
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadLoansExcel.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
    
       // ++++++++++++++++ upload Loans +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#upload_loans_deduction', function (e) {
        e.preventDefault();
            // Confirm the action with the user
            if (!confirm('Are you sure you want to Upload the loan deductions?')) {
                return false; // Exit if not confirmed
            }
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Loan deduction please wait...</i></h3>')

        var file_type = $('#loanId').val();
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
        // alert(file_type);
        form_data.append('uploadFile', file_data);
        form_data.append('file_type', file_type);
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadLoansDeductionExcel.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
    
    
        // ++++++++++++++++ upload Savings +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#upload_savings', function (e) {
        e.preventDefault();
       var month = $('#month').val();
       var year = $('#year').val();
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Savings please wait...</i></h3>')

 
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
         //alert(file_data);
        form_data.append('uploadFile', file_data);
        form_data.append('month', month);
        form_data.append('year', year);
        
        
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadDepositExcel.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
    
        // ++++++++++++++++ upload Staff Shares +++++++++++++++++++++++++++++++++++++++++++++++
    $(document).on('click', '#upload_shares', function (e) {
        e.preventDefault();
       var month = $('#month').val();
       var year = $('#year').val();
       //alert(year);
       $('#msg').html('<h3 id="loading" ><img src="../img/loading.gif" alt=""> <i>Uploading Shares please wait...</i></h3>')

 
        var file_data = $('#uploadFile').prop('files')[0];
        var form_data = new FormData();
         //alert(file_data);
        form_data.append('uploadFile', file_data);
        
        
        
        $('#loading').removeAttr('hidden')
            $.ajax({
                url: 'uploadSharesExcel.php',
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (msg) {
                     //alert(msg)
                    e.preventDefault();
                    // display response from the PHP script, if any
                    $('#msg').html(msg)
                    return false;
                }
            });//end ajax

    });
    
    

    
       //===================== Endorsement of Loan ==================================
$(document).on('submit', '#endorseForm', function(e){
    e.preventDefault(); // Prevent the form from submitting normally

    // Confirm the action with the user
    if (!confirm('Are you sure you want to endorse this record?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data

    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Saving please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/endorsement.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Member Registration successfully.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = '../auth/logout.php'; }, 100);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot Register a Member. Contact system admin.</div>');
            }
        }
    });
});
       //===================== Endorsement of Loan ==================================
$(document).on('submit', '.undertaking-form', function(e){
    e.preventDefault(); // Prevent the form from submitting normally
        // Confirm the action with the user
        if (!confirm('Are you sure you want to endorse this loan?')) {
            return false; // Exit if not confirmed
        }
        var data = $(this).serialize(); // Serialize the form data

    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Saving please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/undertaking.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Undertaking successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = 'index.php'; }, 100);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot Saved undertaking Contact system admin.</div>');
            }
        }
    });
});

// ====================== Membership decision ====================================
$(document).on('submit', '.membership-decision-form', function(e){
   e.preventDefault(); // Prevent the form from submitting normally

    
        // Determine which button was clicked (either Approved or Deapproved)
        var action = '';
        var type = $('#type').val();
        
        if (type == 'decision') {
            if ($('#proceed').is(':focus')) {
                action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
        } else {
            if ($('#approve').is(':focus')) {
                action = 'Approve';
            } else if ($('#deapprove').is(':focus')) {
                action = 'Disapprove';
            }
        }
        
        // Use action for further processing
        //console.log(action);
        
      


    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' the Membership Application?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action; // Append the action to the form data
   


    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/secretary_member_endorse.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
             console.log(msg);
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Membership Registeration Decision successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = '/Chairman/member_list.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Membership Registeration Decision. Contact system admin.</div>');
            }
        }
    });
});

// ====================== Savings Withdrawal decision ====================================
$(document).on('submit', '.sav-withdrawal-decision-form', function(e){
   e.preventDefault(); // Prevent the form from submitting normally

    
        // Determine which button was clicked (either Approved or Deapproved)
        var action = '';
        var type = $('#type').val();
        
        if (type == 'decision') {
            if ($('#proceed').is(':focus')) {
                action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
        } else {
            if ($('#approve').is(':focus')) {
                action = 'Approve';
            } else if ($('#deapprove').is(':focus')) {
                action = 'Disapprove';
            }
        }
        
        // Use action for further processing
        //console.log(action);
        
      


    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' the Saving Withdarwal Application?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action; // Append the action to the form data
   


    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/secretary_withdrawal_endorsement.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
             console.log(msg);
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Member Saving Withdarwal Decision successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = '/Treasurer /withdrawal_endorsement.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Member Saving Withdarwal Decision. Contact system admin.</div>');
            }
        }
    });
});

// ====================== Complete Withdrawal decision ====================================
$(document).on('submit', '.com-withdrawal-decision-form', function(e){
   e.preventDefault(); // Prevent the form from submitting normally

    
        // Determine which button was clicked (either Approved or Deapproved)
        var withdrawaltype = 'com-withdrawal';
        var action = '';
        var type = $('#type').val();
        
        if (type == 'decision') {
            if ($('#proceed').is(':focus')) {
                action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
        } else {
            if ($('#approve').is(':focus')) {
                action = 'Approve';
            } else if ($('#deapprove').is(':focus')) {
                action = 'Disapprove';
            }
        }
        
        // Use action for further processing
        //console.log(action);
        
      


    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' the Saving Withdarwal Application?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action +'&withdrawaltype=' + withdrawaltype; // Append the action to the form data
   


    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/secretary_withdrawal_endorsement.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
             console.log(msg);
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Member Complete Saving Withdarwal Decision successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = '/Treasurer /complete_withdrawals.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Member Saving Withdarwal Decision. Contact system admin.</div>');
            }
        }
    });
});

// ====================== loan decision ====================================
$(document).on('submit', '.loan-decision-form', function(e){
    e.preventDefault(); // Prevent the form from submitting normally

    // Determine which button was clicked (either Approved or Deapproved)
        var action = '';
        var type = $('#type').val();
        if(type== 'decision' ){
            if ($('#proceed').is(':focus')) {
               action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
            
        }else{
            if ($('#Approved').is(':focus')) {
                 action = 'Approve';
            } else if ($('#Deapproved').is(':focus')) {
                action = 'Disapprove';
            }
        }
        

    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' this loan?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action; // Append the action to the form data

    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/process_loan_decision.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Loan Decision successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = 'loans.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Loan Decision. Contact system admin.</div>');
            }
        }
    });
});

// ====================== End of loan decision ====================================

// ====================== Share Purchase Decision ====================================
$(document).on('submit', '.share-decision-form', function(e){
    e.preventDefault(); // Prevent the form from submitting normally

    // Determine which button was clicked (either Approved or Deapproved)
        var action = '';
        var type = $('#type').val();
        if(type== 'decision' ){
            if ($('#proceed').is(':focus')) {
               action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
            
        }else{
            if ($('#Approved').is(':focus')) {
                 action = 'Approve';
            } else if ($('#Deapproved').is(':focus')) {
                action = 'Disapprove';
            }
        }
        

    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' this share?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action; // Append the action to the form data

    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/process_share_decision.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Share Approval successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = 'manage_share_purchase.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Share Approval. Contact system admin.</div>');
            }
        }
    });
});

// ====================== End of Share Purchase decision ====================================

// ====================== Share Transfer Decision ====================================
$(document).on('submit', '.share-transfer-decision-form', function(e){
    e.preventDefault(); // Prevent the form from submitting normally

    // Determine which button was clicked (either Approved or Deapproved)
        var action = '';
        var type = $('#type').val();
        if(type== 'decision' ){
            if ($('#proceed').is(':focus')) {
               action = 'Proceed';
            } else if ($('#Decline').is(':focus')) {
                action = 'Decline';
            }
            
        }else{
            if ($('#Approved').is(':focus')) {
                 action = 'Approve';
            } else if ($('#Deapproved').is(':focus')) {
                action = 'Disapprove';
            }
        }
        

    // Confirm the action with the user
    if (!confirm('Are you sure you want to ' + action + ' this share?')) {
        return false; // Exit if not confirmed
    }

    var data = $(this).serialize(); // Serialize the form data
    data = data + '&action=' + action; // Append the action to the form data

    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/process_share_transfer_decision.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Share Transfer Approval successfully Saved.</div>');
                // Redirect after a short delay
                setTimeout(function(){ window.location = 'manage_share_transfer.php'; }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Share Approval. Contact system admin.</div>');
            }
        }
    });
});

// ====================== End of share Transfer decision ====================================

// ====================== Share Conversion Decision ====================================
$(document).on('submit', '.share-conversion-decision-form', function(e) {
    e.preventDefault(); // Prevent default submission

    // Determine which button was clicked
    var action = '';
    if ($('#proceed').is(':focus')) action = 'Approve';
    else if ($('#decline').is(':focus')) action = 'Decline';

    if (!action) {
        alert('No action detected!');
        return false;
    }

    if (!confirm('Are you sure you want to ' + action + ' this share?')) {
        return false;
    }

    var data = $(this).serialize();
    data += '&action=' + action;

    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

    $.ajax({
        url: "../ajax/process_share_conversion_decision.php",
        type: "POST",
        data: data,
        success: function(msg) {
            if (msg == 1) {
                $('.msg').html('<div class="alert offset-md-2 col-6 alert-success text-primary">Share Conversion Approval successfully Saved.</div>');
                setTimeout(function(){ window.location = 'manage_share_conversion.php'; }, 2000);
            } else if (msg == 2) {
                $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary">Share Conversion Declined.</div>');
                setTimeout(function(){ window.location = 'manage_share_conversion.php'; }, 2000);
            } else {
                $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Share Approval. Contact system admin.</div>');
            }
        },
        error: function() {
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">AJAX error! Contact system admin.</div>');
        }
    });
});

// ====================== End of share Conversion decision ====================================



//===================== Commodity Request ==================================

function setPriceAndLimit(selectEl, index) {
  const price = $(selectEl).find('option:selected').data('price');
  const maxQty = $(selectEl).find('option:selected').data('max');

  $('#price_' + index).val(price);
  const qtyInput = $('#qty_' + index);
  qtyInput.attr('max', maxQty);
  qtyInput.attr('placeholder', 'Max: ' + maxQty);

  updateAmount(index);
}

function updateAmount(index) {
  const qty = parseFloat($(`input[name="quantity_applied[${index}]"]`).val()) || 0;
  const price = parseFloat($(`input[name="unit_price[${index}]"]`).val()) || 0;
  const total = qty * price;
  $('#total_' + index).val(total.toFixed(2));
  updateGrandTotal();
}

function updateGrandTotal() {
  let grandTotal = 0;
  $('input[id^="total_"]').each(function () {
    grandTotal += parseFloat($(this).val()) || 0;
  });
  $('#grand_total').val(grandTotal.toFixed(2));
}

$(document).on('submit', '#commodity_request', function(e) {
  e.preventDefault();

  const data = $(this).serialize();
  const confirmSubmit = confirm("Are you sure you want to submit this commodity request?");
  if (!confirmSubmit) return;

  $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Saving, please wait...</i></div>');

  $.ajax({
    url: "../ajax/commodity_request.php", // The URL to send the form data to
    type: 'POST',
    data: data,
    success: function(response) {
      try {
        const data = JSON.parse(response);
        if (data.success) {
          $('.msg').html('<div class="alert offset-md-2 col-6 alert-success text-primary">Commodity request submitted successfully!</div>');
          setTimeout(() => location.reload(), 1000);
        } else {
          $('.msg').html(`<div class="alert offset-md-2 col-6 alert-danger text-primary">${data.message || 'Submission failed.'}</div>`);
        }
      } catch (e) {
        $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">Invalid server response.</div>');
      }
    },
    error: function(xhr, status, error) {
      $('.msg').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">AJAX error: ' + error + '</div>');
    }
  });
});

//===================== End of Commodity Request ==================================


    
// ====================== unlock loan decision ====================================
 $(document).on('click', '#unlock', function(e) {
    e.preventDefault();

    // Confirm the action with the user
    if (!confirm('Are you sure you want to unlock this loan?')) {
        return false; // Exit if not confirmed
    }

    var loanId = $(this).attr('loan_id');
    var memberId = $(this).attr('member_id');
    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i>Unlocking, please wait...</i></div>');

    var data = { loan_id: loanId, member_id: memberId };  // Send both loanId and memberId

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/unlock_loan_decision.php", // The URL to send the data to
        data: data, // The data to send
        type: "POST", // The HTTP method to use
        success: function(msg) {
            // Handle the response from the server
            if (msg == 1) {
                // Success message
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Unlock Loan Decision successfully saved.</div>');
                // Redirect after a short delay
                setTimeout(function() {
                    window.location = 'loans.php';
                }, 2000);
            } else {
                // Error message
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Cannot save Loan Decision. Contact system admin.</div>');
            }
        },
        error: function() {
            // Handle AJAX error
            $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error during AJAX request. Please try again.</div>');
        }
    });
});



// ====================== unlock loan decision ====================================

    
    //==========Ask the user if he wanted to proceed with the amount submit before it get submiited==================================
    $(document).on('submit', '#savingswithdrawal', function (e) {
        e.preventDefault(); // Prevent the default form submission
    
        var withdrawalAmount = $('#withdrawal_amount').val();
    
        if (confirm(`Are you sure you want to request a withdrawal of ${withdrawalAmount}?`)) {
            
            var data = $(this).serialize();
            
            $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>');
    
            $.ajax({
                url: "../../ajax/savingswithdrawal.php",
                data: data,
                type: "POST",
                success: function (msg) {
                if (msg == -2) {
                    $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary">You already have a pending savings withdrawal awaiting approval.</div>');
                } 
                else if (msg == 1) {
                        $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Withdrawal request submitted successfully.</div>');
                        
                        // Delay redirection to allow the success message to be seen
                      //  setTimeout(function () { 
                      //      window.location.href = '../'; // Redirects to one folder up
                      //  }, 2000); // 2000 milliseconds = 2 seconds, adjust as needed
                    } else {
                        $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">You already have a pending savings withdrawal awaiting approval.</div>');
                    }
                }

                
//                success: function (msg) {
//                    if (msg == 1) {
//                        $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Withdrawal request submitted successfully.</div>');
//                        setTimeout(function () { window.location = '../'; }, 100);              
//                    } else {
//                        $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
//                    }
//                }
            });
        } else {
            // User clicked Cancel, do nothing
            $('.msg').html('<div class="alert offset-md-2 col-6 alert-warning text-primary"> Withdrawal request cancelled.</div>');
        }
    });
    
    
    // Password Reset for Members
    
    $(document).on('submit', '#password-reset', function (e) {
        e.preventDefault();
        var formdata = $(this).serialize();
       
        $('#msg').html('<br><div class="alert alert-warning"><h6 class="text-center"><i>Resetting password... please wait</i></h6></div>');
    
        if (confirm('Are you sure you want to reset this password?') == true) {
            $.ajax({
                url: '../ajax/reset.php',
                data: formdata,
                type: 'post',
                success: function (msg) {
                    if (msg == 1) {
                        $('#msg').html('<br><div class="alert alert-success"><h6 class="text-center"><i>Password successfully changed.</i></h6></div>');
                    } else if (msg == 0) {
                        $('#msg').html('<br><div class="alert alert-danger"><h6 class="text-center"><i>Username does not exist.</i></h6></div>');
                    } else if (msg == -1) {
                        $('#msg').html('<br><div class="alert alert-danger"><h6 class="text-center"><i>Error! Cannot reset password, contact system admin</i></h6></div>');
                    } else {
                        alert('Error, contact system admin');
                    }
                }
            }); // end ajax
        }
    });

    
    //===================== Update Savings Amount==================================
    $(document).on('submit','#update_savings_amount',function(e){
        e.preventDefault();
        $(".hidden-message").show();
        
        var data = $(this).serialize();
        console.log(data)
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        result = confirm("Are you sure to request for monthly savings update?")
        if (!result) {
            return
        }
        $.ajax({
            url: "../../ajax/update_savings_amount.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    alert("Your monthly savings update request sent successuflly")
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Withdrawal request submitted successfully.</div>');
                     setTimeout((function(){ window.location = '../index.php'  }), 100);              

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
                }

            }
        });
    });
    
 //===================== Approve Savings Amount==================================
 
    // $(document).on('submit','#approve_savings_amount',function(e){
    //     e.preventDefault();
    //     $(".hidden-message").show();
        
    //     var data = $(this).serialize();
    //     console.log(data)
    //     // alert(data)
    //     $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
    //     // result = confirm("Are you sure to request for monthly savings update?")
    //     // if (!result) {
    //     //     return
    //     // }
    //     $.ajax({
    //         url: "../../ajax/approved_update_savings.php",
    //         data: data,
    //         type: "POST",
    //         // cache:false, 
    //       success: function(response){
    //         // response is the JSON object returned by the server
    //         console.log(response); // Debugging: see the full object in console
        
    //         if (response.success && response.msg === "1") {
    //             alert(response.message); // Show the server message
    //             $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">' 
    //                           + response.message + '</div>');
    //             setTimeout(function(){ window.location = '/index.php'; }, 100);
    //         } else {
    //             $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary"> Error! Contact system admin</div>');
    //         }
    //     }


    //     });
    // });
    


    //===================== Update New Member Savings Amount==================================
    $(document).on('submit','#update_nm_savings_amount',function(e){
        e.preventDefault();
        $(".hidden-message").show();
        
        var data = $(this).serialize();
        console.log(data)
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        result = confirm("Are you sure to request for monthly savings update?")
        if (!result) {
            return
        }
        $.ajax({
            url: "../../ajax/update_savings_amount.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    alert("your monthly savings update request sent successuflly")
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Withdrawal request submitted successfully.</div>');
                     setTimeout((function(){ window.location = '/UR/index.php'  }), 100);              

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
                }

            }
        });
    });
    
    //===================== complete Withdrawal==================================
    $(document).on('submit','#completeWithdrawal',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize();
        console.log(data)
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        result = confirm("Are you sure to request for complete withdrawal?")
        if (!result) {
            return
        }
        $.ajax({
            url: "../../ajax/complete_withdrawal.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                 //alert(msg);
                if(msg == 1){
                    alert("Complete withdrawal request sent successfully")
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Withdrawal request submitted successfully.</div>');
                     setTimeout((function(){ window.location = 'index.php'  }), 100);              

                }else{
                     alert(msg)
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
                }

            }
        });
    });
    
    
    //===================== add_user==================================
    $(document).on('submit','#adduser',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize();
        console.log(data)
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        result = confirm("Are you sure to Add new User?")
        if (!result) {
            return
        }
        $.ajax({
            url: "../../ajax/adduser.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                 //alert(msg);
                if(msg == 1){
                    alert("User added successfully")
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">User added successfully.</div>');
                     setTimeout((function(){ window.location = 'index.php'  }), 100);              

                }else{
                     alert(msg)
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
                }

            }
        });
    });
    
    
    //===================== business_loan_application ==================================
    $(document).on('submit','#business_loan',function(e){
        e.preventDefault();
        // Check if the guarantor has been verified before submitting the form
        if (!isGuarantorVerified) {
            alert("Please verify your guarantor before submitting the form.");
            return;
        }
        if (!isloanDeserving) {
            alert("You don't have enough funds to apply for this loan.");
            return;
        }
        var data = $(this).serialize();
        console.log(data)
         //alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Applying please wait...</i></div>')
        result = confirm("Are you sure you want to apply for this business_loan ?")
        if (!result) {
            return
        }
        $.ajax({
            url: "../../ajax/business_loan.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                 //alert(msg);
                if(msg ==1){
                    alert("Application request sent successfully")
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Application request submitted successfully.</div>');
                     setTimeout((function(){ window.location = 'index.php'  }), 100);              

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Contact system admin</div>');
                }

            }
        });
    });
    //===================== business_loan_application ==================================
    
//===================== target Savings ==================================
     $(document).on('submit', '#targetsavingsrequest', function(e) {
    e.preventDefault();

    // Check if the guarantor has been verified before submitting the form
    if (!isGuarantorVerified) {
        alert("Please verify your guarantor before submitting the form.");
        return;
    }

    var data = $(this).serialize();
    
    // Parse the serialized data into a key-value object
    let params = new URLSearchParams(data);
    let grade = params.get("grade"); // Extract the grade
    let periodSave = parseInt(params.get("periodSave") || "0", 10); // Default to 0 if NaN
    let periodWithdraw = parseInt(params.get("periodWithdraw") || "0", 10); // Default to 0 if NaN
    let amount = parseInt(params.get("amount") || "0", 10); // Default to 0 if NaN



    // Validation logic
    if (grade === "SP" && (periodSave < 24 || periodWithdraw < 24)) {
        alert("For Senior Staff (SP), both Period to Save and Period to Withdraw must be at least 24 months.");
        return false; // Prevent form submission
    } else if (grade === "JP" && (periodSave < 12 || periodWithdraw < 12)) {
        alert("For Junior Staff (JP), both Period to Save and Period to Withdraw must be at least 12 months.");
        return false; // Prevent form submission
    } else {
        console.log("Validation passed. Submitting form...");
    }

    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Applying please wait...</i></div>');
    result = confirm(`Are you sure you want to apply for this N${amount} Target Saving?`);

    if (!result) {
        return;
    }

    $.ajax({
        url: "../../ajax/targetsavingsrequest.php",
        data: data,
        type: "POST",
        success: function(msg) {
            console.log(msg);
            if (msg == 1) {
                alert("Application request sent successfully");
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Application request submitted successfully.</div>');
                setTimeout(function() { window.location = 'index.php'; }, 100);
            } else {
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary">Error! Contact system admin</div>');
            }
        }
    });
});


    //===================== Target Savings ==================================
    
    
    //===================================checking amount_applied=======================
    let typingTimer; // to prevent mutliple ajax calling while user is typing incase of on change
    $(document).on('input', '#amount_applied', function() {
        clearTimeout(typingTimer); // reset debounce
        const amount = $(this).val();
        $(".amount_msg").text("Checking...");
    
        typingTimer = setTimeout(function() {
            $.ajax({
                url: "../../ajax/getTotal_share.php",
                type: "POST",
                dataType: "json",
                cache: false,
                data: { amount },
                success: function(response) {
                    if (response.status === "success") {
                        $(".amount_msg").text(response.message);
                        isLoanDeserving = true;
                    } else {
                        $(".amount_msg").text(response.message);
                        isLoanDeserving = false;
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    $(".amount_msg").text("Network or server error. Please try again.");
                    isLoanDeserving = false;
                }
            });
        }, 500); // 500ms debounce
    });
    //===================================checking amount_applied=======================
    
    //===================================checking amount_applied=======================
       $(document).on('input','#amount_recommended',function(e){
            var amount = $(this).val();
            var tenor = $('#loan_tenor').val();
            e.preventDefault();
            $(".amount_msg").text("Calculating Loan repayment amount...");
              $.ajax({
                  url: "../../ajax/getLoanRepaymentAmount.php",
                  data: {amount,tenor},
                  type: "POST",  
                  cache:false,
                  dataType: 'JSON',
                  success: function(response) {
                      if (response.status === 'success') {
                          $(".amount_msg").text(response.message.toLocaleString());
                        } else if (response.status === 'error') {
                            // Display error message if verification fails
                           $(".amount_msg").text(response.message.toLocaleString());
                        }
                  }
              })//end ajax
        })
    //===================================checking amount_applied=======================
    
    //===================== verify guarantor ==================================
        $(document).on('click', '#verify_guarantor', function(e) {
            e.preventDefault();
             var username = $(this).attr('rel');
            var guarantor = $('#guarantor_sp_no').val();
            console.log(guarantor); // Log the value of the guarantor
            // checking if user is intend to be a guarantor
                if(username == guarantor){
                    //alert(username);
                 $('.guarantor').html('<div class="alert offset-md-6 col-6 alert-primary"><b> You cannot be a guarantor for yourself</b></div>');
                    isGuarantorVerified = false;
                }
                else if(guarantor.startsWith('JP')  || guarantor.startsWith('jp') || guarantor.startsWith('Jp') || guarantor.startsWith('jP')){
                  $('.guarantor').html('<div class="alert offset-md-6 col-6 alert-danger">This staff is not eligible to be a guarantor</div>');
                   isGuarantorVerified = false;
                }
                else{
                 $('.guarantor').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Verifying, please wait...</i></div>');
        
                    $.ajax({
                        url: "../../ajax/verify_guarantor.php",
                        type: "POST",
                        data: { "guarantor": guarantor }, // Ensure key matches PHP script's expected key
                        dataType: "json", // Expect JSON response
                        success: function(response) {
                            if (response.status === 'success') {
                                $('.guarantor').html('<div class="alert offset-md-2 col-6 alert-success text-primary">' + response.message + ' Name: ' + response.name + '</div>');
                                isGuarantorVerified = true;
                            } else if (response.status === 'error') {
                                // Display error message if verification fails
                                $('.guarantor').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">' + response.message + '</div>');
                                isGuarantorVerified = false;
                            } else {
                                $('.guarantor').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">Unexpected status received. Please try again.</div>');
                               isGuarantorVerified = false;
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            $('.guarantor').html('<div class="alert offset-md-2 col-6 alert-danger text-primary">An unexpected error occurred. Please try again later.</div>');
                             isGuarantorVerified = false;
                        }
                    });
                }
           
        });
    //===================== verify guarantor ==================================

      //===================== Share Purchase  ==================================
      
    $(document).on('submit', '#form-share', function(e) {
    e.preventDefault();
    
    // Serialize the form data
    var data = $(this).serialize();
    
    // Confirm the submission
    var result = confirm("Are you sure you want to submit the application for share purchase?");
    if (!result) {
        return; // If the user clicks "Cancel", return without showing the loading message
    }

    // Display a loading message after user confirms
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Saving, please wait...</i></div>');
    
    // Send the AJAX request
    $.ajax({
        url: "../../ajax/share_purchase.php",
        data: data,
        type: "POST",
        success: function(msg) {
            if (msg == 1) {
                $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary">Share purchase form successfully submitted.</div>');
                
                // Get the username from the form (hidden input)
                var username = $("#username").val();
                
                // Construct the URL with the parameters for redirection
                const url = `print.php?type=acknowledgeshare&spNo=${username}`;

                // Redirect to the print page after a short delay
                setTimeout(function() {
                    window.location.href = url;
                }, 1000);

            } else {
                $('.msg').html('<div class="alert msg offset-md-2 col-6 alert-danger text-primary"> Error! Cannot Register a Member. Contact system admin</div>');
            }
        }
    });
});

   //===================== End of Share Purchase  ==================================
   
   //===================== Verify Buyer for Share Transfer  ==================================
   
   $(document).on('click', '#verify_buyer', function (e) {
    e.preventDefault();
    
    var username = $(this).attr('rel');
    var buyer = $('#buyer_sp_no').val();
    
    // Clear any previous alerts
    $('.buyer').html('');
    var isBuyerVerified = true;

    // Checking if the user is trying to transfer to themselves
    if(username === buyer){
        $('.buyer').html('<div class="alert offset-md-6 col-6 alert-primary"><b> You cannot transfer share to yourself</b></div>');
        isBuyerVerified = false;
    }
    else if(!username){
        $('.buyer').html('<div class="alert offset-md-6 col-6 alert-primary"><b> Username cannot be empty</b></div>');
        isBuyerVerified = false;
    }
    else if (!buyer.trim()) {
        $('.buyer').html('<div class="alert offset-md-6 col-6 alert-primary"><b> Buyer number cannot be empty</b></div>');
        isBuyerVerified = false;
    }
    else {
        // Display loading message
        $('.buyer').html('<div class="alert offset-md-6 col-6 alert-info"><b> Verifying buyer, please wait...</b></div>');
        
        // Disable the button to prevent multiple clicks
        $('#verify_buyer').prop('disabled', true);

        $.ajax({
            url: '../ajax/verifyBuyer.php',
            data: {'buyer': buyer},
            type: 'post',
            success: function (msg) {
                // Display response from the PHP script
                $('.buyer').html(msg);
            },
            error: function(xhr, status, error) {
                console.log("Error Details:", error);  // Logs the error for debugging
                $('.buyer').html('<div class="alert offset-md-6 col-6 alert-danger"><b> Error verifying buyer. Please try again later.</b></div>');
            },
            complete: function() {
                // Re-enable the button after the AJAX request completes
                $('#verify_buyer').prop('disabled', false);
            }
        });
    }
    });


   //===================== End of Verify Buyer for Share Transfer  ==================================
   
   
   //===================== Convert Amount to Words ==================================

   $(document).on('keyup', '#worth', function () {
    var worthValue = $(this).val().trim();

    if (worthValue) {
        // Send AJAX request to convert the number to words
        $.ajax({
            url: '../ajax/convert_to_words.php', // Make sure this file exists and handles conversion
            type: 'post',
            data: {'worth': worthValue},
            beforeSend: function () {
                $('#amount_word').val('Converting...');
            },
            success: function (data) {
                $('#amount_word').val(data);
            },
            error: function (xhr, status, error) {
                console.log("Error:", error);
                $('#amount_word').val('Error converting amount');
            }
        });
    } else {
        $('#amount_word').val('');
    }
  });

   //===================== End of Convert Amount to Words ==================================
   
   //===================== Convert Share Purchase Amount to Words ==================================

   $(document).on('keyup', '#share_amount', function () {
    var worthValue = $(this).val().trim();

    if (worthValue) {
        // Send AJAX request to convert the number to words
        $.ajax({
            url: '../ajax/convert_shares_to_words.php', // Make sure this file exists and handles conversion
            type: 'post',
            data: {'share_amount': worthValue},
            beforeSend: function () {
                $('#share_amount_word').val('Converting...');
            },
            success: function (data) {
                $('#share_amount_word').val(data);
            },
            error: function (xhr, status, error) {
                console.log("Error:", error);
                $('#share_amount_word').val('Error converting amount');
            }
        });
    } else {
        $('#share_amount_word').val('');
    }
  });

   //===================== End of Convert Share Purchase Amount to Words ==================================
   
   //===================== Checking the Share Conversion Amount  ==================================
   
     $(document).on('input', '#amount_paid2', function(){
        var amount_paid = $(this).val();
    
        $.ajax({
            url: '../ajax/check_amount.php',
            data: {'amount_paid': amount_paid},
            type: 'post',
            success: function (msg) {
                if (msg == 1) {
                    // Worth is valid
                    console.log('Amount is valid, continue inputting');
                } else if (msg == -1) {
                    // Worth exceeds total share
                    alert('Amount must be less than your total savings.');
                    $('#amount_paid').val('');
                    $('#amount_paid').focus();
                } else if (msg == -2) {
                    // Remaining balance would fall below 20,000
                    alert('You must maintain a minimum savings balance of ₦2,000 after this transaction.');
                    $('#amount_paid').val('');
                    $('#amount_paid').focus();
                }
            },
            error: function() {
                alert('Error checking amount. Please try again.');
            }
        });
    });


   
   //===================== End of Checking the Share Conversion Amount  ==================================
   
   
   //===================== Convert Share Conversion Amount to Words ==================================

   $(document).on('keyup', '#amount_paid2', function () {
    var worthValue = $(this).val().trim();

    if (worthValue) {
        // Send AJAX request to convert the number to words
        $.ajax({
            url: '../ajax/convert_shares_conversion_to_words.php', // Make sure this file exists and handles conversion
            type: 'post',
            data: {'amount_paid': worthValue},
            beforeSend: function () {
                $('#share_amount_word').val('Converting...');
            },
            success: function (data) {
                $('#share_amount_word').val(data);
            },
            error: function (xhr, status, error) {
                console.log("Error:", error);
                $('#share_amount_word').val('Error converting amount');
            }
        });
    } else {
        $('#share_amount_word').val('');
    }
  });

   //===================== End of Convert Share Conversion Amount to Words ==================================
   
   

   
   //===================== Checking the Share Transfer Amount  ==================================
   
     $(document).on('input', '#worth', function(){
        var worth = $(this).val();
    
        $.ajax({
            url: '../ajax/check_worth.php',
            data: {'worth': worth},
            type: 'post',
            success: function (msg) {
                if (msg == 1) {
                    // Worth is valid
                    console.log('Worth is valid, continue inputting');
                } else if (msg == -1) {
                    // Worth exceeds total share
                    alert('Amount must be less than your total share amount.');
                    $('#worth').val('');
                    $('#worth').focus();
                } else if (msg == -2) {
                    // Remaining balance would fall below 20,000
                    alert('You must maintain a minimum share balance of ₦20,000 after this transaction.');
                    $('#worth').val('');
                    $('#worth').focus();
                }
            },
            error: function() {
                alert('Error checking worth. Please try again.');
            }
        });
    });


   
   //===================== End of Checking the Share Transfer Amount  ==================================
   
   //===================== Add Buyer for Share Transfer  ==================================
   
    $(document).on('click', '#add_share_transfer1', function (e) {
    e.preventDefault();
       var worth = $('#worth').val();
       var amount_word = $('#amount_word').val();
       var sp_no = $('#sp_no').val();
       //alert(sp_no);
       
       // Validate the input fields (optional, but recommended)
    if (!worth || !amount_word) {
        alert('Please fill in all required fields.');
        return;
    }
       
        // Confirm the submission
    var result = confirm("Are you sure you want to transfer this share?");
    if (!result) {
        return; // If the user clicks "Cancel", return without showing the loading message
    }

            $.ajax({
                url: '../ajax/share_transfer.php',
                data: {'worth':worth,'amount_word':amount_word,'sp_no':sp_no},
                type: 'post',
                success: function (msg) {
                    // alert(msg)
                    if(msg == 1){
                        alert('Share was successfully Transfered.')
                    }
                    else{
                        alert('Cannot Transfer the share, contact system admin.')
                    }
                }
            });//end ajax

    });
   
   
     //===================== End of Add Buyer for Share Transfer  ==================================

 //===================== Add Buyer for Share Transfer  ==================================
   
    $(document).on('click', '#add_share_transfer', function (e) {
    e.preventDefault();
       var worth = $('#worth').val();
       var amount_word = $('#amount_word').val();
       var sp_no = $('#sp_no').val();
       //alert(sp_no);
       
       // Validate the input fields (optional, but recommended)
    if (!worth || !amount_word) {
        alert('Please fill in all required fields.');
        return;
    }
       
        // Confirm the submission
    var result = confirm("Are you sure you want to transfer this share?");
    if (!result) {
        return; // If the user clicks "Cancel", return without showing the loading message
    }

            $.ajax({
                url: '../ajax/share_pending_transfer.php',
                data: {'worth':worth,'amount_word':amount_word,'sp_no':sp_no},
                type: 'post',
                success: function (msg) {
                    // alert(msg)
                    if(msg == 1){
                        alert('Share was successfully Transfered and Waiting for Approval.')
                    }
                    else{
                        alert('Cannot Transfer the share, contact system admin.')
                    }
                }
            });//end ajax

    });
   
   
     //===================== End of Add Buyer for Share Transfer  ==================================
     
     //===================== Accept Share Transfer  ==================================
   
    $(document).on('click', '.accept-transfer', function (e) {
    e.preventDefault();
        // Extract the transfer ID from the button's ID
       const transferId = $(this).attr('id').replace('accept_transfer_share', '');
       alert('ok');
       
       // Confirm the submission
        var result = confirm("Are you sure you want to accept this share?");
        if (!result) {
            return; // If the user clicks "Cancel", return without showing the loading message
        }

            $.ajax({
                url: '../ajax/accept_transfer_share.php',
                data:{transfer_id: transferId}, // Pass the transfer ID
                type: 'post',
                success: function (msg) {
                    // alert(msg)
                    if(msg == 1){
                        alert('Transfer successfully accepted!')
                    }
                    else{
                        alert('Failed to accept transfer, contact system admin.')
                    }
                }
            });//end ajax

    });
   
   
     //===================== End of Accept Share Transfer  ==================================


    //===================== Share Conversion from the savings  ==================================
   
    $(document).on('submit', '#share_conversion', function (e) {
    e.preventDefault();
       var amount_paid = $('#amount_paid2').val();
       var amount_word = $('#share_amount_word').val();
       var staff_no = $('#staff_no').val();
       //alert(sp_no);
       
       // Validate the input fields (optional, but recommended)
        // Confirm the submission
    var result = confirm("Are you sure you want to Convert your savings to share?");
    if (!result) {
        return; // If the user clicks "Cancel", return without showing the loading message
    }

            $.ajax({
                url: '../ajax/share_pending_conversion.php',
                data: {'amount_paid':amount_paid,'amount_word':amount_word,'staff_no':staff_no},
                type: 'post',
                success: function (msg) {
                    // alert(msg)
                    if(msg == 1){
                        alert('Share was successfully converted and waiting for Approval.')
                    }
                    else{
                        alert('Cannot Convert the savings to share, contact system admin.')
                    }
                }
            });//end ajax

    });
   
   
     //===================== End of Share Conversions from the savings  ==================================
     
     
     //===================== Checking the Saving Withdrawal Amount  ==================================
   
     $(document).on('input', '#withdrawal_amount', function(){
        var withdrawal_amount = $(this).val();
    
        $.ajax({
            url: '../../ajax/check_withdrawal_amount.php',
            data: {'withdrawal_amount': withdrawal_amount},
            type: 'post',
            success: function (msg) {
                if (msg == 1) {
                    // Worth is valid
                    console.log('Amount is valid, continue inputting');
                } else if (msg == -1) {
                    // Worth exceeds total share
                    alert('Withdraw amount must be less than your total savings.');
                    $('#withdrawal_amount').val('');
                    $('#withdrawal_amount').focus();
                } else if (msg == -2) {
                    // Remaining balance would fall below 20,000
                    alert('You must maintain a minimum savings balance of ₦2,000 after this transaction.');
                    $('#withdrawal_amount').val('');
                    $('#withdrawal_amount').focus();
                }
            },
            error: function() {
                alert('Error checking Withdraw Amount. Please try again.');
            }
        });
    });


   
   //===================== End of Checking the Share Transfer Amount  ==================================
  


    // Ensure the function runs when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        addCurrencySymbolToAll();
    });

     //===================== Share Print Acknowledgement  ==================================
     
    
    
    $(document).on('submit','#form-profile2',function(e){
        e.preventDefault();
        // var rel = $(this).attr('rel');
        // var type = $('#type').val()
        var data = $(this).serialize()+"&type=2";
        
        // alert(data)
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving please wait...</i></div>')
        $.ajax({
            url: "../ajax/update_bio.php",
            data: data,
            type: "POST",
            // cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    $('.msg').html('<div class="alert offset-md-2 col-6 msg alert-success text-primary"> Profile successfuly updated.</div>');
                    // setTimeout((function(){ window.location = 'payment.php'  }), 100);              

                }else{
                    $('.msg').html('<div class="alert msg offset-md-2 col-6  alert-danger text-primary"> Error! Cannot update profile. Contact system admin</div>');
                }

            }
        });
    });

 //===================== Candidate biodata from admin  ==================================

  $(document).on("submit", "#staff-update", function (e) {

    e.preventDefault();

    if (!confirm("Are you sure you want to update?")) {
        return false;
    }

    var data = $(this).serialize();

    $(".msg").html(
        '<div class="alert offset-md-4 col-md-4 alert-primary"><i> Saving...</i></div>'
    );

    $.ajax({
        url: "../ajax/update_admin_bio.php",
        type: "POST",
        data: data,
        success: function (msg) {

            if ($.trim(msg) == "1") {
                $(".msg").html(
                    '<div class="alert offset-md-4 col-md-4 alert-success text-primary">Profile updated successfully.</div>'
                );
            } else {
                $(".msg").html(
                    '<div class="alert offset-md-4 col-md-4 alert-danger text-primary">Error: ' + msg + '</div>'
                );
            }
        },
        error: function () {
            $(".msg").html(
                '<div class="alert offset-md-4 col-md-4 alert-danger text-primary">Server error. Please try again.</div>'
            );
        }
    });

});
    
    
     //===================== contact  ==================================
     $(document).on('submit','#form-contact',function(e){
        e.preventDefault();
        // var jamb = $('#jamb').val();
        var data = $(this).serialize();
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving...</i></div>');
        // alert(data)
        $.ajax({
            url: "ajax/update_contact.php",
            data: data,
            type: "POST",
            cache:false,
            success: function(msg){
                //alert(msg);
                if(msg ==1){
                    $('.msg').html('<div class="alert offset-md-4 col-4 msg alert-success text-primary"> Contact Information Successfuly.</div>')                        
                    setTimeout((function(){ window.location = 'payment.php'  }), 100);              

                }
                else
                $('.msg').html('<div class="alert msg offset-md-4 col-4  alert-danger text-primary"> Error.</div>')

            }
        });
    });

    // o'level
    //===================== o level  ==================================
    $(document).on('click','.olevel',function(e){
        e.preventDefault();
        // alert('ok')
        // var jamb = $('#jamb').val();
        var sitting = $(this).attr('rel');
        var data = $('#form-olevel'+sitting).serialize();
        alert(data)
        $('.msg'+sitting).html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving...</i></div>')
        $.ajax({
            url: "ajax/update_olevel.php",
            data: data,
            type: "POST",
            cache:false,
            success: function(msg){
            //    alert(msg);
                if(msg ==1){
                    $('.msg'+sitting).html('<div class="alert offset-md-1 col-8 msg alert-success text-primary"> Saved.</div>');
                    // setTimeout((function(){ window.location = 'payment.php'  }), 100);              

                }else{
                    $('.msg'+sitting).html('<div class="alert msg offset-md-1 col-8  alert-danger text-primary"> Error.</div>');
                }

            }
        });
    });

    $(document).on('submit','#form-alevel',function(e){
        e.preventDefault();
        // var jamb = $('#jamb').val();
        var data = $(this).serialize();
        $('.msg').html('<div class="alert offset-md-4 col-4  alert-primary"><i> Saving...</i></div>');
        // alert(data)
        $.ajax({
            url: "ajax/update_alevel.php",
            data: data,
            type: "POST",
            cache:false,
            success: function(msg){
                // alert(msg);
                if(msg ==1){
                    $('.msg').html('<br/><div class="alert offset-md-4 col-4 msg alert-success text-primary"> Saved.</div>')                        
                    //setTimeout((function(){ window.location = 'payment.php'  }), 100);              

                }
                else
                $('.msg').html('<br/><div class="alert msg offset-md-4 col-4  alert-danger text-primary"> Error.</div>')

            }
        });
    });

    //===================== upload passport ==========================
    $(document).on('click','#uploadPic',function(e){
        e.preventDefault();
        //alert('ol');
        // $('#upload').hide();
        $('.msg-passport').html('<br/><span class="alert  alert-warning b">' +
                        ' uploading... </span>')
                            .show();
        var file_data = $('#passport').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        spno = $('#spno').val();

        form_data.append('spno', spno);
        alert(spno)
            $.ajax({
                url: "ajax/upload_passport.php",
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(msg2) {
                    // alert(msg2);
                    if(msg2 ==1) {
                        $('.ms').html('<span class="alert alert-danger b">' +
                        ' &times; File is not Document </span>')
                            .show();
                                    $('#upload').show();
                        // setTimeout((function(){ window.location = '#ms'  }), 100);
                        // $('#loader').hide();
                        // $('#submit-cert').show();

                    }
                    else if(msg2==2){
                        $('.msg-passport').html('<span class="text-danger b"> ' +
                        '&times; Sorry, file already exists. </span>')
                            .show();  
                            $('#upload').show();                        

                    }else if(msg2==-1){
                        $('.msg-passport').html('<span class="text-danger b"> ' +
                        '&times; Please select a file and try again. </span>')
                            .show();
                          
                    }
                    else if(msg2==3){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file is too large. </span>')
                            .show();
                        
                    }
                    else if(msg2==4){
                        $('.msg-passport').html('<div class="alert alert-danger b">' +
                        ' &times; Only jpg, png, and pdf file extensions are allowed. </div>')
                            .show();                        
                    }
                    else if(msg2==5){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                        
                    }else if(msg2==7){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                    }
                    else{
                        // alert(msg2)
                        $('.msg-passport').html('<span class="alert-success b">' +
                        ' uploaded. </span>').show();
                        $('#img-pass').html('<b>ok</b>');
                        $('#upload').show();
                        $('#img2').html('<img src="uploads/'+msg2+'" style="height:250px;width:250px;" name="img-pass" id="img-pass" class="img-responsive img-thumbnail" alt=""/>');
                        //$('#pic').val(msg2);

                        
                    }
                   /* else{
                        $('.ms').html('<span class="text-danger b"> <b>&times; '+msg2+'.</b> </span>');
                        setTimeout((function(){ window.location = '#ms'  }), 100);
                        $('.loader').hide();
                        $('#submit-cert').show();

                    }*/
                }
            });

    });

    //===================== upload doc ==========================
    $(document).on('click','#uploadDoc',function(e){
        e.preventDefault();
        var type = $(this).attr('rel')
        var role = $(this).attr('role')
        //alert('ol');
        // $('#upload').hide();
        $('#status'+type).html('<i class="  text-warning b">' +
                         ' uploading... </i>');
        var file_data = $('#'+type).prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        jamb = $('#jamb').val();

        form_data.append('jamb', jamb);
        form_data.append('role', role);
        form_data.append('type', type);
        //alert(jamb)
            $.ajax({
                url: "ajax/upload_doc.php",
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(msg2) {
                    // alert(msg2);
                    if(msg2 ==1) {
                        $('#status'+type).html('<span class="alert alert-danger b">' +
                        ' &times; File is not Document </span>')
                            .show();
                                    $('#upload').show();
                        // setTimeout((function(){ window.location = '#ms'  }), 100);
                        // $('#loader').hide();
                        // $('#submit-cert').show();

                    }
                    else if(msg2==2){
                        $('#status'+type).html('<span class="text-danger b"> ' +
                        '&times; Sorry, file already exists. </span>')
                            .show();  
                            $('#upload').show();                        

                    }else if(msg2==-1){
                        $('#status'+type).html('<span class="text-danger b"> ' +
                        '&times; Please select a file and try again. </span>')
                            .show();
                          
                    }
                    else if(msg2==3){
                        $('#status'+type).html('<span class="text-danger b">' +
                        ' &times; Sorry, your file is too large. </span>')
                            .show();
                        
                    }
                    else if(msg2==4){
                        $('#status'+type).html('<div class="alert alert-danger b">' +
                        ' &times; Only jpg, png, and pdf file extensions are allowed. </div>')
                            .show();                        
                    }
                    else if(msg2==5){
                        $('#status'+type).html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                        
                    }else if(msg2==7){
                        $('#status'+type).html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                    }
                    else if(msg2==6){
                       // $('#upload').show();
                        $('#status'+type).text('Uploaded');
                    }
                    else{
                        // alert(msg2)
                        
                        $('#upload').show();
                        $('#status'+type).text('Error!');
                        //$('#img2').html('<img src="uploads/'+msg2+'" style="height:250px;width:250px;" name="img-pass" id="img-pass" class="img-responsive img-thumbnail" alt=""/>');
                        //$('#pic').val(msg2);                        
                    }
                   /* else{
                        $('.ms').html('<span class="text-danger b"> <b>&times; '+msg2+'.</b> </span>');
                        setTimeout((function(){ window.location = '#ms'  }), 100);
                        $('.loader').hide();
                        $('#submit-cert').show();

                    }*/
                }
            });

    });
    
    
    // Commodity Item Management Functions


// CREATE ITEM
$('#item-form').on('submit', function(e) {
    e.preventDefault();

    if (!confirm("Are you sure you want to submit the Item Creation?")) return;

    var formData = $(this).serialize() + "&action=create_item";

    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: formData,
        success: function(response) {
            try {
                let res = (typeof response === "object") ? response : JSON.parse(response);

                if (res.success) {
                    $('.msg').html('<div class="alert alert-success">' + res.message + '</div>');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    $('.msg').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            } catch (err) {
                console.error("Invalid JSON from server:", err);
                $('.msg').html('<div class="alert alert-danger">Error creating the item. Please contact admin.</div>');
            }
        },
        error: function(xhr, status, error) {
            $('.msg').html('<div class="alert alert-danger">Request failed: ' + error + '</div>');
        }
    });
});


// UPDATE ITEM
$(document).on("click", ".update-item", function(e) {
    var item_id = $("#item_id").val();
    var name = $("#item_name").val();
    var type_id = $("#type_id").val();
    var description = $("#item_description").val();
    var unit = $("#unit").val();
    var is_active = $("#is_active").is(":checked") ? 1 : 0;
    //console.log(item_id, name, type_id, description, unit, is_active);

    $.ajax({
        url: "ajax/commodity_ajax.php",
        method: "POST",
        data: {
            "action": "update_item",
            "item_id": item_id,
            "name": name,
            "type_id": type_id,
            "description": description,
            "unit": unit,
            "is_active": is_active
        },
        success: function(response, x) {
            console.log(response);
            if (x == 'success') {
                $('.msg').html('<div class="alert alert-success">Item updated successfully.</div>');
                setTimeout((function() {
                    $("#item-modal").modal("hide");
                    //refreshItemsTable();
                }), 1000);
            }
        }, error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
            $('.msg').html('<div class="alert alert-danger">Error updating item.</div>');
        }
    })
});

// DELETE ITEM
$(document).on("click", ".delete-item", function(e) {
    var item_id = $(this).data("id");
    //console.log(item_id);

    if (confirm("Are you sure you want to delete this item?")) {
        $.ajax({
            url: "ajax/commodity_ajax.php",
            method: "POST",
            data: {"action": "delete_item", "item_id": item_id},
            success: function(response, x) {
                console.log(response);
                if (x == 'success') {
                    $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    refreshItemsTable();
                }
            }, error: function(xhr, status, error) {
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
                $('.msg').html('<div class="alert alert-danger">Error deleting item.</div>');
            }
        })
    }
});

// EDIT ITEM - LOAD DATA
$(document).on("click", ".edit-item", function(e) {
    var item_id = $(this).data("id");
    //console.log(item_id);

    $.ajax({
        url: "ajax/commodity_ajax.php",
        method: "POST",
        data: {"action": "get_item", "item_id": item_id},
        success: function(response, x) {
            console.log(response);
            if (x == 'success') {
                var item = response.data;
                $("#item_id").val(item.item_id);
                $("#item_name").val(item.name);
                $("#type_id").val(item.type_id);
                $("#item_description").val(item.description);
                $("#unit").val(item.unit);
                $("#is_active").prop("checked", item.is_active == 1);
                $("#item-modal-title").text("Edit Item");
                $("#item-modal").modal("show");
            }
        }, error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
        }
    })
});

// ADD NEW ITEM BUTTON
$(document).on("click", "#add-item-btn", function(e) {
      e.preventDefault();
        
        // 2. Get form data
    var formData = $(this).serialize();
    var action = "create_item";
    formData += "&action=" + encodeURIComponent(action);
    
    alert(formData);
    
    $("#item-form")[0].reset();
    $("#item_id").remove();
    $("#item-modal-title").text("Add New Item");
    $("#item-modal").modal("show");
});


    //refreshItemsTable();
    
    // // Load types for dropdown
    // $.ajax({
    //     url: "ajax/commodity_ajax.php",
    //     method: "POST",
    //     data: {"action": "get_types"},
    //     success: function(response, x) {
    //         console.log(response);
    //         if (x == 'success') {
    //             var dropdown = $('#type_id');
    //             dropdown.empty();
    //             dropdown.append('<option value="">Select Type</option>');
                
    //             response.data.forEach(function(type) {
    //                 dropdown.append(`<option value="${type.type_id}">${type.type_name}</option>`);
    //             });
    //         }
    //     }, error: function(xhr, status, error) {
    //         console.log('Error');
    //         console.log('Status Code: ' + xhr.status);
    //         console.log('Error: ' + error);
    //     }
    // });


    // Schedule Commodity for All Staff

$(document).on('submit', '#schedule-all', function (e) {
        e.preventDefault();
        var formdata = $(this).serialize();
    //   var session = $('#session').val();
    //   var semester = $('#semester').val();
    //   var program = $('#program').val();
    //   var level = $('#level').val();
    //   var id = $('#sp').val();
    //   alert(formdata);

      if (confirm('Are you sure you want to save this schedule for all?') ==true) {
        $('#msg-all').html('<h3 id="loading" ><img src="../../img/loading.gif" alt=""> <i>Scheduling please wait...</i></h3>')
        $.ajax({
            url: '../../ajax/schedule.php',
            data: formdata,
            type: 'post',
            success: function (msg) {
            //   alert(msg)
                if(msg == 1){
                           $('#msg-all').html('<div class="alert alert-success">Schedule successfully saved.</div>')
                }
                else{
                           $('#msg-all').html('<div class="alert alert-danger">Schedule failed. Please contact admin.</div>')
                }

            }
        });//end ajax
      }
            

});

     // Schedule Commodity for Individual Staff
    
    $(document).on('submit', '#schedule-individual', function (e) {
        e.preventDefault();
        var formdata = $(this).serialize();
    //   var session = $('#session').val();
    //   var semester = $('#semester').val();
    //   var program = $('#program').val();
    //   var level = $('#level').val();
    //   var id = $('#sp').val();
    //   alert(formdata);

      if (confirm('Are you sure you want to save this schedule for individual?') ==true) {
        $('#msg-individual').html('<h3 id="loading" ><img src="../../img/loading.gif" alt=""> <i>Scheduling please wait...</i></h3>')
        $.ajax({
            url: '../../ajax/schedule.php',
            data: formdata,
            type: 'post',
            success: function (msg) {
            //   alert(msg)
                if(msg == 1){
                           $('#msg-individual').html('<div class="alert alert-success">Schedule successfully saved.</div>')
                }
                else{
                           $('#msg-individual').html('<div class="alert alert-danger">Schedule failed. Please contact admin.</div>')
                }

            }
        });//end ajax
      }
            

    });


 
 
  $('#item-type-form').on('submit', function(e) {
    
    e.preventDefault();
        
        // 2. Get form data
    var formData = $(this).serialize();
    var action = "create_item_type";
    formData += "&action=" + encodeURIComponent(action);
    
   
    
    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: formData,
        success: function (response,x) {
            //alert(response);
            console.log(response);
            if (x == 'success') {
                $('.msg').html('<div class="alert alert-success">Item type created successfully.</div>');
                setTimeout((function(){  location.reload();  }), 1000);
            }
        }, error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
            $('.msg').html('<div class="alert alert-danger">Error creating item type.</div>');
        },
    })
});

$(document).on("click", ".update-item-type", function(e) {
    var type_id = $("#type_id").val();
    var type_name = $("#type_name").val();
    var description = $("#description").val();
    //console.log(type_id, type_name, description);

    $.ajax({
        url: "ajax/commodity_ajax.php",
        method: "POST",
        data: {"action":"update_item_type", "type_id":type_id, "type_name":type_name, "description":description},
        success: function (response,x) {
            //alert(response);
            console.log(response);
            if (x == 'success') {
                $('.msg').html('<div class="alert alert-success">Item type updated successfully.</div>');
                setTimeout((function(){ 
                    $("#type-modal").modal("hide");
                    refreshItemTypesTable();
                }), 1000); 
            }
        }, error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
            $('.msg').html('<div class="alert alert-danger">Error updating item type.</div>');
        },
    })
});

$(document).on("click", ".delete-item-type", function(e) {
    var type_id = $(this).data("id");
    //console.log(type_id);

    if(confirm("Are you sure you want to delete this item type?")) {
        $.ajax({
            url: "ajax/commodity_ajax.php",
            method: "POST",
            data: {"action":"delete_item_type", "type_id":type_id},
            success: function (response,x) {
                //alert(response);
                console.log(response);
                if (x == 'success') {
                    $('.msg').html('<div class="alert alert-success">Item type deleted successfully.</div>');
                    refreshItemTypesTable();
                }
            }, error: function(xhr, status, error) {
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
                $('.msg').html('<div class="alert alert-danger">Error deleting item type.</div>');
            },
        })
    }
});



$(document).on("click", ".edit-item-type", function(e) {
    var type_id = $(this).data("id");
    //console.log(type_id);

    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: {"action":"get_item_type", "type_id":type_id},
        success: function (response,x) {
            //console.log(response);
            if (x == 'success') {
                var type = response.data;
                $("#type_id").val(type.type_id);
                $("#type_name").val(type.type_name);
                $("#description").val(type.description);
                $("#type-modal-title").text("Edit Item Type");
                $("#type-modal").modal("show");
            }
        }, error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
        },
    })
});

$(document).on("click", "#add-item-type-btn", function(e) {
    $("#item-type-form")[0].reset();
    $("#type_id").remove();
    $("#type-modal-title").text("Add New Item Type");
    $("#type-modal").modal("show");
});

// Add Commodity Name

$('#commodity-name-form').on('submit', function(e) {
    e.preventDefault();

    // Show confirmation dialog
    if (!confirm("Are you sure you want to submit this commodity name?")) {
        return; // Cancel submission
    }

    // Serialize form data
    var formData = $(this).serialize();
    var action = "create_commodity_name";
    formData += "&action=" + encodeURIComponent(action);

    // Send AJAX request
    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: formData,
        dataType: "json", // Expect JSON response
        success: function (response) {
            console.log(response);

            // Only handle 'commodity_name_exists' case
            if (response.message === 'commodity_name_exists') {
                $('.msg').html('<div class="alert alert-warning">Commodity Name already exists.</div>');
            } else if (response.success === true) {
                $('.msg').html('<div class="alert alert-success">Commodity Name created successfully.</div>');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                // Do nothing if response success is false and no message
            }
        },
        error: function(xhr, status, error) {
            console.log('Error');
            console.log('Status Code: ' + xhr.status);
            console.log('Error: ' + error);
            // No generic error message is shown anymore
        }
    });
});

// End of Commodity Name

// Edit Commodity Name

$(document).on("click", ".edit-commodity-name", function () {
    var commodity_name_id = $(this).data("id");

    // Inject modal if not already in DOM
    if ($('#name-modal').length === 0) {
        $('body').append(`
            <div id="name-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="name-modal-title" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">

                  <div class="modal-header">
                    <h5 class="modal-title" id="name-modal-title">Edit Commodity Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <div class="modal-body">
                    <input type="hidden" id="commodity_name_id">
                    <div class="form-group">
                      <label for="commodity_name">Commodity Name</label>
                      <input type="text" id="commodity_name" class="form-control">
                    </div>
                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="save-commodity-name">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>

                </div>
              </div>
            </div>
        `);
    }

    // Now perform AJAX to fetch data and populate modal
    $.ajax({
        url: "../ajax/commodity_ajax.php",
        method: "POST",
        data: {
            action: "get_commodity_name",
            commodity_name_id: commodity_name_id
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                var name = response.data;
                $("#commodity_name_id").val(name.commodity_name_id);
                $("#commodity_name").val(name.commodity_name);
                $("#name-modal-title").text("Edit Commodity Name");
                $("#name-modal").modal("show");
            } else {
                alert("Commodity not found.");
            }
        },
        error: function (xhr, status, error) {
            console.log("AJAX Error:", error);
        }
    });
});

// End of Edit Commodity Name

// Commodity Type Based on Commodity Name

$('#commodity_name_id').on('change', function () {
    const commodityId = $(this).val();
    if (commodityId) {
        $.ajax({
            url: '../ajax/commodity_ajax.php',
            method: 'POST',
            dataType: 'json', // ✅ Important: ensures jQuery parses response
            data: {
                action: 'get_item_type_by_commodity',
                commodity_name_id: commodityId
            },
            success: function (res) {
                if (res.success) {
                    let options = '<option value="">Select Item Type</option>';
                    res.data.forEach(function (type) {
                        options += `<option value="${type.id}">${type.name}</option>`;
                    });
                    $('#type_id').html(options);
                } else {
                    $('#type_id').html('<option value="">No types found</option>');
                }
            },
            error: function () {
                $('#type_id').html('<option value="">AJAX error</option>');
            }
        });
    } else {
        $('#type_id').html('<option value="">Select Item Type</option>');
    }
});

// End of Commodity Type Based on Commodity Name


// Update Commodity Name
$(document).on("click", "#save-commodity-name", function () {
    var id = $("#commodity_name_id").val();
    var name = $("#commodity_name").val();

    if (!name.trim()) {
        alert("Commodity name is required.");
        return;
    }

    $.ajax({
        url: "../ajax/commodity_ajax.php",
        method: "POST",
        data: {
            action: "update_commodity_name",
            commodity_name_id: id,
            commodity_name: name
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                alert("Commodity name updated successfully.");
                $("#name-modal").modal("hide");
                location.reload(); // or refresh just the part of the page if using DataTables etc.
            } else {
                alert(response.message || "Update failed.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
});



// End of Update Commodity Name

// Delete Commodity Name

$(document).on("click", ".delete-commodity-name", function(e) {
    var type_id = $(this).data("id");
    //console.log(type_id);

    if(confirm("Are you sure you want to delete commodity name?")) {
        $.ajax({
            url: "ajax/commodity_ajax.php",
            method: "POST",
            data: {"action":"delete_commodity_name", "commodity_name_id":commodity_name_id},
            success: function (response,x) {
                //alert(response);
                console.log(response);
                if (x == 'success') {
                    $('.msg').html('<div class="alert alert-success">Commodity Name deleted successfully.</div>');
                    refreshItemTypesTable();
                }
            }, error: function(xhr, status, error) {
                console.log('Error');
                console.log('Status Code: ' + xhr.status);
                console.log('Error: ' + error);
                $('.msg').html('<div class="alert alert-danger">Error deleting Commodity Name.</div>');
            },
        })
    }
});



// End of Commodity Name




// Common functions for all batch pages
function showToast(type, message) {
    const toast = $(`<div class="toast toast-${type}">${message}</div>`);
    $('body').append(toast);
    setTimeout(() => toast.remove(), 3000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'NGN' // Change to your currency
    }).format(amount);
}

// For batches.php
function refreshBatchesTable() {
    $.ajax({
        url: 'ajax/commodity_ajax.php',
        type: 'POST',
        data: { action: 'get_batches' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#batches-table tbody').empty();
                
                response.batches.forEach(function(batch) {
                    const row = `
                        <tr>
                            <td>${batch.batch_id}</td>
                            <td>${batch.batch_name}</td>
                            <td>${batch.description || ''}</td>
                            <td>${batch.opening_date}</td>
                            <td>${batch.closing_date}</td>
                            <td>${batch.max_members_per_batch || 'Unlimited'}</td>
                            <td>${batch.max_loan_amount ? formatCurrency(batch.max_loan_amount) : 'No limit'}</td>
                            <td>
                                <span class="badge ${batch.is_active ? 'bg-success' : 'bg-secondary'}">
                                    ${batch.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td>
                                <a href="edit_batch.php?id=${batch.batch_id}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    `;
                    $('#batches-table tbody').append(row);
                });
            } else {
                showToast('error', response.message);
            }
        }
    });
}

// For create_batch.php
$('#create-batch-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        batch_name: $('#batch_name').val(),
        description: $('#batch_description').val(),
        opening_date: $('#opening_date').val(),
        closing_date: $('#closing_date').val(),
        max_members: $('#max_members').val() || null,
        max_loan_amount: $('#max_loan_amount').val() || null
    };
    
    // Validate dates
    if (new Date(formData.opening_date) > new Date(formData.closing_date)) {
        showToast('error', 'Closing date must be after opening date');
        return;
    }
    
    console.log(formData);
    
    
    
    $.ajax({
        url: '../../ajax/commodity_ajax.php',
        type: 'POST',
        data: {
            action: 'create_batch',
            ...formData
        },
        dataType: 'json',
        beforeSend: function() {
            $('#create-batch-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        },
        success: function(response) {
            if (response.success) {
                 $('.msg').html('<div class="alert alert-success">Batch created successfully.</div>');
                setTimeout((function(){  location.reload();  }), 1000);
            } else {
                showToast('error', response.message);
            }
        },
        complete: function() {
            $('#create-batch-btn').prop('disabled', false)
                .html('<i class="fas fa-save"></i> Create Batch');
        }
    });
});

// For edit_batch.php
$('#edit-batch-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        batch_id: $('#batch_id').val(),
        batch_name: $('#batch_name').val(),
        description: $('#batch_description').val(),
        opening_date: $('#opening_date').val(),
        closing_date: $('#closing_date').val(),
        max_members: $('#max_members').val() || null,
        max_loan_amount: $('#max_loan_amount').val() || null,
        is_active: $('#is_active').is(':checked') ? 1 : 0
    };
    
    // Validate dates
    if (new Date(formData.opening_date) > new Date(formData.closing_date)) {
        showToast('error', 'Closing date must be after opening date');
        return;
    }
    
    $.ajax({
        url: '../ajax/commodity_ajax.php',
        type: 'POST',
        data: {
            action: 'update_batch',
            ...formData
        },
        dataType: 'json',
        beforeSend: function() {
            $('#update-batch-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        },
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                setTimeout(() => {
                    window.location.href = '../batches.php';
                }, 1500);
            } else {
                showToast('error', response.message);
            }
        },
        complete: function() {
            $('#update-batch-btn').prop('disabled', false)
                .html('<i class="fas fa-save"></i> Update Batch');
        }
    });
});

 
    
    
    
    
    
    

    //===================== upload passport ==========================
    $(document).on('click','#uploadCandidate',function(e){
        e.preventDefault();
        //alert('ol');
        // $('#upload').hide();
        $('.msg').html('<br/><span class="alert  alert-warning b">' +
                        ' uploading... </span>')
                            .show();
        var file_data = $('#file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        session = $('#session').val();

        form_data.append('session', session);
        alert(session)
            $.ajax({
                // url: "ajax/upload_passport.php",
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(msg2) {
                    // alert(msg2);
                    if(msg2 ==1) {
                        $('.ms').html('<span class="alert alert-danger b">' +
                        ' &times; File is not Document </span>')
                            .show();
                                    $('#upload').show();
                        // setTimeout((function(){ window.location = '#ms'  }), 100);
                        // $('#loader').hide();
                        // $('#submit-cert').show();

                    }
                    else if(msg2==2){
                        $('.msg-passport').html('<span class="text-danger b"> ' +
                        '&times; Sorry, file already exists. </span>')
                            .show();  
                            $('#upload').show();                        

                    }else if(msg2==-1){
                        $('.msg-passport').html('<span class="text-danger b"> ' +
                        '&times; Please select a file and try again. </span>')
                            .show();
                          
                    }
                    else if(msg2==3){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file is too large. </span>')
                            .show();
                        
                    }
                    else if(msg2==4){
                        $('.msg-passport').html('<div class="alert alert-danger b">' +
                        ' &times; Only jpg, png, and pdf file extensions are allowed. </div>')
                            .show();                        
                    }
                    else if(msg2==5){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                        
                    }else if(msg2==7){
                        $('.msg-passport').html('<span class="text-danger b">' +
                        ' &times; Sorry, your file was not uploaded. </span>')
                            .show()
                        ;
                    }
                    else{
                        // alert(msg2)
                        $('.msg-passport').html('<span class="alert-success b">' +
                        ' uploaded. </span>').show();
                        $('#img-pass').html('<b>ok</b>');
                        $('#upload').show();
                        $('#img2').html('<img src="uploads/'+msg2+'" style="height:250px;width:250px;" name="img-pass" id="img-pass" class="img-responsive img-thumbnail" alt=""/>');
                        //$('#pic').val(msg2);

                        
                    }
                   /* else{
                        $('.ms').html('<span class="text-danger b"> <b>&times; '+msg2+'.</b> </span>');
                        setTimeout((function(){ window.location = '#ms'  }), 100);
                        $('.loader').hide();
                        $('#submit-cert').show();

                    }*/
                }
            });

    });
    
    
    
    
     //===================== Endorsement of Loan ==================================
$(document).on('submit', '#form-fetch_savings', function(e){
    e.preventDefault(); // Prevent the form from submitting normally

    // Confirm the action with the user
  
    var data = $(this).serialize(); // Serialize the form data

    console.log(data)
    // Display a loading message
    $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Fetching please wait...</i></div>');

    // Perform the AJAX request
    $.ajax({
        url: "../ajax/fetch_members_savings.php", // The URL to send the form data to
        data: data, // The serialized form data
        type: "POST", // The HTTP method to use
        success: function(msg){
            
            
                // Success message
                $('.msg').html(msg);
                // Redirect after a short delay
                //setTimeout(function(){ window.location = '../auth/logout.php'; }, 100);
            
        }
    });
});


 //===================== form-fetch-withdrawals ================================== 
// $(document).on('submit', '#form-fetch-withdrawals', function(e){
//     e.preventDefault(); // Prevent the form from submitting normally

//     // Confirm the action with the user
//     const from = $('#from').val();
//         const to = $('#to').val();
        
//         if (!from || !to) {
//             alert('Please select both FROM and TO dates.');
//             return;
//         }
        
//         if (from > to) {
//             alert('FROM date cannot be later than TO date.');
//             return;
//         }
        
        
//     //console.log(data)
//     // Display a loading message
//     $('.msg').html('<div class="alert offset-md-4 col-4 alert-primary"><i> Fetching please wait...</i></div>');

//     // Perform the AJAX request
//     $.ajax({
//         url: '../ajax/fetch_withdrawals_ajax.php',
//         type: 'POST',
//          { from: from, to: to },
//         dataType: 'json',
//         success: function(msg){
            
            
//                 // Success message
//                 $('.msg').html(msg);
//                 // Redirect after a short delay
//                 //setTimeout(function(){ window.location = '../auth/logout.php'; }, 100);
            
//         }
//     });
// });



            // Function to load batches when supply selection changes
        function loadSupplyItems(supplyId) {
            // Clear previous batch data
            $('#CommodityItemsTable tbody').html('<tr><td colspan="6" class="text-center"><i class="bi bi-arrow-repeat spin"></i> Loading Items...</td></tr>');
            
            // Hide batch actions until we have data
            $('#batchActions').hide();
            
            // Only proceed if a supply is selected
            if (!supplyId) {
                $('#CommodityItemsTable tbody').html('<tr><td colspan="6" class="text-center">Please select a supply</td></tr>');
                return;
            }
            
            //console.log(supplyId);
            
            // AJAX request to get batches for selected supply
            $.ajax({
                url: '../ajax/commodity_management.php',
                type: 'POST',
                dataType: 'json',
                data: { 
                    action: 'get_supply_items', 
                    supply_id: supplyId 
                },
                success: function(response) {
                    console.log(response);
                    const batchesTable = $('#CommodityItemsTable tbody');
                    batchesTable.empty();
                    
                    // Handle errors
                    if (!response.success) {
                        batchesTable.append('<tr><td colspan="6" class="text-center text-danger">Error: ' + response.message + '</td></tr>');
                        return;
                    }
                    
                    // Handle no batches found
                    if (response.data.length === 0) {
                        batchesTable.append('<tr><td colspan="6" class="text-center">No Item found for this supply</td></tr>');
                        return;
                    }
                    
                    // Populate batches table
                    response.data.forEach(function(batch) {
                        batchesTable.append(`
                            <tr data-id="${batch.commodity_item_id}">
                                <td>${batch.commodity_item}</td>
                                 <td>${batch.price_unit}</td>
                                <td>${batch.allocated_quantity}</td>
                               <td>${batch.remaining_quantity}</td>
                                 <td>${batch.max_senior_quantity}</td>
                                  <td>${batch.max_junior_quantity}</td>
                                <td><span class="badge ${getStatusBadgeClass(batch.is_active)}">
                                    ${batch.is_active == 1 ? 'Active' : 'Closed'}
                                </span></td>
                                   <td>
                                   
                                    <button class="btn btn-sm btn-outline-secondary edit-batch" data-id="${batch.commodity_item_id}">
                                        <i class="bi bi-pencil"></i> Add Allocation
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    // Enable batch actions
                    $('#batchActions').show();
                },
                error: function(xhr, status, error) {
                    $('#CommodityItemsTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading batches: ' + error + '</td></tr>');
                }
            });
        }
        

 
        // Function to load batches when supply selection changes
        function loadBatches(supplyId) {
            // Clear previous batch data
            $('#batchesTable tbody').html('<tr><td colspan="6" class="text-center"><i class="bi bi-arrow-repeat spin"></i> Loading batches...</td></tr>');
            
            // Hide batch actions until we have data
            $('#batchActions').hide();
            
            // Only proceed if a supply is selected
            if (!supplyId) {
                $('#batchesTable tbody').html('<tr><td colspan="6" class="text-center">Please select a supply</td></tr>');
                return;
            }
            
            // AJAX request to get batches for selected supply
            $.ajax({
                url: '../ajax/commodity_management.php',
                type: 'GET',
                dataType: 'json',
                data: { 
                    action: 'get_batches',
                    supply_id: supplyId 
                },
                success: function(response) {
                    const batchesTable = $('#batchesTable tbody');
                    batchesTable.empty();
                    
                    // Handle errors
                    if (!response.success) {
                        batchesTable.append('<tr><td colspan="6" class="text-center text-danger">Error: ' + response.message + '</td></tr>');
                        return;
                    }
                    
                    // Handle no batches found
                    if (response.data.length === 0) {
                        batchesTable.append('<tr><td colspan="6" class="text-center">No batches found for this supply</td></tr>');
                        return;
                    }
                    
                    // Populate batches table
                    response.data.forEach(function(batch) {
                        batchesTable.append(`
                            <tr data-id="${batch.batch_id}">
                                <td>${batch.batch_name}</td>
                                <td>${formatDate(batch.opening_date)} to ${formatDate(batch.closing_date)}</td>
                                <td><span class="badge ${getStatusBadgeClass(batch.is_active)}">
                                    ${batch.is_active == 1 ? 'Active' : 'Closed'}
                                </span></td>
                                <td>${batch.max_members || 'Unlimited'}</td>
                             <td>${batch.max_loan_amount ? '₦' + Number(batch.max_loan_amount).toLocaleString('en-US') : 'Unlimited'}</td>
                                <td>
                                    <a href="addCommodityItems.php?batch=${batch.batch_id}" class="btn btn-sm btn-outline-primary view-items" data-id="${batch.batch_id}">
                                        <i class="bi bi-box-seam"></i> Add/View Items 
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary edit-batch" data-id="${batch.batch_id}">
                                        <i class="bi bi-pencil"></i> View
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    // Enable batch actions
                    $('#batchActions').show();
                },
                error: function(xhr, status, error) {
                    $('#batchesTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading batches: ' + error + '</td></tr>');
                }
            });
        }
        
        // Helper function to format dates
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
        
        // Helper function to get status badge class
       function getStatusBadgeClass(isActive) {
            return isActive == 1 ? 'bg-success' : 'bg-danger';
        }
        
        // Event listener for supply selection change
        $('#supplySelect').change(function() {
            const supplyId = $(this).val();
            loadBatches(supplyId);
            
            // Enable/disable "Add Items" button based on selection
            $('#addItemsBtn').prop('disabled', !supplyId);
            
            // Store the selected supply ID for later use
            $('#supplyId').val(supplyId);
        });
        
         $('#supplyItemsSelect').change(function() {
            const supplyId = $(this).val();
            loadSupplyItems(supplyId);
            
            // Enable/disable "Add Items" button based on selection
            $('#addItemsBtn').prop('disabled', !supplyId);
            
            // Store the selected supply ID for later use
            $('#supplyId').val(supplyId);
        });
 

       // Initialize all Bootstrap modals
    const addItemsModal = new bootstrap.Modal(document.getElementById('addItemsModal'));
    
    // Event listener for Add Items button
    $('#addItemsBtn').click(function() {
        // Get the selected batch ID (you'll need to implement this)
        const batchId = $('#batchesTable tbody tr.active').data('id');
        
        if (!batchId) {
            alert('Please select a batch first');
            return;
        }
        
        // Set the batch ID in the form
        $('#batchId').val(batchId);
        
        // Show the modal
        addItemsModal.show();
    });
    
    // Make table rows selectable
    $(document).on('click', '#batchesTable tbody tr', function() {
        $('#batchesTable tbody tr').removeClass('active');
        $(this).addClass('active');
        $('#addItemsBtn').prop('disabled', false);
    });
    
    

   // let itemsListArray = [];

    $(document).on("click", "#addToSupply", function() {
        
                alert('Hello');
                $('#errorTxt').text(""); // Clear previous errors
        
                // --- 1. GET VALUES FROM FORM ---
                const selectedOption = $('#item_id').find(":selected");
                const itemId = $('#item_id').val();
                const quantity = $('#quantity').val();
                
                console.log(itemId);
        
                // CORRECTLY get item name by reading the text of the selected option
                // and cleaning it up to remove the stock count in parentheses.
                const itemNameFull = selectedOption.text();
                const itemName = itemNameFull.includes('(') ? itemNameFull.split(' (')[0] : itemNameFull;
        
                // CORRECTLY get data attributes. jQuery's .data() method automatically
                // handles the 'data-' prefix.
                const unitPrice = selectedOption.data("unit-price");
                const stock = selectedOption.data("item-qty"); // This variable was missing
        
                // --- 2. VALIDATE INPUTS ---
                const numQuantity = parseInt(quantity);
                const numStock = parseInt(stock);
        
                // Check if an item is selected
                if (!itemId) {
                    $('#errorTxt').text("Please select an item.");
                    return; // Stop the function
                }
        
                // Check if the item already exists in our list
                const isExisting = itemsListArray.some(item => item.itemId === itemId);
                if (isExisting) {
                    $('#errorTxt').text("This item has already been added to the batch.");
                    return;
                }
        
                // Check for valid quantity
                if (isNaN(numQuantity) || numQuantity < 1) {
                    $('#errorTxt').text("Quantity must be at least 1.");
                    return;
                }
        
                // Check if requested quantity exceeds available stock
                if (numQuantity > numStock) {
                    $('#errorTxt').text(`Quantity exceeds stock. Only ${numStock} available.`);
                    return;
                }
        
                // --- 3. ADD ITEM TO ARRAY ---
                // If all checks pass, add the new item object to our array
                itemsListArray.push({
                    itemId: itemId,
                    itemName: itemName,
                    quantity: numQuantity,
                    unitPrice: parseFloat(unitPrice)
                });
        
                // --- 4. REBUILD AND RENDER THE TABLE ---
                let tableBody = "";
                let totalCost = 0;
                let sn = 0;
        
                // CORE FIX: Loop over the CORRECT array (`itemsListArray`)
                itemsListArray.forEach(item => {
                    sn++;
                    const itemTotal = item.unitPrice * item.quantity;
                    totalCost += itemTotal;
        
                    // Use the correct property names ('unitPrice') as defined in the object above
                    tableBody += `
                        <tr>
                            <td>${sn}</td>
                            <td>${item.itemName}</td>
                            <td>${item.quantity}</td>
                            <td>&#8358;${item.unitPrice.toLocaleString()}</td>
                            <td>&#8358;${itemTotal.toLocaleString()}</td>
                            <td><button class="btn btn-danger btn-sm removeSellItem" data-item-id="${item.itemId}">x</button></td>
                        </tr>
                    `;
                });
        
                // --- 5. UPDATE THE TABLE IN THE HTML ---
                $("#tb").html(tableBody);
        
                
        
                // Clear the form fields for the next entry
                $('#quantity').val('');
                $('#item_id').val('').trigger('change'); // .trigger('change') is good for select2
            });
       






//Chairman Withdrawal Approvement
// Master checkbox toggle
    $('#master_checkbox').on('change', function() {
        $('.withdrawal_checkbox').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });

    // Individual checkbox change
    $(document).on('change', '.withdrawal_checkbox', function() {
        updateBulkButtons();
    });

    // Update bulk action buttons state
    function updateBulkButtons() {
        const anyChecked = $('.withdrawal_checkbox:checked').length > 0;
        $('#approve_selected, #reject_selected').prop('disabled', !anyChecked);
    }

    // Bulk Approve
    $('#approve_selected').on('click', function() {
        const selectedIds = $('.withdrawal_checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one request to approve.');
            return;
        }

        if (!confirm(`Approve ${selectedIds.length} selected withdrawal request(s)?`)) {
            return;
        }

        $.ajax({
            url: '../ajax/bulk_approve_withdrawal.php',
            type: 'POST',
            data: { withdrawal_ids: selectedIds, action: 'approve' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Optionally reload or remove approved rows
                    location.reload();
                } else {
                    $('.msg').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            }
        });
    });

    // Bulk Reject
    $('#reject_selected').on('click', function() {
        const selectedIds = $('.withdrawal_checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one request to reject.');
            return;
        }

        if (!confirm(`Reject ${selectedIds.length} selected withdrawal request(s)?`)) {
            return;
        }

        $.ajax({
            url: 'bulk_approve_withdrawal.php',
            type: 'POST',
            data: { withdrawal_ids: selectedIds, action: 'reject' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    location.reload();
                } else {
                    $('.msg').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            }
        });
    });

    // Search functionality
    $('#searchEndorsment').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#endorsementBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });
    
    












});//end ready fxn