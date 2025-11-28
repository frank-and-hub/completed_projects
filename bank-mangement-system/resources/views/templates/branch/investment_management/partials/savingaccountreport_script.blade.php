<script type="text/javascript">
  var savingaccountreport;
  $('.table_hidden').hide();
  $(document).ready(function () {
    
    $('#filter').validate({
      rules: {
        member_id: {
          number: true,
        },
        associate_code: {
          number: true,
        },
        scheme_account_number: {
          //    number : true,
        },
        company_id: {
          required: true,
        }
      },
      messages: {
        member_id: {
          number: 'Please enter valid member id.'
        },
        associate_code: {
          number: 'Please enter valid associate code.'
        },
        scheme_account_number: {
          number: 'Please enter valid account number.'
        },
        company_id: {
          required: 'Please select company.'
        }
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
    $(document).on('change', '#branchid', function () {
      var bId = $('option:selected', this).attr('data-val');
      var sbId = $("#hbranchid option:selected").val();
      if (bId != sbId) {
        $('#branchid').val('');
        swal("Warning!", "Branch does not match from top dropdown state", "warning");
      }
    });
    // AJAX call for autocomplete 
    $("#member_name").keyup(function () {
      $.ajax({
        type: "POST",
        url: "{!! route('admin.investment.searchmember') !!}",
        data: 'keyword=' + $(this).val(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
          $("#search-box").css("background", "#FFF url(LoaderIcon.gif) no-repeat 165px");
        },
        success: function (data) {
          $("#suggesstion-box").show();
          $("#suggesstion-box").html(data);
          $("#member_name").css("background", "#FFF");
        }
      });
    });
    $(document).on('click', '.selectmember', function () {
      var val = $(this).attr('data-val');
      var account = $(this).attr('data-account');
      var id = $(this).attr('value');
      $("#member_name").val(val + ' - (' + account + ')');
      $("#member_id").val(id);
      $("#suggesstion-box").hide();
    });
    var date = new Date();
    $('#start_date').datepicker({
      format: "dd/mm/yyyy",
      orientation: "top",
      autoclose: true
    });
    $('#end_date').datepicker({
      format: "dd/mm/yyyy",
      orientation: "top",
      autoclose: true
    });
    $('input[name="start_date"]').on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });
    $('input[name="start_date"]').on('cancel.daterangepicker', function (ev, picker) {
      $(this).val('');
    });
    // Datatables
    savingaccountreport = $('#SavingAccountReport-listing').DataTable({
      processing: true,
      serverSide: true,
      paging: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('savingaccountreport.listing') !!}",
        "type": "POST",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        "data": function (d) {
          d.searchform = $('form#filter').serializeArray(),
            d.start_date = $('#start_date').val(),
            d.company_id = $('#company_id').val(),
            d.end_date = $('#end_date').val(),
            d.branch_id = $('#branch_id').val(),
            d.plan_id = $('#plan_id').val(),
            d.scheme_account_number = $('#scheme_account_number').val(),
            d.name = $('#name').val(),
            d.member_id = $('#member_id').val(),
            d.amount = $('#amount').val(),
            d.associate_code = $('#associate_code').val(),
            d.is_search = $('#is_search').val(),
            d.investments_export = $('#investments_export').val()
        },

      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'created_at', name: 'created_at' },
        { data: 'tran_by', name: 'tran_by' },
        { data: 'company', name: 'company' },
        { data: 'branch', name: 'branch' },
        // { data: 'branch_code', name: 'branch_code' },
        // { data: 'sector_name', name: 'sector_name' },
        // { data: 'region_name', name: 'region_name' },
        // { data: 'zone_name', name: 'zone_name' },
        { data: 'customer_id', name: 'customer_id' },
        { data: 'member_id', name: 'member_id' },
        { data: 'account_number', name: 'account_number' },
        { data: 'member', name: 'member' },
        {
          data: 'amount', name: 'amount',
          "render": function (data, type, row) {
            return row.amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        { data: 'associate_code', name: 'associate_code' },
        { data: 'associate_name', name: 'associate_name' },
        { data: 'payment_mode', name: 'payment_mode' },

        /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
      ]
    });
    $(savingaccountreport.table().container()).removeClass('form-inline');
    // Show loading image
    $(document).ajaxStart(function () {
      $(".loader").show();
    });
    // Hide loading image
    $(document).ajaxComplete(function () {
      $(".loader").hide();
    });
    $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');

            var formData = jQuery('#filter').serializeObject();

            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text(Math.floor(Math.random() * 10));
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1,'');
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, page,fileName) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            formData['fileName'] = fileName;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('savingaccountreport.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExport(start, limit, formData, chunkSize, page,fileName);
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
  });
  function printDiv(elem) {
    printJS({
      printable: elem,
      type: 'html',
      targetStyles: ['*'],
    })
  }
  function searchForm() {
    if ($('#filter').valid()) {
      $('#is_search').val("yes");
      savingaccountreport.draw();
      $('.table_hidden').show();
    }else{
      $('.table_hidden').hide();
    }
  }
  function resetForm() {
    $('.table_hidden').hide();
    $("#filter")[0].reset();
    $('#branch').empty();
    $('#amount').val('');
    $('#is_search').val("yes");
    $('.table-section').addClass('hideTableData');
    savingaccountreport.draw();
    /*
      $('#is_search').val("yes");
      $('#start_date').val('');
      $('#end_date').val('');
      $('#branch_id').val('');
      $('#plan_id').val('');
      $('#scheme_account_number').val('');
      $('#name').val('');
      $('#member_id').val('');
      $('#associate_code').val('');
      $('#amount_status').val('');
      savingaccountreport.draw();
    */
  }
</script>