<script type="text/javascript">
$(document).ready(function() {
    var state_id="{{ $memberDetail['state_id'] }}";
    var district="{{ $memberDetail['district_id'] }}";
    var city="{{ $memberDetail['city_id'] }}";

    $(document).on('change','#photo',function(){
        $("#upload_form").submit();
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#photo-preview').attr('src', e.target.result);
                $('#photo-preview').attr('style', 'width:200px; height:200px;');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $(document).on('change','#signature',function(){
        $("#signature_form").submit();
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#signature-preview').attr('src', e.target.result);
                $('#signature-preview').attr('style', 'width:100%;');
            }

            reader.readAsDataURL(this.files[0]);
        }
    });

    getcity(state_id,district,city);
    // Branch Form validations
    $('#branch-create').validate({ // initialize the plugin
        rules: {
            name : 'required',
            state : 'required',
            city : 'required',
            zone : 'required',
            pin_code : {
                required: true,
                minlength: 6,
                maxlength: 6,
                digits: true
            },
            address : 'required',
            phone : {
                required: true,
                minlength: 10,
                maxlength: 12,
                digits: true
            },
            password: {
                required: true,
                minlength : 6
            },
            password_confirmation: {
                required: true,
                minlength : 6,
                equalTo : "#password"
            },
        },
        messages: {
            name:{
                required: 'Please enter valid Branch Name.',
            },
            state:{
                required: 'Please select a State.',
            },
            city:{
                required: 'Please select a City.',
            },
            zone:{
                required: 'Please enter Zone/Sector.',
            },
            pin_code:{
                required: 'Please enter Postal Code.',
                minlength: 'Please enter at least 6 digit.',
                maxlength: 'Please enter no more than 6 digit',
                digits: 'Please enter only digits',
            },
            address:{
                required: 'Please enter Address.',
            },
            phone:{
                required: 'Please enter valid Phone Number.',
                minlength: 'Please enter at least 10 digit.',
                maxlength: 'Please enter no more than 12 digit',
                digits:  'Please enter only digits'
            },
            password:{
                required: 'Please enter Password.',
                minlength: 'Please enter at least 6 characters.',
            },
            password_confirmation:{
                required: 'Please enter Password.',
                minlength: 'Please enter at least 6 characters.',
                equalTo:  'Password did not matched'
            },
        }
    });

    $('#branch-update').validate({
        rule: {
            phone : {
                required: true,
                minlength: 10,
                maxlength: 12,
                digits: true
            },
            password: {
                minlength : 6
            },
            password_confirmation: {
                minlength : 6,
                equalTo : "#password"
            },
        },
        message: {
            phone:{
                required: 'Please enter valid Phone Number.',
                minlength: 'Please enter at least 10 digit.',
                maxlength: 'Please enter no more than 12 digit.',
                digits:  'Please enter only digits'
            },
            password:{
                minlength: 'Please enter at least 6 characters.',
            },
            password_confirmation:{
                minlength: 'Please enter at least 6 characters.',
                equalTo:  'Password did not matched'
            },
        }

    });
   /** Ip Address Validation ******/
    $.validator.addMethod('IP4Checker', function(value) {
        var ip = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
        ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
        return value.match(ip);
    }, 'Please enter valid Ip Address.');

    $('#update-ip').validate({ // initialize the plugin
        rules: {
            ip_address : {
                required: true,
                IP4Checker: true
            }
        },
        messages: {
            ip_address:{
                required: 'Please enter Ip Address.',
            },
        }
    });

    $('#add-ip').validate({ // initialize the plugin
        rules: {
            ip_address : {
                required: true,
                IP4Checker: true
            }
        },
        messages: {
            ip_address:{
                required: 'Please enter Ip Address.',
            },
        }
    });

    $( "form[name='change-password']" ).submit(function() {
        alert("hello ");
        return this.some_flag_variable;
    });

    $('#reinvest_transaction').validate({ // initialize the plugin
        rules: {
            'closing_Balance_reinvest' : {required: true, number: true},
            'collection_reinvest_amount' : {number: true},
            'payment_mode' : {required: true}
        },
        messages: {
            closing_Balance_reinvest: {
                required: 'Please enter closing balance.',
            },
            collection_reinvest_amount: {
                required: 'Please enter collection amount.',
            },
            payment_mode: {
                required: 'Please select payment type.',
            },
        }
    });

    $('#investAccountNumber').validate({
        rules: {
            investAccountNumber: {
                required: true,
            },
        },
        messages: {
            investAccountNumber: {
                required: 'Please enter old account number.',
            },
        }
    });

    $('#update_reinvestment').validate({
        rules: {
            photo: {
                // required: true,
                extension: "jpg|jpeg|png|pdf"
            },
            signature: {
                // required: true,
                extension: "jpg|jpeg|png|pdf"
            },
            form_no: {
                required: true,
                number: true,
                //checkFormNo:true,
            },
            application_date: {
                required: true,
                // date : true,
            },
            first_name: "required",
            //last_name: "required",
            email: {
                // required: true,
                email: function (element) {
                    if ($("#email").val() != '') {
                        return true;
                    } else {
                        return false;
                    }
                },
                checkEmail: function (element) {
                    if ($("#email").val() != '') {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            mobile_no: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 12
            },
            dob: {
                required: true,
            },
            gender: "required",
            annual_income: {
                required: true,
                number: true,
            },
            f_h_name: "required",
            bank_account_no: {
                number: true,
            },
            nominee_first_name: "required",
            nominee_gender: "required",
            nominee_dob: {
                required: true,
            },

            nominee_mobile_no: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 12
            },
            parent_nominee_name: {
                required: function (element) {
                    if ($("#is_minor").prop("checked") == true) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            parent_nominee_mobile_no: {
                required: function (element) {
                    if ($("#is_minor").prop("checked") == true) {
                        return true;
                    } else {
                        return false;
                    }
                },
                number: true,
            },
            parent_nominee_mobile_age: {
                required: function (element) {
                    if ($("#is_minor").prop("checked") == true) {
                        return true;
                    } else {
                        return false;
                    }
                },
                number: true,
            },
            address: "required",
            state_id: "required",
            city_id: "required",
            district_id: "required",
            marital_status: "required",
            anniversary_date: {
                required: function (element) {
                    if ($("#married").prop("checked") == true) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },



            pincode: {
                required: true,
                number: true,
                minlength: 6,
                maxlength: 6
            },
            first_id_type: "required",
            first_id_proof_no: {
                required: true,
                checkIdNumber: '#first_id_type',
            },
            first_address_proof: {
                required: function (element) {
                    if ($("#first_same_as").prop("checked") == false) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            second_id_type: "required",
            second_id_proof_no: {
                required: true,
                checkIdNumber: '#second_id_type',
            },
            second_address_proof: {
                required: function (element) {
                    if ($("#second_same_as").prop("checked") == false) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            associate_code: "required",
            associate_name: "required",
        },
        messages: {
            photo: {
                required: 'Please select photo.',
                extension: "Accept only png,jpg or pdf files."
            },
            signature: {
                required: 'Please select signature.',
                extension: "Accept only png,jpg or pdf files."
            },
            form_no: {
                required: "Please enter form number.",
                number: "Please enter a valid number.",
            },
            application_date: {
                required: "Please enter application date.",
                number: "Please enter a valid date.",
            },
            first_name: {
                required: "Please enter first name.",
            },
            last_name: {
                required: "Please enter last name.",
            },
            email: {
                required: "Please enter email id.",
                email: "Please enter valid email id.",
            },
            mobile_no: {
                required: "Please enter mobile number.",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  10 or maximum 12 digit.",
                maxlength: "Please enter minimum  10 or maximum 12 digit."
            },
            dob: {
                required: "Please enter date of birth date.",
                date: "Please enter valid date.",
            },
            marital_status: "Please select marital status",
            gender: "Please select gender.",
            occupation: "Please select occupation.",
            annual_income: {
                required: "Please enter annual income.",
                number: "Please enter valid number.",
            },
            mother_name: "Please enter mother name.",
            f_h_name: "Please enter father/husband name.",
            bank_account_no: {
                number: "Please enter valid number.",
            },
            nominee_first_name: "Please enter nominee name.",
            nominee_gender: "Please select nominee gender.",
            nominee_dob: {
                required: "Please enter nominee date of birth.",
            },

            nominee_mobile_no: {
                required: "Please enter nominee mobile number.",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  10 or maximum 12 digit.",
                maxlength: "Please enter minimum  10 or maximum 12 digit."
            },
            parent_nominee_name: {
                required: "Please enter nominee parent name.",
            },
            parent_nominee_mobile_no: {
                required: "Please enter nominee parent name.",
                number: "Please enter valid number.",
            },
            parent_nominee_mobile_age: {
                required: "Please enter nominee parent age.",
                number: "Please enter valid number.",
            },
            address: "Please enter address.",
            state_id: "Please select state.",
            city_id: "Please  select city.",
            district_id: "Please select district.",
            pincode: {
                required: "Please enter pincode.",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  6 or maximum 6 digit.",
                maxlength: "Please enter minimum  6 or maximum 6 digit."
            },
            first_id_type: "Please select id type.",
            first_id_proof_no: {
                required: "Please enter id number.",
            },
            first_address_proof: {
                required: "Please enter address.",
            },
            second_id_type: "Please select id type.",
            second_id_proof_no: {
                required: "Please enter id number.",
            },
            second_address_proof: {
                required: "Please enter address.",
            },
            associate_code: "Please enter associate code.",
            associate_name: "Please enter associate name.",
            'investmentplan' : 'required',
            'memberid' : 'required',
            'form_number' : {required: true, number: true},
            'ssbacount' : 'required',
            'fn_first_name' : 'required',
            //'fn_second_name' : 'required',
            'fn_relationship' : 'required',
            'fn_dob' : 'required',
            'fn_age' : 'required',
            'fn_age' : 'required',
            'fn_percentage' : 'required',
            'fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'ssb_fn_first_name' : 'required',
            'ssb_fn_relationship' : 'required',
            'ssb_fn_dob' : 'required',
            'ssb_fn_age' : 'required',
            'ssb_fn_percentage' : 'required',
            'ssb_fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'sn_first_name' : 'required',
            'sn_relationship' : 'required',
            'sn_dob' : 'required',
            'sn_age' : 'required',
            'sn_percentage' : 'required',
            'sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'phone-number' : {number: true,minlength: 10,maxlength:12},
            'monthly-deposite-amount' : {required: true, number: true},
            'tenure' : 'required',
            'payment-mode' : {required: true},
            'cheque-number' : {required: true, number: true},
            'bank-name' : 'required',
            'branch-name' : 'required',
            'cheque-date' : 'required',
            'transaction-id' : 'required',
            'date' : 'required',
            'fn_gender' : 'required',
            'sn_gender' : 'required',
            'amount' : {required: true, number: true},
            'ssbamount' : {required: true, number: true},
            'closing_Balance_reinvest' : {required: true, number: true},
            'collection_reinvest_amount' : {number: true},
            'payment_mode' : {required: true}
        },
        submitHandler: function() {
            $('.update-re-button').prop('disabled', true);
            return true;
        },
    });

    /*$('#reinvest_plane').validate({ 
    });

    $('#reinvest_transaction').validate({
    });*/

    jQuery.validator.addClassRules("deposit-amount", {
          number: true,
    });

    jQuery.validator.addClassRules("renewal_date", {
      required: true,
    });
    
    var branchTable = $('#branch').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.listing') !!} ",
            "type": "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'state_id', name: 'state_id'},
            {data: 'city_id', name: 'city_id'},
            {data: 'phone', name: 'phone'},
            {data: 'address', name: 'address'},
            {data: 'created_at', name: 'created_at'},
            {data: 'status', name: 'status', searchable: false ,
                "render":function(data, type, row){
                    if(row.status==0){
                        return "<span class='badge badge-danger'>Disabled</span>";
                    }else{
                        return "<span class='badge badge-success'>Active</span>";
                    }
                }
            },
            {data: 'action', name: 'action', searchable: false ,orderable: false, className: "text-center",}
        ]
    });
    /* get city from state **/
    $(document).on('change','#state',function(){
        var stateId = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('cities') !!}" ,
            data: {'stateId':stateId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var select = $('.city');
                select.empty().append(' <option >--Select City--</option>');
                $.each(response, function(key, value) {
                    $('.city').append($("<option></option>")
                            .attr("value", key)
                            .text(value));
                });
            }
        });
    });

    $(document).on('change','#branch-name',function () {
        var branchName = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('check.branch') !!}" ,
            data: {'branchName':branchName},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if ( response.status == true ) {
                    $(this).addClass('error');
                    $('#branch-name').after('<label id="branch-name-error" class="error" for="branch-name">Branch name all ready exist.</label>');
                    $('#branch-name').val('');
                }
            }
        });
    })  

    $('.investment-renewal-from').hide();

    $(document).on('click','.next-button',function () {
        var previousForm = $(this).attr('previous-form');
        var nextForm = $(this).attr('next-form');
        $('.'+previousForm+'').hide();
        $('.'+nextForm+'').show();
    })

    var pName = $('#pName').val();
    var aNumber = $('#editplanNumber').val();
    var pId = $('#plan-id').val();
    $.ajax({
        type: "POST",  
        url: "{!! route('admin.reinvestment.editplanform') !!}",
        data: {'pName':pName,'aNumber':aNumber},
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $("#plan-content-div").html(response); 
             
            $('#payment-mode').find('option').remove();
            $('#payment-mode').append('<option data-val="cash" value="0">Cash</option>');   
            $('#fn_dob,#sn_dob,.dateofbirth,.calendardate').datepicker( {
               format: "dd/mm/yyyy",
               orientation: "top",
               autoclose: true
            }); 
        }
    });

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
        },
        submitHandler: function() {

            var paymentVal = $( "#payment-mode option:selected" ).val();
            var investmentPlan = $( "#investmentplan option:selected" ).val();
            var ssbAccountAvailability = $('input[name="ssb_account_availability"]:checked').val(); 
            var aviBalance = $('#hiddenbalance').val(); 
            var mAccount = $('#hiddenmemberaccount').val();
            var ssbAccount = $('#ssbacount').val();
            var rdAccount = $('#rdacount').val();
            var depositeBalance = $('#amount').val();
            var fnPercentage = $('#fn_percentage').val();
            var snPercentage = $('#sn_percentage').val();

            if(snPercentage){
                snPercentage = $('#sn_percentage').val();
            }else{
                snPercentage = 0;   
            }

            if(ssbAccountAvailability==0){
                if(investmentPlan == '3' || investmentPlan == '6' || investmentPlan == '8'){
                    if(mAccount != ssbAccount){
                        $('#ssbaccount-error').show();
                        $('#ssbaccount-error').html('SSB Account Number does not exists.');
                        //event.preventDefault();
                        return false;
                    }
                }
            }

            if(investmentPlan != '2'){
                if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                    $('#percentage-error').show();
                    $('#percentage-error').html('Percentage should be equal to 100.');
                    //event.preventDefault();
                    return false;
                }
            }

            if(paymentVal == '3'){
                if ( parseInt(depositeBalance) > parseInt(aviBalance) ) {
                    $('#balance-error').show();
                    $('#balance-error').html('Sufficient amount not available in your account.');
                    //event.preventDefault();
                    return false;
                }/*else{
                    return true;  
                }*/
            }else{
                $('#balance-error').html('');
                //return true;
            }
            $('.submit-investment').prop('disabled', true);
            return true;
        }
    });

    // Select payment option
    $(document).on('change','#payment-mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');   
        $('.p-mode').hide();
        $('.'+paymentMode+'').show();    
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

    $(document).on('keyup','.sa-nominee-percentage',function(){
        var inputId = $(this).attr('data-id');
        var value = $(this).val();
        var check = $('#sa_second_nominee_add').val();
        var buttonClass = $('#sa_second_nominee_add').attr('data-button-class');
        if(check > 0){
            if(value <= 100 && value != ''){
                var otherVal = 100-parseInt(value);
                $('#'+inputId+'').val(otherVal);
            }else{
                $(this).val('');
                $('#'+inputId+'').val(0);
            }
        }else{
            if(value == 100){
                $('.sa-second-nominee-botton').prop("disabled",true) ;
            }else{
                $('.sa-second-nominee-botton').prop("disabled",false) ;
            }
        }
    });

    $(document).on('keyup','.nominee-percentage',function(){
        var inputId = $(this).attr('data-id');
        var value = $(this).val();
        var check = $('#second_nominee_add').val();
        var buttonClass = $('#second_nominee').attr('data-button-class');
        if(check > 0){
            if(value <= 100 && value != ''){
                var otherVal = 100-parseInt(value);
                $('#'+inputId+'').val(otherVal);
            }else{
                $(this).val('');
                $('#'+inputId+'').val(0);
            }
        }else{
            if(value == 100){
                $('.second-nominee-botton').prop("disabled",true) ;
            }else{
                $('.second-nominee-botton').prop("disabled",false) ;
            }
        }
    });

    $(document).on('click','.add-second-nominee',function(){
        $(this).val('Remove Nominee');
        var inputClass = $(this).attr('data-class');
        var inputValue = $(this).attr('data-val');
        $('.'+inputClass+'').show();
        $('#'+inputValue+'').val(1);
        $('.second-nominee-input').addClass('remove-second-nominee');
        $('.second-nominee-input').removeClass('add-second-nominee');
    });

    $(document).on('click','.remove-second-nominee',function(){
        $(this).val('Add Nominee');
        var inputClass = $(this).attr('data-class');
        var inputValue = $(this).attr('data-val');
        $('.'+inputClass+'').hide(); 
        $('#'+inputValue+'').val(0);
        $('.second-nominee-input').addClass('add-second-nominee');
        $('.second-nominee-input').removeClass('remove-second-nominee');
        $('#sn_first_name').val('');
        $('#sn_relationship').val('');
        $('#sn_dob').val('');
        $('#sn_age').val('');
        $('#sn_percentage').val('');
    });

    $(document).on('keyup change','.ffd-tenure,.ffd-amount',function(){
        var tenure = $( ".ffd-tenure option:selected" ).val();
        var principal = $('.ffd-amount').val();
        var time = tenure;
        if(time >= 0 && time <= 36){
            var rate = 8;
        }else if(time >= 37 && time <= 48){
            var rate = 8.25;
        }else if(time >= 49 && time <= 60){
            var rate = 8.50;
        }else if(time >= 61 && time <= 72){
            var rate = 8.75;
        }else if(time >= 73 && time <= 84){
            var rate = 9;
        }else if(time >= 85 && time <= 96){
            var rate = 9.50;
        }else if(time >= 97 && time <= 108){
            var rate = 10;
        }else if(time >= 109 && time <= 120){
            var rate = 11;
        }
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
       // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var result =  ( principal*( Math.pow((1 + irate / 100), year)));
        if(Math.round(result) > 0 && tenure <= 120){
           $('.ffd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.ffd-maturity-amount-val').val(Math.round(result));
            $('.ffd-interest-rate').val(rate);
        }else{
            $('.ffd-maturity-amount').html('');
            $('.ffd-maturity-amount-val').val('');
            $('.ffd-interest-rate').val('');
        }
        
    });

    $(document).on('keyup change','.frd-tenure,.frd-amount',function(){
        var tenure = $( ".frd-tenure option:selected" ).val();
        var principal = $('.frd-amount').val();
       // var principal = $(this).val();
        var time = tenure;
        if(time >= 0 && time <= 12){
            var rate = 5;
        }else if(time >= 13 && time <= 24){
            var rate = 6;
        }else if(time >= 25 && time <= 36){
            var rate = 6.50;
        }else if(time >= 37 && time <= 48){
            var rate = 7;
        }else if(time >= 49 && time <= 60){
            var rate = 9;
        }
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
      //  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var freq = 4;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }

        var result = maturity;

        if(Math.round(result) > 0 && tenure <= 60){
            $('.frd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.frd-interest-rate').val(rate);
            $('.frd-maturity-amount-cal').val( Math.round(result) );
        }else{
            $('.frd-interest-rate').val('');
            $('.frd-maturity-amount-cal').val('');
            $('.frd-maturity-amount').html('');
        }
        
    });

    $(document).on('keyup change','.dd-tenure,.dd-amount',function(){
        var tenure = $( ".dd-tenure option:selected" ).val();
        var principal = $('.dd-amount').val();
        var time = tenure;
        if(time >= 0 && time <= 12){
            var rate = 6;
        }else if(time >= 13 && time <= 24){
            var rate = 6.50;
        }else if(time >= 25 && time <= 36){
            var rate = 7;
        }else if(time >= 37 && time <= 60){
            var rate = 7.25;
        }
        var ci = 12;
        var freq = 12;
        var irate = rate / ci;
        var year = time/12;
        var days = time*30;

        var monthlyPricipal = principal*30;
        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=monthlyPricipal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        var result = maturity;
        // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 && tenure <= 60){
            $('.dd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.dd-interest-rate').val(rate);
            $('.dd-maturity-amount-val').val(Math.round(result));
        }else{
            $('.dd-maturity-amount').html('');
            $('.dd-interest-rate').val('');
            $('.dd-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.mis-tenure,.mis-amount',function(){
        var tenure = $( ".mis-tenure option:selected" ).val();
        var principal = $('.mis-amount').val();
        var time = tenure;
        if(time >= 0 && time <= 60){
            var rate = 10;
        }else if(time >= 61 && time <= 84){
            var rate = 10.50;
        }else if(time >= 85 && time <= 120){
            var rate = 11;
        }
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
      //;  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var result = (((principal * rate ) / 12) / 100 );

        if(Math.round(result) > 0 && tenure <= 120){
            $('.mis-maturity-amount').html('Maturity Amount :'+Math.round(result)+'/Month');
            $('.mis-maturity-amount-val').val(Math.round(result));
            $('.mis-maturity-amount-cal').val( rate );
        }else{
            $('.mis-maturity-amount').html('');
            $('.mis-maturity-amount-cal').val('');
            $('.mis-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.fd-tenure,.fd-amount',function(){
        var tenure = $( ".fd-tenure option:selected" ).val();
        var principal = $('.fd-amount').val();
        var specialCategory = $('#specialcategory').val();

        var time = tenure;

        console.log("time", time);
        if(time >= 0 && time <= 48){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10;
            } else {
                var rate = 10.25;
            }
        }else if(time >= 49 && time <= 60){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.25;
            } else {
                var rate = 10.50;
            }
        }else if(time >= 61 && time <= 72){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.50;
            } else {
                var rate = 10.75;
            }
        }else if(time >= 73 && time <= 96){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.75;
            } else {
                var rate = 11;
            }
        }else if(time >= 97 && time <= 120){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 11;
            } else {
                var rate = 11.25;
            }
        }
        console.log("rate", rate);
        console.log("specialCategory", specialCategory);


        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
       // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);

        var result =  ( principal*( Math.pow((1 + irate / 100), year)));
        if(Math.round(result) > 0 && tenure <= 120){
            $('.fd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.fd-interest-rate').val(rate);
            $('.fd-maturity-amount-val').val(Math.round(result));
        }else{
            $('.fd-maturity-amount').html('');
            $('.fd-interest-rate').val('');
            $('.fd-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.rd-tenure,.rd-amount',function(){
        var tenure = $( ".rd-tenure option:selected" ).val();
        var principal = $('.rd-amount').val();
        var specialCategory = $('#specialcategory').val();
        var time = tenure;
        if(time >= 0 && time <= 36){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 8;
            } else {
                var rate = 8.50;
            }

        }else if(time >= 37 && time <= 60){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 9;
            } else {
                var rate = 9.50;
            }
        }else if(time >= 61 && time <= 84){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10;
            } else {
                var rate = 10.50;
            }
        }
        console.log("rate RD", rate);
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 4;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
       // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 && tenure <= 84){
            $('.rd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.rd-maturity-amount-val').val(Math.round(result));
            $('.rd-interest-rate').val(rate);
        }else{
            $('.rd-maturity-amount').html('');
            $('.rd-maturity-amount-val').val('');
            $('.rd-interest-rate').val('');
        }

    });

    $(document).on('keyup','.ssmb-amount',function(){
        var principal = $('.ssmb-amount').val();
        var time = 12;
        var tenure = 7;
        var rate = 9;
        var ci = 1;
        var irate = 8 / ci;
        var year = time / 12;
        var freq = 4;
        var perYearSixtyPecent = ((principal * 12)*60/100);
        var carryAmount = 0;
        var carryForwardInterest = 0;
        var oldMaturity = 0;

        var maturity=0;


        // for(var i=1; i<=time;i++){
        //    perYearWithInterest = principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        //     console.log("maturity-before-"+i+"--", perYearWithInterest);
        //     if(i%12 == 0){
        //        maturity+= maturity-perYearSixtyPecent;
        //        carryForwardInterest = ( maturity*( Math.pow((1 + irate / 100), i-1)));
        //     }
        // }


        for(var j=1; j<=tenure;j++){
            var perYearWithInterest = 0;
            for(var i=1; i<=time;i++){
                perYearWithInterest+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
            }
            if(j > 1){
                carryForwardInterest = ( oldMaturity*( Math.pow((1 + rate / 100), 1)));
                    console.log("carryForwardInterest", carryForwardInterest);

                maturity = Math.round(perYearWithInterest + carryForwardInterest);
                console.log("maturity", maturity);
                oldMaturity = Math.round(maturity - perYearSixtyPecent);
                console.log("oldMaturity", oldMaturity);

            }else{
                oldMaturity = Math.round(perYearWithInterest-perYearSixtyPecent);
                maturity+= oldMaturity;
                console.log("oldMaturity", oldMaturity);
            }

        }






        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 ){
            $('.ssmb-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.ssmb-maturity-amount-val').val(Math.round(result));
            $('.ssmb-interest-rate').val(rate);
            $('.ssmb-tenure').val(tenure);
        }else{
            $('.ssmb-maturity-amount').html('');
            $('.ssmb-maturity-amount-val').val('');
            $('.ssmb-interest-rate').val('');
            $('.ssmb-tenure').html('');
        }

    });

    $(document).on('keyup','.sj-amount',function(){
        var principal = $('.sj-amount').val();
        var specialCategory = $('#specialcategory').val();
        var time = 84;
        var rate = 10.50;

        console.log("rate SJ", rate, 'principal', principal);
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 1;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 ){
           // $('.sj-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.sj-tenure').val(time);
            $('.sj-maturity-amount-val').val(Math.round(result));
            $('.sj-interest-rate').val(rate);
        }else{
            $('.sj-maturity-amount').html('');
            $('.sj-tenure').val('');
            $('.sj-maturity-amount-val').val('');
            $('.sj-interest-rate').val('');
        }

    });

    $(document).on('keyup','.sb-amount',function(){
        var tenure = 120;
        var principal = $(this).val();
        var time = tenure;
        var rate = 11;
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 1;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);


        // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0){
            $('.sb-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.sb-maturity-amount-cal').val(rate);
            $('.sb-maturity-amount-val').val(Math.round(result));
            $('.sb-tenure').val(tenure);
        }else{
            $('.sb-maturity-amount').html('');
            $('.sb-maturity-amount-cal').val('');
            $('.sb-maturity-amount-val').val();
            $('.sb-tenure').val('');
        }
        
    });

    $(document).on('change','#fn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#fn_gender_male').attr('disabled', true);
            $('#fn_gender_female').attr('checked', 'true');
            $('#fn_gender_female').attr('readonly', 'true');
        } else {
            $('#fn_gender_male').removeAttr('disabled', false);
            $('#fn_gender_male').removeAttr('checked');
            $('#fn_gender_female').removeAttr('readonly');
            $('#fn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#sn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#sn_gender_male').attr('disabled', true);
            $('#sn_gender_female').attr('checked', 'true');
            $('#sn_gender_female').attr('readonly', 'true');
        } else {
            $('#sn_gender_male').removeAttr('disabled', false);
            $('#sn_gender_male').removeAttr('checked');
            $('#sn_gender_female').removeAttr('readonly');
            $('#sn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#ssb_fn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#ssb_fn_gender_male').attr('disabled', true);
            $('#ssb_fn_gender_female').attr('checked', 'true');
            $('#ssb_fn_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_fn_gender_male').removeAttr('disabled', false);
            $('#ssb_fn_gender_male').removeAttr('checked');
            $('#ssb_fn_gender_female').removeAttr('readonly');
            $('#ssb_fn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#ssb_sn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#ssb_sn_gender_male').attr('disabled', true);
            $('#ssb_sn_gender_female').attr('checked', 'true');
            $('#ssb_sn_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_sn_gender_male').removeAttr('disabled', false);
            $('#ssb_sn_gender_male').removeAttr('checked');
            $('#ssb_sn_gender_female').removeAttr('readonly');
            $('#ssb_sn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('keyup','#closing_Balance_reinvest',function(){
        $('#opening_Balance_reinvest').val($(this).val());
        $('#opening-balance').val($(this).val());
        $('#total_reinvest_amount').val( $(this).val() );
    });

    $(document).on('change','.deposit-amount',function(){
        var amount = $(this).val();
        $('#collection_reinvest_amount').val(amount);
        var allAmount = $('.deposit-amount');
        var totalAmount = parseFloat('0.00').toFixed(2);
        $('.deposit-amount').each(function() {
            if ( $(this).val() ) {
                totalAmount =  parseFloat(totalAmount) + parseFloat( $(this).val() );
            }
        });
        $('#total_reinvest_amount').val( parseFloat(totalAmount).toFixed(2) );
    })

    $('.renewal_date,#dob,#anniversary_date,#nominee_dob,#fn_dob,#sn_dob').datepicker( {
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true
    });

    $(document).on('change','#fn_dob,#sn_dob',function(){
        moment.defaultFormat = "DD/MM/YYYY";
        var today1 = new Date();
        var date11 = $(this).val();
        var date = moment(date11, moment.defaultFormat).toDate();
        var today = moment(today1, moment.defaultFormat).toDate();
        var inputId = $(this).attr('data-val');
        dob = new Date(date);

        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
        $('#'+inputId+'').val(age);        
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

function addNewRow() {
    var table = document.getElementById("add-transaction");
    console.log(table.rows.length);
    var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    cell1.innerHTML = rowCount+".";
    cell2.innerHTML = '<input type="text" name="renewal_date['+rowCount+']" id="renewal_date" class="form-control renewal_date" value="">';
    cell3.innerHTML = '<div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+rowCount+'" ' +
        'name="ramount['+rowCount+']" class="form-control rupee-txt deposit-amount amount amount-'+rowCount+'"></div>';
    var deleteRow = document.getElementById('delete-row');
    if (rowCount == 11 ) {
        deleteRow.innerHTML = '<button type="button" class=" btn btn-primary legitRipple" onclick="deleteRow()">Delete Row</button>';
    }

    $('.renewal_date,#dob,#anniversary_date,#nominee_dob,#fn_dob,#sn_dob').datepicker( {
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true
    });

    $(document).on('change','#state_id',function(){
          var state_id = $(this).val(); 
         getcity(state_id,district,city);

    });
}

function getcity(state_id,district,city)
{
 $.ajax({
            type: "POST",  
            url: "{!! route('admin.districtlists') !!}",
            dataType: 'JSON',
            data: {'state_id':state_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {  
              $('#district_id').find('option').remove();
              $('#district_id').append('<option value="">Select district</option>');
               $.each(response.district, function (index, value) { 
                      $("#district_id").append("<option value='"+value.id+"'>"+value.name+"</option>");
                  });
               $('#district_id').val(district);

            }
        }); 

        $.ajax({
            type: "POST",  
            url: "{!! route('admin.citylists') !!}",
            dataType: 'JSON',
            data: {'district_id':state_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) { 
              $('#city_id').find('option').remove();
              $('#city_id').append('<option value="">Select city</option>');
               $.each(response.city, function (index, value) { 
                      $("#city_id").append("<option value='"+value.id+"'>"+value.name+"</option>");
                  }); 
               $('#city_id').val(city);
            }
        });
}

function deleteRow() {
    var table = document.getElementById("add-transaction");
    var rowCount = table.rows.length;
    console.log( rowCount );
    table.deleteRow(rowCount -1);
    var deleteRow = document.getElementById('delete-row');
    if ( rowCount <= 12 ) {
        deleteRow.innerHTML = '';
    }
}

</script>