<script type="text/javascript">
  var loanFromBankListing;
  $('.col-md-4').addClass('col-lg-6').removeClass('col-md-4');
  // $('.col-lg-12').removeClass('col-lg-12');
  $('.col-form-label.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
  $('.col-lg-12.error-msg').addClass('col-lg-8').removeClass('col-lg-12');
  $(document).ready(function() {


    $("#start_date").hover(function() {
      $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        startHighlight: true,
        autoclose: true,
        endDate: $('#create_application_date').val(),
        startDate: '01/04/2021',

      })
    })
    $("#end_date").hover(function() {
      $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        startHighlight: true,
        autoclose: true,
        startDate: $('#start_date').val(),
        startDate: '01/04/2021',

      })
    })



    var selected_account = $('#selectedOption').val();
    $.validator.addMethod("decimal", function(value, element, p) {
      if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
        $.validator.messages.decimal = "";
        result = true;
      } else {
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;
      }
      return result;
    }, "");



    $.validator.addMethod("bchkLoan", function(value, element, p) {
      loan_amount = $('#loan_amount').val();
      emi_amount = $('#emi_amount').val();
      if (parseFloat(emi_amount) < parseFloat(loan_amount)) {
        $.validator.messages.bchkLoan = "";
        result = true;
      } else {
        $.validator.messages.bchkLoan = "Emi amount should be less than loan amount.";
        result = false;
      }
      return result;

    }, "");

    
    $.validator.addMethod("dateCheck", function(value, element, params) {
      var startDate = $('#start_date').val();
      var endDate = $('#end_date').val();

      var startParts = startDate.split("/");
      var endParts = endDate.split("/");

      var start = new Date(startParts[2], startParts[1] - 1, startParts[0]);
      var end = new Date(endParts[2], endParts[1] - 1, endParts[0]);

      if (start < end) {
        $.validator.messages.dateCheck = "";
        return true;
      } else {
        $.validator.messages.dateCheck = "End date must be greater than start date.";
        return false;
      }
    }, "");



    $.validator.addMethod("zero", function(value, element, p) {
      if (value >= 0) {
        $.validator.messages.zero = "";
        result = true;
      } else {
        $.validator.messages.zero = "Amount must be greater than 0.";
        result = false;
      }
      return result;
    }, "");

    $.validator.addMethod("chkBill", function(value, element, p) {
      if ($("#received_type").val() == 2) {
        if (parseFloat($('#loan_amount').val()) <= parseFloat($('#bill_amount_due').val())) {
          $.validator.messages.chkBill = "";
          result = true;
        } else {
          $.validator.messages.chkBill = "Loan Amount must be less than or equal to  bill balance";
          result = false;
        }
      } else {
        $.validator.messages.chkBill = "";
        result = true;
      }
      return result;
    }, "");
    $(document).on('change', '#company_id', function() {
      var company_id = $('#company_id').val();
      $.ajax({
        type: "POST",
        url: "{!! route('admin.bank_list_by_company') !!}",
        dataType: 'JSON',
        data: {
          'company_id': company_id
        },
        success: function(response) {
          $('#received_bank_account').val('');
          $('#received_bank_name').find('option').remove();
          $('#received_bank_name').append('<option value="">Select bank</option>');
          $.each(response.bankList, function(index, value) {
            $("#received_bank_name").append("<option value='" + value.id + "'>" + value.bank_name + "</option>");
          });
        }
      });
    });
    $(document).on('change', '#company_id', function() {
      var company_id = $('#company_id').val();
      $('#vendor_bill_id').val('');
      $('#bill_amount_due').val('');
      $.ajax({
        type: "POST",
        url: "{!! route('admin.vendorList.loan-from-bank') !!}",
        dataType: 'JSON',
        data: {
          'company_id': company_id
        },
        success: function(response) {
          $('#vendor_id').find('option').remove();
          $('#vendor_id').append('<option value="">Select Vendor</option>');
          $.each(response.vendorList, function(index, value) {
            $("#vendor_id").append("<option value='" + value.id + "'>" + value.name + "</option>");
          });
        }
      });
    });

    $('#loan_from_bank').validate({
      rules: {

        bank_name: {
          required: true,
        },
        branch_id: {
          required: true,
        },
        loan_amount: {
          required: true,
          number: true,
          decimal: true,
          zero: true,
          chkBill: function(element) {
            if (($("#received_type").val() == 2)) {
              return true;
            } else {
              return false;
            }
          },
        },
        remark: {
          required: true,
        },
        loan_account_number: {
          required: true,
          number: true,
          decimal: true,
          zero: true,

        },
        loan_interest_rate: {
          required: true,
          number: true,
          decimal: true,
          zero: true,
        },
        no_of_emi: {
          required: true,
          number: true,
          zero: true,
        },
        received_bank_name: {
          required: true,
        },
        received_bank_account: {
          required: true,
        },
        address: {
          required: true,
        },
        emi_amount: {
          required: true,
          decimal: true,
          bchkLoan: true,
        },
        start_date: {
          required: true,
        },
        end_date: {
          required: true,
          dateCheck: true,
        },
        received_type: {
          required: true,
        },
        head_type: {
          required: true,
        },

        vendor_id: {
          required: function(element) {
            if (($("#received_type").val() == 2)) {
              return true;
            } else {
              return false;
            }
          },
        },
        vendor_bill_id: {
          required: function(element) {
            if (($("#received_type").val() == 2)) {
              return true;
            } else {
              return false;
            }
          },
        },
        bill_amount_due: {
          required: function(element) {
            if (($("#received_type").val() == 2)) {
              return true;
            } else {
              return false;
            }
          },
          zero: true,
        },

      },
      messages: {
        date: {
          "required": "Select enter date .",
        },
        bank_name: {
          "required": "Please enter bank name.",
        },
        branch_id: {
          "required": "Please enter branch.",
        },
        loan_amount: {
          "required": "Please enter loan amount.",
        },
        remark: {
          "required": "Please enter remark.",
        },
        loan_account_number: {
          "required": "Please enter account number.",
        },
        loan_interest_rate: {
          "required": "Please enter interest rate.",
        },
        no_of_emi: {
          "required": "Please enter number of emi.",
        },
        received_bank_name: {
          "required": "Please enter received bank name."
        },
        received_bank_account: {
          "required": "Please enter received bank account."
        },
        address: {
          "required": "Please enter address."
        },
        emi_amount: {
          "required": "Please enter emi amount."
        },

        start_date: {
          "required": "Please enter from date."
        },
        end_date: {
          "required": "Please enter end date."
        },
        received_type: {
          "required": "Please select received type."
        },
        vendor_id: {
          "required": "Please select vendor."
        },
        vendor_detail: {
          "required": "Please select bill."
        },
        bill_amount_due: {
          "required": "Please enter bill amount."
        },
        head_type: {
          "required": "Please select loan type."
        },
      }
    })


    $('#received_bank_name').on('change', function(selected_account) {
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
          $('#received_bank_account').append('<option value="">Select account number</option>');
          $.each(response.account, function(index, value) {
            $("#received_bank_account").append("<option value='" + value.id + "'>" + value.account_no + "</option>");

          });
        }
      });
    })

    $('#vendor_id').on('change', function(selected_account) {
      var vendor_id = $(this).val();
      $('#bill_amount_due').val('0.00');
      $('#vendor_bill_id').val('');
      $.ajax({
        type: "POST",
        url: "{!! route('admin.get_vendor_bill') !!}",
        dataType: 'JSON',
        data: {
          'vendor_id': vendor_id
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {

          $('#vendor_bill_id').find('option').remove();
          $('#vendor_bill_id').append('<option value="">Select bill</option>');
          $.each(response.bill, function(index, value) {
            $("#vendor_bill_id").append("<option value='" + value.id + "'>" + value.bill_number + "</option>");

          });
        }
      });
    })

    $('#vendor_bill_id').on('change', function(selected_account) {
      var vendor_bill_id = $(this).val();
      $('#bill_amount_due').val('0.00');
      $.ajax({
        type: "POST",
        url: "{!! route('admin.get_vendor_bill_due') !!}",
        dataType: 'JSON',
        data: {
          'vendor_bill_id': vendor_bill_id
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {

          $('#bill_amount_due').val(response);
        }
      });
    })



    $('#received_type').on('change', function() {
      $('#bank_detailget').hide();
      $('#vendor_detail').hide();
      type = $('#received_type').val();
      if (type == 1) {
        $('#bank_detailget').show();

      }
      if (type == 2) {

        $('#vendor_detail').show();

      }
    })





    // Show and Hide Payment Type using Transaction Type
    $('#transaction_type').on('change', function() {
      let type = $('option:selected', this).val();
      if (type == 0) {
        $('#bank_detail').show();
        $('#fixed_asset').hide();

      } else if (type == 1) {

        $('#fixed_asset').show();
        $('#bank_detail').hide();

      } else {
        $('#bank_detail').hide();
        $('#fixed_asset').hide();
      }
    })

    $('#head1').on('change', function() {
      let value = $('option:selected', this).val()
      $.ajax({
        type: 'POST',
        url: "{!! route('admin.account_head_get') !!}",
        dataType: 'JSON',
        data: {
          headId: value
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          console.log(response);
          $("#head2").find('option').remove();
          if (response.data.length > 0) {
            $("#head2").attr('disabled', false);
            $("#head2").append('<option>' + "Please Select Head2" + '</option>');
            $.each(response.data, function(key, value) {
              $("#head2").append('<option value=' + value.head_id + ' >' + value.sub_head + '</option>');
            });
          } else {
            $("#head2").attr('disabled', true);

          }
        }
      })
    })
    $('#head2').on('change', function() {
      let value = $('option:selected', this).val()
      $.ajax({
        type: 'POST',
        url: "{!! route('admin.account_head_get') !!}",
        dataType: 'JSON',
        data: {
          headId: value
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {


          $("#head3").find('option').remove();
          if (response.data.length > 0) {
            $(".child_asset3").show();
            //$("#head3").append('<option>' +"Please Select Head3"+ '</option>');

            $("#head3").attr('disabled', false);
            $.each(response.data, function(key, value) {
              $("#head3").append('<option value=' + value.head_id + ' >' + value.sub_head + '</option>');
            });
          } else {
            $(".child_asset3").hide();
          }
        }
      })
    })

    $('#loan_account_no').on('change', function() {
      var loan_account_number_id = $(this).val();
      $.ajax({
        type: "POST",
        url: "{!! route('admin.getloanAccount') !!}",
        dataType: 'JSON',
        data: {
          'id': loan_account_number_id
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {

          $.each(response, function(index, value) {

            if (value.loan_account_number === loan_account_number_id) {
              swal("Error", "Account Number Already Exists!", "warning");

              $('#loan_account_no').val("");

            }

          })
          // $('#bank_name').val(response.loanData.bank_name);
          // $('#loan_amount').val(parseFloat(response.loanData.loan_amount).toFixed(2));
          // $('#current_loan_amount').val(parseFloat(response.loanData.current_balance).toFixed(2));
          // $('#account_head_id').val(response.loanData.account_head_id);
          // $('#loan_from_bank_id').val(response.loanData.id);               

        }
      });
    })



    $(document).ajaxStart(function() {
      $(".loader").show();
    });

    $(document).ajaxComplete(function() {
      $(".loader").hide();
    });

    $("#loan_from_bank").submit(function(event) {
      if ($('#loan_from_bank').valid()) {
        $('input[type="submit"]').prop('disabled', true);
      }
    })

  });
</script>