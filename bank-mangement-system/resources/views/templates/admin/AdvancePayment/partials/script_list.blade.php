<script type="text/javascript">
  var memberTable;
  $(document).ready(function() {

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

    memberTable = $('#Advance_request').DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      shorting: false,
      language: {
        infoFiltered: ''
      },
      ordering: false,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#Advance_request').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.advancePayment.AdvancedRequestListing') !!}",
        "type": "POST",
        "data": function(d) {
          // d.searchform = $('form#filter').serializeArray(),
          d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch').val(),
            d.company_id = $('#company_id').val(),
            d.status = $('#status').val(),

            d.paymentType = $('#paymentType').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex'
        },

        {
          data: 'branch_id',
          name: 'branch_id'
        },

        {
          data: 'created_at',
          name: 'created_at'
        },

        {
          data: 'type',
          name: 'type'
        },

        {
          data: 'type_id',
          name: 'type_id'
        },


        {
          data: 'demand_amount',
          name: 'demand_amount'
        },

        {
          data: 'description',
          name: 'description'
        },

        {
          data: 'image',
          name: 'image'
        },

        {
          data: 'status',
          name: 'status'
        },

        {
          data: 'status_date',
          name: 'status_date'
        },

        {
          data: 'amount',
          name: 'amount'
        },

        {
          data: 'company',
          name: 'company'
        },

        {
          data: 'created_by',
          name: 'created_by'
        },

        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false
        },

        // {
        //   data: 'sub_type',
        //   name: 'sub_type'
        // },

        // {
        //   data: 'status_remark',
        //   name: 'status_remark'
        // },


        // {
        //   data: 'created_by_id',
        //   name: 'created_by_id'
        // },

        // {
        //   data: 'updated_at',
        //   name: 'updated_at'
        // },

      ]
    });
    $(memberTable.table().container()).removeClass('form-inline');

    memberInvestmentPaymentTable = $('#member_investment_payment_listing').DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      shorting: false,
      language: {
        infoFiltered: ''
      },
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#chequefilter').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.member.investmentchequepaymentlisting') !!}",
        "type": "POST",
        "data": function(d) {
          // d.searchform = $('form#filter').serializeArray(),
          d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch_id').val(),
            d.paymentType = $('#paymentType').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex'
        },
        {
          data: 's-branch',
          name: 's-branch'
        },
        {
          data: 'branch_code',
          name: 'branch_code'
        },
        {
          data: 'sector',
          name: 'sector'
        },
        {
          data: 'regan',
          name: 'regan'
        },
        {
          data: 'zone',
          name: 'zone'
        },
        {
          data: 'amount',
          name: 'amount'
        },
        {
          data: 'transaction_date',
          name: 'transaction_date'
        },
        {
          data: 'cheque_date',
          name: 'cheque_date'
        },
        {
          data: 'cheque_number',
          name: 'cheque_number'
        },
        {
          data: 'bank_name',
          name: 'bank_name'
        },
        {
          data: 'branch_name',
          name: 'branch_name'
        },
        {
          data: 'status',
          name: 'status'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false
        },
      ]
    });
    $(memberInvestmentPaymentTable.table().container()).removeClass('form-inline');



    // Export Function 
    $('.member_export').on('click', function(e) {
      e.preventDefault();
      var extension = $(this).attr('data-extension');

      $('#member_export').val(extension);
      if (extension == 0) {
        // return false;
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");
        doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);
      } else {

        $('#member_export').val(extension);

        $('form#filter').attr('action', "{!! route('admin.exportAdvanceRequestList') !!}");

        $('form#filter').submit();
      }
    });


    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;

      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.exportAdvanceRequestList') !!}",
        data: formData,
        success: function(response) {
          console.log(response);
          if (response.result == 'next') {
            start = start + chunkSize;
            doChunkedExport(start, limit, formData, chunkSize);
            $(".loaders").text(response.percentage + "%");
          } else {
            var csv = response.fileName;
            console.log('DOWNLOAD');
            $(".spiners").css("display", "none");
            $("#cover").fadeOut(100);
            window.open(csv, '_blank');
          }
        }
      });
    }

    // A function to turn all form data into a jquery object
    jQuery.fn.serializeObject = function() {
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

    $(document).ajaxStart(function() {
      $(".loader").show();
    });

    $(document).ajaxComplete(function() {
      $(".loader").hide();
    });


    $.validator.addMethod("dateDdMm", function(value, element, p) {

      if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
        $.validator.messages.dateDdMm = "";
        result = true;
      } else {
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;
      }

      return result;
    }, "");

    $('#filter').validate({
      rules: {
        start_date: {
          dateDdMm: true,
        },
        end_date: {
          dateDdMm: true,
        },
        member_id: {
          number: true,
        },
        associate_code: {
          number: true,
        },

      },
      messages: {
        member_id: {
          number: 'Please enter valid member id.'
        },
        associate_code: {
          number: 'Please enter valid associate code.'
        },
      },
      errorElement: 'span',
      errorPlacement: function(error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function(element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
          $(element.form).find("input[type=radio]").each(function(which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
          $(element.form).find("input[type=radio]").each(function(which) {
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
    });


    $('.export-req').on('click', function(e) {
      e.preventDefault();
      var extension = $(this).attr('data-extension');

      var formData = jQuery('#filter').serializeObject();

      var chunkAndLimit = 50;
      $(".spiners").css("display", "block");
      $(".loaders").text(Math.floor(Math.random() * 10));
      doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1);
      $("#cover").fadeIn(100);
    });
    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize, page) {
      formData['start'] = start;
      formData['limit'] = limit;
      formData['page'] = page;
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.exportAdvanceRequestList') !!}",
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.result == 'next') {
            start = start + chunkSize;
            page = page + 1;
            doChunkedExport(start, limit, formData, chunkSize, page);
            $(".loaders").text(response.percentage + "%");
          } else {
            var csv = response.fileName;
            console.log('DOWNLOAD');
            $(".spiners").css("display", "none");
            $("#cover").fadeOut(100);
            window.open(csv, '_blank');
          }
        }
      });
    }

  });

  function searchForm() {
    if ($('#filter').valid()) {
      $('#is_search').val("yes");
      $('.flisting').show();
      memberTable.draw();
    }
  }

  function resetForm() {
    var form = $("#filter"),
      validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('.flisting').hide();
    $('#is_search').val("no");
    $('#paymentType').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#status').val('');
    $('#start_date').val('');
    $('#company_id').val(0);
    $('#company_id').trigger('change');

    memberTable.draw();
  }

  function searchCheckForm() {
    if ($('#chequefilter').valid()) {
      $('#is_search').val("yes");
      memberInvestmentPaymentTable.draw();
    }
  }

  function resetCheckForm() {
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#branch_id').val('');
    $('#status').val('');
    memberInvestmentPaymentTable.draw();
  }


  $(document).on('keyup', '#member_id', function() {
    $('#show_mwmber_detail').html('');
    var code = $(this).val();
    if (code != '') {
      $.ajax({
        type: "POST",
        url: "{!! route('admin.member_blacklist_member_data') !!}",
        dataType: 'JSON',
        data: {
          'code': code
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.msg_type == "error2") {
            $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Member blocked!</strong> </div>');
          } else {
            if (response.msg_type == "success") {
              $('#show_mwmber_detail').html(response.view);
              //$('#id').val(response.id); 
            } else if (response.msg_type == "error1") {
              $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Member already associate!</strong> </div>');
            } else {
              $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found!</strong> </div>');
            }
          }
        }
      });
    }

  });



</script>