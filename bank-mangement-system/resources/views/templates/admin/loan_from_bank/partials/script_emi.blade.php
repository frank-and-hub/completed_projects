<script type="text/javascript">
  var loanFromBankListing;

  $(document).ready(function() {
    var today = new Date();

    /*$("#date").hover(function(){
          $('#date').datepicker({
              format:"dd/mm/yyyy",
                startHighlight: true, 
                autoclose:true, 
                endDate: $('#create_application_date').val(),
              })
       })*/

    $('#date').on('change', function() {
      $("#received_bank_name").val('');
      $("#received_bank_name").trigger("change");
    });

    var selected_account = $('#selectedOption').val();

    $.validator.addMethod("decimal", function(value, element, p) {

      if (this.optional(element) || $.isNumeric(value) == true)

      {

        $.validator.messages.decimal = "";

        result = true;

      } else {

        $.validator.messages.decimal = "Please enter valid numeric number.";

        result = false;

      }

      return result;

    }, "");



    $.validator.addMethod("bchk", function(value, element, p) {

      emi_principal_amount = $('#emi_principal_amount').val();
      current_balance = $('#current_loan_amount').val();
      if (parseFloat(emi_principal_amount) <= parseFloat(current_balance)) {
        $.validator.messages.bchk = "ss";
        result = true;
      } else {
        $.validator.messages.bchk = "Emi Principal Amount Should be less than or equal to current balance";
        result = false;
      }
      return result;
    }, "");

    $.validator.addMethod("chkbank", function(value, element, p) {
      amount = parseFloat($('#emi_interest_rate').val()) + parseFloat($('#emi_principal_amount').val());
      if (parseFloat($('#bank_balance').val()) >= parseFloat(amount)) {
        $.validator.messages.chkbank = "s";
        result = true;
      } else {
        $.validator.messages.chkbank = "Bank available balance must be grather than or equal to  sum of emi principal amount and emi interest amount";
        result = false;
      }

      return result;
    }, "");




    $.validator.addMethod("zero", function(value, element, p) {

      if (value >= 0)

      {

        $.validator.messages.zero = "ss";

        result = true;

      } else {

        $.validator.messages.zero = "Amount must be greater than 0.";

        result = false;

      }



      return result;

    }, "");



    $('#loan_emi').validate({

      rules: {

        loan_account_number: {

          required: true,
          number: true,

        },

        bank_name: {

          required: true,

        },
        branch_name: {

          required: true,

        },
        loan_date: {

          required: true,

        },
        company_id: {

          required: true,

        },

        date: {

          required: true,

        },

        emi_number: {

          required: true,

        },

        emi_principal_amount: {

          required: true,

          number: true,

          decimal: true,

          zero: true,
          bchk: true,
          //  chkbank:true,

        },

        emi_interest_rate: {

          required: true,

          number: true,

          decimal: true,

          zero: true,
          // chkbank:true,

        },

        loan_amount: {

          required: true,

          number: true,

          decimal: true,

          zero: true,

        },

        received_bank_name: {

          required: true,

        },

        received_bank_account: {

          required: true,

        },

        current_loan_amount: {

          required: true,

        },
        bank_balance: {
          required: true,
          chkbank: true,
        }


      },

      messages: {

        bank_name:

        {

          "required": "Please enter bank name.",

        },

        branch_name: {

          "required": "Please enter branch name.",

        },

        emi_principal_amount: {

          "required": "Please enter principal emi amount.",

        },

        loan_amount: {

          "required": "Please enter loan amount.",

        },



        loan_account_number: {

          "required": "Please enter account number.",

        },

        emi_interest_rate: {

          "required": "Please enter interest rate.",

        },

        emi_number: {

          "required": "Please enter emi number.",

        },

        received_bank_name: {

          "required": "Please enter received bank name."

        },

        received_bank_account: {

          "required": "Please enter received bank account."

        },

        current_loan_amount: {

          "required": "Please enter current loan  amount.",

        },
        bank_balance: {

          "required": "Bank balance must be greater than zero.",

        }

      },
      submitHandler: function(){
        $("#submitm").prop('disabled', true);
        return true;
      }

    })
  
    $('#received_bank_account').on('change', function() {
      var bank_id = $('#received_bank_name').val();
      var account_id = $('#received_bank_account').val();
      var entrydate = $('#date').val();
      var companyId = $('#company_id').val();
      $('#bank_balance').val('0.00');

      $.ajax({
        type: "POST",
        url: "{!! route('admin.bankChkbalance') !!}",
        dataType: 'JSON',
        data: {
          'account_id': account_id,
          'bank_id': bank_id,
          'companyId': companyId,
          'entrydate': entrydate
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          // alert(response.balance);
          $('#bank_balance').val(response.balance);
          $("#bank_balance").trigger("keyup");

        }
      });

    })








    
    $('#company_id').on('change', function() {
      var company_id = $(this).val();
      $('#bank_balance').val('0.00');
      $.ajax({
        type: "POST",
        url: "{!! route('admin.loan_from_bank.bank_account_list') !!}",
        dataType: 'JSON',
        data: {
          'company_id': company_id
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          $('#received_bank_name').find('option').remove();
          $('#received_bank_name').append('<option value="">Please Select Bank</option>');
          $.each(response.account, function(index, value) {
            $("#received_bank_name").append("<option value='" + value.id + "'>" + value.bank_name + "</option>");
          });
        }
      });
    })











    $('#received_bank_name').on('change', function(selected_account) {
      var bank_id = $(this).val();
      $('#bank_balance').val('0.00');

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



    $('#loan_account_number').on('change', function() {

      var loan_account_number_id = $(this).val();

      $('#bank_name').val('');
      $('#received_bank_account').val('');
      $('#loan_amount').val('');
      $('#current_loan_amount').val('');
      $('#account_head_id').val('');
      $('#loan_from_bank_id').val('');
      $('#loan_date').val('');
      $('#date').val('');
      var cmpny = $('#company_id').val();
      $("#company_id option[value='" + cmpny + "']").text('Select loan account number').val(null);
      $('#branch_name').val('');

      $('#received_bank_name').find('option').remove();
      $('#received_bank_name').append('<option value="">Please Select Bank</option>');
      if (loan_account_number_id != '') {
        $.ajax({

          type: "POST",

          url: "{!! route('admin.get_loan_account_detail') !!}",

          dataType: 'JSON',

          data: {
            'id': loan_account_number_id
          },

          headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

          },

          success: function(response) {
            if (response.loanData) {
              $('#bank_name').val(response.loanData.bank_name);

              $('#loan_amount').val(parseFloat(response.loanData.loan_amount).toFixed(2));

              $('#current_loan_amount').val(parseFloat(response.loanData.current_balance).toFixed(2));

              $('#account_head_id').val(response.loanData.account_head_id);

              $('#loan_from_bank_id').val(response.loanData.id);
              $('#loan_date').val(response.dateGet);
              $("#company_id option[value='']").text(response.company_name).val(response.company_id);
              $('#company_id').trigger('change');
              $("#branch_name").val(response.loanData.branch_name);

              $('#date').datepicker({
                format: "dd/mm/yyyy",
                startHighlight: true,
                autoclose: true,
                endDate: new Date(),
                startDate: '01/04/2021',



              })
            } else {
              $('#bank_name').val('');
              $('#loan_amount').val('');
              $('#current_loan_amount').val('');
              $('#account_head_id').val('');
              $('#loan_from_bank_id').val('');
              $('#loan_date').val('');
              $('#date').val('');
            }
          }

        });
      }



    })


    $(document).ajaxStart(function() {

      $(".loader").show();

    });



    $(document).ajaxComplete(function() {

      $(".loader").hide();

    });

  });
</script>