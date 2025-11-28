<script type="text/javascript">



$(document).ready(function() {

    $('#emergancy_payment_date').hover(function () {
        var today = $(".create_application_date").val();
        $('#emergancy_payment_date').val(today);
    $('#emergancy_payment_date').datepicker({

        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true,
        endDate: today,
        maxDate: today,
        startDate: today,


    }); 
        }
    );

      $.validator.addMethod("greaterThanZero", function(value, element) {
                return this.optional(element) || (parseFloat(value) > 0);
            }, "Value must be greater than 0.");


    $('#add_emergancy_maturity').validate({ // initialize the plugin

        rules: {

            'paymentType' : {required: true},

            'branch' : {required: true},

            'date' : {required: true},
            'emergancy_mobile_number' : {number:true,maxlength:12,minlength:10},

            'emergancy_ifsc_code': { checkIfsc:true },
            'emergancy_bank_account_number' : {number: true,minlength: 8,maxlength: 20},
            'emergancy_maturity_payable': {number: true,greaterThanZero: true},

        },

        submitHandler: function() {

            var countFreshExpense = $( "#count-emergancy-maturity" ).val();

            if(countFreshExpense == 0){

                swal("Warning!", "Please create emergancy maturity!", "warning");

                return false;

            }

            $('.submit-demand-advice').prop('disabled', true);

            return true;

        }

    });

    $('#transfer-emergancy-maturity').validate({ // initialize the plugin
        rules: {
            'payment_date' : {required: true,maxpDate: true},
        },
        submitHandler: function() {
            var count = $( "#selected_records" ).val();
            if(count == ''){
                swal("Warning!", "Please select records!", "warning");
                return false;
            }

            $('.transfer-emergancy-button').prop('disabled', true);
            return true;
        }
    });

    $(document).on('change','#emergancy_account_number',function(){

        var val = $(this).val();

       
                    $.ajax({

                        type: "POST",  

                        url: "{!! route('admin.emergancymaturity.getinvestment') !!}",

                        dataType: 'JSON',

                        data: {'val':val},

                        headers: {

                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                        },

                        success: function(response) {
       
                            if(response.status == 200 ){

                                $('branch_id').append('<option value="">---Please Select Branch ---</option>');
                                $.each(response.bankList, function(key, value) {
                                    $('#branch_id').append('<option value="'+value.branch.id+'" data-value="'+value.branch.branch_code+'">'+value.branch.name+'</option>')

                                })
                                if(response.investmentDetails.branch_id )
                                    {
                                        $('#branch_id option[value='+response.investmentDetails.branch_id +']').attr("selected", "selected");
                                        $('#branch_id option[value!='+response.investmentDetails.branch_id +']').prop("disabled", "disabled");
                                    };
                                if(response.IsReinvests == 0 && response.eliMbAmount > 0){
                                    $('.fd-interest-amount').show();
                                    $('.eli-amount').hide();
                                    $('#fd_interest_amt').val(response.eliMbAmount);
                                }else if(response.IsReinvests == 0 && response.eliAmount > 0){
                                    $('.eli-amount').show();
                                    $('.fd-interest-amount').hide();
                                    $('#eli_amt').val(response.eliAmount);
                                }else{
                                    $('.eli-amount').hide();
                                    $('.fd-interest-amount').hide();
                                    $('#fd_interest_amt').val('');
                                    $('#eli_amt').val('');
                                }

                                if(response.investmentDetails){
                                    $('#company_id').val(response.investmentDetails.company_id);
                                    var created_date = moment(response.investmentDetails.created_at).format('DD/MM/YYYY');  

                                    $('#emergancy_opening_date').val(created_date);

                                    $('#emergancy_investmnet_id').val(response.investmentDetails.id);

                                    $('#emergancy_plan_name').val(response.investmentDetails.plan.name);

                                    $('#emergancy_tenure').val(response.investmentDetails.tenure);

                                    $('#emergancy_deposite_amount').val(response.investmentDetails.current_balance);
                                }

                                $('#emergancy_maturity_amount').val(parseInt(response.finalAmount).toFixed(2));

                                $('.f_amount').val(response.finalAmount);

                                if(response.investmentDetails.member){

                                    $('#emergancy_account_holder_name').val(response.investmentDetails.member.first_name+' '+response.investmentDetails.member.last_name);

                                    $('#emergancy_mobile_number').val(response.investmentDetails.member.mobile_no);
                                }

                                if(response.investmentDetails.ssb){

                                    $('#emergancy_ssb_account').val(response.investmentDetails.ssb.account_no);
                                }

                                if(response.investmentDetails.member_bank_detail){

                                    $('#emergancy_bank_name').val(response.investmentDetails.member_bank_detail.bank_name);

                                    var finalAmount = parseInt(response.eliMbAmount)+parseInt(response.eliAmount)

                                    $('#final_tds_amount').val(finalAmount);

                                    $('#emergancy_bank_account_number').val(response.investmentDetails.member_bank_detail.account_no);

                                    $('#emergancy_ifsc_code').val(response.investmentDetails.member_bank_detail.ifsc_code);
                                }  


                            }else{

                                $('#emergancy_opening_date').val('');

                                $('#emergancy_investmnet_id').val('');

                                $('#emergancy_plan_name').val('');

                                $('#emergancy_tenure').val('');

                                $('#emergancy_deposite_amount').val('');

                                $('#emergancy_mobile_number').val('');

                                $('#emergancy_ssb_account').val('');

                                $('#emergancy_account_holder_name').val('');    

                                $('#emergancy_maturity_amount').val('');

                                $('#emergancy_bank_account_number').val('');

                                $('#emergancy_ifsc_code').val('');

                                $('#tds_amount').val('');

                                $('#final_tds_amount').val('');

                                swal("Warning!", ""+response.message+"", "warning");

                            }

                        }

                    });
                
       

    });



    /*$(document).on('click','.add-emergancy-maturity',function(){

        var investmentId = $('#emergancy_investmnet_id').val();

        var accountnumber = $('#emergancy_account_number').val();

        var openingDate = $('#emergancy_opening_date').val();

        var planName = $('#emergancy_plan_name').val();

        var tenure = $('#emergancy_tenure').val();

        var accountHolderName = $('#emergancy_account_holder_name').val();

        var depositeAmount = $('#emergancy_deposite_amount').val();

        var maturityAmount = $('#emergancy_maturity_amount').val();

        var maturityPayable = $('#emergancy_maturity_payable').val();

        var mobileNumber = $('#emergancy_mobile_number').val();

        var ssbAccount = $('#emergancy_ssb_account').val();

        var bankAccountNumber = $('#emergancy_bank_account_number').val();

        var bankAccountName = $('#emergancy_bank_name').val();

        var ifscCode = $('#emergancy_ifsc_code').val();

        var paymentDate = $('#emergancy_payment_date').val();

        var countRecord = $('#count-emergancy-maturity').val();

        var maturityAmount = $('#emergancy_maturity_amount').val();

        var maturityPayable = $('#emergancy_maturity_payable').val();
        
         var branch_id = $('#branch_id').val();
        

        if(accountnumber == '' || maturityPayable == '' || mobileNumber == '' || paymentDate == ''  || branch_id == ''){

            swal("Warning!", "Please fill all required fields!", "warning");

            return false;

        }else if($.isNumeric(maturityPayable) != true || $.isNumeric(mobileNumber) != true){

            swal("Warning!", "Maturity Payable Amount,Mobile Number must be numeric!", "warning");

            return false;

        }else if(parseInt(maturityPayable) < parseInt(depositeAmount)){

            swal("Warning!", "Maturity Payable amount should be grather than deposit amount till Date amount!", "warning");

            return false;

        }else{

            $('#emergancy_account_number').val('');

            $('#emergancy_opening_date').val('');

            $('#emergancy_plan_name').val('');

            $('#emergancy_tenure').val('');

            $('#emergancy_account_holder_name').val('');

            $('#emergancy_deposite_amount').val('');

            $('#emergancy_maturity_amount').val('');

            $('#emergancy_maturity_payable').val('');

            $('#emergancy_mobile_number').val('');

            $('#emergancy_ssb_account').val('');

            $('#emergancy_bank_account_number').val('');

            $('#emergancy_ifsc_code').val('');

            $('#emergancy_payment_date').val('');



            var countRec = parseInt(countRecord)+1;

            $('.emergancy-maturity-table').append('<tr><td>'+openingDate+'</td><input type="hidden" name="investment['+countRecord+'][id]" value="'+investmentId+'"><td>'+planName+'</td><td>'+tenure+'</td><td>'+accountHolderName+'</td><td>'+depositeAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityAmount]" value="'+maturityAmount+'"><td>'+maturityAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityPayable]" value="'+maturityPayable+'"><td>'+maturityPayable+' &#8377</td><td><input type="file" name="investment['+countRecord+'][bill_photo]"></td><input type="hidden" name="investment['+countRecord+'][mobilenumber]" value="'+mobileNumber+'"><td>'+mobileNumber+'</td><input type="hidden" name="investment['+countRecord+'][ssbaccount]" value="'+ssbAccount+'"><td>'+ssbAccount+'</td><input type="hidden" name="investment['+countRecord+'][bankname]" value="'+bankAccountName+'"><td>'+bankAccountName+'</td><input type="hidden" name="investment['+countRecord+'][bankaccount]" value="'+bankAccountNumber+'"><td>'+bankAccountNumber+'</td><input type="hidden" name="investment['+countRecord+'][ifsc]" value="'+ifscCode+'"><td>'+ifscCode+'</td><input type="hidden" name="investment['+countRecord+'][paymentDate]" value="'+paymentDate+'"><td>'+paymentDate+'</td><td> &nbsp; <a href="javascript:void(0);" class="remCF">Remove</a></td></tr>');

            $('#count-emergancy-maturity').val(countRec);

            return true;

        }

    });*/

    $(document).on('click','.add-emergancy-maturity',function(){

        var investmentId = $('#emergancy_investmnet_id').val();

        var companyId = $('#company_id').val();

        var accountnumber = $('#emergancy_account_number').val();

        var openingDate = $('#emergancy_opening_date').val();

        var planName = $('#emergancy_plan_name').val();

        var tenure = $('#emergancy_tenure').val();

        var accountHolderName = $('#emergancy_account_holder_name').val();

        var depositeAmount = $('#emergancy_deposite_amount').val();

        var maturityAmount = $('#emergancy_maturity_amount').val();

        var maturityPayable = $('#emergancy_maturity_payable').val();

        var mobileNumber = $('#emergancy_mobile_number').val();

        var ssbAccount = $('#emergancy_ssb_account').val();

        var bankAccountNumber = $('#emergancy_bank_account_number').val();

        var bankAccountName = $('#emergancy_bank_name').val();

        var ifscCode = $('#emergancy_ifsc_code').val();

        var paymentDate = $('#emergancy_payment_date').val();

        var countRecord = $('#count-emergancy-maturity').val();

        var maturityAmount = $('#emergancy_maturity_amount').val();

        var maturityPayable = $('#emergancy_maturity_payable').val();

        var tdsAmount = $('#tds_amount').val();

        var tdsFinalAmount = $('#final_tds_amount').val();

        var tdsPer = $('#tds_per').val();

        var tdsPerAmount = $('#tds_per_amount').val();

        var branch_id = $('#branch_id').val();

        if(accountnumber == '' || maturityPayable == '' || mobileNumber == '' || paymentDate == '' || branch_id == ''){

            swal("Warning!", "Please fill all required fields!", "warning");

            return false;

        }else if($.isNumeric(maturityPayable) != true || $.isNumeric(mobileNumber) != true){

            swal("Warning!", "Maturity Payable Amount,Mobile Number must be numeric!", "warning");

            return false;

        }else if(parseInt(maturityPayable) < parseInt(depositeAmount)){

            swal("Warning!", "Maturity Payable amount should be grather than deposit amount till Date amount!", "warning");

            return false;

        }else{

            $('#emergancy_account_number').val('');

            $('#emergancy_opening_date').val('');

            $('#emergancy_plan_name').val('');

            $('#emergancy_tenure').val('');

            $('#emergancy_account_holder_name').val('');

            $('#emergancy_deposite_amount').val('');

            $('#emergancy_maturity_amount').val('');

            $('#emergancy_maturity_payable').val('');

            $('#emergancy_mobile_number').val('');

            $('#emergancy_ssb_account').val('');

            $('#emergancy_bank_account_number').val('');

            $('#emergancy_ifsc_code').val('');

            $('#emergancy_payment_date').val('');

            $('#tds_amount').val('');

            $('#final_tds_amount').val('');

            $('#tds_per').val('');

            $('#tds_per_amount').val('');

            var countRec = parseInt(countRecord)+1;

            //$('.emergancy-maturity-table').append('<tr><td>'+openingDate+'</td><input type="hidden" name="investment['+countRecord+'][id]" value="'+investmentId+'"><td>'+planName+'</td><td>'+tenure+'</td><td>'+accountHolderName+'</td><td>'+depositeAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityAmount]" value="'+maturityAmount+'"><td>'+maturityAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityPayable]" value="'+maturityPayable+'"><td>'+maturityPayable+' &#8377</td><td><input type="file" name="investment['+countRecord+'][bill_photo]"></td><input type="hidden" name="investment['+countRecord+'][mobilenumber]" value="'+mobileNumber+'"><td>'+mobileNumber+'</td><input type="hidden" name="investment['+countRecord+'][ssbaccount]" value="'+ssbAccount+'"><td>'+ssbAccount+'</td><input type="hidden" name="investment['+countRecord+'][bankname]" value="'+bankAccountName+'"><td>'+bankAccountName+'</td><input type="hidden" name="investment['+countRecord+'][bankaccount]" value="'+bankAccountNumber+'"><td>'+bankAccountNumber+'</td><input type="hidden" name="investment['+countRecord+'][ifsc]" value="'+ifscCode+'"><td>'+ifscCode+'</td><input type="hidden" name="investment['+countRecord+'][paymentDate]" value="'+paymentDate+'"><td>'+paymentDate+'</td>  <input type="hidden" name="investment['+countRecord+'][tdsAmount]" value="'+tdsAmount+'"><td>'+tdsAmount+'</td><input type="hidden" name="investment['+countRecord+'][tdsFinalAmount]" value="'+tdsFinalAmount+'"><td>'+tdsFinalAmount+'</td><input type="hidden" name="investment['+countRecord+'][tdsPer]" value="'+tdsPer+'"><input type="hidden" name="investment['+countRecord+'][tdsPerAmount]" value="'+tdsPerAmount+'">  <td> &nbsp; <a href="javascript:void(0);" class="remCF">Remove</a></td></tr>');

            $('.emergancy-maturity-table').append('<tr><td>'+openingDate+'</td><input type="hidden" name="investment['+countRecord+'][id]" value="'+investmentId+'"><input type="hidden" name="investment['+countRecord+'][account_number]" value="'+accountnumber+'"><input type="hidden" name="investment['+countRecord+'][company_id]" value="'+companyId+'"><td>'+planName+'</td><td>'+tenure+'</td><td>'+accountHolderName+'</td><td>'+depositeAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityAmount]" value="'+maturityAmount+'"><td>'+maturityAmount+' &#8377</td><input type="hidden" name="investment['+countRecord+'][maturityPayable]" value="'+maturityPayable+'"><td>'+maturityPayable+' &#8377</td><td><input type="file" name="investment['+countRecord+'][bill_photo]"></td><input type="hidden" name="investment['+countRecord+'][mobilenumber]" value="'+mobileNumber+'"><td>'+mobileNumber+'</td><input type="hidden" name="investment['+countRecord+'][ssbaccount]" value="'+ssbAccount+'"><td>'+ssbAccount+'</td><input type="hidden" name="investment['+countRecord+'][bankname]" value="'+bankAccountName+'"><td>'+bankAccountName+'</td><input type="hidden" name="investment['+countRecord+'][bankaccount]" value="'+bankAccountNumber+'"><td>'+bankAccountNumber+'</td><input type="hidden" name="investment['+countRecord+'][ifsc]" value="'+ifscCode+'"><td>'+ifscCode+'</td><input type="hidden" name="investment['+countRecord+'][paymentDate]" value="'+paymentDate+'"><td>'+paymentDate+'</td><input type="hidden" name="investment['+countRecord+'][tdsAmount]" value="'+tdsAmount+'"><td>'+tdsAmount+'</td><input type="hidden" name="investment['+countRecord+'][tdsFinalAmount]" value="'+tdsFinalAmount+'"><td>'+tdsFinalAmount+'</td><input type="hidden" name="investment['+countRecord+'][tdsPer]" value="'+tdsPer+'"><input type="hidden" name="investment['+countRecord+'][tdsPerAmount]" value="'+tdsPerAmount+'">  <td> &nbsp; <a href="javascript:void(0);" class="remCF">Remove</a></td></tr>');

            $('#count-emergancy-maturity').val(countRec);

            return true;

        }

    });

    $(".emergancy-maturity-table").on('click','.remCF',function(){

        var countRecord = $('#count-emergancy-maturity').val();

        var countRec = parseInt(countRecord)-1;

        $('#count-emergancy-maturity').val(countRec);

        $(this).parent().parent().remove();

    });



    emergancyTable = $('#emergancy-maturity-table').DataTable({

        processing: true,

        serverSide: true,

        pageLength: 20,

        scroll: true,

        lengthMenu: [10, 20, 40, 50, 100],

        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      

            var oSettings = this.fnSettings ();

            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

            return nRow;

        },

        ajax: {

            "url": "{!! route('admin.emergancymaturity.listing') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'checkbox', name: 'checkbox',orderable: false, searchable: false},
            {data: 'Company_name', name: 'Company_name'},
            {data: 'Branch_name', name: 'Branch_name'},


            {data: 'opening_date', name: 'opening_date'},
            {data: 'account_number', name: 'account_number'},


            {data: 'plan_name', name: 'plan_name'},

            {data: 'tenure', name: 'tenure'},
            {data: 'customer_id', name: 'customer_id'},
            {data: 'member_id', name: 'member_id'},

            {data: 'account_holder_name', name: 'account_holder_name'},

            {data: 'deposit_amount', name: 'deposit_amount'},

            {data: 'tds_amount', name: 'tds_amount'},

            {data: 'maturity_amount', name: 'maturity_amount'},

            {data: 'maturity_amount_payable', name: 'maturity_amount_payable'},

            {data: 'final_amount', name: 'final_amount'},

            {data: 'mobile_number', name: 'mobile_number'},

            {data: 'ssb_account', name: 'ssb_account'},

            {data: 'bank_name', name: 'bank_name'},

            {data: 'bank_account', name: 'bank_account'},

            {data: 'ifsc', name: 'ifsc'},

            {data: 'letter_photo', name: 'letter_photo'},

            {data: 'payment_date', name: 'payment_date'},

        ],"ordering": false

    });

    $(emergancyTable.table().container()).removeClass( 'form-inline' );



    // Handle click on "Select all" control

    $('#select_all').on('click', function(){

      var rows = emergancyTable.rows({ 'search': 'applied' }).nodes();

      $('input[type="checkbox"]', rows).prop('checked', this.checked);

    });



    // Handle click on checkbox to set state of "Select all" control

    $('#emergancy-maturity-table tbody').on('change', 'input[type="checkbox"]', function(){

        if(!this.checked){

            var el = $('#select_all').get(0);

            if(el && el.checked && ('indeterminate' in el)){

                el.indeterminate = true;

            }

        }

    });



    $(document).on('change','#select_all,#emergancy_maturity_record',function(){

        var checked = [];

        var unchecked = [];

        $('input[name="emergancy_maturity_record"]:checked').each(function() {
           checked.push(this.value);
        });



        $('input[type=checkbox]:not(:checked)').each(function() {

            if(Math.floor(this.value) == this.value && $.isNumeric(this.value)) 

            unchecked.push(this.value);

        });



        $('#selected_records').val(checked);

        $('#pending_records').val(unchecked);

    });

    $(document).on('change','#emergancy_maturity_payable',function(){

        var investmentAccount  = $('#emergancy_account_number').val();

        var payableAmount = $('#emergancy_maturity_payable').val();
        var depositAmount = $('#emergancy_deposite_amount').val();
        var globaldate = $('.created_at').val();
        //var fAmount = $('.f_amount').val();

        var interestRateAmount = parseInt(payableAmount)-parseInt(depositAmount);

        $.ajax({
            type: "POST",  
            url: "{!! route('admin.emergancymaturity.tds') !!}",
            dataType: 'JSON',
            data: {'investmentAccount':investmentAccount,'interestRateAmount':interestRateAmount,'globaldate':globaldate},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#tds_amount').val(response.investmentTds);
                $('#tds_per').val(response.tdsPercentage);
                $('#tds_per_amount').val(response.tdsPercentageAmount);

                var tdsAmount = response.investmentTds;
                var tdsPer = response.tdsPercentage;
                var tdsPerAmount = response.tdsPercentageAmount;
                
                if(parseInt(payableAmount) < parseInt(depositAmount)){
                    swal("Warning!", "Payable amount should be greater than deposit amount!", "warning");
                    $('#emergancy_maturity_payable').val('');

                    if(parseInt(interestRateAmount) >= parseInt(tdsPerAmount)){
                        //var tdsAmount = tdsPer*interestRateAmount/100;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }else{
                        //var tdsAmount = 0;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }
                }else if(parseInt(payableAmount) >= parseInt(depositAmount)){

                    var interestAmount = parseInt(payableAmount)-parseInt(depositAmount);

                    if(parseInt(interestAmount) >= parseInt(tdsPerAmount)){
                        //var tdsAmount = tdsPer*interestAmount/100;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }else{
                        //var tdsAmount = 0;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }
                }else{
                    if(parseInt(interestRateAmount) >= parseInt(tdsPerAmount)){
                        //var tdsAmount = tdsPer*interestRateAmount/100;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }else{
                        //var tdsAmount = 0;
                        var finalAmount = parseInt(payableAmount)-parseInt(tdsAmount);
                    }
                }

                var eliMbAmount = parseInt($('#fd_interest_amt').val());
                var eliAmount = parseInt($('#eli_amt').val());
                if(eliMbAmount){
                    eliMbAmount = 0;
                }else{
                    eliMbAmount = 0;
                }
                if(eliAmount){
                    eliAmount = eliAmount;
                }else{
                    eliAmount = 0;
                }

                var fAmount = parseInt(finalAmount)+parseInt(eliMbAmount)+parseInt(eliAmount);

                $('#final_tds_amount').val(fAmount);
            }
        });

    });

    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });



});

function searchForm() {
        $('#is_search').val('yes');
        emergancyTable.draw();

    }
    function resetForm(){
        $('#company_id').val('0').trigger('change');
        $('#is_search').val('no');
        $('#company_id').val('0');
        emergancyTable.draw();
    }

</script>