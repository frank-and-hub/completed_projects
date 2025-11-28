<script type="text/javascript">
$(document).ready(function() {

    $('.col-md-4').addClass('col-lg-6').removeClass('col-md-4');
    $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
    $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');

    
   
    $('#maturity_date1').datepicker({
        format: "dd/mm/yyyy",
        startHighlight: true,
        autoclose: true,
        orientation: 'bottom',
        
    });
    
    // ONLY NUMERIC VALUES IN AMOUNT AND ROI
    $("#roi, #amount, #fd_no").on("input", function() {
        $(this).val($(this).val().replace(/[^0-9.-]/g, ""));
    });


    $("#bank_name").on("input", function() {
        $(this).val($(this).val().replace(/[^a-zA-Z\s]/g, ""));
    });

    // company create date

    $('#company_id').on('change', function() {

        $("#branch  option:not([value='29'])").remove();

        var company_id = $(this).val();
        if (company_id === '' || company_id === null) {
            $('#company_create_date').val('');
            $('#maturity_date').val('');
            $('#date').val('');
            $('#date').datepicker('destroy');
            $('#maturity_date').datepicker('destroy');
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: "{{route('admin.vendor.companydate')}}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                },
                success: function(response) {
                    $('#company_create_date').val(response);
                    $('#date').val('');
                    let globalDatee = $('#bill_reate_application_date').val();
                    $('#date').datepicker({
                        format: "dd/mm/yyyy",
                        startHighlight: true,
                        autoclose: true,
                        orientation: 'bottom',
                        endDate: globalDatee,

                    }).on('change', function() {
                        var selectedDate = $('#date').val();
                        $('#maturity_date').val('');
                        $('#maturity_date').datepicker({
                            format: "dd/mm/yyyy",
                            startHighlight: true,
                            autoclose: true,
                            orientation: 'bottom',
                        })
                        $('#maturity_date').datepicker('setStartDate',
                            selectedDate);
                    });
                    $('#date').datepicker('setStartDate', response);

                }
            });
        }
    });

    // company create date end

    // Get Bank Balance

    $('#received_bank_account').on('change',function(){
    if($('#received_bank_account').val()== ''){
        $('#bank_account_balance').val('0.00');
        return false;
    }
    var amount =$("#amount").val();
    var bank_id=$('#received_bank_name').val();
    var companyId = $( "#company_id option:selected" ).val();
    var account_id=$('#received_bank_account').val();
    var convertedDate=$('#date').val();
    var parts = convertedDate.split('/');
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    var entrydate = year + '/' + month + '/' + day;
      $.ajax({
              type: "POST",
              url: "{!! route('admin.bankChkbalance') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate,'companyId':companyId},
              success: function(response) {
                if($('#received_bank_account').val()== ''){
                    $('#bank_account_balance').val('0.00');
                    return false;
                }
                $('#bank_account_balance').val(response.balance);
                var bank_balance = response.balance;
                if(amount > bank_balance){
                    $.validator.addMethod("lessThanEquals", function (value, element, param) {
                        var $otherElement = $(param);
                        return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
                    return value > target.val();
                    }, "Amount should be less than OR equals current available bank amount.");
                   
                }else{
                    
                }
              }
          });    
    });


    // end get bank balance


    $('#date').on('change',function(){
        // $('#company_id').trigger('change');
        if($('#received_bank_account').val()== ''){
            $('#bank_account_balance').val('0.00');
        }
    var amount =$("#amount").val();
    var bank_id=$('#received_bank_name').val();
    var companyId = $( "#company_id option:selected" ).val();
    var account_id=$('#received_bank_account').val();
    var convertedDate=$('#date').val();
    var parts = convertedDate.split('/');
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    var entrydate = year + '/' + month + '/' + day;
      $.ajax({
              type: "POST",
              url: "{!! route('admin.bankChkbalance') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate,'companyId':companyId},
              success: function(response) {
                $('#bank_account_balance').val(response.balance);
                var bank_balance = response.balance;
                if(amount > bank_balance){
                    $.validator.addMethod("lessThanEquals",
                    function (value, element, param) {
                        var $otherElement = $(param);
                        return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
                    return value > target.val();
                    }, "Amount should be less than OR equals current available bank amount.");
                    // $('#amount').val('');
                }else{
                    
                }
              }
          });    
    });


    $.validator.addMethod("zero", function(value, element, p) {
        if (value >= 0) {
            $.validator.messages.zero = "";
            result = true;
        } else {
            $.validator.messages.zero = "Amount must be greater than or equal to 0.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("decimal", function(value, element, p) {
        if (this.optional(element) || $.isNumeric(value) == true) {
            $.validator.messages.decimal = "";
            result = true;
        } else {
            $.validator.messages.decimal = "Please Enter valid numeric number.";
            result = false;
        }
        return result;
    }, "");

    function dateFormat(inputDate, format) {
        //parse the input date
        const date = new Date(inputDate);
        //extract the parts of the date
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();
        //replace the month
        format = format.replace("MM", month.toString().padStart(2, "0"));
        //replace the year
        if (format.indexOf("yyyy") > -1) {
            format = format.replace("yyyy", year.toString());
        } else if (format.indexOf("yy") > -1) {
            format = format.replace("yy", year.toString().substr(2, 2));
        }
        //replace the day
        format = format.replace("dd", day.toString().padStart(2, "0"));
        return format;
    }
    // $('.submit').on('click', function() {
    //     var minDateVal = $('.minvalDate').val().split('/');
    //     var currentDateVal = $('#currentdate').val().split('/');
    //     var value = $('#maturity_date').val().split('/');
    //     var fullDate = new Date()
    //     // console.log(fullDate);
    //     //Thu May 19 2011 17:25:38 GMT+1000 {}
    //     //convert month to 2 digits
    //     var twoDigitMonth = ((fullDate.getMonth().length + 1) === 1) ? (fullDate.getMonth() + 1) : '0' +
    //         (fullDate.getMonth() + 1);
    //     var strDate = fullDate.getDate() + "-" + twoDigitMonth + "-" + fullDate.getFullYear();
    //     console.log('strDate', strDate)
    //     var maxd = new Date(strDate);
    //     var mind = new Date(minDateVal[2] + '-' + minDateVal[1] + '-' + minDateVal[0]);
    //     var mdate = new Date(value[2] + '-' + value[1] + '-' + value[0]);
    //     $('.msg').html('');
    //     if (mdate >= fullDate) {
    //         //console.log("currentdate", mdate);
    //         $('.msg').html("Please Select Valid Date");
    //         return false;
    //     } else {
    //         console.log("value", value);
    //         return true;
    //     }
    // });
    // $.validator.addMethod("checkDate", function(value, element,p) {   
    // 	var minDateVal = $('.minvalDate').val();
    // 	var currentDateVal = $('#currentdate').val();
    //	var mDate = $('#maturity_date').val().split("-");
    //console.log("mDate", mDate);
    // 	if(value >= minDateVal && value <= currentDateVal)
    // 	{
    // 		$.validator.messages.checkDate = "";
    // 		result = true;
    // 	}else{
    // 		$.validator.messages.checkDate = "Please Select  Valid date.";
    // 	result = false;  
    // 	}
    // return result;
    // }, "");
    $('#company_fd_close').validate({
        rules: {
            remark: {
                required: true
            },
            received_bank_name: {
                required: true
            },
            received_bank_account: {
                required: true
            }
        },
        messages: {

            remark: {
                required: 'Please Enter Remark',
            },
            received_bank_account: {
                required: 'Please Select Bank Account',
            },
            received_bank_name: {
                required: 'Please Select Bank',
            },
        }
    })
    $('#company_fd').validate({
        rules: {
            bank_name: {
                required: true
            },
            branch_id: {
                required: true
            },
            fd_no: {
                required: true
            },
            date: {
                required: true
            },
            maturity_date: {
                required: true,
                // checkDate: true
            },
            amount: {
                required: true,
                zero: true,
                decimal: true,
               lessThanEquals: "#bank_account_balance",
            },
            roi: {
                required: true
            },
            received_bank_name: {
                required: true
            },
            received_bank_account: {
                required: true
            },
            remark: {
                required: true
            }
        },
        messages: {
            bank_name: {
                required: 'Please Enter Bank Name',
            },
            fd_no: {
                required: 'Please Enter FD Number',
            },
            date: {
                required: 'Please Enter Date',
            },
            maturity_date: {
                required: 'Please Enter Maturity Date',
            },
            amount: {
                required: "Please Enter the Amount",
                decimal: "Please enter a valid amount.",
            },
            roi: {
                required: "Please Enter the Amount",
                decimal: "Please enter a valid amount.",
            },
            received_bank_name: {
                required: 'Please Select Receive Bank Name',
            },
            received_bank_account: {
                required: 'Please Select Account Number',
            },
            remark: {
                required: 'Please Enter Remark',
            },
        }
    })

    $('#company_id').on('change', function() {
        
        $('#received_bank_account').find('option').remove();
        $('#received_bank_account').append('<option value="">Select account number</option>');
        var company_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.banks_list') !!}",
            dataType: 'JSON',
            data: {
                'company_id': company_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#received_bank_name').find('option').remove();
                $('#received_bank_name').append(
                    '<option value="">Select Bank</option>');
                $.each(response.banks, function(index, value) {
                    $("#received_bank_name").append("<option value='" + value
                        .id + "'>" + value.bank_name + "</option>");
                });
            }
        });
    });
    $('#received_bank_name').on('change', function(selected_account) {
        $("#bank_account_balance").val('0.00');
        var date = $('#date').val();
        if(date==''){
            swal("Warning!", "PLease Select Date First", "warning");
            $('#received_bank_name').val('');
            $('#received_bank_account').val('');
            return false;
        }
        var bank_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.bank_account_list') !!}",
            dataType: 'JSON',
            data: {
                'bank_id': bank_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#received_bank_account').find('option').remove();
                $('#received_bank_account').append(
                    '<option value="">Select account number</option>');
                $.each(response.account, function(index, value) {
                    $("#received_bank_account").append("<option value='" + value
                        .id + "'>" + value.account_no + "</option>");
                });
            }
        });
    })
})
const stringToDate = function(dateString) {
    const [dd, mm, yyyy] = dateString.split("-");
    return new Date(`${yyyy}-${mm}-${dd}`);
};
</script>