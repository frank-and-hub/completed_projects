<script type="text/javascript">
  $(document).ready(function () {
    var id = '@if($accountDetail) {{ $accountDetail->id  }} @endif';
    var code = '{{ $code }}';


    var date = new Date();
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true
    });

    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true
    });



    var passbookTable;
    passbookTable = $('#listtansaction').DataTable({
      processing: true,
      serverSide: true,
      aaSorting: false,
      ordering: false,
      pageLength: 100,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('branch.transaction_listing') !!}",
        "type": "POST",
        "data": { 'id': id, 'code': code },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [
        { data: 'tranid', name: 'tranid' },
        { data: 'tranid', name: 'tranid' },
        { data: 'tran_by', name: 'tran_by' },
        { data: 'date', name: 'date' },
        { data: 'description', name: 'description' },
        { data: 'reference_no', name: 'reference_no' },
        {
          data: 'withdrawal', name: 'withdrawal',
          "render": function (data, type, row) {
            if (row.withdrawal) {
              return row.withdrawal + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            } else {
              return "";
            }
          }
        },
        {
          data: 'deposit', name: 'deposit',
          "render": function (data, type, row) {
            if (row.deposit) {
              return row.deposit + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            } else {
              return "";
            }
          }
        },
        {
          data: 'opening_balance', name: 'opening_balance',
          "render": function (data, type, row) {
            if (row.opening_balance) {
              return row.opening_balance + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            } else {
              return "";
            }
          }
        },
        { data: 'action', name: 'action' },
      ]
    });
    $(passbookTable.table().container()).removeClass('form-inline');
    $('#fillter').validate({
      // rules: {
      //   start_date: {
      //       required: true, 
      //     }, 
      //     end_date: {
      //       required: true, 
      //     },  
      // },
      // messages: {
      //   start_date: {
      //       required: "Please select start date.",

      //     },
      //     end_date: {
      //       required: "Please select end date.",

      //     },
      // },
      rules: {
        transaction_id_from: {
          required: true,
          number: true,
        },
        transaction_id_to: {
          required: true,
          number: true,
        },
      },
      messages: {
        transaction_id_from: {
          required: "Please enter Transaction ID.",
          number: "Please enter a valid number.",
        },
        transaction_id_to: {
          required: "Please enter Transaction ID.",
          number: "Please enter a valid number.",
        },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
          $(element.form).find("input[type=radio]").each(function (which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
          $(element.form).find("input[type=radio]").each(function (which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
    });
    $('#renew-correction-form').validate({
      rules: {
        corrections: "required"
      },
      messages: {
        corrections: "Please enter correction ression"
      },
      submitHandler: function (form) {
          $('#submitform').prop('disabled', true);
          form.submit();
        }
    });
    $(document).on('click', '.rewal-correction', function () {
      var cStatus = $(this).attr('data-correction-status');
      if (cStatus == '0') {
        swal("Warning!", 'Correction request already submitted!', "warning");
      }
    });
    $(document).ajaxStart(function () {
      $(".loader").show();
    });
    $(document).ajaxComplete(function () {
      $(".loader").hide();
    });
  });
</script>