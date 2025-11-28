<script type="text/javascript">
  var adminBusinessTable;

  $(document).ready(function() {

    $('#start_date').on('blur', function() {
      var default_date = $('#default_date').val();
      $('#start_date').val(default_date);
    });
    $('#end_date').on('blur', function() {
      var default_date = $('#default_date').val();
      $('#end_date').val(default_date);
    });

    let branchid = $("#hbranchid option:selected").val();
    var date = new Date();
    var currentDate = new Date();

    // Subtract 3 months
    currentDate.setMonth(currentDate.getMonth() - 3);

    // Check if the year needs to be adjusted
    if (currentDate.getMonth() > new Date().getMonth()) {
      currentDate.setFullYear(currentDate.getFullYear() - 1);
    }

    // Get the updated year, month, and day
    var year = currentDate.getFullYear();
    var month = currentDate.getMonth(); // Note: Months are zero-based (0 for January, 11 for December)
    var day = currentDate.getDate();

    // Store the updated date in a variable
    var updatedDate = new Date(year, month, day);
    const currentDatee = $("#created_at").val();
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      startDate: "01/01/2019", // Set the start date to three months ago
      endDate: date,
      autoclose: true,
      orientation: 'bottom'
    });
    //.datepicker('setDate', currentDate).datepicker('fill')

    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      todayHighlight: true,
      endDate: date,
      autoclose: true,
      orientation: 'bottom'
    });
    //.datepicker('setDate', currentDate).datepicker('fill')
    adminBusinessTable = $('#admin_bussiness_listing').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 10,
      lengthMenu: [10, 20, 40, 100,200],

      "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#admin_bussiness_listing').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },

      ajax: {
        "url": "{!! route('admin.report.mother_branch_business_listing') !!}",
        "type": "POST",
        "data": function(d) {
          console.log(d);

          d.branchid = branchid,
            d.searchform = $('form#filter').serializeArray(),
            d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch').val(),
            d.is_search = $('#is_search').val(),
            d.export = $('#export').val(),
            d.company_id = $('#company_id').val(),
            d.length = d.length

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
          data: 'company_name',
          name: 'company_name'
        },
        {
          data: 'branch',
          name: 'branch'
        },
        {
          data: 'branch_code',
          name: 'branch_code'
        },

        {
          data: 'daily_new_ac',
          name: 'daily_new_ac'
        },
        {
          data: 'daily_deno_sum',
          name: 'daily_deno_sum'
        },

        {
          data: 'daily_renew_ac',
          name: 'daily_renew_ac'
        },
        {
          data: 'daily_renew',
          name: 'daily_renew'
        },

        {
          data: 'monthly_ncc_ac',
          name: 'monthly_ncc_ac'
        },
        {
          data: 'monthly_ncc_amt',
          name: 'monthly_ncc_amt'
        },

        {
          data: 'monthly_renew_ac',
          name: 'monthly_renew_ac'
        },
        {
          data: 'monthly_renew_amt',
          name: 'monthly_renew_amt'
        },

        {
          data: 'fd_new_ac',
          name: 'fd_new_ac'
        },
        {
          data: 'fd_deno_sum',
          name: 'fd_deno_sum'
        },

        {
          data: 'ssb_ncc_ac',
          name: 'ssb_ncc_ac'
        },
        {
          data: 'ssb_ncc_amt',
          name: 'ssb_ncc_amt'
        },

        {
          data: 'ssb_ren_ac',
          name: 'ssb_ren_ac'
        },
        {
          data: 'ssb_ren_amt',
          name: 'ssb_ren_amt'
        },

        {
          data: 'other_mi',
          name: 'other_mi'
        },
        {
          data: 'other_stn',
          name: 'other_stn'
        },

        {
          data: 'new_mi_joining',
          name: 'new_mi_joining'
        },
        {
          data: 'new_associate_joining',
          name: 'new_associate_joining'
        },

        {
          data: 'banking_ac',
          name: 'banking_ac'
        },
        {
          data: 'banking_amt',
          name: 'banking_amt'
        },

        // {data: 'total_payment', name: 'total_payment'},
        // {data: 'total_expense', name: 'total_expense'}, 
        {
          data: 'total_withdrawal',
          name: 'total_withdrawal'
        },
        {
          data: 'total_payment',
          name: 'total_payment'
        },

        {
          data: 'ncc',
          name: 'ncc'
        },
        {
          data: 'ncc_ssb',
          name: 'ncc_ssb'
        },
        {
          data: 'tcc',
          name: 'tcc'
        },
        {
          data: 'tcc_ssb',
          name: 'tcc_ssb'
        },

        {
          data: 'loan_ac_no',
          name: 'loan_ac_no'
        },
        {
          data: 'loan_amt',
          name: 'loan_amt'
        },

        {
          data: 'loan_recv_ac_no',
          name: 'loan_recv_ac_no'
        },
        {
          data: 'loan_recv_amt',
          name: 'loan_recv_amt'
        },

        {
          data: 'loan_aginst_ac_no',
          name: 'loan_aginst_ac_no'
        },
        {
          data: 'loan_aginst_amt',
          name: 'loan_aginst_amt'
        },

        {
          data: 'loan_aginst_recv_ac_no',
          name: 'loan_aginst_recv_ac_no'
        },
        {
          data: 'loan_aginst_recv_amt',
          name: 'loan_aginst_recv_amt'
        },


        {
          data: 'cash_in_hand',
          name: 'cash_in_hand'
        },


        /*  {data: 'action', name: 'action',orderable: false, searchable: false},*/

      ]

    });
    $(document).ajaxStart(function() {
      $(".loader").show();
    });

    $(document).ajaxComplete(function() {
      $(".loader").hide();
    });


    $('.export').on('click', function(e) {
      e.preventDefault();
      var extension = $(this).attr('data-extension');
      $('#emp_application_export').val(extension);

      var formData = jQuery('#filter').serializeObject();
      var chunkAndLimit = 50;
      $(".spiners").css("display", "block");
      $(".loaders").text("0%");
      doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
      $("#cover").fadeIn(100);
    });


    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
      formData['start'] = start;
      formData['limit'] = limit;
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: "{!! route('admin.motherbranch_business.report.export') !!}",
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


    $(document).on('change', '#zone', function() {

      var zone = $('#zone').val();

      $('#region').find('option').remove();

      $('#sector').find('option').remove();

      $('#branch_id').find('option').remove();

      $.ajax({

        type: "POST",

        url: "{!! route('admin.report.branchRegionByZone') !!}",

        dataType: 'JSON',

        data: {
          'zone': zone
        },

        headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        },

        success: function(response) {

          $('#region').find('option').remove();

          $('#region').append('<option value="">Select Region</option>');

          $.each(response.data, function(index, value) {
            if (value.regan != null) {

              $("#region").append("<option value='" + value.regan + "'>" + value.regan + "</option>");
            }

          });

        }

      });
    });

    $(document).on('change', '#region', function() {

      var region = $('#region').val();
      $.ajax({
        type: "POST",
        url: "{!! route('admin.report.branchSectorByRegion') !!}",
        dataType: 'JSON',
        data: {
          'region': region
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {

          $('#sector').find('option').remove();

          $('#sector').append('<option value="">Select Sector </option>');

          $.each(response.data, function(index, value) {
            if (value.sector != null) {
              $("#sector").append("<option value='" + value.sector + "'>" + value.sector + "</option>");
            }
          });
        }
      });
    });

    $(document).on('change', '#sector', function() {

      var sector = $('#sector').val();

      $.ajax({
        type: "POST",
        url: "{!! route('admin.report.branchBySector') !!}",
        dataType: 'JSON',
        data: {
          'sector': sector
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          $('#branch_id').find('option').remove();
          $('#branch_id').append('<option value="">Select Branch </option>');
          $.each(response.data, function(index, value) {
            $("#branch_id").append("<option value='" + value.id + "'>" + value.name + "(" + value.branch_code + ")</option>");
          });
        }
      });
    });



    $.validator.addMethod("currentdate", function(value, element, p) {
      moment.defaultFormat = "DD/MM/YYYY HH:mm";
      var f1 = moment($('#start_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var f2 = moment($('#end_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var from = new Date(Date.parse(f1));
      var to = new Date(Date.parse(f2));
      if (to >= from) {
        $.validator.messages.currentdate = "";
        result = true;
      } else {
        $.validator.messages.currentdate = "To date must be greater than current from date.";
        result = false;
      }
      return result;

    }, "")



    $.validator.addMethod("dateDdMm", function(value, element, p) {
      if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
        $.validator.messages.dateDdMm = "";
        result = true;
      } else {
        $.validator.messages.dateDdMm = "Please enter valid date.";
        result = false;
      }
      return result;
    }, "");

    $('#filter').validate({
      rules: {
        start_date: {
          "required": true,
          dateDdMm: function(element) {
            if ($("#start_date").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        end_date: {
          "required": true,
          dateDdMm: function(element) {
            if ($("#end_date").val() != '') {
              return true;
            } else {
              return false;
            }
          },
          currentdate: function(element) {
            if ($("#end_date").val() != '') {
              return true;
            } else {
              return false;
            }
          },
        },
        branch_id: {
          "required": true,
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

  function searchForm() {
    if ($('#filter').valid()) {
      $('#is_search').val("yes");
      $(".table-section").removeClass('datatable');
      adminBusinessTable.draw();
    }
  }

  function resetForm() {
    var form = $("#filter"),
      validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    const currentDate = $("#created_at").val();
    $('#start_date').val('');
    $('#end_date').val('');
    $('#branch_id').val('');
    $('#company_id').val('0');
    $('#zone').val('');
    $('#region').val('');
    $('#sector').val('');
    $('#is_search').val('no');

    var sector = '';

    // $.ajax({

    //     type: "POST",

    //     url: "{!! route('admin.report.branchBySector') !!}",

    //     dataType: 'JSON',

    //     data: {'sector':sector},

    //     headers: {

    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

    //     },

    //     success: function(response) {

    //       $('#branch_id').find('option').remove();

    //       $('#branch_id').append('<option value="">Select Branch </option>');

    //        $.each(response.data, function (index, value) {
    //            $("#branch_id").append("<option value='"+value.id+"'>"+value.name+"("+value.branch_code+")</option>");

    //           });
    //     }
    // });
    adminBusinessTable.draw();
    $(".table-section").addClass("datatable");
  }
</script>