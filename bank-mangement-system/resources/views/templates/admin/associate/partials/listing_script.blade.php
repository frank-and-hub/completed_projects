<script type="text/javascript">
  $(document).ajaxStart(function () {
    $(".loader").show();
  });

  $(document).ajaxComplete(function () {
    $(".loader").hide();
  });
  var memberTable;
  $('.myopt').hide();
  $(document).ready(function () {
    $('#year').on('change', function () {
      $('#month').val($('#month_set').val());
      $('#month_set').val("");
      var selectedYear = $(this).val();
      $('#month option.myopt').each(function () {
        var allowedYears = $(this).data('year');
        if (allowedYears && allowedYears.includes(Number(selectedYear))) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    });
    $("#year").trigger("change");
    $('#commissionFilter').validate({
      rules: {
        associate_code: {
          number: true,
        },
        year: {
          required: true,
        },
        month: {
          required: true,
        },
      },
      messages: {
        associate_code: {
          number: 'Please enter valid associate code.'
        },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
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


    $('#kotabusinessFilter').validate({
      rules: {
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
      errorPlacement: function (error, element) {
        error.addClass(' ');
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


    var date = new Date();
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true,
      orientation: 'bottom',
    });

    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true,
      orientation: 'bottom',

    });

    memberTable = $('#member_listing').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#member_listing').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.associate_listing') !!}",
        "type": "POST",
        "data": function (d) {
          d.searchform = $('form#filter').serializeArray(),
            d.year = $('#year').val(),
            d.month = $('#month').val(),
            d.branch_id = $('#branch').val(),
            d.company_id = $('#company_id').val(),
            d.customer_id = $('#customer_id').val(),
            d.name = $('#name').val(),
            d.associate_code = $('#associate_code').val(),
            d.sassociate_code = $('#sassociate_code').val(),
            d.achieved = $('#achieved').val(),
            d.is_search = $('#is_search').val(),
            d.member_export = $('#member_export').val()
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
        data: 'branch',
        name: 'branch'
      },

      // {data: 'branch_code', name: 'branch_code'},
      {
        data: 'm_id',
        name: 'm_id'
      },
      {
        data: 'member_id',
        name: 'member_id'
      },
      {
        data: 'name',
        name: 'name'
      },
      {
        data: 'join_date',
        name: 'join_date'
      },
      {
        data: 'associate_code',
        name: 'associate_code'
      },
      {
        data: 'associate_name',
        name: 'associate_name'
      },
      // {data: 'sector_name', name: 'sector_name'},
      // {data: 'region_name', name: 'region_name'},
      // {data: 'zone_name', name: 'zone_name'},

      {
        data: 'dob',
        name: 'dob'
      },

      {
        data: 'nominee_name',
        name: 'nominee_name'
      },
      {
        data: 'relation',
        name: 'relation'
      },
      {
        data: 'nominee_age',
        name: 'nominee_age'
      },
      {
        data: 'email',
        name: 'email',
        orderable: true,
        searchable: true
      },
      {
        data: 'mobile_no',
        name: 'mobile_no'
      },

      {
        data: 'status',
        name: 'status'
      },
      {
        data: 'is_upload',
        name: 'is_upload'
      },
      // {data: 'achieved_target', name: 'achieved_target'},
      {
        data: 'address',
        name: 'address'
      },
      {
        data: 'state',
        name: 'state'
      },
      {
        data: 'district',
        name: 'district'
      },
      {
        data: 'city',
        name: 'city'
      },
      {
        data: 'village',
        name: 'village'
      },
      {
        data: 'pin_code',
        name: 'pin_code'
      },
      {
        data: 'firstId',
        name: 'firstId'
      },
      {
        data: 'secondId',
        name: 'secondId'
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      },
      ], "ordering": false,
    });
    $(memberTable.table().container()).removeClass('form-inline');

    associateCommissionTable = $('#associate-commission-listing').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#associate-commission-listing').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.associate.commissionlist') !!}",
        "type": "POST",
        "data": function (d) {
          d.searchform = $('form#commissionFilter').serializeArray(),
            d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch_id').val(),
            d.associate_code = $('#associate_code').val(),
            d.is_search = $('#is_search').val(),
            d.associate_name = $('#associate_name').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [{
        data: 'DT_RowIndex',
        name: 'DT_RowIndex'
      },
      // {
      //   data: 'company_name',
      //   name: 'company_name'
      // },
      {
        data: 'branch_name',
        name: 'branch_name'
      },

      {
        data: 'associate_name',
        name: 'associate_name'
      },
      {
        data: 'associate_code',
        name: 'associate_code'
      },
      {
        data: 'associate_carder',
        name: 'associate_carder'
      },
      {
        data: 'collection_amount_all',
        name: 'collection_amount_all',
        "render": function (data, type, row) {
          return row.collection_amount_all + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'collection_amount',
        name: 'collection_amount',
        "render": function (data, type, row) {
          return row.collection_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'commission_amount',
        name: 'commission_amount',
        "render": function (data, type, row) {
          return row.commission_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      },
      ], "ordering": false,
    });
    $(associateCommissionTable.table().container()).removeClass('form-inline');




    associateCommissionDetailTable = $('#associate-commission-detail').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#associate-commission-detail').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.associate.commissionDetaillist') !!}",
        "type": "POST",
        "data": function (d) {
          d.searchform = $('form#commissionFilterDetail').serializeArray(),
            d.year = $('#year').val(),
            d.month = $('#month').val(),
            d.plan_id = $('#plan_id').val(),
            d.is_search = $('#is_search').val(),
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
        "render": function (data, type, row) {
          return row.total_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'qualifying_amount',
        name: 'qualifying_amount',
        "render": function (data, type, row) {
          return row.qualifying_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'commission_amount',
        name: 'commission_amount',
        "render": function (data, type, row) {
          return row.commission_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
        }
      },
      {
        data: 'percentage',
        name: 'percentage',
        "render": function (data, type, row) {
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

      ], "ordering": false,
    });
    $(associateCommissionDetailTable.table().container()).removeClass('form-inline');


    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.associate.export') !!}");
        $('form#filter').submit();
        return true;
    });
  */
    $('.export').on('click', function (e) {

      e.preventDefault();
      var extension = $(this).attr('data-extension');
      $('#member_export').val(extension);
      if (extension == 0) {
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");
        doChunkedExportsa(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);
      } else {
        $('#member_export').val(extension);

        $('form#filter').attr('action', "{!! route('admin.associate.export') !!}");

        $('form#filter').submit();
      }
    });


    // function to trigger the ajax bit
    function doChunkedExportsa(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.associate.export') !!}",
        data: formData,
        success: function (response) {
          console.log(response);
          if (response.result == 'next') {
            start = start + chunkSize;
            doChunkedExportsa(start, limit, formData, chunkSize);
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
    jQuery.fn.serializeObject = function () {
      var o = {};
      var a = this.serializeArray();
      jQuery.each(a, function () {
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
    /*
    $('.exportcommission').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
        $('form#commissionFilter').attr('action',"{!! route('admin.associate.exportcommission') !!}");
        $('form#commissionFilter').submit();
        return true;
    });
  */
    $('.exportcommission').on('click', function (e) {

      e.preventDefault();
      var extension = $(this).attr('data-extension');
      $('#commission_export').val(extension);
      if (extension == 0) {
        var formData = jQuery('#commissionFilter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");
        doChunkedExports(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);
      } else {
        $('#commission_export').val(extension);

        $('form#commissionFilter').attr('action', "{!! route('admin.associate.exportcommission') !!}");

        $('form#commissionFilter').submit();
      }
    });


    // function to trigger the ajax bit
    function doChunkedExports(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.associate.exportcommission') !!}",
        data: formData,
        success: function (response) {
          console.log(response);
          if (response.result == 'next') {
            start = start + chunkSize;
            doChunkedExports(start, limit, formData, chunkSize);
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

    // A function to turn

    $('.exportkotabusiness').on('click', function () {
      var extension = $(this).attr('data-extension');
      $('#kotareport_export').val(extension);
      $('form#kotabusinessFilter').attr('action', "{!! route('admin.associate.exportkotabusiness') !!}");
      $('form#kotabusinessFilter').submit();
      return true;
    });


    /*
         $('.exportcommissionDetail').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#commission_export').val(extension); 
            $('form#commissionFilterDetail').attr('action',"{!! route('admin.associate.exportcommissionDetail') !!}");
            $('form#commissionFilterDetail').submit();
            return true;
        });
      */
    $('.exportcommissionDetail').on('click', function (e) {
      e.preventDefault();
      var extension = $(this).attr('data-extension');
      $('#commission_export').val(extension);
      if (extension == 0) {
        var formData = jQuery('#commissionFilterDetail').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");
        doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);
      } else {
        $('#commission_export').val(extension);

        $('form#commissionFilterDetail').attr('action', "{!! route('admin.associate.exportcommissionDetail') !!}");

        $('form#commissionFilterDetail').submit();
      }
    });


    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.associate.exportcommissionDetail') !!}",
        data: formData,
        success: function (response) {
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


    jQuery.fn.serializeObject = function () {
      var o = {};
      var a = this.serializeArray();
      jQuery.each(a, function () {
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


    $(document).on('keyup', '#associate_code', function () {
      if ($('#commissionFilter').valid()) {
        var associate_code = $(this).val();
        $.ajax({
          type: "POST",
          url: "{!! route('admin.associate.getAssociateCarder') !!}",
          dataType: 'JSON',
          data: {
            'associate_code': associate_code
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            if (response != 0) {

              $("#cader_id option[value=" + response.carder.current_carder_id + "]").prop("selected", true);
            }
          }
        })
      }
    });

    $(document).ajaxStart(function () {
      $(".loader").show();
    });

    $(document).ajaxComplete(function () {
      $(".loader").hide();
    });




    $.validator.addMethod("dateDdMm", function (value, element, p) {

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
      errorPlacement: function (error, element) {
        error.addClass(' ');
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


  });

  function searchForm() {
    if ($('#filter').valid()) {
      $('#is_search').val("yes");
      $(".table-section").removeClass('hideTableData');
      memberTable.draw();
    }
  }

  function searchCommissionForm() {
    if ($('#commissionFilter').valid()) {
      $('#is_search').val("yes");
      $(".table-section").removeClass('hideTableData');
      associateCommissionTable.draw();
    }
  }



  function searchCommissionDetailForm() {
    if ($('#commissionFilterDetail').valid()) {
      $('#is_search').val("yes");
      associateCommissionDetailTable.draw();
    }
  }

  function resetForm() {
    $('#company_id').val('');
    $('#member_name').val('');
    $('#name').val('');
    $('#associate_code').val('');
    $('#branch').val('');
    $('#year').val('');
    $('#customer_id').val('');
    $('#year').trigger('change');
    $('#sassociate_code').val('');
    $('#achieved').val('');
    $(".table-section").addClass("hideTableData");
    memberTable.draw();
  }

  function resetCommissionForm() {
    $('#is_search').val("no");
    $('#member_name').val('');
    $('#name').val('');
    $('#company_id').val(0);
    $('#company_id').trigger('change');
    $('#associate_code').val('');
    $('#branch_id').val('');
    $('#year').val('');
    $('#year').trigger('change');
    $('#start_date').val('');
    $('#associate_name').val('');
    $('#achieved').val('');
    $(".table-section").addClass("hideTableData");
    associateCommissionTable.draw();
  }

  function resetCommissionDetailForm() {
    $('#is_search').val("yes");
    $('#year').val('{{$year}}');
    $('#month').val('{{$month}}');
    $('#plan_id').val(' ');
    associateCommissionDetailTable.draw();
  }


</script>