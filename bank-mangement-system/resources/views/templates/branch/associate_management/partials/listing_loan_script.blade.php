<script type="text/javascript">
  $(document).ajaxStart(function() {
    $(".loader").show();
  });

  $(document).ajaxComplete(function() {
    $(".loader").hide();
  });
  var memberTable;
  $(document).ready(function() {



    var date = new Date();
    var Startdate = new Date();
    Startdate.setMonth(Startdate.getMonth() - 3);
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      startDate: Startdate,
      endDate: date,
      autoclose: true
    });

    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true
    });







    associateCommissionDetailTable = $('#associate-commission-detail').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#associate-commission-detail').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('branch.associate.commissionDetaillistLoan') !!}",
        "type": "POST",
        "data": function(d) {
          d.searchform = $('form#commissionFilterDetail').serializeArray(),
            d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.is_search = $('#is_search').val(),
            d.commission_export = $('#commission_export').val(),
            d.id = $('#id').val()
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
          data: 'month',
          name: 'month'
        },
        {
          data: 'account_number',
          name: 'account_number'
        },
        {
          data: 'plan_name',
          name: 'plan_name'
        },
        {
          data: 'total_amount',
          name: 'total_amount',
          "render": function(data, type, row) {
            return row.total_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'qualifying_amount',
          name: 'qualifying_amount',
          "render": function(data, type, row) {
            return row.qualifying_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'commission_amount',
          name: 'commission_amount',
          "render": function(data, type, row) {
            return row.commission_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'percentage',
          name: 'percentage',
          "render": function(data, type, row) {
            return row.percentage + "%";
          }
        },
        {
          data: 'carder_from',
          name: 'carder_from'
        },
        {
          data: 'carder_to',
          name: 'carder_to'
        },

      ]
    });
    $(associateCommissionDetailTable.table().container()).removeClass('form-inline');






    /*
         $('.exportcommissionDetail').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#commission_export').val(extension); 
            $('form#commissionFilterDetail').attr('action',"{!! route('branch.associate.exportcommissionDetailLoan') !!}");
            $('form#commissionFilterDetail').submit();
            return true;
        });
    */
    $('.exportcommissionDetail').on('click', function(e) {

      e.preventDefault();
      var extension = $(this).attr('data-extension');
      $('#commission_export').val(extension);
      var month = $("#month").val();
      var year = $("#year").val();
      var plan_id = $("#plan_id").val();
      if (extension == 0) {
       
        var formData = jQuery('#commissionFilterDetail').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");

        doChunkedExportel(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);
      } else {
        $('#commission_export').val(extension);

        $('form#commissionFilterDetail').attr('action', "{!! route('branch.associate.exportcommissionDetailLoan') !!}");

        $('form#commissionFilterDetail').submit();
      }

    });

    function doChunkedExportel(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;


      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('branch.associate.exportcommissionDetailLoan') !!}",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function(response) {
          console.log(response);
          if (response.result == 'next') {
            start = start + chunkSize;
            doChunkedExportel(start, limit, formData, chunkSize);
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
        associate_code: {
          number: true,
        },

      },
      messages: {
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


  });





  function searchCommissionDetailForm() {
    if ($('#commissionFilterDetail').valid()) {
      $('#is_search').val("yes");
      associateCommissionDetailTable.draw();
    }
  }




  function resetCommissionDetailForm() {
    $('#is_search').val("no");
    $('#month').val('');
    $('#year').val('');
    $('#plan_id').val('');
    associateCommissionDetailTable.draw();
  } 
  $('#year').on('change', function() {
    $('#month').val($('#month_set').val());
    $('#month_set').val("");
    var selectedYear = $(this).val();
    $('#month option.myopt').each(function() {
      var allowedYears = $(this).data('year');
      if (allowedYears && allowedYears.includes(Number(selectedYear))) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
  
  $('#year').trigger('change');
</script>