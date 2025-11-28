<script type="text/javascript">
  // Reset Button
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
    $('#settlement').val('');
    $('#start_date').val('');
    $('#company_id').val(0);
    $('#company_id').trigger('change');
    memberTable.draw();
  }

  // Search Fillter
  function searchForm() {
    if ($('#filter').valid()) {
      $('#is_search').val("yes");
      $('.flisting').show();
      memberTable.draw();
    }
  }



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
      ordering: false,
      language: {
        infoFiltered: ''
      },
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
        "url": "{!! route('admin.advancePayment.PaymentListing') !!}",
        "type": "POST",
        "data": function(d) {
          // d.searchform = $('form#filter').serializeArray(),
          d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch').val(),
            d.company_id = $('#company_id').val(),
            d.settlement = $('#settlement').val(),
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
          data: 'date',
          name: 'date'
        },

        {
          data: 'type',
          name: 'type'
        },

        {
          data: 'name',
          name: 'name'
        },

        {
          data: 'amount',
          name: 'amount'
        },
        {
          data: 'description',
          name: 'description'
        },

        {
          data: 'settled_amount',
          name: 'settled_amount'
        },

        {
          data: 'excess',
          name: 'excess'
        },

        {
          data: 'payment_settlement',
          name: 'payment_settlement'
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
        //   data: 'branch_code',
        //   name: 'branch_code'
        // },

        // {
        //   data: 'sub_type',
        //   name: 'sub_type'
        // },

      ]
    });


  });


  // Export Function 
  $('.payment_export').on('click', function(e) {
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
      url: "{!! route('admin.exportAdvancePaymentList') !!}",
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
</script>