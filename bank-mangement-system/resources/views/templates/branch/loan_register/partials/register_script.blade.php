<script type="text/javascript">
    $(document).ready(function() {
        var today = new Date();
        $('.date_of_birth').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true,
            endDate: "today",
            maxDate: today
        });
        $('#register-plan').validate({ // initialize the plugin
            rules: {
                'loan': 'required',
                'amount': {
                    required: true,
                    number: true,
                    checkAmount: true
                },
                'days': 'required',
                'months': 'required',
                'purpose': 'required',
                'group_activity': 'required',
                'group_leader_member_id': 'required',
                'number_of_member': 'required',
                'salary': {
                    required: true,
                    number: true
                },
                'acc_member_id': {
                    required: true,
                    number: true
                },
                'applicant_id': {
                    required: true,
                    number: true
                },
                'emp_code': {
                    required: true,
                },
                'applicant_address_permanent': 'required',
                'applicant_address_temporary': 'required',
                'applicant_monthly_income': {
                    required: true,
                    number: true
                },
                'applicant_year_from': {
                    required: true,
                    number: true
                },
                'applicant_bank_name': 'required',
                'applicant_bank_account_number': {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'applicant_ifsc_code': {
                    required: true,
                    checkIfsc: true,
                },
                'applicant_cheque_number_1': {
                    required: true,
                    number: true,
                    notEqual: "#applicant_cheque_number_2",
                    notEqual1: "#co-applicant_cheque_number-1",
                    notEqual2: "#co-applicant_cheque_number_2",
                    notEqual3: "#guarantor_cheque_number_1",
                    notEqual4: "#guarantor_cheque_number_2"
                },
                'applicant_cheque_number_2': {
                    required: true,
                    number: true,
                    notEqual: "#applicant_cheque_number_1",
                    notEqual1: "#co-applicant_cheque_number-1",
                    notEqual2: "#co-applicant_cheque_number_2",
                    notEqual3: "#guarantor_cheque_number_1",
                    notEqual4: "#guarantor_cheque_number_2"
                },
                'applicant_id_proof': 'required',
                'applicant_id_number': {
                    required: true,
                    checkIdNumber: '#applicant_id_proof'
                },
                'applicant_id_file': {
                    extension: "jpf|jpg|pdf|jpeg"
                },
                'applicant_address_id_proof': 'required',
                'applicant_address_id_number': {
                    required: true,
                    checkIdNumber: '#applicant_address_id_proof'
                },
                'applicant_address_id_file': {
                    extension: "jpf|jpg|pdf|jpeg"
                },
                'applicant_income': 'required',
                'applicant_income_file': {
                    extension: "jpf|jpg|pdf|jpeg"
                },
                'applicant_security': 'required',
                'co-applicant_address_permanent': 'required',
                'co-applicant_address_temporary': 'required',
                'co-applicant_bank_account_number': {
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'co-applicant_ifsc_code': {
                    checkIfsc: true
                },
                'co-applicant_monthly_income': {
                    required: true,
                    number: true
                },
                'co-applicant_year_from': {
                    required: true,
                    number: true
                },
                'co-applicant_id_file': {
                    extension: "jpf|jpg|pdf|jpeg",
                    required: true
                },
                // 'guarantor_member_id': {
                //     required: true,
                //     number: true
                // },
                // 'guarantor_auto_member_id': {
                //     required: true,
                //     // number: true
                // },
                'guarantor_name': 'required',
                'guarantor_father_name': 'required',
                'guarantor_dob': 'required',
                'guarantor_marital_status': 'required',
                'local_address': 'required',
                'guarantor_ownership': 'required',
                'guarantor_temporary_address': 'required',
                'guarantor_mobile_number': {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 12
                },
                'guarantor_educational_qualification': 'required',
                'guarantor_dependents_number': {
                    required: true,
                    number: true
                },
                'guarantor_bank_account_number': {
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'guarantor_ifsc_code': {
                    checkIfsc: true
                },
                'guarantor_monthly_income': {
                    required: true,
                    number: true
                },
                'guarantor_year_from': {
                    required: true,
                    number: true
                },
                'guarantor_id_file': {
                    extension: "jpf|jpg|pdf|jpeg",
                    required: true
                },
                'guarantor_more_upload_file': {
                    extension: "jpf|jpg|pdf|jpeg"
                },
                'guarantor_security': 'required',
                'co-applicant_auto_member_id': 'required',
                'emi_mode_option': 'required',
                'acc_auto_member_id': 'required',
                'group_associate_id': 'required',
                'guarantor_occupation_id': 'required',
            }
        });
        $(document).on('change', '#emp_code', function() {
            var code = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('branch.employeeDataGet') !!}",
                dataType: 'JSON',
                data: {
                    'code': code,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('.show_emp_detail').hide();
                    $('#employee_name').val('');
                    $('#applicant_occupation_name').val('');
                    $('#applicant_designation').val('');
                    $('#applicant_monthly_income').val('').attr('readonly',true);;
                    $('#applicant_year_from').val('').attr('readonly',true);
                    $('#applicant_organization').val('').attr('readonly',true);;
                    if(response.data?.employee_code)
                    {
                        if (response.msg == 1 && !response?.data?.loan?.emp_code) {
                        const  joiningDate =  response?.data?.employee_date;
                        const splitDate = joiningDate?.split('-');
                        $('.show_emp_detail').show();
                        $('#employee_name').val(response.data.employee_name);
                        $('#applicant_occupation_name').val('Private Employee');
                        $('#applicant_designation').val(response.designation);
                        $('#applicant_monthly_income').val(response.data.salary).attr('readonly',true);;
                        $('#applicant_year_from').val(splitDate[0]).attr('readonly',true);
                        $('#applicant_organization').val(response?.data?.company?.name).attr('readonly',true);;
                        $('#error_msg_emp').hide();
                        } else if(response.msg == 1 && response?.data?.loan?.emp_code){
                            $('#emp_code').val('');
                            $('#employee_name').val('');
                            $('#applicant_occupation_name').val('');
                            $('#applicant_designation').val('');
                            $('#applicant_monthly_income').val('').attr('readonly',true);;
                            $('#applicant_year_from').val('').attr('readonly',true);
                            $('#applicant_organization').val('').attr('readonly',true);;
                            swal("Warning!", "Employee  has already taken a loan!", "warning");
                        }
                    }
                    else {
                        $('#error_msg_emp').show();
                        $('#emp_code').val('');
                        $('#error_msg_emp').html('<div class="alert alert-danger alert-block">  <strong>Employee not found.!</strong> </div>');
                    }
                }
            })
        });
        $('#customer_id').on('change', function() {
            const customerId = $(this).val();
            const url = "{!! route('get_customer_details') !!}"
            const companyId = $('#loan option:selected').attr('data-company_id');
            const loantype = $('#loan option:selected').val();
            const applicationDate = $('.application_date').val();
            const attVal = $(this).attr('data-val');
            const category = $("#loan option:selected").attr('data-category');
            const loanCompany =$('#loan option:selected').data('company_id');
            if(loantype.length == 0) {
                $(this).val('');
                swal('Warning','Please Select Loan Plan First','warning');
            } 
            else{
                $.post(url, {customerId: customerId,companyId: companyId,loantype: category,applicationDate: applicationDate,}, function(response) {
                    
                if (response.msg_type == "success") {
                    if(category == 2){
                        $.ajax({
                            type: "POST",
                            url: "{!! route('branch.getEmployeeData') !!}",
                            dataType: 'JSON',
                            data: {
                                'customerId': customerId,
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                
                                if (response.code == '1') {
                                    
                                    
                                    if(response.data.company_id != loanCompany ){
                                        $('.applicant-member-detail').html('');
                                        $('#customer_id').val('');
                                        
                                        swal('Warning', 'Customer is not from selected plans Company', 'warning');
                                        $('#customer_detailssss').val('');
                                        // $('.applicant-member-detail').hide();
                                        return false;
                                    }
                                    // $('#emp_code').val(response.dataDesignation.designation_name);
                                    $('#emp_code').val(response.data.employee_code);
                                    $('#employee_name').val(response.data.employee_name);
                                    const org = ['Samraddh Bestwin Micro Finance Association', 'ROYAL RAO BALAJI MICRO FINANCE FOUNDATION', 'UJALA MICRO FINANCE'];
                                    const companyId = response.data.company_id;

                                    if (companyId >= 1 && companyId <= org.length) {
                                
                                    const selectedOrganization = org[companyId - 1];

                                    $('#applicant_organization').val(selectedOrganization);
                                    }

                                    $('.applicant_designation_name').val(response.dataDesignation.designation_name);
                                    // console.log('sdfsdfsdfsdfsdf');
                                    $('#applicant_monthly_income').val(response.data.salary);
                                } else {
                                    swal('Warning', 'Customer is not an Employee', 'warning');

                                    // $('#register-plan').hide();

                                    
                                }
                            },
                            error: function(xhr, status, error) {
                                
                                // Handle the error appropriately, e.g., show an alert or log the error.
                            }
                        });

                    }
                    if(category != 3)
                    {
                        $('.submit-loan-form').show();
                    }
                    if (response?.member?.member_id_proofs) {
                        $("#applicant_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.first_id_no + '');
                        $("#applicant_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.second_id_no + '');
                    }
                    else{
                        $("#applicant_id_proof option").removeAttr("data-proof-val");
                        $("#applicant_address_id_proof option").removeAttr("data-proof-val");
                    }
                    $('#age').val(response.age);
                    $('#newUser').val(response?.newUser);
                    $('.' + attVal + '-member-detail').show();
                    $('.' + attVal + '-member-detail').html(response.view);
                    $('#company_id').val(companyId);
                    $("#" + attVal + "_id_proof").val('');
                    $("#" + attVal + "_address_id_proof").val('');
                    $("#" + attVal + "_id_number").val('');
                    $("#" + attVal + "_address_id_number").val('');
                    $('#' + attVal + '_occupation_name').val(response.member?.occupation?.name);
                    $('#' + attVal + '_designation').val(response.member?.getCarderNameCustom?.name);
                    $('#customerId').val(customerId);
                    if (category != 4) {
                        
                        $('#gstStatus').val(response.gstData.IntraState);
                        $('#gstFileAmount').val(response.gstFileChargeData.gst_percentage);
                        $('#gstFileStatus').val(response.gstFileChargeData.IntraState);
                        $('#gstPercentage').val(response.gstData.gst_percentage);
                        $('#gstFilePercentage').val(response.gstFileChargeData.gst_percentage);
                        $('#ecsStatus').val(response.gstEcsChargeData?.IntraState);
                        
                        $('#ecsFileamount').val(response.gstEcsChargeData?.gst_percentage);
                        $('#gstecsPercentage').val(response.gstEcsChargeData?.gst_percentage);
                    }    
                    if (category == 4) {
                        if(response.loanExist > 0){
                            swal("Warning!", "Customer has already taken a loan  !", "warning");
                            $('#' + attVal + '_id').val('');
                            $('#' + attVal + '_member_id').val('');
                            $('#' + attVal + '_occupation_name').val('');
                            $('#' + attVal + '_occupation').val('');
                            $('.' + attVal + '-occupation-name').val('');
                            $('.' + attVal + '-occupation').val('');
                            $('#' + attVal + '_designation').val('');
                            $("#" + attVal + "_id_proof").val('');
                            $("#" + attVal + "_address_id_proof").val('');
                            $("#" + attVal + "_id_number").val('');
                            $("#" + attVal + "_address_id_number").val('');
                            $('#total_deposit').val('');
                            $('#customer_id').val('');
                            $('.applicant-member-detail').hide();
                            $('.loan-against-investment-plan').hide();
                        }else{
                            $('#gstFileAmount').val(0);
                            $('#gstFileStatus').val(false);
                            $('#gstPercentage').val(0);
                            $('#gstFilePercentage').val(0);
                            $('.loan-against-investment-plan').show();
                            $('.investment-plan-input-number').html('');
                            
                            if (response?.member?.customer_investment?.length != 0) {
                                var count = response.member.customer_investment.length;
                                //$.cookie('planTbaleCounter', '');
                                var isRecordExist = false;
                                var i = 0;
                                var invesmentLength = response.member.customer_investment.length;
                                $.each(response.member.customer_investment, function(key, value) {
                                    console.log(value,"value");
                                    var months = value.tenure * 12;
                                    let cdate = new Date(value.created_at);
                                    let formattedDate = value.maturity_date;
                                    let parts = formattedDate.split('-');
                                    let maturityDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                                    let newDate = cdate.getDate() + "/" + (cdate.getMonth() + 1) + "/" + cdate.getFullYear()
                                    var dt = new Date(value.created_at);
                                    dt.setMonth(months);
                                    let current_datetime = new Date(dt)
                                    let duenewDate = current_datetime.getDate() + "/" + (current_datetime.getMonth() + 1) + "/" + current_datetime.getFullYear()
                                    if (value.plan_id != 1) {
                                        $.ajax({
                                            type: "POST",
                                            url: "{!! route('loan.getplanname') !!}",
                                            dataType: 'JSON',
                                            async: false,
                                            data: {
                                                'planid': value.plan_id
                                            },
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(response) {
                                                var now = new Date();
                                                var sixMonthsFromNow = new Date(now.setMonth(now.getMonth() - 6));
                                                //alert(sixMonthsFromNow.toISOString());
                                                //alert(value.created_at);
                                                if (sixMonthsFromNow.toISOString() >= value.created_at) {
                                                    isRecordExist = true;
                                                    $.cookie('planTbaleCounter', key);
                                                    var list_fieldHTML = '<tr  row="'+key+'" class="table-row"><td class="plan-name">' + response.planName + '<input type="hidden" name="investmentplanloanid[' + key + ']" value="' + value.id + '" class="form-control investment_id"><input type="hidden" name="plan_id[' + key + ']" value="' + value.plan_id + '" class="form-control plan_id"></td><td class="account-id">' + value.account_number + '</td><td class="open-date">' + newDate + '</td><td class="due-date">' + maturityDate + '</td><td class="deposite-amount">' + value.current_balance + '<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-' + key + ' form-control" value="' + value.current_balance + '"></td><td class="plan-months">' + Math.round(months) + '</td><td class="loan-amount-input"><input data-input="' + key + '" data-loanExist="hii" type="text" name="ipl_amount[' + key + ']" class="ipl_amount ipl_amount-' + key + ' form-control" style="width: 104px"></td></tr>';
                                                    $('.investment-plan-input-number').append(list_fieldHTML);
                                                }
                                            }
                                        });
                                    }
                                    i++;
                                    if (i == invesmentLength && isRecordExist == false) {
                                        swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                        $('.applicant-member-detail').html('');
                                        $('.' + attVal + '-member-detail').html('');
                                        $('#' + attVal + '_member_id').val('');
                                        $('#' + attVal + '_id').val('');
                                    }
                                });
                            }else if(response?.member?.customer_investment?.length == 0){
                                swal("Warning!", "Investment Plan Not Present!", "warning");
                                $('.applicant-member-detail').html('');
                                $('.' + attVal + '-member-detail').html('');
                                $('#' + attVal + '_member_id').val('');
                                $('#' + attVal + '_id').val('');
                            } else {
                                swal("Warning!", "Setting Not Updated For this Plan!", "warning");
                                $('.applicant-member-detail').html('');
                                $('.' + attVal + '-member-detail').html('');
                                $('#' + attVal + '_member_id').val('');
                                $('#' + attVal + '_id').val('');
                            }
                        }
                        }
                    }
                    else if(response.msg_type == 'already')
                    {
                        
                        $('#customer_id').val('');
                            // swal("Warning!", "Customer has already taken a loan!", "warning");
                            if(response.status == '0'){
                            swal("Warning!", "Customer has already taken a loan & loan status is Pending currently!", "warning");
                            }
                            else if(response.status == '1'){
                                swal("Warning!", "Customer has already taken a loan & loan status is Approved for AC :" + response.ac + "!", "warning");
                            }
                            else if(response.status == '2'){
                                swal("Warning!", "Customer has already taken a loan & loan status is Rejected for AC :" + response.ac + "!", "warning");
                            }
                            else if(response.status == '4'){
                                swal("Warning!", "Customer has already taken a loan & loan status is Due  for AC :" + response.ac + "!", "warning");
                            }
                            else if(response.status == '6'){
                                swal("Warning!", "Customer has already taken a loan & loan status is Hold !", "warning");
                            } else {
                                swal("Warning!", "Customer has already taken a loan & loan status is Rejected & Hold  for AC :" + response.ac + "!", "warning");
                            }
                        $('#' + attVal + '_id').val('');
                        $('#' + attVal + '_member_id').val('');
                        $('#' + attVal + '_occupation_name').val('');
                        $('#' + attVal + '_occupation').val('');
                        $('.' + attVal + '-occupation-name').val('');
                        $('.' + attVal + '-occupation').val('');
                        $('#' + attVal + '_designation').val('');
                        $("#" + attVal + "_id_proof").val('');
                        $("#" + attVal + "_address_id_proof").val('');
                        $("#" + attVal + "_id_number").val('');
                        $("#" + attVal + "_address_id_number").val('');
                        $('#total_deposit').val('');
                    }
                else if(response.msg_type == "dem_issue"){
                    swal("Warning!", response.msg, "warning");
                                $('.applicant-member-detail').html('');
                                $('.' + attVal + '-member-detail').html('');
                                $('#' + attVal + '_member_id').val('');
                                $('#' + attVal + '_id').val('');
                } else {
                    $('#customer_id').val('');
                    $('.' + attVal + '-member-detail').html('<div class="alert alert-danger alert-block">  <strong>'+response.msg_type+'</strong> </div>');
                    $('#' + attVal + '_id').val('');
                    $('#' + attVal + '_member_id').val('');
                    $('#' + attVal + '_occupation_name').val('');
                    $('#' + attVal + '_occupation').val('');
                    $('.' + attVal + '-occupation-name').val('');
                    $('.' + attVal + '-occupation').val('');
                    $('#' + attVal + '_designation').val('');
                    $("#" + attVal + "_id_proof").val('');
                    $("#" + attVal + "_address_id_proof").val('');
                    $("#" + attVal + "_id_number").val('');
                    $("#" + attVal + "_address_id_number").val('');
                    $('#total_deposit').val('');
                    if (attVal == 'guarantor') {
                        $('#guarantor_occupation_id').html('');
                        $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                        $('#guarantor_occupation_id').prop('disabled', false);
                        //$('.guarantor-name-section').show();
                        $('.guarantor-member-detail-box').show();
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
                $('#member_id').val(response?.member?.member_company?.id);
            })
            }
        })
        calculateLoanAgainstInvestmentAmount = (inputElement,tenure,month,plan,investmentPlanCurrentBalance,loanAmount) => {
            const url = "{!! route('branch.check_loan_against_investment_percentage') !!}";
            $.post(url,{
                'tenure':tenure,
                'month' : month,
                'plan' : plan
            },function(response){
                const calculateLoanPercentage =  (investmentPlanCurrentBalance) * response/100;    
                if(loanAmount > calculateLoanPercentage ) 
                {
                    $(inputElement).val('');
                    swal('Sorry','Sorry You cannot Apply more than '+calculateLoanPercentage + ' Amount','warning');
                    return false;
                }
                else{
                        const node = $(inputElement).attr('id');
                        var loantype = $("#loan option:selected").val();
                        const category = $("#loan option:selected").attr('data-category');
                        const tenure = $("#emi_mode_option option:selected").attr('data-tenure');
                        const tenureId = $("#loan option:selected").attr('data-tenure');
                        const emiOption = $('#loan option:selected').attr('data-emioption');
                        const ROI = $("#loan option:selected").attr('data-rointerest');
                        const aDate = $('.application_date').val();
                        var moPayment = $("#emi_mode_option option:selected").attr('data-emioption');
                        var moPaymentValue = $("#emi_mode_option option:selected").attr('data-tenure');;
                        ((node == 'emi_mode_option') && $('#amount').val(''));
                        const  dataval = $(inputElement).attr('data-input');
                        const  dAmount = $('.hidden_deposite_amount-'+dataval+'').val();
                        var  sum = 0;
                        $(".ipl_amount").each(function(){
                            sum += +$(this).val();
                        });
                        $('#amount').val(sum);
                        $('.c-amount').val(sum);
                        $('#loan_amount').val(sum);
                        $('#amount').prop('readonly', true);
                        getFileChargeajax(category, sum, aDate, tenureId, 'tenure', node, emiOption, ROI, tenure,'',loantype);
                    }
                })
        }
        $(document).on('change', '.ipl_amount', function() {
            var trRow = $(this).closest('tr');
            const inputValue = $(this).val();
            const inputElement = $(this);
            // Disable other rows
            $('tr').not(trRow).addClass('disabled');
            // Disable input fields within disabled rows
            if(inputValue.length > 0)
            {
                $('tr').not(trRow).find('input').prop('disabled', true);
                trRow.find('input').prop('disabled', false);
            }
            else{
                $('tr').not(trRow).find('input').prop('disabled', false);
                trRow.find('input').prop('disabled', true); 
            }
            const createdDate = $(this).closest('.table-row').find('.open-date').text();
            const tenure = $(this).closest('.table-row').find('.plan-months').text();
            const planId = $(this).closest('.table-row').find('.plan-name .plan_id').val();
            const investmentId = $(this).closest('.table-row').find('.plan-name .investment_id').val();

            const currentSystemDate = $('.application_date').val();
            const start = new Date(convertDateFormat(createdDate));
             const end = new Date(convertDateFormat(currentSystemDate));
            // Calculate the difference in months
            let months = (end.getFullYear() - start.getFullYear()) * 12;
            months -= start.getMonth();
            months += end.getMonth();
            // Ensure the result is non-negative
            finalDiffMonth = months <= 0 ? 0 : months;
            const date = moment(createdDate, 'DD/MM/YYYY').toDate();
            const formattedDate = moment(date).format('DD/MM/YYYY'); 
            const convertToDateFormate = moment(currentSystemDate, 'DD/MM/YYYY').toDate();
            const currentFormatedDate = moment(convertToDateFormate).format('DD/MM/YYYY');
            const diffTwoDates = moment.duration(moment(currentFormatedDate, 'DD/MM/YYYY').diff(moment(formattedDate, 'DD/MM/YYYY'))).asMonths();
            // const finalDiffMonth = parseInt(diffTwoDates);
            const investmentPlanCurrentBalance = $(this).closest('.table-row').find('.deposite-amount').text(); 

            $.ajax({
                type: "POST",
                url: "{!! route('branch.loan.investment.data.exist') !!}",
                dataType: 'JSON',
                data: {
                    'investmentId': investmentId,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response == false){
                        const responseStatus = (finalDiffMonth > 0 ) && calculateLoanAgainstInvestmentAmount(inputElement,tenure,finalDiffMonth,planId,investmentPlanCurrentBalance,inputValue);
                    }else{
                        $('.ipl_amount').val('');
                        $('tr').not(trRow).find('input').prop('disabled', false);
                        trRow.find('input').prop('disabled', true);
                        swal("Warning!", "Loan is already running on this investment", "warning");
                    }
                }
            });
            // const responseStatus = (finalDiffMonth > 0 ) && calculateLoanAgainstInvestmentAmount(inputElement,tenure,finalDiffMonth,planId,investmentPlanCurrentBalance,inputValue);
        });
        $('.investment-plan-input-number').on('change','.ipl_amount',function(){
    });
    function convertDateFormat(dateString) {
      const [day, month, year] = dateString.split('/');
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }
        $(document).on('change', '#purpose', function() {
            var value = $(this).val();
            $('#loan_purpose').val(value);
        });
        $(document).on('change', '#loan', function() {
            const loanDetail = $('option:selected', this).val();
            const loanType = $('option:selected', this).attr('data-loanType');
            const aDate = $('.application_date').val();
            const emiOptionValue = $('option:selected', this).attr('data-emioption');
            const tenure = $('option:selected', this).attr('data-tenure');
            const category = $("#loan option:selected").attr('data-category');

            const ecsType = $("#loan option:selected").attr('data-ecstype');
            ecsType == 1 ? $('.esc').show() : $('.esc').hide();


            if (ecsType == 0) {
                $('#bank').val(0);
                $('#ssb').val(0);
            }else{
                $('#bank').val(1);
                $('#ssb').val(2);
            }
            const company = $("#loan option:selected").attr('data-company_id');
            if(company == 2){
                if (emiOptionValue == 1) {
                    $('#bank').prop('disabled', false).attr('checked', true);
                    $('#ssb').prop('disabled', true).attr('checked', false);
                }
                else if (emiOptionValue == 2){
                $('#bank').prop('disabled', true).attr('checked', false);
                $('#ssb').prop('disabled', false).attr('checked', true);
                }
                else if(emiOptionValue == 3){
                $('#ssb').attr('checked', true);
                $('#bank').attr('checked', false).prop('disabled', false);
                $('#ssb').prop('disabled', false);
                }
            }
            else if(emiOptionValue == 3){
                $('#ssb').attr('checked', true);
                $('#bank').attr('checked', false).prop('disabled', false);
                $('#ssb').prop('disabled', false);
            }
            else if (emiOptionValue == 2){
                $('#bank').prop('disabled', true).attr('checked', false);
                $('#ssb').prop('disabled', false).attr('checked', true);
            }else if(emiOptionValue == 1){
                $('#bank').prop('disabled', true).attr('checked', false);
                $('#ssb').prop('disabled', false).attr('checked', true);
            }


            if(category != 3)
            {
                $('.submit-loan-form').hide();
                $('#customer_id').prop('readonly',false);
            }
            else{
                $('#customer_id').prop('readonly',true);
                $('.submit-loan-form').show();
            }
            $('#customer_id').val('');
            let emiOption = '';
            if (emiOptionValue == 1) {
                emiOption = 'Months';
            } else if (emiOptionValue == 2) {
                emiOption = 'Weeks';
            } else if (emiOptionValue == 3) {
                emiOption = 'Days';
            }
            $('#file_charge').val('');
            $('#loan_emi').val('');
            $('.emi_option').val('');
            $('.emi_period').val('');
            $('#loan_amount').val('');
            $('#loan_purpose').val('');
            $("#register-plan")[0].reset();
            var loan = $('option:selected', this).attr('data-val');
            $('.applicant-member-detail').html('');
            $('.co-applicant-member-detail').html('');
            $('.guarantor-member-detail').html('');
            $('#loan_category').val(category);
            if (category == '1') {
                // tenureAppend(aDate, loanDetail, 'tenure');
                $('.applciant-deatils-box').show();
                $('.coapplciant-deatils-box').show();
                $('.guarantor-deatils-box').show();
                $('.group-information').hide();
                $('.staff-loan-section').show();
                //$('.personal-loan-section').show();
                $('.other-loan-section').hide();
                $('.bank-details-section').show();
                $('.emi-mode-section').hide();
                $('.group-emi-mode-section').hide();
                $('.loan-against-investment-plan').hide();
                $('.group-loan-member-table').hide();
                $('.salary-section').hide();
                $('.group-emi-mode').hide();
                $('.staff-emi-mode').hide();
                $('.investmentloan-emi-mode').hide();
                $('.personal-emi-mode').show();
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
                $('.cheque-box').show();
                $('.applicant-box').show();
                $('#amount').attr('readonly', false);
                $('.show_emp_detail').hide();
            } else if (category == '3') {
                $('.group-information').show();
                $('.applciant-deatils-box').show();
                $('.coapplciant-deatils-box').show();
                $('.guarantor-deatils-box').show();
                $('.staff-loan-section').hide();
                $('.personal-loan-section').hide();
                $('.other-loan-section').hide();
                $('.bank-details-section').hide();
                $('.emi-mode-section').hide();
                $('.group-emi-mode-section').hide();
                $('.loan-against-investment-plan').hide();
                $('.group-loan-member-table').hide();
                $('.salary-section').hide();
                $('.staff-emi-mode').hide();
                $('.investmentloan-emi-mode').hide();
                $('.personal-emi-mode').hide();
                $('.group-emi-mode').show();
                $('.cheque-box').show();
                $('.applicant-box').hide();
                $('.emi_mode_option').val(tenure + ' ' + emiOption);
                $('.loan-emi-amount').html('');
                $('#amount').attr('readonly', false);
                $('.show_emp_detail').hide();
                console.log(tenure + ' ' + emiOption, category);
            } else if (category == '2') {
                $('.show_emp_detail').show();
                $('.group-information').hide();
                $('.applciant-deatils-box').show();
                $('.coapplciant-deatils-box').hide();
                $('.guarantor-deatils-box').show();
                $('.staff-loan-section').show();
                $('.personal-loan-section').hide();
                $('.other-loan-section').hide();
                $('.bank-details-section').show();
                $('.emi-mode-section').hide();
                $('.group-emi-mode-section').hide();
                $('.loan-against-investment-plan').hide();
                $('.group-loan-member-table').hide();
                $('.salary-section').show();
                $('.personal-emi-mode').hide();
                $('.group-emi-mode').hide();
                $('.investmentloan-emi-mode').hide();
                $('.staff-emi-mode').show();
                $('.cheque-box').show();
                $('.applicant-box').show();
                $('.emp_staff').show();
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
                //$('#amount').attr('readonly',true);
            } else if (category == '4') {
                $('.show_emp_detail').hide();
                $('.applciant-deatils-box').show();
                $('.coapplciant-deatils-box').hide();
                $('.guarantor-deatils-box').hide();
                $('.group-information').hide();
                $('.staff-loan-section').show();
                $('.personal-loan-section').hide();
                $('.other-loan-section').hide();
                $('.bank-details-section').show();
                $('.loan-against-investment-plan').hide();
                $('.emi-mode-section').hide();
                $('.group-emi-mode-section').hide();
                $('.group-loan-member-table').hide();
                $('.salary-section').hide();
                $('.personal-emi-mode').hide();
                $('.group-emi-mode').hide();
                $('.staff-emi-mode').hide();
                $('.investmentloan-emi-mode').show();
                $('.cheque-box').hide();
                $('.applicant-box').show();
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
                $('#amount').attr('readonly', true);
            } else {
                $('.applciant-deatils-box').hide();
                $('.coapplciant-deatils-box').hide();
                $('.guarantor-deatils-box').hide();
                $('.group-information').hide();
                $('.staff-loan-section').hide();
                $('.other-loan-section').hide();
                $('.bank-details-section').hide();
                $('.emi-mode-section').hide();
                $('.group-emi-mode-section').hide();
                $('.loan-against-investment-plan').hide();
                $('.group-loan-member-table').hide();
                $('.salary-section').hide();
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
            }
            $('#emi_mode_option').val(tenure + ' ' + emiOption);
            $('#loan_type').val(loanType);
            $('.loanId').val(loanDetail);
        });
        const showpay = (amount, month, rate, divideBy) => {
            console.log("sdd", amount, month, rate, divideBy);
            if ((amount == null || amount.length == 0) || (month == null || month.length == 0) || (rate == null || rate.length == 0)) {
                var emi = '';
                return emi;
            } else if (rate == 0 && amount != null && month != null) {
                var emi = amount / month ;
                return Math.ceil(emi);
            }else {
                var princ = amount;
                var term = month;
                var intr = rate / divideBy;
                var emi = princ * intr / (1 - (Math.pow(1 / (1 + intr), term)));
                return Math.round(emi);
            }
        }
        const calculateEmi = (emiOption, value, tenure, ROI, insuranceDetail, fileCharge, dInput,loantype,loanId,ecsC) => {
            if(loantype != 4)
            {
                const gstStatus = $("#gstStatus").val();
                const gstPercentage = $("#gstPercentage").val();
                const gstecsPercentage = $("#gstecsPercentage").val();
                const gstFileStatus = $("#gstFileStatus").val();
                const gstFilePercentage = $("#gstFilePercentage").val();
                const ecsFileStatus  = $('#ecsStatus').val();

                const chargeType = insuranceDetail.charge_type;
                let genearateFilegstAmount;
                let genearateEcsgstAmount= 0;
                
                calculateFileGst = () => {
                switch (gstFileStatus) {
                    case 'true':
                        genearateFilegstAmount = ((fileCharge * gstFilePercentage) / 100) / 2;
                        break;
                    case 'false':
                        genearateFilegstAmount = ((fileCharge * gstFilePercentage) / 100);
                }
                $('#gstFileAmount').val(Math.ceil(genearateFilegstAmount));
                $('#gstFileStatus').val((gstFileStatus));
                $('#ml_gst_status').val(1);
                $('.g-loan-hidden-gst-file-charge-' + dInput + '').val(Math.ceil(genearateFilegstAmount));
                $('.g-loan-hidden-gst-file-status-' + dInput + '').val((gstFileStatus));
            }
            calculateFileGst();
            var InsuranceAmount = () => {
                let genearategstAmount,insAmount;
                const age = $('#age').val();
                switch (value >= insuranceDetail.min_amount && value <= insuranceDetail.max_amount) {
                    case true:
                        if(age < 60 )
                        {   
                             insAmount = (chargeType == 1 ? insuranceDetail.charge : ((value * insuranceDetail.charge) / 100));
                            $('#insurance_charge').val(insAmount)
                            $('.g-loan-hidden-insurance-charge-' + dInput + '').val(insAmount);
                        }
                        else{
                             insAmount =0;
                            $('#insurance_charge').val(insAmount)
                            $('.g-loan-hidden-insurance-charge-' + dInput + '').val(insAmount);
                        }
                        calculateGst = () => {
                            switch (gstStatus) {
                                case 'true':
                                    genearategstAmount = ((insAmount * gstPercentage) / 100) / 2;
                                    break;
                                case 'false':
                                    genearategstAmount = ((insAmount * gstPercentage) / 100);
                            }
                            $('#gstAmount').val(Math.ceil(genearategstAmount));
                            $('#gstStatus').val((gstStatus));
                            $('.g-loan-hidden-gst-charge-' + dInput + '').val(Math.ceil(genearategstAmount));
                            $('.g-loan-hidden-gst-status-' + dInput + '').val((gstStatus));
                        }
                        calculateGst();
                        break;
                    case false:
                        $('#insurance_charge').val(0);
                        $('#gstAmount').val(0);
                        $('#gstStatus').val(0);
                        break;
                    default:
                        $('#insurance_charge').val('');
                        $('#gstAmount').val('');
                        $('#gstStatus').val('');
                }
            }
            InsuranceAmount();
            const 
                calculateEcsGst = () => {
                    switch (ecsFileStatus) {
                        case 'true':
                            genearateEcsgstAmount = ((ecsC * gstecsPercentage) / 100) / 2;
                            break;
                        case 'false':
                            genearateEcsgstAmount = ((ecsC * gstecsPercentage) / 100);
                    }
                    console.log('genearateEcsgstAmount',genearateEcsgstAmount)
                    $('#ecsFileamount').val(Math.ceil(genearateEcsgstAmount));
                    $('#ecsStatus').val((ecsFileStatus));
                    $('#ml_gst_status').val(1);
                    $('.g-loan-hidden-ecs-charge-' + dInput + '').val(Math.ceil(
                        genearateEcsgstAmount));
                    $('.g-loan-hidden-ecs-status-' + dInput + '').val((ecsFileStatus));
                    $('.g-loan-hidden-ecs-amount-' + dInput + '').val((ecsC));

                }
                calculateEcsGst();
            }
            moPaymentValue = tenure;
            let divided = '';
            let emioption = '';
            let genearategstAmount = '';
            let genearateFilegstAmount = '';
            switch (emiOption) {
                case '1':
                    divided = 1200;
                    emioption = 'months';
                    break;
                case '2':
                    divided = 5200;
                    emioption = 'weeks';
                    break;
                case '3':
                    divided = 36500;
                    emioption = 'days';
                    break;
                default:
            }
            var loanEmi = showpay(value, tenure, ROI, divided);
            $('#interest-rate').val(ROI);
            $('#loan_amount').val(value);
            $('#loan_emi').val(loanEmi);
            $('.emi_period').val(tenure);
            $('.emi_option').val(emiOption);
            if (emioption == 'weeks' && moPaymentValue == 26) {
                $('.loan-emi-amount').html('');
            } else if (emioption == 'weeks' && moPaymentValue == 52) {
                $('.loan-emi-amount').html('');
            } else if (loanEmi) {
                $('.loan-emi-amount').html('Loan EMI: ' + loanEmi);
            } else {
                $('.loan-emi-amount').html('');
            }
            if (loanEmi) {
                $('.loan-emi-amount').html('Loan EMI: ' + loanEmi);
            } else {
                $('.loan-emi-amount').html('');
            }
            $('#loan_amount').val(value);
            $('#loan-amount-error').html('');
            $('.g-loan-hidden-emi-' + dInput + '').val(loanEmi);
            $('.g-loan-hidden-interest-rate-' + dInput + '').val(ROI);
            $('.loan-emi-amount').html('Loan EMI: ' + loanEmi);
        }
        var guarantor = [];
        var groupMemberId = [];
        var today = new Date();
        const getFileChargeajax = (loantype, value, aDate, tenureId, purpose, node, emiOption, ROI, tenure, dInput,loanId) => {
            var insuranceDetail = 0;
            var fileCharge = 0;
          if(loantype != 4)
          {
            $.ajax({
                type: 'POST',
                url: "{!! route('branch.getFileCharge')!!}",
                dataType: 'JSON',
                data: {
                    'loanType': loanId,
                    'amount': value,
                    'applicationDate': aDate,
                    'tenure': tenureId,
                    'purpose': purpose,
                    'emiOption': emiOption,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if (response.loans) {
                        if (parseFloat(value) >= parseFloat(response.loans.min_amount) && parseFloat(value) <= parseFloat(response.loans.max_amount) && response.loans.charge_type == 1 && response.loans.type == 1) {
                             fileCharge = response.loans.charge;
                        } else if (parseFloat(value) >= parseFloat(response.loans.min_amount) && parseFloat(value) <= parseFloat(response.loans.max_amount) && response.loans.charge_type == 0 && response.loans.type == 1) {
                             fileCharge = response.loans.charge * value / 100;
                        } else {
                             fileCharge = '';
                        }
                        if (response.insurance != null) {
                            if (response.insurance.type == 2) {
                                insuranceDetail = response.insurance;
                            }
                            console.log("insuranceDetail",insuranceDetail);
                        }
                        if (loantype != '3') {
                            $('#file_charge').val(fileCharge);
                        } else {
                            $('.g-loan-hidden-file-charge-' + dInput + '').val(fileCharge);
                        }
                        if(loantype != '3'){
                            if(response.ecsCharge?.charge != null){
                                var ecsC = response.ecsCharge?.charge;
                                $('.ecsCharge').val(ecsC);
                                $('#ecs_charges').val(ecsC);

                            }
                        }else{
                                var ecsC = response.ecsCharge?.charge;
                                $('.hidden-g-ecsCharge' + dInput + '').val(ecsC);
                                $('.hidden-g-ecs_charges' + dInput + '').val(ecsC);
                        }
                        calculateEmi(emiOption, value, tenureId, ROI,insuranceDetail, fileCharge, dInput,loantype,loanId,ecsC);
                    } else {
                        if (node != 'emi_mode_option') {
                            $('#amount').val('');
                            $('#file_charge').val('');
                            $('.g-loan-hidden-file-charge-' + dInput + '').val('');
                            $('.g-loan-member-amount-' + dInput + '').val('');
                            swal("Warning!", "File charge is not created on selected date", "warning");
                            return false;
                        }
                    }
                }
            })
          }
          else{
            calculateEmi(emiOption, value, tenureId, ROI,0, 0, dInput,loantype,loanId);
          }
        }
        $(document).on('change', '#amount', function() {
            const node = $(this).attr('id');
            var value = parseInt($('#amount').val());
            var loantype = $("#loan option:selected").val();
            const minAmount = parseInt($("#loan option:selected").attr('data-min'));
            const maxAmount = parseInt($("#loan option:selected").attr('data-max'));
            const category = $("#loan option:selected").attr('data-category');
            const tenure = $("#emi_mode_option option:selected").attr('data-tenure');
            const tenureId = $("#loan option:selected").attr('data-tenure');
            const emiOption = $('#loan option:selected').attr('data-emioption');
            const ROI = $("#loan option:selected").attr('data-rointerest');
            const aDate = $('.application_date').val();
            var moPayment = $("#emi_mode_option option:selected").attr('data-emioption');
            const insuranceDetail = $("#emi_mode_option option:selected").attr('data-gstInsurance');
            console.table(insuranceDetail);
            var moPaymentValue = $("#emi_mode_option option:selected").attr('data-tenure');;
            ((node == 'emi_mode_option') && $('#amount').val(''));
            if (category == 1 || category == 2) {
                if ((value < minAmount || value > maxAmount) && value != '') {
                    $('#loan-amount-error').show();
                    $('#loan-amount-error').html('Please enter amount between ' + minAmount + ' to ' + maxAmount);
                    $('#loan_emi').val('');
                    $('.loan-emi-amount').html('');
                    return false;
                } else {
                    if (aDate == '') {
                        $('#amount').val('');
                        swal("Warning!", "Please Select Application date first", "warning");
                    } else {
                        getFileChargeajax(category, value, aDate, tenureId, 'tenure', node, emiOption, ROI, tenure,'',loantype);
                    }
                }
            } else {
                $('#loan-amount-error').html('');
            }
        });
        $(document).on('change', '#acc_auto_member_id,#group_associate_id', function() {
            var memberid = $(this).val();
            const companyId = $('#loan option:selected').attr('data-company_id');
            var attVal = $(this).attr('data-val');
            $.ajax({
                type: "POST",
                url: "{!! route('loan.associatemember') !!}",
                dataType: 'JSON',
                data: {
                    'memberid': memberid,
                    'companyId': companyId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.resCount > 0) {
                        $('.' + attVal + '-member-detail-not-found').hide();
                        $('.' + attVal + '-member-detail').show();
                        var firstName = response.member[0].first_name ? response.member[0].first_name : '';
                        var lastName = response.member[0].last_name ? response.member[0].last_name : '';
                        var ass_name = firstName + ' ' + lastName;
                        ass_name ? $('#acc_name').val(ass_name) : $('#acc_name').val("Name N/A");
                        response.bAccount ? $('.' + attVal + '-bank-account').val(response.bAccount) : $('.' + attVal + '-bank-account').val("");
                        response.bIfsc ? $('.' + attVal + '-ifsc-code').val(response.bIfsc) : $('.' + attVal + '-ifsc-code').val("");
                        response.bName ? $('.' + attVal + '-bank-name').val(response.bName) : $('.' + attVal + '-bank-name').val("");
                        response.member[0].carders_name ? $('#acc_carder').val(response.member[0].carders_name) : $('#acc_carder').val("Carder N/A");
                        $('.ass-member-id').val(response.member[0].id);
                        $('.' + attVal + '-id').val(response.member[0].id);
                        $('.' + attVal + '-name').val(ass_name);
                        $("#co-applicant_member_id").val(response?.member[0]?.member_company?.id);
                        $("#co-applicant_auto_member_id").val(response?.member[0]?.member_id);
                        $("#co-applicant_occupation_name").val(response?.member[0]?.occupation?.name);
                        $("#co-applicant_designation").val(response?.member[0]?.carders_name);
                    if (response.member[0].member_id_proofs) {
                        $("#co-applicant_id_proof option").removeAttr("data-proof-val");
                        $("#co-applicant_address_id_proof option").removeAttr("data-proof-val");
                        $("#co-applicant_id_proof option[value=" + response.member[0].member_id_proofs.first_id_type_id + "]").attr("data-proof-val", '' + response.member[0].member_id_proofs.first_id_no + '');
                        $("#co-applicant_address_id_proof option[value=" + response.member[0].member_id_proofs.second_id_type_id + "]").attr("data-proof-val", '' + response.member[0].member_id_proofs.second_id_no + '');
                    }
                    else{
                        $("#co-applicant_id_proof option").removeAttr("data-proof-val");
                        $("#co-applicant_address_id_proof option").removeAttr("data-proof-val");
                    }
                    } else {
                        $('.' + attVal + '-bank-account').val("");
                        $('.' + attVal + '-ifsc-code').val("");
                        $('.' + attVal + '-bank-name').val("");
                        $('.' + attVal + '-member-detail').hide();
                        $('.' + attVal + '-member-detail-not-found').show();
                        $('#acc_auto_member_id').val('');
                        $('#group_associate_id').val('');
                        $('.ass-member-id').val('');
                    }
                }
            });
        });
        $(document).on('change', '#guarantor_auto_member_id,#group_auto_member_id', function() {
            $.cookie('planTbaleCounter', '');
            var memberid = $(this).val();
            var attVal = $(this).attr('data-val');
            const loantype = $('#loan option:selected').attr('data-category');
            const companyId = $('#loan option:selected').attr('data-company_id');
            var type = 'member';
            var associateId = $('#acc_auto_member_id').val();
            if (loantype != '3' && attVal == 'co-applicant') {
                if (memberid != associateId) {
                    $('#co-applicant_auto_member_id').val('');
                    $('.co-applicant-member-detail').hide();
                    $('#' + attVal + '_designation').val('');
                    $("#" + attVal + "_id_proof").val('');
                    $("#" + attVal + "_address_id_proof").val('');
                    $("#" + attVal + "_id_number").val('');
                    $("#" + attVal + "_address_id_number").val('');
                    swal("Warning!", "Co applicant and associate must be same!", "warning");
                    return false;
                } else {
                    $('.co-applicant-member-detail').show();
                }
            }
            /*else if(loantype == 3 && attVal == 'group-loan'){
                        var groupMemberId = $('#group_auto_member_id').val();
                        var gassociateId = $('#group_associate_id').val();
                        if(groupMemberId != gassociateId){
                            $('#group_auto_member_id').val('');
                            $('.group-loan-member-detail').hide();
                            swal("Warning!", "Co applicant and member must be same!", "warning");
                            return false;
                        }else{
                            $('.group-loan-member-detail').show();
                        }
                    }*/
            else if (loantype == '3' && attVal == 'co-applicant') {
                var gassociateId = $('#group_associate_id').val();
                if (memberid != gassociateId) {
                    $('#co-applicant_auto_member_id').val('');
                    $('.co-applicant-member-detail').hide();
                    $('#' + attVal + '_designation').val('');
                    $("#" + attVal + "_id_proof").val('');
                    $("#" + attVal + "_address_id_proof").val('');
                    $("#" + attVal + "_id_number").val('');
                    $("#" + attVal + "_address_id_number").val('');
                    swal("Warning!", "Co applicant and associate must be same!", "warning");
                    return false;
                } else {
                    $('.co-applicant-member-detail').show();
                }
            }
            var currentRequest = $.ajax({
                type: "POST",
                url: "{!! route('loan.member') !!}",
                dataType: 'JSON',
                data: {
                    'memberid': memberid,
                    'loantype': loantype,
                    'attVal': attVal,
                    'type': type,
                    'companyId': companyId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function(response) {
                    if (response.msg_type == "success") {
                        $("#" + attVal + "_id_proof").val('');
                        $("#" + attVal + "_address_id_proof").val('');
                        $("#" + attVal + "_id_number").val('');
                        $("#" + attVal + "_address_id_number").val('');
                        // if (attVal == 'group-loan' && response.member.saving_account.length == 0) {
                        //     $('#group_auto_member_id').val('');
                        //     swal("Warning!", "Please open saving account (SSB) then register for loan", "warning");
                        //     $('.' + attVal + '-member-detail').html('');
                        //     $('#' + attVal + '_member_id').val('');
                        //     $('#' + attVal + '_occupation_name').val('');
                        //     $('#' + attVal + '_occupation').val('');
                        //     $('.' + attVal + '-occupation-name').val('');
                        //     $('.' + attVal + '-occupation').val('');
                        //     return false;
                        // } 
                        //  {
                            $('#applicant_designation').val(response.carderName);
                            if (response.member.member_id_proofs) {
                                $("#applicant_id_proof option").removeAttr("data-proof-val");
                                 $("#applicant_address_id_proof option").removeAttr("data-proof-val");
                                $("#applicant_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.first_id_no + '');
                                $("#applicant_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.second_id_no + '');
                            }
                            else{
                                $("#applicant_id_proof option").removeAttr("data-proof-val");
                                $("#applicant_address_id_proof option").removeAttr("data-proof-val");
                                }
                        // }
                        /*else if(attVal=='group-loan' && jQuery.inArray(memberid, guarantor) == -1){
                                                $('#group_auto_member_id').val('');
                                                swal("Warning!", "Applicant should be group member!", "warning");
                                                $('.'+attVal+'-member-detail').html('');
                                                $('#'+attVal+'_member_id').val('');
                                                $('#'+attVal+'_occupation_name').val('');
                                                $('#'+attVal+'_occupation').val('');
                                                $('.'+attVal+'-occupation-name').val('');
                                                $('.'+attVal+'-occupation').val('');
                                                return false;
                                            }*/
                        if (attVal == 'guarantor') {
                            //$('.guarantor-name-section').hide();
                            $('#guarantor_occupation_id').html('');
                            $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                            $('#guarantor_occupation_id').append('<option selected value="' + response.occupation_id + '">' + response.occupation + '</option>');
                            $('#guarantor_occupation_id').prop('disabled', true);
                            //$('.guarantor-member-detail-box').hide();
                            /*if(jQuery.inArray(memberid, guarantor) !== -1){
                                $('#guarantor_auto_member_id').val('');
                                swal("Warning!", "Guarantor should not be a group member!", "warning");
                                $('.'+attVal+'-member-detail').html('');
                                $('#'+attVal+'_member_id').val('');
                                $('#'+attVal+'_occupation_name').val('');
                                $('#'+attVal+'_occupation').val('');
                                $('.'+attVal+'-occupation-name').val('');
                                $('.'+attVal+'-occupation').val('');
                                return false;
                            }*/
                            //$('.guarantor-name-section').hide();
                            var gfirstName = response.member.first_name ? response.member.first_name : '';
                            var glastName = response.member.last_name ? response.member.last_name : '';
                            $('#' + attVal + '_name').val(gfirstName + ' ' + glastName);
                            $('#' + attVal + '_father_name').val(response.member.father_husband);
                            $('#' + attVal + '_dob').val(moment(response.member.dob).format('DD/MM/YYYY'));
                            $("#guarantor_marital_status option[value=" + response.member.marital_status + "]").attr('selected', 'selected');
                            $('#local_address').val(response.member.address);
                            $('#' + attVal + '_mobile_number').val(response.member.mobile_no);
                        }
                        $('.' + attVal + '-member-detail').html(response.view);
                        $('#' + attVal + '_member_id').val(response?.member?.member_company?.id);
                        $('#' + attVal + '_occupation_name').val(response.occupation);
                        $('#' + attVal + '_occupation').val(response.occupation_id);
                        $('.' + attVal + '-occupation-name').val(response.occupation);
                        $('.' + attVal + '-occupation').val(response.occupation_id);
                        $('#' + attVal + '_designation').val(response.carderName);
                        if (response.member.member_id_proofs) {
                            $("#" + attVal + "_id_proof option").removeAttr("data-proof-val");
                            $("#" + attVal + "_address_id_proof option").removeAttr("data-proof-val");
                            $("#" + attVal + "_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.first_id_no + '');
                            $("#" + attVal + "_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id + "]").attr("data-proof-val", '' + response.member.member_id_proofs.second_id_no + '');
                        }
                        else{
                                $("#" + attVal + "_id_proof option").removeAttr("data-proof-val");
                                $("##" + attVal + "_address_id_proof option").removeAttr("data-proof-val");
                                }
                        if (loantype == 4) {
                            $('.loan-against-investment-plan').show();
                            $('.investment-plan-input-number').html('');
                            if (response.member['associate_investment'].length != 0) {
                                var count = response.member['associate_investment'].length;
                                //$.cookie('planTbaleCounter', '');
                                var isRecordExist = false;
                                var i = 0;
                                var invesmentLength = response.member['associate_investment'].length;
                                $.each(response.member['associate_investment'], function(key, value) {
                                    console.log('ttt', key + ": " + value.id);
                                    var months = value.tenure * 12;
                                    let cdate = new Date(value.created_at)
                                    let newDate = cdate.getDate() + "/" + (cdate.getMonth() + 1) + "/" + cdate.getFullYear()
                                    var dt = new Date(value.created_at);
                                    dt.setMonth(months);
                                    let current_datetime = new Date(dt)
                                    let duenewDate = current_datetime.getDate() + "/" + (current_datetime.getMonth() + 1) + "/" + current_datetime.getFullYear()
                                    if (value.plan_id != 1) {
                                        $.ajax({
                                            type: "POST",
                                            url: "{!! route('loan.getplanname') !!}",
                                            dataType: 'JSON',
                                            async: false,
                                            data: {
                                                'planid': value.plan_id
                                            },
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(response) {
                                                var now = new Date();
                                                var sixMonthsFromNow = new Date(now.setMonth(now.getMonth() - 6));
                                                //alert(sixMonthsFromNow.toISOString());
                                                //alert(value.created_at);
                                                if (sixMonthsFromNow.toISOString() >= value.created_at) {
                                                    isRecordExist = true;
                                                    $.cookie('planTbaleCounter', key);
                                                    var list_fieldHTML = '<tr><td class="plan-name">' + response.planName + '<input type="hidden" name="investmentplanloanid[' + key + ']" value="' + value.id + '" class="form-control"></td><td class="account-id">' + value.account_number + '</td><td class="open-date">' + newDate + '</td><td class="due-date">' + duenewDate + '</td><td class="deposite-amount">' + value.current_balance + '<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-' + key + ' form-control" value="' + value.current_balance + '"></td><td class="plan-months">' + months + '</td><td class="loan-amount-input"><input data-input="' + key + '" type="text" name="ipl_amount[' + key + ']" class="ipl_amount ipl_amount-' + key + ' form-control" style="width: 104px"></td></tr>';
                                                    $('.investment-plan-input-number').append(list_fieldHTML);
                                                }
                                            }
                                        });
                                    }
                                    i++;
                                    if (i == invesmentLength && isRecordExist == false) {
                                        swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                        $('.applicant-member-detail').html('');
                                        $('.' + attVal + '-member-detail').html('');
                                        $('#' + attVal + '_member_id').val('');
                                        $('#' + attVal + '_id').val('');
                                    }
                                });
                            } else {
                                swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                $('.applicant-member-detail').html('');
                                $('.' + attVal + '-member-detail').html('');
                                $('#' + attVal + '_member_id').val('');
                                $('#' + attVal + '_id').val('');
                            }
                        }
                        //$('#amount').val('');
                    } else if (response.msg_type == "warning") {
                        $('#' + attVal + '_id').val('');
                        $('.' + attVal + '-member-detail').html('');
                        $('#' + attVal + '_member_id').val('');
                        $('#' + attVal + '_occupation_name').val('');
                        $('#' + attVal + '_occupation').val('');
                        $('.' + attVal + '-occupation-name').val('');
                        $('.' + attVal + '-occupation').val('');
                        $('#' + attVal + '_designation').val('');
                        $("#" + attVal + "_id_proof").val('');
                        $("#" + attVal + "_address_id_proof").val('');
                        $("#" + attVal + "_id_number").val('');
                        $("#" + attVal + "_address_id_number").val('');
                        $('#group_auto_member_id').val('');
                        swal("Warning!", response.msg, "warning");
                        return false;
                    }
                    /* else if(response.msg_type=="warning")
                     {
                         $('#'+attVal+'_id').val('');
                         $('.'+attVal+'-member-detail').html('');
                         $('#'+attVal+'_member_id').val('');
                         $('#'+attVal+'_occupation_name').val('');
                         $('#'+attVal+'_occupation').val('');
                         $('.'+attVal+'-occupation-name').val('');
                         $('.'+attVal+'-occupation').val('');
                         $('#'+attVal+'_designation').val('');
                         $("#"+attVal+"_id_proof").val('') ;
                         $("#"+attVal+"_address_id_proof").val('') ;
                         $("#"+attVal+"_id_number").val('') ;
                         $("#"+attVal+"_address_id_number").val('') ;
                         swal("Warning!", "Guarantor can not be applicant!!", "warning");
                         return false;
                     }*/
                    else {
                        $('.' + attVal + '-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                        $('#' + attVal + '_id').val('');
                        $('#' + attVal + '_member_id').val('');
                        $('#' + attVal + '_occupation_name').val('');
                        $('#' + attVal + '_occupation').val('');
                        $('.' + attVal + '-occupation-name').val('');
                        $('.' + attVal + '-occupation').val('');
                        $('#' + attVal + '_designation').val('');
                        $("#" + attVal + "_id_proof").val('');
                        $("#" + attVal + "_address_id_proof").val('');
                        $("#" + attVal + "_id_number").val('');
                        $("#" + attVal + "_address_id_number").val('');
                        if (attVal == 'guarantor') {
                            $('#guarantor_occupation_id').html('');
                            $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                            $('#guarantor_occupation_id').prop('disabled', false);
                            $('.guarantor-name-section').show();
                            $('.guarantor-member-detail-box').show();
                            $('.guarantor-name-section').show();
                            $('#' + attVal + '_name').val('');
                            $('#' + attVal + '_father_name').val('');
                            $('#' + attVal + '_dob').val('');
                            $("#guarantor_marital_status").val('');
                            $('#local_address').val('');
                            $('#' + attVal + '_mobile_number').val('');
                        }
                        $('.loan-against-investment-plan').hide();
                        $('.investment-plan-input-number').html('');
                    }
                }
            });
        });
        // Get registered member by id
        $(document).on('change', '#group_leader_member_id', function() {
            var memberid = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('loan.groupmember') !!}",
                dataType: 'JSON',
                data: {
                    'memberid': memberid
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == "success") {
                        if (response.member.saving_account.length == 0) {
                            $(this).val('');
                            swal("Warning!", "You have not any ssb account", "warning");
                            $('#group_leader_member_id').val('');
                            $('.group-member-detail').hide();
                            $('.group-member-detail-not-found').hide();
                            $('#group_leader_m_id').val('');
                            $('#group_lm_name').val('');
                            return false;
                        }
                        $('.group-member-detail').show();
                        $('.group-member-detail-not-found').hide();
                        $('#group_leader_m_id').val(response.member.id);
                        $('#group_member_id').val(response.member.id);
                        var firstName = response.member.first_name ? response.member.first_name : '';
                        var lastName = response.member.last_name ? response.member.last_name : '';
                        var name = firstName + ' ' + lastName;
                        $('#group_lm_name').val(name);
                    } else {
                        $('.group-member-detail').hide();
                        $('.group-member-detail-not-found').show();
                        $('#group_leader_m_id').val('');
                    }
                }
            });
        });
        $(document).on('change', '#applicant_id_proof,#applicant_address_id_proof,#co-applicant_id_proof,#co-applicant_address_id_proof,#guarantor_id_proof,#guarantor_address_id_proof', function() {
            var sectionval = $('option:selected', this).attr('data-val');
            var idType = $('option:selected', this).val();
            var proofValue = $('option:selected', this).attr('data-proof-val');
            const customerId = $('#member_id').val();
            var loanType = $("#loan option:selected").val();
            if ($(this).val() == 1) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
            } else if ($(this).val() == 2) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- HR-0619850034761 Or UP14 20160034761');
            } else if ($(this).val() == 3) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
            } else if ($(this).val() == 4) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
            } else if ($(this).val() == 5) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
            } else if ($(this).val() == 6) {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter id proof number');
            } else {
                $('#' + sectionval + '_id_tooltip').attr('data-original-title', 'Enter id proof number');
            }
            // const url = "{!! route('branch.get_member_id_proof') !!}"
            // $.post(url,{
            //     'id_type':idType,
            //     'customer_id':customerId,
            // },function(response){
            //     if(response)
            //     {   
            //         $('#applicant_id_number').val(response.first_id_no);
            //         $('#applicant_id_number').attr('readonly',true);
            //     }
            // })
            if ($('#' + sectionval + '_id').val() != '') {
                $('#' + sectionval + '_id_number').val(proofValue);
            } else {
                $('#' + sectionval + '_id_number').val('');
            }
            if (sectionval == 'applicant_address') {
                if ($('#applicant_id').val() != '') {
                    $('#' + sectionval + '_id_number').val(proofValue);
                } else {
                    $('#' + sectionval + '_id_number').val('');
                }
            }
            if (sectionval == 'co-applicant' || sectionval == 'co-applicant_address') {
                if ($('#co-applicant_auto_member_id').val() != '') {
                    $('#' + sectionval + '_id_number').val(proofValue);
                } else {
                    $('#' + sectionval + '_id_number').val('');
                }
            }
            if (sectionval == 'guarantor' || sectionval == 'guarantor_address') {
                if ($('#guarantor_auto_member_id').val() != '') {
                    $('#' + sectionval + '_id_number').val(proofValue);
                } else {
                    $('#' + sectionval + '_id_number').val('');
                }
            }
            if (sectionval == 'applicant' && loanType == 3) {
                if ($('#group_auto_member_id').val() != '') {
                    $('#applicant_id_number').val(proofValue);
                } else {
                    $('#applicant_id_number').val('');
                }
            }
            if (sectionval == 'applicant_address' && loanType == 3) {
                if ($('#group_auto_member_id').val() != '') {
                    $('#applicant_address_id_number').val(proofValue);
                } else {
                    $('#applicant_address_id_number').val('');
                }
            }
        });
        $.validator.addMethod("checkIdNumber", function(value, element, p) {
            if ($(p).val() == 1) {
                if (this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid voter id number";
                    result = false;
                }
            } else if ($(p).val() == 2) {
                if (this.optional(element) || /^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9][0-9])[0-9]{7}$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid driving licence number";
                    result = false;
                }
            } else if ($(p).val() == 3) {
                if (this.optional(element) || /^(\d{12}|\d{16})$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid aadhar card  number";
                    result = false;
                }
            } else if ($(p).val() == 4) {
                if (this.optional(element) || /^[A-Z][0-9]{7}$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid passport  number";
                    result = false;
                }
            } else if ($(p).val() == 5) {
                if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid pan card no";
                    result = false;
                }
            } else if ($(p).val() == 6) {
                if (this.optional(element) || value != '') {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter ID Number";
                    result = false;
                }
            } else {
                if (this.optional(element) || value != '') {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter ID Number";
                    result = false;
                }
            }
            return result;
        }, "");
        $.validator.addMethod("lessThanEquals",
            function(value, element, param) {
                var $otherElement = $(param);
                return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
                return value > target.val();
            }, "Amount should be less than OR equals closer amount.");
        jQuery.validator.addMethod("notEqual", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        jQuery.validator.addMethod("notEqual1", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        jQuery.validator.addMethod("notEqual2", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        jQuery.validator.addMethod("notEqual3", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        jQuery.validator.addMethod("notEqual4", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        jQuery.validator.addMethod("notEqual5", function(value, element, param) {
            return this.optional(element) || value != $(param).val();
        }, "Cheque Number should be different");
        $(document).on('click', '#co_applicant_checkbox', function() {
            $('#co_applicant_checkbox_val').val(1);
            var loanType = $("#loan option:selected").val();
            if (loanType == 3) {
                var associateId = $('#group_associate_id').val();
            } else {
                var associateId = $('#acc_auto_member_id').val();
            }
            if ($("input[type=checkbox]").is(":checked")) {
                $("#co-applicant_auto_member_id").val(associateId);
                $("#co-applicant_auto_member_id").trigger("change");
            } else {
                $("#co-applicant_auto_member_id").val('');
                $("#co-applicant_auto_member_id").trigger("change");
            }
        });
        var x = 0; //Initial field counter
        var list_maxField = 10;
        $("#more-doc-button").click(function() {
            var hiddenDoc = $('.hidden_more_doc').val();
            if (hiddenDoc == 1) {
                $('.more-doc').show();
                var countVal = $(this).attr('data-val');
                var increaseVal = countVal + 1;
                if (x < list_maxField) {
                    x++; //Increment field counter
                    var list_fieldHTML =
                        '<div class="form-group row flex-grow-1"><label class="col-form-label col-lg-2">Doc Title</label><div class="col-lg-3"><input type="text" name="guarantor_more_doc_title[' +
                        x +
                        ']" id="guarantor_more_doc_title" class="form-control"></div><label class="col-form-label col-lg-2">Upload File</label><div class="col-lg-4"><input type="file" name="guarantor_more_upload_file[' +
                        x +
                        ']" id="guarantor_more_upload_file" class="form-control"></div><span><a href="javascript:void(0);" class="remove-doc-button" >Remove</a></span></div>'; //New input field html
                    $('.more-doc').append(list_fieldHTML); //Add field html
                }
            } else {
                $('.more-doc').show();
                $('.hidden_more_doc').val(1);
            }
        });
        $('.more-doc').on('click', '.remove-doc-button', function() {
            $(this).closest('div.row').remove(); //Remove field html
            x--; //Decrement field counter
        });
        $(document).on('click', '.group-leader-m-id', function() {
            var glAutoId = $(this).val();
            var glID = $(this).attr('data-group');
            $('#group_leader_m_id').val(glAutoId);
            $('#group_member_id').val(glAutoId);
            $('#group_leader_member_id').val(glID);
            $('#customer_id').val(glID);
           $('#customer_id').trigger('change');     
        });
        $(document).on('change', '#applicant_income,#co-applicant_income,#guarantor_income', function() {
            var value = $(this).val();
            var section = $('option:selected', this).attr('data-val');
            if (value == 2) {
                $('.' + section + '-salary-remark').show();
            } else {
                $('.' + section + '-salary-remark').hide();
            }
        });
        $(document).on('change', '#guarantor_occupation_id', function() {
            var value = $('option:selected', this).text();
            if (value == 'Other') {
                $('.occupation-other-remark').show();
                $('.occupation-fields').hide();
            } else {
                $('.occupation-other-remark').hide();
                $('.occupation-fields').show();
            }
        });
        $(document).on('change', '#number_of_member', function() {
            var mNumber = $(this).val();
            $('.m-input-number').html('');
            $('#group_leader_m_id').val('');
            $('#group_leader_member_id').val('');
            if (mNumber > 0) {
                $('.group-loan-member-table').show();
            } else {
                $('.group-loan-member-table').hide();
            }
            var x = 1;
            for (var x = 1; x <= mNumber; x++) {
                var list_fieldHTML = '<tr><td><input data-input="' + x + '" type="text" name="m_id[' +
                    x + ']" class="g-loan-member-id g-loan-member-id-' + x +
                    ' form-control" style="width: 104px" required><input data-input="' + x +
                    '" type="hidden" name="m_id[' + x +
                    ']" class="g-loan-member-id g-loan-hidden-m-id-' + x + '"><input data-input="' + x +
                    '" type="hidden" name="is_m_id[' + x +
                    ']" class="g-loan-member-is-id g-loan-hidden-is-m-id-' + x + '"><input data-input="' + x +
                    '" type="hidden" name="ml_emi[' + x +
                    ']" class="g-loan-hidden-emi g-loan-hidden-emi-' + x +
                    '"  placeholder="Emi Charge" ><input data-input="' + x +
                    '" type="hidden" name="ml_file_charge[' + x +
                    ']" class="g-loan-hidden-file-charge g-loan-hidden-file-charge-' + x +
                    '" placeholder="File Charge"><input data-input="' + x +
                    '" type="hidden" name="ml_insurance_charge[' + x +
                    ']" class="g-loan-hidden-insurance-charge g-loan-hidden-insurance-charge-' + x +
                    '" placeholder="insuranceCharge"><input data-input="' + x +
                    '" type="hidden" name="ml_gst_charge[' + x +
                    ']" class="g-loan-hidden-gst-charge g-loan-hidden-gst-charge-' + x +
                    '" placeholder="gstCharge"><input data-input="' + x +
                    '" type="hidden" name="ml_gst_status[' + x +
                    ']" class="g-loan-hidden-gst-status g-loan-hidden-gst-status-' + x +
                    '" placeholder="gstStatus"><input data-input="' + x +
                    '" type="hidden" name="ml_gst_file_charge[' + x +
                    ']" class="g-loan-hidden-gst-file-charge  g-loan-hidden-gst-file-charge-' + x +
                    '" placeholder="gstFileCharge"><input data-input="' + x +
                    '" type="hidden" name="ml_gst_file_status[' + x +
                    ']" class="g-loan-hidden-gst-file-status g-loan-hidden-gst-file-status-' + x +
                    '" placeholder="gstFileStatus"><input data-input="' + x +
                    '" type="hidden" name="ml_interest_rate[' + x +
                    ']" class="g-loan-hidden-interest-rate g-loan-hidden-interest-rate-' + x +
                    '"  placeholder="Rate"></td><td><input data-input="' + x +
                    '" type="text" name="m_name[' + x +
                    ']" class="g-loan-member-name g-loan-member-name-' + x +
                    ' form-control" style="width: 104px" readonly=""><input data-input="' + x +
                    '" type="hidden" name="ecs_charges[' + x +
                    ']" class="g-loan-hidden-ecs-charge g-loan-hidden-ecs-charge-' + x +
                    '"  placeholder="Rate"><input data-input="' + x +
                    '" type="hidden" name="ecsStatus[' + x +
                    ']" class="g-loan-hidden-ecs-status g-loan-hidden-ecs-status-' + x +
                    '"  placeholder="Rate"><input data-input="' + x +
                    '" type="hidden" name="ecsFileamount[' + x +
                    ']" class="g-loan-hidden-ecs-amount g-loan-hidden-ecs-amount-' + x +
                    '"  placeholder="Rate"><input data-input="' + x +
                    '" type="hidden" name="gstecsPercentage[' + x +
                    ']" class="g-loan-hidden-ecs-percentage g-loan-hidden-ecs-percentage-' + x +
                    '"  placeholder="Rate"></td><td><input data-input="' + x +
                    '" type="text" name="f_name[' + x +
                    ']" class="g-loan-member-fname g-loan-member-fname-' + x +
                    ' form-control" style="width: 104px" readonly=""></td><td><input data-input="' + x +
                    '" type="text" name="m_amount[' + x +
                    ']" class="g-loan-member-amount g-loan-member-amount-' + x +
                    ' form-control" style="width: 104px" required></td><td><input data-input="' + x +
                    '" type="text" readonly name="m_total_deposit_amount[' + x +
                    ']" class="g-loan-member-total-deposit-amount g-loan-member-total-deposit-amount-' +
                    x + ' form-control" style="width: 104px"></td><td><a target="blank" data-input="' +
                    x + '" class="g-loan-member-s g-loan-member-s-' + x +
                    '" href="javascript:void(0);" style="width: 104px"></a></td><td><input data-input="' +
                    x + '" type="text" name="m_bn[' + x +
                    ']" class="g-loan-member-bn g-loan-member-bn-' + x +
                    ' form-control" style="width: 104px" readonly=""></td><td><input data-input="' + x +
                    '" type="text" name="m_bac[' + x +
                    ']" class="g-loan-member-bac g-loan-member-bac-' + x +
                    ' form-control" style="width: 104px" readonly=""></td><td><input data-input="' + x +
                    '" type="text" name="m_bi[' + x + ']" class="g-loan-member-bi g-loan-member-bi-' +
                    x +
                    ' form-control" style="width: 104px" readonly=""></td><td><div class="custom-control custom-radio mb-3 "><input type="radio" id="group-leader-' +
                    x +
                    '" class="custom-control-input group-leader-m-id" name="g_l_m_id" data-group="" value="0"><label class="custom-control-label" for="group-leader-' +
                    x + '">Yes</label></div></td></tr>';
                $('.m-input-number').append(list_fieldHTML); //Add field html
            }
            $('.group-loan-amount').val('');
            $('#loan_amount').val('');
        });   
        $('.m-input-number').on('change', '.g-loan-member-id', function() {
            var mId = $(this).val();
            var dInput = $(this).attr('data-input');
            var created_date = $('#created_date').val();
            var loantype = $("#loan option:selected").val();
            $('.g-loan-member-total-deposit-amount-' + dInput + '').val('');
            guarantor.push(mId);
            $.ajax({
                type: "POST",
                url: "{!! route('loan.groupmember') !!}",
                dataType: 'JSON',
                data: {
                    'memberid': mId,
                    datesys: created_date,
                    'loantype': loantype
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == "success") {
                        //$('.g-loan-member-id-'+dInput+'').val(response.member.id);
                        // if (response.member.saving_account.length == 0) {
                        //     $(this).val('');
                        //     swal("Warning!",
                        //         "Please open saving account (SSB) then register for loan",
                        //         "warning");
                        //     $('.g-loan-member-id-' + dInput + '').val('')
                        //     $('.g-loan-member-name-' + dInput + '').val('');
                        //     $('.g-loan-member-fname-' + dInput + '').val('');
                        //     $('.g-loan-member-bn-' + dInput + '').val('');
                        //     $('.g-loan-member-s-' + dInput + '').html('');
                        //     $('.g-loan-member-s-' + dInput + '').attr('href', '');
                        //     $('.g-loan-member-bac-' + dInput + '').val('');
                        //     $('.g-loan-member-bi-' + dInput + '').val('');
                        //     $('.g-loan-member-id-' + dInput + '').val('');
                        //     $('.g-loan-hidden-m-id-' + dInput + '').val('');
                        //     $('#group-leader-' + dInput + '').val('');
                        //     $('#group-leader-' + dInput + '').attr('data-group', '');
                        //     return false;
                        // }
                        var firstName = response.member.first_name ? response.member
                            .first_name : '';
                        var lastName = response.member.last_name ? response.member
                            .last_name : '';
                        var ass_name = firstName + ' ' + lastName;
                        $('.g-loan-member-name-' + dInput + '').val(ass_name);
                        $('.g-loan-member-fname-' + dInput + '').val(response.member.father_husband);
                        $('.g-loan-member-s-' + dInput + '').html(response.member.signature);
                        // $('.g-loan-member-s-' + dInput + '').html(response.signature);
                        // $('.g-loan-member-s-' + dInput + '').attr('href','asset/profile/member_signature/' + response.member.signature);
                        $('.g-loan-member-s-' + dInput + '').attr('href', response.signature);
                        if (response.member.member_bank_details.length > 0 && response.member.saving_account.length != 0) {
                            $('.g-loan-member-bn-' + dInput + '').val(response.member.member_bank_details[0].bank_name);
                            // $('.g-loan-member-bac-' + dInput + '').val(response.member.saving_account[0]['account_no']);
                            $('.g-loan-member-bac-' + dInput + '').val(response.member.member_bank_details[0]['account_no']);
                            $('.g-loan-member-bi-' + dInput + '').val(response.member.member_bank_details[0].ifsc_code);
                        } else {
                            $('.g-loan-member-bn-' + dInput + '').val('');
                            $('.g-loan-member-bac-' + dInput + '').val('');
                            $('.g-loan-member-bi-' + dInput + '').val('');
                        }
                        $('.g-loan-member-total-deposit-amount-' + dInput + '').val(response.totalDeposit)
                        $('.g-loan-hidden-m-id-' + dInput + '').val(response.member.id);
                        $('.g-loan-hidden-is-m-id-' + dInput + '').val(response?.member?.member_company?.id);
                        $('#group-leader-' + dInput + '').val(response.member.id);
                        $('#group-leader-' + dInput + '').attr('data-group', mId);
                        $('#group-leader-1').prop('checked',true);
                        $('#group_leader_member_id').val(mId);
                        $('#customer_id').val($('.g-loan-member-id-1').val());
                        $('#group_leader_member_id').val( $('.g-loan-member-id-1').val());
                        $('#group_leader_m_id').val($('.g-loan-hidden-m-id-1').val());
                        $('#customer_id').trigger('change');
                    } else if (response.msg_type == "warning") {
                        var moPaymentType = $(".group-information option:selected").attr(
                            'data-val');
                        var moPayment = $(".group-information option:selected").val();
                        $('.g-loan-member-name-' + dInput + '').val('');
                        $('.g-loan-member-fname-' + dInput + '').val('');
                        $('.g-loan-member-bn-' + dInput + '').val('');
                        $('.g-loan-member-bac-' + dInput + '').val('');
                        $('.g-loan-member-s-' + dInput + '').html('');
                        $('.g-loan-member-s-' + dInput + '').attr('href', '');
                        $('.g-loan-member-bi-' + dInput + '').val('');
                        $('.g-loan-member-id-' + dInput + '').val('');
                        $('.g-loan-hidden-m-id-' + dInput + '').val('');
                        $('.g-loan-member-amount-' + dInput + '').val('');
                        $('#group-leader-' + dInput + '').val('');
                        $('#group-leader-' + dInput + '').attr('data-group', '');
                        $('.g-loan-member-total-deposit-amount-' + dInput + '').val('');
                        var sum = 0;
                        $(".g-loan-member-amount").each(function() {
                            sum += +$(this).val();
                        });
                        $('.group-loan-amount').val(sum);
                        $('#loan_amount').val(sum);
                        $('.g-loan-hidden-emi-' + dInput + '').val('');
                        $('.g-loan-hidden-file-charge-' + dInput + '').val('');
                        $('#total_deposit').val('');
                        swal("Warning!", response.msg, "warning");
                        return false;
                        /*if(moPaymentType == 'days' && moPayment == 100){
                            var loanEmi = showpay(value,moPayment,70.20,36500);
                        }else if(moPaymentType == 'days' && moPayment == 200){
                            var loanEmi = showpay(value,moPayment,68.40,36500);
                        }else if(moPaymentType == 'weeks' && moPayment == 12){
                            var loanEmi = showpay(value,moPayment,107.94,5200);
                        }else if(moPaymentType == 'weeks' && moPayment == 24){
                            var loanEmi = showpay(value,moPayment,107.94,5200);
                        }
                        $('#loan_emi').val(loanEmi);
                        $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
                        if(sum >= 10000 && sum <= 25000)  {
                            $('#file_charge').val(500);
                        }else if(sum > 25000 && sum <= 50000){
                            $('#file_charge').val(1000);
                        }else if(sum > 50000){
                            var fileCharge = 2*sum/100;
                            $('#file_charge').val(fileCharge);
                        }*/
                        /*swal("Warning!", "Guarantor can not be applicant!!", "warning");
                        return false; */
                    } else {
                        var moPaymentType = $(".group-information option:selected").attr(
                            'data-val');
                        var moPayment = $(".group-information option:selected").val();
                        //$('.g-loan-member-id-'+dInput+'').val('');
                        $('.g-loan-member-name-' + dInput + '').val('');
                        $('.g-loan-member-fname-' + dInput + '').val('');
                        $('.g-loan-member-bn-' + dInput + '').val('');
                        $('.g-loan-member-bac-' + dInput + '').val('');
                        $('.g-loan-member-s-' + dInput + '').html('');
                        $('.g-loan-member-s-' + dInput + '').attr('href', '');
                        $('.g-loan-member-bi-' + dInput + '').val('');
                        $('.g-loan-member-id-' + dInput + '').val('');
                        $('.g-loan-hidden-m-id-' + dInput + '').val('');
                        $('.g-loan-member-amount-' + dInput + '').val('');
                        $('#group-leader-' + dInput + '').val('');
                        $('#group-leader-' + dInput + '').attr('data-group', '');
                        $('.g-loan-member-total-deposit-amount-' + dInput + '').val('');
                        var sum = 0;
                        $(".g-loan-member-amount").each(function() {
                            sum += +$(this).val();
                        });
                        $('.group-loan-amount').val(sum);
                        $('#loan_amount').val(sum);
                        $('.g-loan-hidden-emi-' + dInput + '').val('');
                        $('.g-loan-hidden-file-charge-' + dInput + '').val('');
                        swal("Error!", "Invalid Customer Id!", "error");
                    }
                }
            });
        });
        $('.m-input-number').on('change', '.g-loan-member-amount', function() {
            var dInput = $(this).attr('data-input');
            var loantype = $("#loan option:selected").val();
            var fAamount = $('.group-loan-amount').val();
            var mId = $('.g-loan-member-id-' + dInput + '').val();
            var value = $('.g-loan-member-amount-' + dInput + '').val();
            const node = $(this).attr('id');
            var loantype = $("#loan option:selected").val();
            const ROI = $("#loan option:selected").attr('data-rointerest');
            const aDate = $('.application_date').val();
            var moPayment = $("#emi_mode_option option:selected").attr('data-emioption');
            const insuranceDetail = $("#emi_mode_option option:selected").attr('data-gstInsurance');
            const tenure = $("#emi_mode_option option:selected").attr('data-tenure');
            const tenureId = $("#loan option:selected").attr('data-tenure');
            const emiOption = $('#loan option:selected').attr('data-emioption');
            const category = $("#loan option:selected").attr('data-category');
            const minAmount = parseInt($("#loan option:selected").attr('data-min'));
            const maxAmount = parseInt($("#loan option:selected").attr('data-max'));
            if (mId != '') {
                // if ($(this).val() > 9999) {
                    var sum = 0;
                    $(".g-loan-member-amount").each(function() {
                        if(parseInt($(this).val()) < minAmount || parseInt($(this).val()) > maxAmount)
                        {
                            $(this).val('');
                            swal('warning','Please enter amount between ' + minAmount + ' to ' + maxAmount,'warning');
                            return false;
                        }
                        else{
                            sum += +$(this).val();
                            $('.group-loan-amount').val(sum);
                        }   
                    });
                    // if (sum <= 200000) {
                    // }
                    //else{
                    //    swal("Error!", "Total amount should be between 20,000 to 2,00,000", "error");
                    //    $('.g-loan-member-amount-'+dInput+'').val('');
                    //    var sum = 0;
                    //     $(".g-loan-member-amount").each(function(){
                    //         sum += +$(this).val();
                    //     });
                    //     $('#loan_amount').val(sum);
                    //     $('.group-loan-amount').val(sum);
                    //     $('#loan_emi').val('');
                    //     //$('.loan-emi-amount').html('Loan EMI: ');
                    //     $('.loan-emi-amount').html('');
                    // }
                // } else {
                //     $('.g-loan-member-amount-' + dInput + '').val('');
                //     var sum = 0;
                //     $(".g-loan-member-amount").each(function() {
                //         sum += +$(this).val();
                //     });
                //     $('.group-loan-amount').val(sum);
                //     $('#loan_amount').val(sum);
                //     swal("Error!", "Enter amount greater than 10000", "error");
                //     $('#loan_emi').val('');
                //     //$('.loan-emi-amount').html('Loan EMI: ');
                //     $('.loan-emi-amount').html('');
                // }
                // if (moPaymentType == 'days' && moPayment == 100) {
                //     var loanEmi = showpay(value, moPayment, 70.20, 36500);
                //     var rate = 70.20;
                // } else if (moPaymentType == 'days' && moPayment == 200) {
                //     var loanEmi = showpay(value, moPayment, 68.40, 36500);
                //     var rate = 68.40;
                // } else if (moPaymentType == 'weeks' && moPayment == 12) {
                //     var loanEmi = showpay(value, moPayment, 107.94, 5200);
                //     var rate = 107.94;
                // } else if (moPaymentType == 'weeks' && moPayment == 24) {
                //     var loanEmi = showpay(value, moPayment, 60.55, 5200);
                //     var rate = 60.55;
                // } else if (moPaymentType == 'weeks' && moPayment == 26) {
                //     var loanEmi = showpay(value, moPayment, 44.857, 5200);
                //     var rate = 44.857;
                // } else if (moPaymentType == 'weeks' && moPayment == 52) {
                //     var loanEmi = showpay(value, moPayment, 46.69911, 5200);
                //     var rate = 46.69911;
                // }
                // if (value >= 10000 && value <= 25000) {
                //     var fileCharge = 500;
                // } else if (value > 25000 && value <= 50000) {
                //     var fileCharge = 1000;
                // } else if (value > 50000) {
                //     var fileCharge = 2 * value / 100;
                // }
                console.log("gf",loantype, value, aDate, tenureId, "tenure", node, emiOption, ROI,
                    tenure, dInput);
                getFileChargeajax(category, value, aDate, tenureId, "tenure", node, emiOption, ROI,
                tenureId, dInput,loantype)
            }
        });
        //Loader Set
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        // Hide loading image
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    })
</script>