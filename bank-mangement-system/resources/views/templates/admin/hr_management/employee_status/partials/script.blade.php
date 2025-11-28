<script type="text/javascript">
  var EmployeeStatusTable;
  $(document).on('keyup', '#employee_code', function () {
    $('#employee_detail').hide();
    $('#employee_detail').html('');
    var employee_code = $(this).val();
    if (employee_code != '') {
      $.ajax({
        type: "POST",
        url: "{!! route('admin.hr.employeestatus.status_check') !!}",
        dataType: 'JSON',
        data: { 'employee_code': employee_code, 'type': 'status' },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          console.log(response);
          if (response.msg_type == "success") {
            $('#employee_detail').show();
            if(response.is_resigne==1)
            {
              $('#employee_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not change status of resign employee.</strong> </div>');
            }
            else if (response.is_terminate==1)
            {
              $('#employee_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not change status of terminated employee.</strong> </div>');
            }
            
            else{
              $('#status').val(response.status);
              $('#employee_detail').show();
              $('#employee_detail').html(response.view);
              $('#Change_status').prop('disabled', false);
            }
              
          }
          else 
          {
            $('#employee_detail').show();
			      $('#Change_status').prop('disabled', true);
            if (response.msg_type == "error2") {
              $('#employee_detail').html('<div class="alert alert-danger alert-block">  <strong>Employee Blocked!</strong> </div>');
            }
            else {
              $('#employee_detail').html('<div class="alert alert-danger alert-block">  <strong>Employee not found!</strong> </div>');
            }
          }
        },error: function () {
			$('#Change_status').prop('disabled', true);
		}
      });
    }
  });
  $(document).ready(function () {
    EmployeeStatusTable = $('#employee_status_listing').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      sorting: false,
      bFilter: false,
      ordering: false,
      "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        var oSettings = this.fnSettings();
        $('html, body').stop().animate({
          scrollTop: ($('#employee_status_listing').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.hr.employeestatus.listing') !!}",
        "type": "POST",
        "data": function (d) {
          d.searchform = $('form#filter').serializeArray(),
            d.employee_code = $('#employee_code').val(),
            d.is_search = $('#is_search').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'company_name', name: 'company_name' },
        { data: 'branch', name: 'branch' },
        { data: 'name', name: 'name' },
        { data: 'code', name: 'code' },      
        { data: 'designation', name: 'designation' },
        { data: 'status', name: 'status' },
      ],"ordering": false
    });
    $('#filter').validate({ // initialize the plugin
      rules: {
        'employee_code': 'required',
      },
      messages: {
        employee_code: {
          required: 'Please Enter Employee Code',
        },
      },
    });
    $(EmployeeStatusTable.table().container()).removeClass('form-inline');
    $('.export').on('click', function (e) {
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
        url: "{!! route('admin.hr.employeestatus.export') !!}",
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
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
  });
  $(document).ajaxStart(function () {
    $(".loader").show();
  });
  $(document).ajaxComplete(function () {
    $(".loader").hide();
  });
  function resetForm() {
    $('#employee_detail').hide();
    $('#employee_code').val('');
  }
</script>