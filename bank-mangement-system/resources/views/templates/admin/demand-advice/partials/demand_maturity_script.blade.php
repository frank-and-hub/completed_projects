<script type="text/javascript">
var demandAdviceTable;
$(document).ready(function() {
    $('#fdate').on('blur',function(){
    var default_date = $('#default_date').val();
    $('#fdate').val(default_date);
  });
  $('#tdate').on('blur',function(){
    var default_date = $('#default_date').val();
    $('#tdate').val(default_date);
  });
    let branchid = $( "#hbranchid option:selected" ).val();
    $('#add_demand_advice,#edit_demand_advice').validate({ // initialize the plugin
        rules: {
            'paymentType' : {required: true},
            'company_id' : {required: true},
            'branch' : {required: true},
            'date' : {required: true},
            'death_help_date' : {required: true},
            'expenseType' : {required: true},
            'employee_code' : {required: true},
            'employee_name' : {required: true},
            //'particular' : {required: true},
            'advance_amount' : {required: true},
            'advanced_salary_employee_code' : {required: true},
            'advanced_salary_employee_name' : {required: true},
            'advanced_salary_mobile_number' : {required: true,number:true},
            'advanced_salary_amount' : {required: true,number: true},
            'advanced_salary_letter_photo' : {required: true},
            'advanced_salary_narration' : {required: true},
            /*'advanced_salary_ssb_account' : {required: true},*/
            'advanced_salary_bank_name' : {required: true},
            'advanced_salary_bank_account_number' : {required: true},
            'advanced_salary_ifsc_code' : {required: true},
            'advanced_salary_party_name' : {required: true},
            'advanced_rent_mobile_number' : {required: true,number:true},
            'advanced_rent_amount' : {required: true,number: true},
            'advanced_rent_narration' : {required: true},
            // 'advanced_rent_ssb_account' : {required: true},
            'advanced_rent_bank_name' : {required: true},
            'advanced_rent_bank_account_number' : {required: true},
            'advanced_rent_ifsc_code' : {required: true},
            'maturity_account_number' : {required: true},
            'advanced_rent_party_name' : {required :true},
            'maturity_opening_date' : {required: true},
            'maturity_plan_name' : {required: true},
            'maturity_tenure' : {required: true},
            'maturity_amount' : {required: true},
            'maturity_mobile_number' : {required: true,number:true},
            //'maturity_ssb_account' : {required: true},
            'maturity_bank_account_number' : {number:true,minlength: 8,maxlength: 20},
            'maturity_ifsc_code' : {checkIfsc:true},
            'maturity_letter_photo' : {required: true, filesize: 160000},
            'prematurity_account_number' : {required: true},
            //'prematurity_opening_date' : {required: true},
            //'prematurity_plan_name' : {required: true},
            //'prematurity_tenure' : {required: true},
            //'prematurity_amount' : {required: true},
            'prematurity_mobile_number' : {required: true,number:true},
            //'prematurity_ssb_account' : {required: true},
            //'prematurity_bank_account_number' : {required: true},
            //'prematurity_ifsc_code' : {required: true},
            'prematurity_letter_photo' : {required: true ,filesize: 160000},
            'death_help_category' : {required: true},
            //'death_help_nominee_member_id' : {required: true,number:true},
            'death_help_mobile_number' : {required: true,number:true},
            'maturity_prematurity_date' : {required: true},
            'maturity_prematurity_type' : {required: true},
            'death_help_letter_photo' : {required: true},
			'payment_mode': {required: true},
            'ta_employee_code' : {required: true},
            'ta_employee_name' : {required: true},
            'ta_particular' : {required: true},
            'ta_advance_amount' : {required: true,number:true},
            // 'maturity_category' : {required: true},
            // 'prematurity_category' : {required: true},
            'death_help_account_number': {required: true},
        },
        submitHandler: function() {
            var countFreshExpense = $( "#count-fresh-expense" ).val();
            var paymentType = $( "#paymentType option:selected" ).val();
            var paymentVal = $( "#expenseType option:selected" ).val();
            var counttaExpense = $( "#count-ta-expense" ).val();
            if(paymentType == 0 && paymentVal != '' && paymentVal == 0 && countFreshExpense == 0){
                swal("Warning!", "Please create expense!", "warning");
                return false;
            }else if(paymentType == 0 && paymentVal != '' && paymentVal == 1 && counttaExpense == 0){
                swal("Warning!", "Please create expense!", "warning");
                return false;
            }
            $('.submit-demand-advice').prop('disabled', true);
            return true;
        }
    });
    $('#add_ta_advanced').validate({ // initialize the plugin
        rules: {
            'payment_date' : {required: true},
            'amount_mode' : {required: true},
            'branch_id' : {required: true},
            'cash_type' : {required: true},
            'bank' : {required: true},
            'bank_account_number' : {required: true},
            'mode' : {required: true},
            'cheque_number' : {required: true},
            'utr_number' : {required: true},
            'amount' : {required: true},
            'neft_charge' : {required: true},
            'total_amount' : {required: true},
        },
        submitHandler: function() {
            var counttaExpense = $( "#count-ta-expense" ).val();
            var amountMode = $('option:selected', "#amount_mode").val();
            var amount = $( "#amount" ).val();
            var totalAmount = $( "#total_amount" ).val();
            var availableBalance = $("#available_balance").val();
            var cashBalance = $( "#cash_in_hand_balance" ).val();
            if(counttaExpense == 0){
                swal("Warning!", "Please create expense!", "warning");
                return false;
            }
            if(amountMode == 0 && parseInt(amount) > parseInt(cashBalance)){
                swal("Warning!", "Insufficient balance!", "warning");
                return false;
            }
            if(amountMode == 2 && parseInt(totalAmount) > parseInt(availableBalance)){
                swal("Warning!", "Insufficient balance!", "warning");
                return false;
            }
            $('.submit-ta-advanced').prop('disabled', true);
            return true;
        }
    });
    $('#filter_ta_advanced_report').validate({ // initialize the plugin
        rules: {
            'employee_code' : {required: true},
            'employee_name' : {required: true},
        },
    });
    $.validator.addMethod("maxDate", function(value, element) {
        var sDate = $('.created_at').val();
        var curDate = moment(sDate).format('YYYY-M-D');
        var valueDate = moment(value).format('YYYY-M-D');
        // console.log(value);
        if (value <= curDate)
            return true;
        return false;
    }, "Invalid date!");
    $.validator.addMethod('filesize', function(value, element, param) {
  return this.optional(element) || (element.files[0].size <= param)
}, 'File size must be less than  160 KB');
    /*$.validator.addMethod("maxpDate", function(value, element) {
        var sDate = $('#mdate').val();
        var curDate = moment(sDate).format('DD/MM/YYYY');
        if (value >= curDate)
            return true;
        return false;
    }, "Invalid date!");*/
    $('#application_filter_report').validate({
        rules: {
            'expense_type' : {required: true},
            'advice_type' : {required: true},
            'voucher_number' : {number: true},
            'date_from' : {required: true},
            'date_to' : {required: true},
        },
    });
    $('#filter_maturity').validate({
        rules: {
            'date_to' : {required: true},
            'expense_type' : {required: true},
            'advice_type' : {required: true},
            'voucher_number' : {number: true},
            'date_from' : {required: true},
        },
    });
    $('#filter_report').validate({
        rules: {
            'voucher_number' : {number: true},
        },
    });
    $('#transferr_demand_advice_amount').validate({ // initialize the plugin
        rules: {
            'payment_date' : {required: true/*,maxpDate: true*/},
            'is_assests' : {required: true},
            'assets_category' : {required: true},
            'assets_subcategory' : {required: true},
            'amount_mode' : {required: true},
            'branch_id' : {required: true},
            'cash_type' : {required: true},
            'bank' : {required: true},
            'bank_account_number' : {required: true},
            'mode' : {required: true},
            'cheque_number' : {required: true},
            'utr_number' : {required: true},
            'amount' : {required: true},
            'neft_charge' : {required: true,number: true},
            'total_amount' : {required: true},
        },
        submitHandler: function() {
            var countFreshExpense = $( "#selected_fresh_expense_records" ).val();
            var amountMode = $('option:selected', "#amount_mode").val();
            var amount = $( "#amount" ).val();
            var totalAmount = $( "#total_amount" ).val();
            var availableBalance = $("#available_balance").val();
            var cashBalance = $( "#cash_in_hand_balance" ).val();
            if(countFreshExpense == ''){
                swal("Warning!", "Please select records!", "warning");
                return false;
            }
            if(amountMode == 0 && parseInt(amount) > parseInt(cashBalance)){
                swal("Warning!", "Insufficient balance!", "warning");
                return false;
            }
            if(amountMode == 2 && parseInt(totalAmount) > parseInt(availableBalance)){
                swal("Warning!", "Insufficient balance!", "warning");
                return false;
            }
            $('.submit-transfer-button').prop('disabled', true);
            return true;
        }
    });
    $('#transfer-rent-payable').validate({ // initialize the plugin
        rules: {
            'is_assests' : {required: true},
        },
        submitHandler: function() {
            var countApplicationRecord = $( "#selected_records" ).val();
            if(countApplicationRecord == ''){
                swal("Warning!", "Please select at least one record!", "warning");
                return false;
            }
            $('.approve-application').prop('disabled', true);
            return true;
        }
    });
    $('#delete-demand-application').validate({ // initialize the plugin
        rules: {
            'is_assests' : {required: true},
        },
        submitHandler: function() {
            var countApplicationRecord = $( "#select_deleted_records" ).val();
            if(countApplicationRecord == ''){
                swal("Warning!", "Please select at least one record!", "warning");
                return false;
            }
            $('.delete-demand-application').prop('disabled', true);
            return true;
        }
    });
    $('#demandRejectReason').validate({ // initialize the plugin
        rules: {
            'rejectreason' : {required: true},
        },
    });
    $(document).on('click', '.reject-demand-advice', function(e){
        const modalTitle = $(this).attr('modal-title');
        const demandId = $(this).attr('demandId');
        $('#demandRejectReason').attr('action',"{!! route('admin.demand.rejectReason') !!}")
        $('#exampleModalLongTitle').html(modalTitle);
        $('#demandId').val(demandId);
    })
    $(document).on('change','#paymentType',function(){
        var paymentType = $('option:selected', this).attr('data-val');
        $('#expenseType').val('');
        $('.input-type').val('');
        $('.payment-type-box').hide();
        $('.payment-type-sub-box').hide();
        $('.input').val('');
        $('.'+paymentType).show();
    });
    $(document).on('change','#expenseType,#libilty_type,#libilty_head,#maturity_prematurity_type,#death_help_category',function(){
        var paymentType = $('option:selected', this).attr('data-val');
        var liabilityArray = ['42-liability','43-liability','44-liability','45-liability','46-liability','47-liability'];
        $('.input').val('');
        $('.payment-type-sub-box').hide();
        if(paymentType == 1 && paymentType != ''){
            if(jQuery.inArray(paymentType, liabilityArray) !== -1){
                $('.'+paymentType).show();
            }else{
                $('.libility-other').show();
            }
        }else{
            $('.'+paymentType).show();
        }
    });
    $(document).on('click','.add-fresh-expense',function(){
        var expenseCategoryTitle = $('option:selected', '#expense_category').attr('data-val');
        var expenseCategory = $('option:selected', '#expense_category').val();
        if($('option:selected', '#expense_subcategory1').val()){
            var expenseSubCategoryTitle1 = $('option:selected', '#expense_subcategory1').attr('data-val');
            var expenseSubCategory1 = $('option:selected', '#expense_subcategory1').val();
        }else{
            var expenseSubCategoryTitle1 = '';
            var expenseSubCategory1 = '';
        }
        var subcat2 = $('#subcategory_value2').val();
        if($('option:selected', '#expense_subcategory2').val()){
            var expenseSubCategoryTitle2 = $('option:selected', '#expense_subcategory2').attr('data-val');
            var expenseSubCategory2 = $('option:selected', '#expense_subcategory2').val();
        }else{
            var expenseSubCategoryTitle2 = '';
            var expenseSubCategory2 = '';
        }
        var subcat3 = $('#subcategory_value3').val();
        if($('option:selected', '#expense_subcategory3').val()){
            var expenseSubCategoryTitle3 = $('option:selected', '#expense_subcategory3').attr('data-val');
            var expenseSubCategory3 = $('option:selected', '#expense_subcategory3').val();
        }else{
            var expenseSubCategoryTitle3 = '';
            var expenseSubCategory3 = '';
        }
        var party_name = $('#party_name').val();
        var particular = $('#particular').val();
        var mobile_number = $('#mobile_number').val();
        var amount = $('#amount').val();
        var billNumber = $('#bill_no').val();
        var countRecord = $('#count-fresh-expense').val();
        if(subcat2 > 0){
            if(expenseSubCategory2 == ''){
                swal("Warning!", "Please fill all required fields!", "warning");
                return false;
            }
        }
        if(subcat3 > 0){
            if(expenseSubCategory3 == ''){
                swal("Warning!", "Please fill all required fields!", "warning");
                return false;
            }
        }
        if(expenseCategory == '' || expenseSubCategory1 == '' || party_name == '' || particular == '' || mobile_number == '' || amount == '' || billNumber == ''){
            swal("Warning!", "Please fill all required fields!", "warning");
            return false;
        }else if($.isNumeric(mobile_number) == false || $.isNumeric(amount) != true || $.isNumeric(billNumber) != true){
            swal("Warning!", "Mobile Number,Amount,Bill Number must be numeric!", "warning");
            return false;
        }else{
            $('#expense_category').val('');
            $('#expense_subcategory1').val('');
            $('#expense_subcategory2').val('');
            $('.expense_subcategory2_box').hide();
            $('#expense_subcategory3').val('');
            $('.expense_subcategory3_box').hide();
            $('#party_name').val('');
            $('#particular').val('');
            $('#mobile_number').val('');
            $('#amount').val('');
            $('#bill_no').val('');
            $('#bill_photo').val('');
            $('#subcategory_value2').val(0);
            $('#subcategory_value3').val(0);
            var countRec = parseInt(countRecord)+1;
            $('.fresh-expense-total-amount').before('<tr><td>'+expenseCategoryTitle+'</td><input type="hidden" name="fresh_expense['+countRecord+'][id]" value=""><input type="hidden" name="fresh_expense['+countRecord+'][expenseCategory]" value="'+expenseCategory+'"><td>'+expenseSubCategoryTitle1+'</td><input type="hidden" name="fresh_expense['+countRecord+'][expenseSubCategory1]" value="'+expenseSubCategory1+'"><td>'+expenseSubCategoryTitle2+'</td><input type="hidden" name="fresh_expense['+countRecord+'][expenseSubCategory2]" value="'+expenseSubCategory2+'"><td>'+expenseSubCategoryTitle3+'</td><input type="hidden" name="fresh_expense['+countRecord+'][expenseSubCategory3]" value="'+expenseSubCategory3+'"><td>'+party_name+'</td><input type="hidden" name="fresh_expense['+countRecord+'][party_name]" value="'+party_name+'"><td>'+particular+'</td><input type="hidden" name="fresh_expense['+countRecord+'][particular]" value="'+particular+'"><td>'+mobile_number+'</td><input type="hidden" name="fresh_expense['+countRecord+'][mobile_number]" value="'+mobile_number+'"><td>'+amount+' &#8377</td><input type="hidden" name="fresh_expense['+countRecord+'][amount]" class="fe-amount" value="'+amount+'"><td>'+billNumber+'</td><input type="hidden" name="fresh_expense['+countRecord+'][billNumber]" value="'+billNumber+'"><td><input type="file" name="fresh_expense['+countRecord+'][bill_photo]"></td><td> &nbsp; <a href="javascript:void(0);" class="remCF">Remove</a></td></tr>');
            var items = document.getElementsByClassName('fe-amount');
            var totalFeAmount = 0;
            for (var i = 0; i < items.length; i++){
                totalFeAmount = parseInt(totalFeAmount)+parseInt(items[i].value)
            }
            $('.fe_total_amount').html('Total Amount: '+totalFeAmount);
            $('#count-fresh-expense').val(countRec);
            return true;
        }
    });
    $(document).on('click','.add-ta-expense',function(){
        var expenseCategoryTitle = $('option:selected', '#ta_expense_category').attr('data-val');
        var expenseCategory = $('option:selected', '#ta_expense_category').val();
        var expenseSubCategoryTitle = $('option:selected', '#ta_expense_subcategory').attr('data-val');
        var expenseSubCategory = $('option:selected', '#ta_expense_subcategory').val();
        var amount = $('#ta_amount').val();
        var billNumber = $('#ta_bill_no').val();
        var countRecord = $('#count-ta-expense').val();
        if(expenseCategory == '' || expenseSubCategory == '' || amount == '' || billNumber == ''){
            swal("Warning!", "Please fill all required fields!", "warning");
            return false;
        }else if($.isNumeric(amount) != true || $.isNumeric(billNumber) != true){
            swal("Warning!", "Amount,Bill Number must be numeric!", "warning");
            return false;
        }else{
            $('#ta_expense_category').val('');
            $('#ta_expense_subcategory').val('');
            $('#ta_amount').val('');
            $('#ta_bill_no').val('');
            $('#ta_bill_photo').val('');
            var countRec = parseInt(countRecord)+1;
            $('.ta-expense-table').append('<tr><td>'+expenseCategoryTitle+'</td><input type="hidden" name="ta_expense['+countRecord+'][id]" value=""><input type="hidden" name="ta_expense['+countRecord+'][expenseCategory]" value="'+expenseCategory+'"><td>'+expenseSubCategoryTitle+'</td><input type="hidden" name="ta_expense['+countRecord+'][expenseSubCategory]" value="'+expenseSubCategory+'"><td>'+amount+' &#8377</td><input type="hidden" name="ta_expense['+countRecord+'][amount]" value="'+amount+'" class="ta-expense-amount"><td>'+billNumber+'</td><input type="hidden" name="ta_expense['+countRecord+'][billNumber]" value="'+billNumber+'"><td><input type="file" name="ta_expense['+countRecord+'][bill_photo]"></td><td> &nbsp; <a href="javascript:void(0);" class="remta">Remove</a></td></tr>');
            var items = document.getElementsByClassName('ta-expense-amount');
            var total = 0;
            for (var i = 0; i < items.length; i++){
                total = parseInt(total)+parseInt(items[i].value)
            }
            var advancedAmount = $('#ta_advance_amount').val();
            if(total == 0){
                $('#difference_amount').val(0);
            }else{
                $('#difference_amount').val(parseInt(advancedAmount)-parseInt(total));
            }
            $('#count-ta-expense').val(countRec);
            $('.pay-expenses').trigger('click');
            $('#amount_mode').val('');
            $('#amount_mode').trigger('change');
            return true;
        }
    });
    $(".fresh-expense-table").on('click','.remCF',function(){
        $(this).parent().parent().remove();
        var items = document.getElementsByClassName('fe-amount');
        var totalFeAmount = 0;
        for (var i = 0; i < items.length; i++){
            totalFeAmount = parseInt(totalFeAmount)+parseInt(items[i].value)
        }
        $('.fe_total_amount').html('Total: '+totalFeAmount);
    });
    $(".ta-expense-table").on('click','.remta',function(){
        var countRecord = $('#count-ta-expense').val();
        var countRec = parseInt(countRecord)-1;
        $('#count-ta-expense').val(countRec);
        $(this).parent().parent().remove();
        $('.pay-expenses').trigger('click');
        $('#amount_mode').val('');
        $('#amount_mode').trigger('change');
        var items = document.getElementsByClassName('ta-expense-amount');
        var total = 0;
        for (var i = 0; i < items.length; i++){
            total = parseInt(total)+parseInt(items[i].value)
        }
        var advancedAmount = $('#ta_advance_amount').val();
        if(total == 0){
            $('#difference_amount').val(0);
        }else{
            $('#difference_amount').val(parseInt(advancedAmount)-parseInt(total));
        }
    });
    $(document).on('click','.edit-fresh-expense',function(){
        $('.create-expense').hide();
        $('.edit-expense').show();
        var fId = $(this).attr('data-id');
        var expenseCategory = $('.'+fId+'-category').val();
        var expenseSubCategory1 = $('.'+fId+'-subcategory1').val();
        var expenseSubCategory2 = $('.'+fId+'-subcategory2').val();
        var expenseSubCategory3 = $('.'+fId+'-subcategory3').val();
        if(expenseCategory){
            $("#edit_expense_category option[value=" + expenseCategory +"]").prop("selected",true) ;
        }
        if(expenseSubCategory1){
            $("#edit_expense_subcategory1 option[value=" + expenseSubCategory1 +"]").prop("selected",true) ;
        }
        if(expenseSubCategory2){
            $("#edit_expense_subcategory2 option[value=" + expenseSubCategory2 +"]").prop("selected",true) ;
        }
        $('#edit_expense_subcategory2').attr("disabled", true);
        if(expenseSubCategory3){
            $("#edit_expense_subcategory3 option[value=" + expenseSubCategory3 +"]").prop("selected",true) ;
        }
        $('#edit_expense_subcategory3').attr("disabled", true);
        var party_name = $('.'+fId+'-party_name').val();
        var particular = $('.'+fId+'-particular').val();
        var mobile_number = $('.'+fId+'-mobile_number').val();
        var amount = $('.'+fId+'-amount').val();
        var billNumber = $('.'+fId+'-billNumber').val();
        $('.sub-account-head').hide();
        $('.sub-account-head-'+expenseCategory).show();
        $("#party_name").val(party_name) ;
        $("#particular").val(particular) ;
        $("#mobile_number").val(mobile_number) ;
        $("#amount").val(amount) ;
        $("#bill_no").val(billNumber) ;
        $('.add-update-fe-button').html('<a href="javascript:void(0);" class="btn btn-primary update-fresh-expense" data-id="'+fId+'" style="margin-bottom: 10px;">Update </a>');
    });
    $(document).on('click','.edit-ta-expense',function(){
        var fId = $(this).attr('data-id');
        var expenseCategory = $('.'+fId+'-category').val();
        var expenseSubCategory = $('.'+fId+'-subcategory').val();
        var amount = $('.'+fId+'-amount').val();
        var billNumber = $('.'+fId+'-billNumber').val();
        $('.ta-sub-account-head').hide();
        $('.ta-sub-account-head-'+expenseCategory).show();
        $("#ta_expense_category option[value=" + expenseCategory +"]").prop("selected",true) ;
        $("#ta_expense_subcategory option[value=" + expenseSubCategory +"]").prop("selected",true) ;
        $("#ta_amount").val(amount) ;
        $("#ta_bill_no").val(billNumber) ;
        $('.add-update-ta-button').html('<a href="javascript:void(0);" class="btn btn-primary update-ta-expense" data-id="'+fId+'" style="margin-bottom: 10px;">Update </a>');
    });
    $(document).on('click','.update-fresh-expense',function(){
        var fId = $(this).attr('data-id');
        var expenseCategoryTitle = $('option:selected', '#edit_expense_category').attr('data-val');
        var expenseCategory = $('option:selected', '#edit_expense_category').val();
        if($('option:selected', '#edit_expense_subcategory1').val()){
            var expenseSubCategoryTitle1 = $('option:selected', '#edit_expense_subcategory1').attr('data-val');
            var expenseSubCategory1 = $('option:selected', '#edit_expense_subcategory1').val();
        }else{
            var expenseSubCategoryTitle1 = '';
            var expenseSubCategory1 = '';
        }
        var subcat2 = $('#edit_subcategory_value2').val();
        if($('option:selected', '#edit_expense_subcategory2').val()){
            var expenseSubCategoryTitle2 = $('option:selected', '#edit_expense_subcategory2').attr('data-val');
            var expenseSubCategory2 = $('option:selected', '#edit_expense_subcategory2').val();
        }else{
            var expenseSubCategoryTitle2 = '';
            var expenseSubCategory2 = '';
        }
        var subcat3 = $('#edit_subcategory_value3').val();
        if($('option:selected', '#edit_expense_subcategory3').val()){
            var expenseSubCategoryTitle3 = $('option:selected', '#edit_expense_subcategory3').attr('data-val');
            var expenseSubCategory3 = $('option:selected', '#edit_expense_subcategory3').val();
        }else{
            var expenseSubCategoryTitle3 = '';
            var expenseSubCategory3 = '';
        }
        var party_name = $('#party_name').val();
        var particular = $('#particular').val();
        var mobile_number = $('#mobile_number').val();
        var amount = $('#amount').val();
        var billNumber = $('#bill_no').val();
        var countRecord = $('#count-fresh-expense').val();
        if(subcat2 > 0){
            if(expenseSubCategory2 == ''){
                swal("Warning!", "Please fill all required fields!", "warning");
                return false;
            }
        }
        if(subcat3 > 0){
            if(expenseSubCategory3 == ''){
                swal("Warning!", "Please fill all required fields!", "warning");
                return false;
            }
        }
        if(expenseCategory == '' || expenseSubCategory1 == '' || party_name == '' || particular == '' || mobile_number == '' || amount == '' || billNumber == ''){
            swal("Warning!", "Please fill all fields!", "warning");
            return false;
        }else{
            $('#expense_category').val('');
            $('#expense_subcategory1').val('');
            $('#expense_subcategory2').val('');
            $('.expense_subcategory2_box').hide();
            $('#expense_subcategory3').val('');
            $('.expense_subcategory3_box').hide();
            $('#party_name').val('');
            $('#particular').val('');
            $('#mobile_number').val('');
            $('#amount').val('');
            $('#bill_no').val('');
            $('#bill_photo').val('');
            $('#edit_subcategory_value2').val(0);
            $('#edit_subcategory_value3').val(0);
            $('.'+fId+'-category-td').html(expenseCategoryTitle);
            $('.'+fId+'-subcategory1-td').html(expenseSubCategoryTitle1);
            $('.'+fId+'-subcategory2-td').html(expenseSubCategoryTitle2);
            $('.'+fId+'-subcategory3-td').html(expenseSubCategoryTitle3);
            $('.'+fId+'-party-name-td').html(party_name);
            $('.'+fId+'-particular-td').html(particular);
            $('.'+fId+'-mobile-number-td').html(mobile_number);
            $('.'+fId+'-amount-td').html(amount);
            $('.'+fId+'-billNumber-td').html(billNumber);
            $('.'+fId+'-category').val(expenseCategory);
            $('.'+fId+'-subcategory1').val(expenseSubCategory1);
            $('.'+fId+'-subcategory2').val(expenseSubCategory2);
            $('.'+fId+'-subcategory3').val(expenseSubCategory3);
            $('.'+fId+'-party_name').val(party_name);
            $('.'+fId+'-particular').val(particular);
            $('.'+fId+'-mobile_number').val(mobile_number);
            $('.'+fId+'-amount').val(amount);
            $('.'+fId+'-billNumber').val(billNumber);
            $('.add-update-fe-button').html('<a href="javascript:void(0);" class="btn btn-primary add-fresh-expense">Add </a>');
            $('.create-expense').show();
            $('.edit-expense').hide();
        }
    });
    $(document).on('click','.update-ta-expense',function(){
        var fId = $(this).attr('data-id');
        var expenseCategoryTitle = $('option:selected', '#ta_expense_category').attr('data-val');
        var expenseCategory = $('option:selected', '#ta_expense_category').val();
        var expenseSubCategoryTitle = $('option:selected', '#ta_expense_subcategory').attr('data-val');
        var expenseSubCategory = $('option:selected', '#ta_expense_subcategory').val();
        var amount = $('#ta_amount').val();
        var billNumber = $('#ta_bill_no').val();
        var countRecord = $('#count-ta-expense').val();
        if(expenseCategory == '' || expenseSubCategory == '' || amount == '' || billNumber == ''){
            swal("Warning!", "Please fill all fields!", "warning");
            return false;
        }else{
            $('#ta_expense_category').val('');
            $('#ta_expense_subcategory').val('');
            $('#ta_amount').val('');
            $('#ta_bill_no').val('');
            $('#ta_bill_photo').val('');
            $('.'+fId+'-category-td').html(expenseCategoryTitle);
            $('.'+fId+'-subcategory-td').html(expenseSubCategoryTitle);
            $('.'+fId+'-amount-td').html(amount);
            $('.'+fId+'-billNumber-td').html(billNumber);
            $('.'+fId+'-category').val(expenseCategory);
            $('.'+fId+'-subcategory').val(expenseSubCategory);
            $('.'+fId+'-amount').val(amount);
            $('.'+fId+'-billNumber').val(billNumber);
            $('.add-update-ta-button').html('<a href="javascript:void(0);" class="btn btn-primary add-ta-expense">Add </a>');
        }
    });
    // Datatables
    demandAdviceTable = $('#demand-advice-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.demandadvice.list') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'payment_type', name: 'payment_type'},
            {data: 'sub_payment_type', name: 'sub_payment_type'},
            {data: 'branch', name: 'branch'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(demandAdviceTable.table().container()).removeClass( 'form-inline' );
    demandAdviceReportTable = $('#demand-advice-report-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.demandadvice.reportlist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter_report').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'name', name: 'name'},
            {data: 'nominee_name', name: 'nominee_name'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'ac_opening_date', name: 'ac_opening_date'},
            {data: 'advice_type', name: 'advice_type'},
            {data: 'expense_type', name: 'expense_type'},
            {data: 'date', name: 'date'},
            {data: 'voucher_number', name: 'voucher_number'},
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'is_loan', name: 'is_loan'},
           /* {data: 'owner_name', name: 'owner_name'},
            {data: 'particular', name: 'particular'},
            {data: 'mobile_number', name: 'mobile_number'},*/
            {data: 'total_amount', name: 'total_amount'},
            {data: 'payment_trf_amt', name: 'payment_trf_amt'},
            {data: 'tds_amount', name: 'tds_amount'},
            {data: 'interest_amount', name: 'interest_amount'},
            {data: 'total_payable_amount', name: 'total_payable_amount'},
            {data: 'neft_charge', name: 'neft_charge'},
            {data: 'account_number', name: 'account_number'},
            {data: 'ssb_account_number', name: 'ssb_account_number'},
            {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'ifsc_code', name: 'ifsc_code'},
            {data: 'print', name: 'print'},
			{data: 'maturity_payment_mode', name: 'maturity_payment_mode'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(demandAdviceReportTable.table().container()).removeClass( 'form-inline' );
    demandAdviceMaturityTable = $('#demand-advice-maturity-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.demandadvices.demand_advice_maturity_list') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter_maturity').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},
            {data: 'branch_name', name: 'branch_name'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector', name: 'sector'},
            // {data: 'regan', name: 'regan'},
            // {data: 'zone', name: 'zone'},
            {data: 'date', name: 'date'},
            {data: 'member_name', name: 'member_name'},
            {data: 'maturity_amount_tds', name: 'maturity_amount_tds'},
            {data: 'maturity_amount_till_date', name: 'maturity_amount_till_date'},
            {data: 'maturity_amount_payable', name: 'maturity_amount_payable'},
            {data: 'voucher_number', name: 'voucher_number'},
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'account_number', name: 'account_number'},
            {data: 'letter_photos', name: 'letter_photos'},
			{data: 'maturity_payment_mode', name: 'maturity_payment_mode'},
            {data: 'status', name: 'status'},
            {data: 'calculate_maturity', name: 'calculate_maturity'},
        ],"ordering": false,
    });
    $(demandAdviceMaturityTable.table().container()).removeClass( 'form-inline' );
    taAdvancedReportTable = $('#ta-advanced-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.taadvanced.list') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter_ta_advanced_report').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'payment_type', name: 'payment_type'},
            {data: 'sub_payment_type', name: 'sub_payment_type'},
            {data: 'branch', name: 'branch'},
            {data: 'employee_code', name: 'employee_code'},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'advanced_amount', name: 'advanced_amount'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(taAdvancedReportTable.table().container()).removeClass( 'form-inline' );
    demandAdviceApplicationTable = $('#demand-advice-application-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.demandadvice.applicationlist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#application_filter_report').serializeArray()
            d.branchid = branchid
      },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'checkbox', name: 'checkbox',orderable: false, searchable: false},
            {data: 'company_name', name: 'company_name'},
            {data: 'branch_name', name: 'branch_name'},
			{data: 'maturity_payment_mode', name: 'maturity_payment_mode'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'zone', name: 'zone'},
            {data: 'account_number', name: 'account_number'},
            {data: 'member_name', name: 'member_name'},
            // {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'is_loan', name: 'is_loan'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'tds_amount', name: 'tds_amount'},
            {data: 'interest_amount', name: 'interest_amount'},
            // {data: 'total_payable_amount', name: 'total_payable_amount'},
            {data: 'final_amount', name: 'final_amount'},
            {data: 'date', name: 'date'},
            {data: 'created_at', name: 'created_at'},
            {data: 'advice_type', name: 'advice_type'},
            {data: 'expense_type', name: 'expense_type'},
            {data: 'voucher_number', name: 'voucher_number'},
            /*{data: 'advice_number', name: 'advice_number'},
            {data: 'owner_name', name: 'owner_name'},
            {data: 'particular', name: 'particular'},
            {data: 'mobile_number', name: 'mobile_number'},*/
            {data: 'letter_photos', name: 'letter_photos'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action'},
        ],"ordering": false,
    });
    $(demandAdviceApplicationTable.table().container()).removeClass( 'form-inline' );
    $(document).on('click', '.delete-demand-advice', function(e){
        var url = $(this).attr('href');
        e.preventDefault();
        swal({
          title: "Are you sure, you want to delete this demand advice?",
          text: "",
          icon: "warning",
          buttons: [
            'No, cancel it!',
            'Yes, I am sure!'
          ],
          dangerMode: true,
        }).then(function(isConfirm) {
          if (isConfirm) {
            location.href = url;
          }
        });
    })
    /*$(document).on('change','#subaccounttype',function(){
        var accountType = $('option:selected', this).val();
        if(accountType == 2){
            $('.account-number').show();
            $('.account-head-list').hide();
        }else{
            $('.account-number').hide();
            $('.account-head-list').show();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.getaccounthead') !!}",
                data: {'accountType':accountType},
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount > 0){
                        $('#accounthead').html('');
                        $.each(response.accountHeads, function( index, value ) {
                          $('#accounthead').append('<option value="'+value.id+'">'+value.title+'</option>');
                        });
                    }else{
                        swal("Error!", "Account Heads not found!", "error");
                    }
                }
            });
        }
    });*/
    $(".date-from,.date-to,#date,#maturity_prematurity_date,#death_help_date,#payment_date").hover(function(){
        const today = $('#create_application_date').val();
      $('.date-from,.date-to,#date,#maturity_prematurity_date,#death_help_date,#payment_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom",
        autoclose: true,
        endDate: today,
        startDate: '01/04/2021',
          })
   })
    $(document).on('change','#expense_category',function(){
        var expenseCategory = $('option:selected', this).val();
        $('#expense_subcategory').val('');
        $('.expense-subcategory').hide();
        $('.'+expenseCategory+'-expense').show();
    });
    /*$(document).on('change','#ta_expense_category',function(){
        var expenseCategory = $('option:selected', this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getsubaccountbycategory') !!}",
            data: {'expenseCategory':expenseCategory},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0){
                    $('#ta_expense_subcategory').html('');
                    $.each(response.subAccountHeads, function( index, value ) {
                      $('#ta_expense_subcategory').append('<option data-val="'+value.title+'" value="'+value.id+'">'+value.title+'</option>');
                    });
                }else{
                    swal("Error!", "Sub Account Heads not found!", "error");
                }
            }
        });
    });*/
    // Get registered member by id
    $(document).on('change','#ta_employee_code,#advanced_salary_employee_code',function(){
        var employee_code = $(this).val();
        var classVal = $(this).attr('data-val');
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getemployee') !!}",
            dataType: 'JSON',
            data: {'employee_code':employee_code},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0){
                    $('#'+classVal+'_employee_id').val(response.employeeDetails[0].id);
                    $('#'+classVal+'_employee_name').val(response.employeeDetails[0].employee_name);
                    $('#'+classVal+'_mobile_number').val(response.employeeDetails[0].mobile_no);
                    $('#'+classVal+'_ssb_account').val(response.employeeDetails[0].ssb_account);
                    $('#'+classVal+'_bank_name').val(response.employeeDetails[0].bank_name);
                    $('#'+classVal+'_bank_account_number').val(response.employeeDetails[0].bank_account_no);
                    $('#'+classVal+'_ifsc_code').val(response.employeeDetails[0].bank_ifsc_code);
                }else{
                    $('#'+classVal+'_employee_id').val('');
                    $('#'+classVal+'_employee_code').val('');
                    $('#'+classVal+'_employee_name').val('');
                    $('#'+classVal+'_mobile_number').val('');
                    $('#'+classVal+'_ssb_account').val('');
                    $('#'+classVal+'_bank_name').val('');
                    $('#'+classVal+'_bank_account_number').val('');
                    $('#'+classVal+'_ifsc_code').val('');
                    swal("Warning!", "Employee Code not found!", "warning");
                }
            }
        });
    });
    /*$(document).on('change','#advanced_salary_employee_code',function(){
        var val = $(this).val();
        var classVal = $(this).attr('data-val');
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getemployee') !!}",
            dataType: 'JSON',
            data: {'val':val},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.employeeDetails ){
                    $('#'+classVal+'_employee_name').val(response.employeeDetails.employee_name);
                    $('#'+classVal+'_mobile_number').val(response.employeeDetails.mobile_no);
                    $('#'+classVal+'_ssb_account').val(response.employeeDetails.ssb_account);
                    $('#'+classVal+'_bank_name').val(response.employeeDetails.bank_name);
                    $('#'+classVal+'_bank_account_number').val(response.employeeDetails.bank_account_no);
                    $('#'+classVal+'_ifsc_code').val(response.employeeDetails.bank_ifsc_code);
                }else{
                    $('#'+classVal+'_employee_name').val('');
                    $('#'+classVal+'_mobile_number').val('');
                    $('#'+classVal+'_ssb_account').val('');
                    $('#'+classVal+'_bank_name').val('');
                    $('#'+classVal+'_bank_account_number').val('');
                    $('#'+classVal+'_ifsc_code').val('');
                }
            }
        });
    });*/
    $(document).on('change','#advanced_rent_party_name',function(){
        var val = $(this).val();
        var classVal = $(this).attr('data-val');
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getowner') !!}",
            dataType: 'JSON',
            data: {'val':val},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.ownerDetails ){
                    $('#'+classVal+'_mobile_number').val(response.ownerDetails.owner_mobile_number);
                    $('#'+classVal+'_ssb_account').val(response.ownerDetails.owner_ssb_number);
                    $('#'+classVal+'_bank_name').val(response.ownerDetails.owner_bank_name);
                    $('#'+classVal+'_bank_account_number').val(response.ownerDetails.owner_bank_account_number);
                    $('#'+classVal+'_ifsc_code').val(response.ownerDetails.owner_bank_ifsc_code);
                }else{
                    $('#'+classVal+'_mobile_number').val('');
                    $('#'+classVal+'_ssb_account').val('');
                    $('#'+classVal+'_bank_name').val('');
                    $('#'+classVal+'_bank_account_number').val('');
                    $('#'+classVal+'_ifsc_code').val('');
                }
            }
        });
    });
    $(document).on('change','#maturity_account_number,#prematurity_account_number,#death_help_account_number',function(){
        var val = $(this).val();
        var date = $('#maturity_prematurity_date').val();
        const branch = $('#branch').val();
        const companyId = $('#company_id').val();
        var classVal = $(this).attr('data-val');
        var type = $('option:selected', '#paymentType').val();
        if(type == 2){
           var subtype = $('option:selected', '#maturity_prematurity_type').val();
        }else{
            var subtype = $('option:selected', '#death_help_category').val();
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getinvestment') !!}",
            dataType: 'JSON',
            data: {'val':val,'type':type,'subtype':subtype,'date':date,'branch':branch,'company_id':companyId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var signature = response.signature;
                var photo = response.photo;
                if(response.status == 200 ){
                    if(response.investmentDetails){
                        var created_date = moment(response.investmentDetails.created_at).format('DD/MM/YYYY');
                        $('#'+classVal+'_opening_date').val(created_date);
                        $('#'+classVal+'_investmnet_id').val(response.investmentDetails.id);
                        $('#'+classVal+'_plan_name').val(response.investmentDetails.plan.name);
                        $('#'+classVal+'_tenure').val(response.investmentDetails.tenure);
                        $('#'+classVal+'_deno').val(response.investmentDetails.deposite_amount);
                        $('#'+classVal+'_deposited_amount').val(response.investmentDetails.current_balance);
                        $('#'+classVal+'_amount').val(response.investmentDetails.current_balance);
                        if(response.investmentDetails.branch_id )
                        {
                            $('#branch option[value='+response.investmentDetails.branch_id +']').attr("selected", "selected");
                            $('#branch option[value!='+response.investmentDetails.branch_id +']').prop("disabled", "disabled");
                        };
                    }
                    if(response.investmentDetails.member){
                        $('#'+classVal+'_account_holder_name').val(response.investmentDetails.member.first_name+' '+response.investmentDetails.member.last_name);
                        $('#'+classVal+'_father_name').val(response.investmentDetails.member.father_husband);
                        if((response.investmentDetails.member.signature)){
                            $('.'+classVal+'_signature').html(' <img src="'+signature+'" alt="signature" width="180" height="100">');
                        }else{
                            $('.'+classVal+'_signature').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');
                        }
                        if((response.investmentDetails.member.photo)){
                            $('.'+classVal+'_photo').html(' <img src="'+photo+'" alt="photo" width="180" height="100">');
                        }else{
                            $('.'+classVal+'_photo').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="photo" width="180" height="100">');
                        }
                        if(type == 4){
                            /*if(response.investmentDetails.investment_nomiees){
                                $('#'+classVal+'_mobile_number').val(response.investmentDetails.investment_nomiees[0].phone_number);
                            }*/
                        }else{
                            $('#'+classVal+'_mobile_number').val(response.investmentDetails.member.mobile_no);
                        }
                    }
                    if(response.investmentDetails.ssb){
                        $('#'+classVal+'_ssb_account').val(response.investmentDetails.ssb.account_no);
                    }
                    if(response.investmentDetails.member_bank_detail){
                        $('#'+classVal+'_bank_account_number').val(response.investmentDetails.member_bank_detail.account_no);
                        $('#'+classVal+'_ifsc_code').val(response.investmentDetails.member_bank_detail.ifsc_code);
                    }
                    if(type != 4){
                        $('.m_category').hide();
                        if(response.isDefaulter == 0){
                            $("."+classVal+"_regular_category").show();;
                        }else if(response.isDefaulter == 1){
                            $("."+classVal+"_defaulter_category").show();;
                        }
                        $("#"+classVal+"_category option[value=" + response.isDefaulter +"]").prop("selected",true) ;
                    }
                    $('#death_help_death_claim_amount').val(response.finalAmount);
                }else{
                    $('#maturity_account_number').val('');
                    $('#'+classVal+'_opening_date').val('');
                    $('#'+classVal+'_investmnet_id').val('');
                    $('#'+classVal+'_plan_name').val('');
                    $('#'+classVal+'_tenure').val('');
                    $('#'+classVal+'_amount').val('');
                    $('#'+classVal+'_mobile_number').val('');
                    $('#'+classVal+'_ssb_account').val('');
                    $('#prematurity_account_number').val('');
                    $('#'+classVal+'_account_holder_name').val('');
                    $('#'+classVal+'_father_name').val('');
                    $('#'+classVal+'_bank_account_number').val('');
                    $('#'+classVal+'_ifsc_code').val('');
                    $("#"+classVal+"_category").val('');
                    $('#'+classVal+'_deno').val('');
                    $('#'+classVal+'_deposited_amount').val('');
                    $('#death_help_death_claim_amount').val('');
                    $('.'+classVal+'_photo').remove('');
                    $('.'+classVal+'_signature').remove('');
                    swal("Warning!", ""+response.message+"", "warning");
                }
            }
        });
    });
    $(document).on('change','#death_help_nominee_member_id',function(){
        var val = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getmemberdata') !!}",
            dataType: 'JSON',
            data: {'val':val},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.count > 0){
                    $('#nominee_name').val(response.mDetails.first_name+' '+response.mDetails.last_name);
                    $('#nominee_mobile_number').val(response.mDetails.mobile_no);
                    if(response.mDetails.saving_account != ''){
                        $('#nominee_ssb_account').val(response.mDetails.saving_account[0].account_no);
                    }else{
                        $('#nominee_ssb_account').val('');
                    }
                }else{
                    $('#nominee_name').val('');
                    $('#nominee_mobile_number').val('');
                    $('#nominee_ssb_account').val('');
                    swal("Warning!", "Member Id Not Found!", "warning");
                }
            }
        });
    });
    $(document).on('change','#death_help_category', function () {
        $('#death_help_account_number').val('');
        $('#death_help_opening_date').val('');
        $('#death_help_plan_name').val('');
        $('#death_help_tenure').val('');
        $('#death_help_account_holder_name').val('');
        $('#death_help_deno').val('');
        $('#death_help_deposited_amount').val('');
        $('#death_help_death_claim_amount').val('');
        $('#death_help_nominee_name').val('');
        $('#death_help_ssb_account').val('');
    });
    $(document).on('click','.calculate-maturity',function(){
        var investmentId = $(this).attr('data-id');
        var paymentType = $(this).attr('data-payment-type');
        var planType = $(this).attr('data-investmentType');
        var subPaymentType = $(this).attr('data-sub-payment-type');
        var adviceId = $(this).attr('data-advice-id');
        var demandId = $(this).attr('data-advice-id');
        var isCal = $(this).attr('data-val');
        $('.acc_number').text("Account number : "+$(this).attr('data-acc'));
        $('.deno').text("Deno Amount : "+$(this).attr('data-deno'));
        $('.created_at').text("Creation Date : "+$(this).attr('data-creation'));
        $('.tenure').text("Tenure : "+$(this).attr('data-tenure')+" Months");
        $('#maturity-calculation-form').modal('hide');
        /*if(isCal == 1){
            swal("Warning!", "Already Calculate Maturity!", "warning");
            return false;
        }*/
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getinvestmentdata') !!}",
            data: {'investmentId':investmentId,'paymentType':paymentType,'subPaymentType':subPaymentType,'demandId':demandId},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.status != 400)
                {
                $('#maturity-calculation-form').modal('show');
                $(".maturity-calculation-list").html('');
                $(".maturity-calculation-list").html(response.html);
                var tdsAmount =$(".tds-amount").html();
                var tdsPercentage =$(".tds-percentage").html();
                var tdsPercentageOnAmount =$(".tds-percentage-on-amount").html();
                var tAmount =$(".total-amount").html();
                var fAmount =$(".final-amount").html();
                var interest = $('.interest-rate-amount').text().replace('₹','');
                var tdeposit = $('.deposite-amount').text().replace('₹','');

                // if(planType != 5)
                // {
                    $('.'+investmentId+'_maturity_amount_tds').val(tdsAmount);
                    $('.'+investmentId+'_tds_interest').val(tdsPercentage);
                    $('.'+investmentId+'_tds_interest_on_amount').val(tdsPercentageOnAmount);
                    $('.'+investmentId+'_maturity_amount_till_date').val(tAmount);
                    $('.'+investmentId+'_maturity_amount_payable').val(fAmount);
                    $('.'+investmentId+'-calculate-maturity').attr('data-val',1);
                    $('.'+investmentId+'_interest').val(interest);
                    $('.'+investmentId+'_total_deposit_amount').val(tdeposit);

                // }
                // else{
                    $(".ok").attr("id",investmentId);
                    $(".ok").attr("data-advice-id",adviceId);
                    $(".ok").attr("data-investmentType",planType);
                // }
                $('#investment-maturity-form').append('<input type="hidden" name="demand_advice_id['+adviceId+']" value="'+adviceId+'">');
                $('#investment-maturity-form').append('<input type="hidden" name="interest['+adviceId+']" value="'+interest+'">');
            } else{
                    swal('warning',response.message,'warning');
                }
            }
        });
    });
    $(document).on('click','.ok',function(){
        var investmentId = $(this).attr('id');
        var adviceId = $(this).attr('data-advice-id');
        var planType = $(this).attr('data-investmentType');
        var tds = $('.tds-amount').text().replace('₹','');
        var tdsPercentage = $('.tds-percentage').text().replace('₹','');
        var tdsPercentageOnAmount = $('.tds-percentage-on-amount').text().replace('₹','');
        var tAmount = $('.total-amount').text().replace('₹','');
        var fAmount = $('.final-amount').text().replace('₹','');
        var interest = $('.interest-rate-amount').text().replace('₹','');
        if(planType == 5)
        {
            $('.'+investmentId+'_maturity_amount_tds').val(tds);
            $('.'+investmentId+'_tds_interest').val(tdsPercentage);
            $('.'+investmentId+'_tds_interest_on_amount').val(tdsPercentageOnAmount);
            $('.'+investmentId+'_maturity_amount_till_date').val(tAmount);
            $('.'+investmentId+'_maturity_amount_payable').val(fAmount);
            $('.'+investmentId+'_interest').val(interest);
            $('.'+investmentId+'-calculate-maturity').attr('data-val',1);
            $('#investment-maturity-form').append('<input type="hidden" name="demand_advice_id['+adviceId+']" value="'+adviceId+'">');
            $('#investment-maturity-form').append('<input type="hidden" name="interest['+adviceId+']" value="'+interest+'">');
        }
    })
    $(document).on('click','.submit-maturity',function(){
        $(".maturity_amount_till_date,.maturity_amount_payable").each(function() {
            if($(this).val() == ''){
                swal("Warning!", "Please Calculate All Maturity!", "warning");
                return false;
            }else{
            }
        })
        /*$('form#investment-maturity-form').attr('action',"{!! route('admin.demand.saveInvestmentMaturityAmount') !!}");
        $('form#investment-maturity-form').submit();
        return true;*/
    });
    $(document).on('change','.maturity_amount_payable',function(){
        var investmentId = $(this).attr('data-investment-id');
        var payableAmount = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.gettds') !!}",
            dataType: 'JSON',
            data: {'investmentId':investmentId,'payableAmount':payableAmount},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('.'+investmentId+'_maturity_amount_tds').val(response.investmentTds);
                $('.'+investmentId+'_tds_interest').val(response.tdsPercentage);
                $('.'+investmentId+'_tds_interest_on_amount').val(response.tdsPercentageAmount);
            }
        });
    });
    // Handle click on "Select all" control
    $('#select_all').on('click', function(){
      var rows = demandAdviceApplicationTable.rows({ 'search': 'applied' }).nodes();
      $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
    // Handle click on checkbox to set state of "Select all" control
    $('#demand-advice-application-table tbody').on('change', 'input[type="checkbox"]', function(){
        if(!this.checked){
            var el = $('#select_all').get(0);
            if(el && el.checked && ('indeterminate' in el)){
                el.indeterminate = true;
            }
        }
    });
    $(document).on('change','#select_all,#demand_advice_record',function(){
        var checked = [];
        var unchecked = [];
        $('input[name="demand_advice_record"]:checked').each(function() {
           checked.push(this.value);
        });
        $('input[type=checkbox]:not(:checked)').each(function() {
            if(Math.floor(this.value) == this.value && $.isNumeric(this.value))
            unchecked.push(this.value);
        });
        $('#selected_records').val(checked);
        $('#select_deleted_records').val(checked);
        $('#pending_records').val(unchecked);
    });
    $('#demand-advice-approve').DataTable();
    // Handle click on "Select all" control
    $('#select_all_fresh_expense').on('click', function(){
      var rows = demandAdviceFreshExpenseTable.rows({ 'search': 'applied' }).nodes();
      $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
    // Handle click on checkbox to set state of "Select all" control
    $('#fresh-expense-approve tbody').on('change', 'input[type="checkbox"]', function(){
        if(!this.checked){
            var el = $('#select_all').get(0);
            if(el && el.checked && ('indeterminate' in el)){
                el.indeterminate = true;
            }
        }
    });
    $(document).on('change','#select_all_fresh_expense,#fresh_expense_record',function(){
        var checked = [];
        var unchecked = [];
        var total = 0;
        $('input[name="fresh_expense_record"]:checked').each(function() {
           checked.push(this.value);
           total = parseInt(total)+parseInt($(this).attr('data-amount'));
        });
        $('input[type=checkbox]:not(:checked)').each(function() {
            if(Math.floor(this.value) == this.value && $.isNumeric(this.value))
            unchecked.push(this.value);
        });
        var neft_charge = $('#neft_charge').val();
        if(neft_charge == ''){
            neft_charge = 0;
        }else{
            neft_charge = $('#neft_charge').val();
        }
        if(total == ''){
            totalamount = 0;
        }else{
            totalamount = total;
        }
        $('#amount').val(parseInt(totalamount));
        $('#total_amount').val(parseInt(totalamount)+parseInt(neft_charge));
        $('#selected_fresh_expense_records').val(checked);
        $('#pending_fresh_expense_records').val(unchecked);
    });
    demandAdviceFreshExpenseTable = $('#fresh-expense-approve').DataTable();
    $('#advice_type').on('change', function(){
      var dataType = $('option:selected', this).attr('data-type');
      $('.advice-type').hide();
      $('#expense_type').val('');
      $('.'+dataType+'').show();
    });
   
    $(document).on('change','#is_assests',function(){
        var type = $(this).val();
        if(type == 0 && type != ''){
            $('.is-assets').show();
        }else if(type == 1 && type != ''){
            $('.is-assets').hide();
        }else{
            $('.is-assets').hide();
        }
        $('#assets_category').val('');
        $('#assets_subcategory').val('');
        $('#assestcategory').val('')
        $('#assest_sub_category').val('')
    });
    $(document).on('change','#assets_category', function () {
        var account = $('option:selected', this).val();
        $('#assets_subcategory').val('');
        $('.parent-id').hide();
        $('.'+account+'-parent-id').show();
    });
    $(document).on('change','.assets_category', function () {
        var id = $(this).val();
        $('#assest_sub_category').val(id);
        var data_row_id = $(this).attr("data-row-id");
        $.ajax({
            type: "POST",
            url: "{!! route('admin.get_head_details') !!}",
            dataType: 'JSON',
            data: {'head_id':id, 'data_row_id':data_row_id},
            headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                data_row_id = parseInt(data_row_id) + parseInt(1);
                for(var i=data_row_id; i<15; i++){
                    $(".MainHead"+i).remove();
                }
                if(response.status == "1"){
                    var htmls = response.heads;
                    $(".mainHeads").append(htmls);
                    $(".mainHeads").addClass("col-md-8");
                }
            }
        })
    });
    $(document).on('change','#bank', function () {
        var account = $('option:selected', this).val();
        $('#bank_account_number').val('');
        $('.c-bank-account').hide();
        $('.'+account+'-bank-account').show();
        $('#available_balance').val('');
    });
    $(document).on('change','#bank_account_number', function () {
        var account = $('option:selected', "#bank_account_number").attr('data-account');
        var bank_id = $('option:selected', "#bank").val();
        var date = $('#payment_date').val();
        if(date == ''){
            swal("Warning!", "Please select a date first!", "warning");
            return false;
        }
        $('#cheque_number').val('');
        $('.c-cheque').hide();
        $('.'+account+'-c-cheque').show();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.get_bank_balance') !!}",
            dataType: 'JSON',
            data: {'account_id':account,'entry_date':date,'bank_id':bank_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var amount = parseFloat(response);
                $('#available_balance').val(amount.toFixed(2));
            }
        });
    });
    $(document).on('change','#branch,#cash_type', function () {
        var branch_id = $('option:selected', '#branch').val();
        var cashType = $('option:selected', '#cash_type').val();
        const companyId = $('#company_id option:selected').val();
        var date = $('#payment_date').val();
        if(date == ''){
            swal("Warning!", "Please select a date first!", "warning");
            return false;
        }
        if(branch_id == ''){
            swal("Warning!", "Please select a branch first!", "warning");
            return false;
        }
        if(cashType == ''){
            swal("Warning!", "Please select a cash type first!", "warning");
            return false;
        }
		/*
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demadadvice.getbranchdaybookamount') !!}",
            dataType: 'JSON',
            data: {'branch_id':branch_id,'date':date},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(cashType == 0){
                    var amount = parseFloat(response.microDayBookAmount);
                    $('#cash_in_hand_balance').val(amount.toFixed(2));
                }else if(cashType == 1){
                    var amount = parseFloat(response.loanDayBookAmount);
                    $('#cash_in_hand_balance').val(amount.toFixed(2));
                }
            }
        });
		*/
		$.ajax({
			type: "POST", 
			url: "{!! route('admin.branchBankBalanceAmount') !!}",
			dataType: 'JSON',
			data: {'branch_id':branch_id,'entrydate':date,'company_id':companyId},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) { 
				if(cashType == 0){
                    var amount = parseFloat(response.balance);
                    $('#cash_in_hand_balance').val(amount.toFixed(2));
                }else if(cashType == 1){
                    var amount = parseFloat(response.balance);
                    $('#cash_in_hand_balance').val(amount.toFixed(2));
                }
			}
		});
    });
    $(document).on('change','#payment_date', function () {
        var amount_mode = $('option:selected', '#amount_mode').val();
        var date = $('#payment_date').val();
        if(amount_mode == '0'){
            var branch_id = $('option:selected', '#branch_id').val();
            var cashType = $('option:selected', '#cash_type').val();
            var date = $('#payment_date').val();
            if(branch_id == ''){
                swal("Warning!", "Please select a branch first!", "warning");
                return false;
            }
            if(cashType == ''){
                swal("Warning!", "Please select a cash type first!", "warning");
                return false;
            }
			/*
            $.ajax({
                type: "POST",
                url: "{!! route('admin.demadadvice.getbranchdaybookamount') !!}",
                dataType: 'JSON',
                data: {'branch_id':branch_id,'date':date}, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(cashType == 0){
                        var amount = parseFloat(response.microDayBookAmount);
                        $('#cash_in_hand_balance').val(amount.toFixed(2));
                    }else if(cashType == 1){
                        var amount = parseFloat(response.loanDayBookAmount);
                        $('#cash_in_hand_balance').val(amount.toFixed(2));;
                    }
                }
            });
			*/
			$.ajax({
				type: "POST", 
				url: "{!! route('admin.branchBankBalanceAmount') !!}",
				dataType: 'JSON',
				data: {'branch_id':branch_id,'entrydate':date},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) { 
					if(cashType == 0){
                        var amount = parseFloat(response.balance);
                        $('#cash_in_hand_balance').val(amount.toFixed(2));
                    }else if(cashType == 1){
                        var amount = parseFloat(response.balance);
                        $('#cash_in_hand_balance').val(amount.toFixed(2));;
                    }
				}
			});
        }else if(amount_mode == '2'){
            var account = $('option:selected', "#bank_account_number").attr('data-account');
            const bank_id = $('option:selected', "#bank").val();
            $('#cheque_number').val('');
            $('.c-cheque').hide();
            $('.'+account+'-c-cheque').show();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_bank_balance') !!}",
                dataType: 'JSON',
                data: {'account_id':account,'entry_date':date,'bank_id':bank_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var amount = parseFloat(response);
                    $('#available_balance').val(amount.toFixed(2));
                }
            });
        }
    });
    $('#neft_charge').on('change',function(){
        var neftAmount = $(this).val();
        var amount = $('#amount').val();
        if(amount ==  ''){
            amount = 0;
        }else{
            amount = $('#amount').val();
        }
        if(neftAmount ==  ''){
            neftAmount = 0;
        }else{
            neftAmount = $(this).val();
        }
        $('#total_amount').val(parseInt(amount)+parseInt(neftAmount));
    });
    $('#assets_category,#assets_subcategory').on('change',function(){
        var assetsType = $('option:selected', '#assets_category').val();
        var assetsSubType = $('option:selected', '#assets_subcategory').val();
        $('#assestcategory').val(assetsType)
        $('#assest_sub_category').val(assetsSubType)
    });
    $('.pay-expenses').on('click',function(){
        $('.payment-option').show();
        var advancedamount = $('#ta_advance_amount').val();
        var items = document.getElementsByClassName('ta-expense-amount');
        var total = 0;
        for (var i = 0; i < items.length; i++){
            total = parseInt(total)+parseInt(items[i].value)
        }
        if(advancedamount == total && total != ''){
            $('.amount-mode-section').hide();
            $('.ssb-option').hide();
            $('.cash-option').hide();
            $('.bank-option').hide();
            $('#amount').hide();
            $('#amount').val('');
            $('.adjustment_level').val(0);
        }else if(advancedamount < total && total != ''){
            $('.amount-mode-section').show();
            $('.cash-option').show();
            $('.ssb-option').show();
            $('.bank-option').show();
            $('#amount').show();
            $('#amount').val(total)
            $('.adjustment_level').val(1);
        }else if(advancedamount > total && total != ''){
            $('.amount-mode-section').show();
            $('.cash-option').show();
            $('.ssb-option').hide();
            $('.bank-option').hide();
            $('#amount').show();
            $('#amount').val(total)
            $('.adjustment_level').val(2);
        }else{
            $('.amount-mode-section').hide();
            $('.cash-option').hide();
            $('.ssb-option').hide();
            $('.bank-option').hide();
            $('#amount').hide();
            $('#amount').val('')
            $('.adjustment_level').val('');
        }
    });
     $('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#demand_advice_report_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter_report').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#demand_advice_report_export').val(extension);
			$('form#filter_report').attr('action',"{!! route('admin.demandadvice.export') !!}");
			$('form#filter_report').submit();
		}
	});
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.demandadvice.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100);
					window.open(csv, '_blank');
                }
            }
        });
    }
    jQuery.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    $(document).on('change','.expense_category ',function(){
        var hId = $(this).attr("data-row-id");
        var head_id = $(this).val();
        if(head_id > 0){
            $.ajax({
                type: "POST",
                url: "{!! route('admin.getHeadLedgerData') !!}",
                dataType: 'JSON',
                data: {'hId':hId,'head_id':head_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#subcategory_value2').val(0);
                    $('#subcategory_value3').val(0);
                    $("#expense_subcategory"+hId).empty();
                    if(hId == 2){
                        $("#expense_subcategory3").empty();
                        $("#expense_subcategory4").empty();
                    }else if(hId == 3){
                        $("#expense_subcategory4").empty();
                    }
                    $("#expense_subcategory"+hId).append("<option value=''>Choose Sub Head</option>");
                    if(response.length > 0){
                        $("#subcategory_value"+hId).val(1)
                        for(var k=0; k<response.length; k++){
                            $("#expense_subcategory"+hId).append("<option data-val="+response[k].sub_head+" value="+response[k].head_id+">"+response[k].sub_head+"</option>");
                        }
                        $(".expense_subcategory"+hId).css("display","block");
                    }
                }
            });
        }
    });
    $(document).on('change','.edit_expense_category ',function(){
        var hId = $(this).attr("data-row-id");
        var head_id = $(this).val();
        if(head_id > 0){
            $.ajax({
                type: "POST",
                url: "{!! route('admin.getHeadLedgerData') !!}",
                dataType: 'JSON',
                data: {'hId':hId,'head_id':head_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#edit_subcategory_value2').val(0);
                    $('#edit_subcategory_value3').val(0);
                    $("#edit_expense_subcategory"+hId).empty();
                    $('#edit_expense_subcategory2').attr("disabled", false);
                    $('#edit_expense_subcategory3').attr("disabled", false);
                    if(hId == 2){
                        $("#edit_expense_subcategory3").empty();
                        $("#edit_expense_subcategory4").empty();
                    }else if(hId == 3){
                        $("#edit_expense_subcategory4").empty();
                    }
                    $("#edit_expense_subcategory"+hId).append("<option value=''>Choose Sub Head</option>");
                    if(response.length > 0){
                        $("#edit_subcategory_value"+hId).val(1)
                        for(var k=0; k<response.length; k++){
                            $("#edit_expense_subcategory"+hId).append("<option data-val="+response[k].sub_head+" value="+response[k].head_id+">"+response[k].sub_head+"</option>");
                        }
                        $(".edit_expense_subcategory"+hId).css("display","block");
                    }
                }
            });
        }
    });
    $('.print-report').click(function() {
        window.print();
    });
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});
function searchForm()
{
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        demandAdviceTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('.date-from').val('');
    $('.date-to').val('');
    $('#filter_branch').val("");
    $('#advice_type').val("");
    demandAdviceTable.draw();
}
function searchtaAdvancedReport()
{
    if($('#filter_ta_advanced_report').valid())
    {
        $('#is_search').val("yes");
        taAdvancedReportTable.draw();
    }
}
function resettaAdvancedReport()
{
    var form = $("#filter_ta_advanced_report"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('.date-from').val('');
    $('.date-to').val('');
    $('#ta_employee_code').val('');
    $('#ta_advanced_employee_name').val('');
    $('#is_search').val("no");
    taAdvancedReportTable.draw();
}
function searchReportForm()
{
    if($('#filter_report').valid())
    {
        $('#is_search').val("yes");
        demandAdviceReportTable.draw();
    }
}
function resetReportForm()
{
    var form = $("#filter_report"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#date_from').val('');
    $('#date_to').val('');
    $('#filter_branch').val('');
    $('#advice_type').val('');
    $('#status').val("");
    $('#is_search').val("no");
    demandAdviceReportTable.draw();
}
function searchMaturityForm()
{
    if($('#filter_maturity').valid())
    {
        $('#is_search').val("yes");
        demandAdviceMaturityTable.draw();
    }
}
function resetMaturityForm()
{
    var form = $("#filter_maturity"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#date').val('');
    $('#to-date').val('');
    $('#company_id').val('');
    $('#company_id').trigger("change");
    $('#advice_type').val('');
    $('#status').val("");
    $('#account_number').val("");
    $('#is_search').val("no");
    demandAdviceMaturityTable.draw();
}
function searchApplicationForm()
{
    if($('#application_filter_report').valid())
    {
        $('#is_search').val("yes");
        // $('#appdaTatable').removeClass('appdaTatable');
        demandAdviceApplicationTable.draw();
    }
}
function resetApplicationForm()
{
    var form = $("#application_filter_report"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('.date-from').val('');
    $('.date-to').val('');
    $('#filter_branch').val('');
    $('#advice_type').val('');
    $('#expense_type').val('');
    $('.advice-type').hide();
    $('#is_search').val("no");
    $('#company_id').val('');
    $('#company_id').trigger("change");
    $(".odd").remove();
    $(".even").remove();
    // $('#appdaTatable').addClass('appdaTatable');
    demandAdviceApplicationTable.draw();
}
function printDiv(elem) {
   $("."+elem).print({
        //Use Global styles
        globalStyles : true,
        //Add link with attrbute media=print
        mediaPrint : true,
        //Custom stylesheet
        stylesheet : "{{url('/')}}/asset/print.css",
        //Print in a hidden iframe
        iframe : false,
        //Don't print this
        noPrintSelector : ".avoid-this",
        //Add this at top
      //  prepend : "Hello World!!!<br/>",
        //Add this on bottom
       // append : "<span><br/>Buh Bye!</span>",
       header: null,               // prefix to html
      footer: null,
        //Log to console when printing is done via a deffered callback
        deferred: $.Deferred().done(function() {    })
    });
}
$(document).on('change', '#payment_mode', function(e){
    var payment_mode = $("#payment_mode").val(); 
	var maturity_prematurity_type = $('option:selected', '#maturity_prematurity_type').val();
    if(payment_mode == "SSB"){
		$('#maturity_bank_account_number').prop('required',false);
		$('#prematurity_bank_account_number').prop('required',false);
		$('#maturity_ifsc_code').prop('required',false);
		$('#prematurity_ifsc_code').prop('required',false);
		$('#maturity_ssb_account').prop('required',true);		
		$('#prematurity_ssb_account').prop('required',true);
		$('.required__ifsc_code').hide();
		$('.required__bank_account_number').hide();
		$('.required__ssb_account').show();		
            $(".bank_account_number_div").css("display","none");
            if(maturity_prematurity_type=="0"){
				var account_number = $("#maturity_account_number").val();
			}else if(maturity_prematurity_type=='1'){
				var account_number = $("#prematurity_account_number").val();
			}else{
				swal("Warning!", "Please select Maturity / Prematurity type", "warning");
				return false;
			}
            $.ajax({
                type: "POST",  
                url: "{!! route('admin.getSSBAccountNumber') !!}",
                dataType: 'JSON',
                data: {'account_number':account_number},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response["status"] == "1"){
                        $("#ssb_account_number_div").css("display","block");
                        $("#ssb_account_number").val(response["account_number"]);
                    } else {
                        swal("Warning!", "This account does not have ssb account", "warning");
                        $("#ssb_account_number,#payment_mode").val("");
                    }
                }
            });  
		$("#customer_bank_name,#customer_account_number,#customer_isfc,#company_bank_name,#company_account_number").val("");	
    }
    if(payment_mode == "BANK"){
		$('#maturity_bank_account_number').prop('required',true);
		$('#prematurity_bank_account_number').prop('required',true);
		$('#maturity_ifsc_code').prop('required',true);
		$('#prematurity_ifsc_code').prop('required',true);
		$('#maturity_ssb_account').prop('required',false);
		$('#prematurity_ssb_account').prop('required',false);
		$('.required__ifsc_code').show();
		$('.required__bank_account_number').show();
		$('.required__ssb_account').hide();
        $("#ssb_account_number_div").css("display","none");
        $(".bank_account_number_div").css("display","block");
    }
    if(payment_mode == "Cash"){
        $("#ssb_account_number_div").css("display","none");
        $(".bank_account_number_div").css("display","none");
		$("#customer_bank_name,#customer_account_number,#customer_isfc,#company_bank_name,#company_account_number").val("");
    } 
});
$(document).on('change', '#company_bank_name', function(e){	
	var ac_no = $('option:selected',this).data("account");
	$("#company_account_number").val(ac_no);
});
$(document).on('change', '#paymentType', function(e){ 
	var paymentType = $("#paymentType").val();
	if( (paymentType == "2") || (paymentType == "4") ){
		$("#bank_account_number_div1").css("display","block");
	} else {
		$("#bank_account_number_div1").css("display","none");
	}
})	
</script>