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

            'cheque_id' : 'required', 

            'cheque-number' : {required: true, number: true},

            'bank-name' : 'required',

            'branch-name' : 'required',

            'cheque-date' : 'required',

            'transaction-id' : 'required',

            'date' : 'required',

            'fn_gender' : 'required',

            'sn_gender' : 'required',

            'account_n' : 'required',

            'account_b' : 'required',

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



$(document).on('change','#cheque_id',function(){ 

    var cheque_id=$('#cheque_id').val();



                    $('#cheque-number').val('');

                 $('#bank-name').val('');

                 $('#branch-name').val('');

                 $('#cheque-date').val('');

                 $('#cheque-amt').val('');

          $.ajax({

              type: "POST",  

              url: "{!! route('admin.approve_cheque_details') !!}",

              dataType: 'JSON',

              data: {'cheque_id':cheque_id},

              headers: {

                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

              },

              success: function(response) { 

                //alert(response.id);

                 $('#cheque-number').val(response.cheque_no);

                 $('#bank-name').val(response.bank_name);

                 $('#branch-name').val(response.branch_name);

                 $('#cheque-date').val(response.cheque_create_date);

                 $('#cheque-amt').val(parseFloat(response.amount).toFixed(2));

                 $('#cheque_detail').show();



              }

          });



  });





    // Select payment option

    /*$(document).on('change','#payment-mode',function(){

        var paymentMode = $('option:selected', this).attr('data-val');   

        $('.p-mode').hide();

        $('.'+paymentMode+'').show(); 



           if(paymentMode=='cheque-mode')

        {

            



                      $.ajax({

                          type: "POST",  

                          url: "{!! route('admin.approve_recived_cheque_lists') !!}",

                          dataType: 'JSON', 

                          headers: {

                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                          },

                          success: function(response) { 

                            $('#cheque_id').find('option').remove();

                            $('#cheque_id').append('<option value="">Select cheque number</option>');

                             $.each(response.cheque, function (index, value) { 

                                    $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+"  ( "+parseFloat(value.amount).toFixed(2)+")</option>");

                                }); 



                          }

                      });



               

        } 

    });*/

    $(document).on('change','#payment-mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');   
        var accountNumber = $('#hiddenaccount').val();
        var accountBalance = $('#hiddenbalance').val();

        $('#cheque-number').val('');
                 $('#bank-name').val('');
                 $('#branch-name').val('');
                 $('#cheque-date').val('');
                 $('#cheque-amt').val('');

                 $('#cheque_detail').hide();
                 
        $('.p-mode').hide();
        $('.'+paymentMode+'').show();    
        if(paymentMode=='cheque-mode')
        {
            

                      $.ajax({
                          type: "POST",  
                          url: "{!! route('admin.approve_recived_cheque_lists') !!}",
                          dataType: 'JSON', 
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                            $('#cheque_id').find('option').remove();
                            $('#cheque_id').append('<option value="">Select cheque number</option>');
                             $.each(response.cheque, function (index, value) { 
                                    $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+" ( "+parseFloat(value.amount).toFixed(2)+")</option>");
                                }); 

                          }
                      });

               
        }  
        if(paymentMode=='online-transaction-mode')
        {
          $.ajax({
              type: "POST",  
              url: "{!! route('admin.getBankList') !!}",
              dataType: 'JSON', 
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#rd_online_bank_id').find('option').remove();
                $('#rd_online_bank_id').append('<option value="">Select Deposit Bank</option>');
                $.each(response.samraddhBanks, function (index, value) { 
                $("#rd_online_bank_id").append("<option value='"+value.id+"' data-account='"+value.bank_account.account_no+"'>"+value.bank_name+"  </option>");
            }); 
              }
          });
        }   
        if(paymentMode == 'ssb-account'){
            accountNumber ? $('#account_n').val(accountNumber) : $('#account_n').val("Account Number N/A");
            accountBalance ? $('#account_b').val(accountBalance) : $('#account_b').val("Account Balance N/A");
        }
    });

    $(document).on('change','#rd_online_bank_id', function () {
        var bank_id = $('option:selected', this).val();
       $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_account_list') !!}",
              dataType: 'JSON',
              data:{bank_id:bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                 $('#rd_online_bank_ac_id').find('option').remove();
                 $('#rd_online_bank_ac_id').append('<option value="">Select Deposit Bank Account</option>');
                    $.each(response.account, function (index, value) { 
                    $("#rd_online_bank_ac_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
            });     
            }
       })
    });

    var formName = $('#formName').val();

    var investmentId = $('#investmentId').val();

    var viewEditAction = $('#action').val();

    $.ajax({

        type: "POST",  

        url: "{!! route('admin.investment.editplanform') !!}",

        data: {'formName':formName,'investmentId':investmentId,'viewEditAction':viewEditAction},

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        },

        success: function(response) {

            $(".plan-content-div").html(response);  

            if($('#payment-mode').val()==1)

            {

                $('#payment-mode').attr('readonly', true);  

                $('#amount').attr('readonly', true);

                $("#payment-mode option[value='']").hide();

                $("#payment-mode option[value=0]").hide();

                $("#payment-mode option[value=2]").hide();

                $("#payment-mode option[value=3]").hide();

                $("#payment-mode option[value=4]").hide();



                    $.ajax({

                          type: "POST",  

                          url: "{!! route('admin.assign_cheque_details') !!}",

                          dataType: 'JSON', 

                          data: {'cheque_no': $('#cheque_id_get').val()},

                          headers: {

                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                          },

                          success: function(response) { 

                            $('#cheque_id').find('option').remove();

                             $("#cheque_id").append("<option value='"+response.id+"'>"+response.cheque_no+"  ( "+parseFloat(response.amount).toFixed(2)+")</option>");

                             $( "#cheque_id" ).change();



                          }

                      });



                



            } 

            $('.fn_dateofbirth,.sn_dateofbirth,.dateofbirth,.calendardate').datepicker( {

               format: "dd/mm/yyyy",

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

        if(time >= 0 && time <= 18){

            var rate = 9;

        }else if(time >= 19 && time <= 48){

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

    // Calculate age from date

    $(document).on('change','.kanyadhan-dob',function(){

        moment.defaultFormat = "DD/MM/YYYY";

        var date1212 = $(this).val();

        var date = moment(date1212, moment.defaultFormat).toDate();

        var inputId = $(this).attr('data-val');



        dob1 = new Date(date);

        var today1 = new Date();

        var dob = moment(dob1, moment.defaultFormat).toDate();

        var today = moment(today1, moment.defaultFormat).toDate();

        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));

       

        var tenure = 18-age;

        if(age >= 0 && tenure >= 0){

            $('#'+inputId+'').val(age);  

            $('#tenure').val(18-age); 

            $.ajax({

                type: "POST",  

                url: "{!! route('admin.investment.kanyadhanamount') !!}",

                data: {'fa_code':709,'tenure':tenure},

                dataType: 'JSON',

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

                success: function(response) {

                    if(response.resCount > 0){

                        $('.monthly-deposite-amount').val(response.investmentAmount[0].amount);

                        var tenure = $('.kanyadhan-yojna-tenure').val();

                        var principal = response.investmentAmount[0].amount;

                        if(tenure >= 8 && tenure <= 18){

                            var rate = 11;

                        }else if(tenure >= 6 && tenure <= 7){

                            var rate = 10.50;

                        }else if(tenure < 6){

                            var rate = 10;

                        }

                        var ci = 1;

                        var time = tenure*12;

                        var irate = rate / ci;

                        var year = time / 12;

                        var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);

                        $('.maturity-amount').html('Maturity Amount :'+Math.round(result));

                    }else{

                       $('.monthly-deposite-amount').val('');

                       $('.maturity-amount').html('');

                    } 

                }

            });

        }else{

            $(this).val('');

            $('#'+inputId+'').val('');  

            $('#tenure').val(''); 

            $('.monthly-deposite-amount').val('');

            $('.maturity-amount').html('');

            alert('Please select a valid date');

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

    // Show loading image

    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });

    // Hide loading image

    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });
    $('#company_id').on('change',e=>{
        var customAssociateRegister = $('form[name="register-plan"]')[0];
        customAssociateRegister.reset();
    });

	$(document).on('click','#fn_dob',function(){

            $('#fn_dob').datepicker({

			  format: "dd/mm/yyyy"

			});    

    });

	
    $(document).on('change','#fn_dob, #sn_dob',function(){

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

	  
});



</script>