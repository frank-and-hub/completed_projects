<script type="text/javascript">
$(document).ready(function() {

    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'investmentplan' : 'required',
            'memberid' : 'required',
            'form_number' : {required: true, number: true},
            'ssbacount' : 'required',
            'fn_first_name' : 'required',
            //'fn_second_name' : 'required',
            'fn_relationship' : 'required',
            'fn_dob' : 'required',
            'fn_age' : 'required',
            'fn_percentage' : 'required',
            'fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'sn_first_name' : 'required',
            //'sn_second_name' : 'required',
            'sn_relationship' : 'required',
            'sn_dob' : 'required',
            'sn_age' : 'required',
            'sn_percentage' : 'required',
            'sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            //'guardian-ralationship' : 'required',
            'phone-number' : {number: true,minlength: 10,maxlength:12},
            'monthly-deposite-amount' : {required: true, number: true},
            //'daughter-name' : 'required',
            //'dob' : 'required',
            //'tenure' : 'required',
            //'age' : 'required',
            'payment-mode' : 'required',
            'cheque-number' : {required: true, number: true},
            'bank-name' : 'required',
            'branch-name' : 'required',
            'cheque-date' : 'required',
            'transaction-id' : 'required',
            'date' : 'required',
            'fn_gender' : 'required',
            'sn_gender' : 'required',
            'amount' : {required: true, number: true},
        }
    });

    

    // Show investment form according to plan
    $(document).on('change','#investmentplan',function(){
        var plan = $('option:selected', this).attr('data-val');
        $('#plan_type').val(plan);
        $.ajax({
            type: "POST",  
            url: "{!! route('investment.planform') !!}",
            data: {'plan':plan},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(".plan-content-div").html(response);   
                $('.fn_dateofbirth,.sn_dateofbirth,,#dob,#cheque-date,#date,#re_member_dob').datepicker({ 
                   format: "yyyy-mm-dd",
                   orientation: "top",
                   autoclose: true
                }); 
            }
        });       
    });
    

    // Select payment option
    $(document).on('change','#payment-mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');   
        $('.p-mode').hide();
        $('.'+paymentMode+'').show();    
    });

    var formName = $('#formName').val();
    var investmentId = $('#investmentId').val();
    $.ajax({
        type: "POST",  
        url: "{!! route('investment.editplanform') !!}",
        data: {'formName':formName,'investmentId':investmentId},
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $(".plan-content-div").html(response);   
            $('.fn_dateofbirth,.sn_dateofbirth,.dateofbirth,.calendardate').datepicker( {
               format: "yyyy-mm-dd",
               orientation: "top",
               autoclose: true
            }); 
        }
    });

    // Calculate age from date
    $(document).on('change','.fn_dateofbirth,.sn_dateofbirth,#dob',function(){
        var date = $(this).val();
        var inputId = $(this).attr('data-val');
        dob = new Date(date);
        var today = new Date();
        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
        $('#'+inputId+'').val(age);            
    });

    $(document).on('click','#primary_account',function(){
        //var val = $(this).val();
        if ($("input[type=checkbox]").is( 
                      ":checked")) { 
            $('#hidden_primary_account').val(1) 
        } else { 
            $('#hidden_primary_account').val(0) 
        }  

    });

    $(document).on('click','.investment-correction',function(){
      var cStatus = $(this).attr('data-correction-status');
      if(cStatus == '0'){     
        swal("Warning!", 'Correction request already submitted!', "warning");
      }
    });

    $('#member-correction-form').validate({ // initialize the plugin
         rules:{
                corrections:{
                    required:true,
                },
                
            },
            messages:{
                corrections:{
                    "required":"Please enter description."
                },
                
            }
    });

    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    // Hide loading image
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

});

</script>