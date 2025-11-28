<script type="text/javascript">
  var memberTable;
  $(document).on('keyup', '#account_number', function () {
    $('#account_detail').hide();
    $('#account_detail').html('');
    var account_number = $(this).val();
    if (account_number != '') {
      $.ajax({
        type: "POST",
        url: "{!! route('admin.investment.ssbaccountstatus.status_check') !!}",
        dataType: 'JSON',
        data: { 'account_number': account_number, 'type': 'status' },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          console.log(response);
          if (response.msg_type == "success") {
            $('#transaction_status').val(response.transaction_status);
            $('#account_detail').show();
            $('#account_detail').html(response.view);
			$('#Change_status').prop('disabled', false);
          }
          else {
            $('#account_detail').show();
			$('#Change_status').prop('disabled', true);
            if (response.msg_type == "error2") {
              $('#account_detail').html('<div class="alert alert-danger alert-block">  <strong>Account Blocked!</strong> </div>');
            }
            else {
              $('#account_detail').html('<div class="alert alert-danger alert-block">  <strong>Account not found!</strong> </div>');
            }
          }
        },error: function () {
			$('#Change_status').prop('disabled', true);
		}
      });
    }
  });
  $(document).ready(function () {
    memberTable = $('#ssb_member_account_listing').DataTable({
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
          scrollTop: ($('#ssb_member_account_listing').offset().top)
        }, 1000);
        $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
        return nRow;
      },
      ajax: {
        "url": "{!! route('admin.investment.ssbaccountstatus.listing') !!}",
        "type": "POST",
        "data": function (d) {
          d.searchform = $('form#filter').serializeArray(),
            d.account_number = $('#account_number').val(),
            d.is_search = $('#is_search').val()
          d.member_export = $('#member_export').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'account_no', name: 'account_no' },
        { data: 'branch', name: 'branch' },
        { data: 'branch_code', name: 'branch_code' },
        { data: 'member_id', name: 'member_id' },
        { data: 'member_name', name: 'member_name' },
        { data: 'current_balance', name: 'current_balance' },
        { data: 'transaction_status', name: 'transaction_status' },
      ],"ordering": false
    });
    $('#filter').validate({ // initialize the plugin
      rules: {
        'account_number': 'required',
      },
      messages: {
        account_number: {
          required: 'Please Enter Account Number',
        },
      },
    });
    $(memberTable.table().container()).removeClass('form-inline');
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
        url: "{!! route('admin.investment.ssbaccountstatus.export') !!}",
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
    /*
     $('.export').on('click',function(e){
      e.preventDefault();
      var extension = $(this).attr('data-extension');
          $('#member_export').val(extension);
      if(extension == 0)
      {
          var formData = jQuery('#filter').serializeObject();
          var chunkAndLimit = 50;
      $(".spiners").css("display","block");
      $(".loaders").text("0%");
          doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
      $("#cover").fadeIn(100);
      }
      else{
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.investment.ssbaccountstatus.export') !!}");
        $('form#filter').submit();
      }
    });
    // function to trigger the ajax bit
      function doChunkedExport(start,limit,formData,chunkSize){
          formData['start']  = start;
          formData['limit']  = limit;
          jQuery.ajax({
              type : "post",
              dataType : "json",
              url :  "{!! route('admin.investment.ssbaccountstatus.export') !!}",
              data : formData,
              success: function(response) {
                  console.log(response);
                  if(response.result=='next'){
                      start = start + chunkSize;
                      doChunkedExport(start,limit,formData,chunkSize);
            $(".loaders").text(response.percentage+"%");
                  }else{
            var csv = response.fileName;
                      console.log('DOWNLOAD');
            $(".spiners").css("display","none");
            $("#cover").fadeOut(100); 
            window.open(csv, '_blank');
                  }
              }
          });
      }
    */
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
    $('#account_detail').hide();
    $('#account_number').val('');
  }
</script>