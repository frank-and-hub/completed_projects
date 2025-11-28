<script type="text/javascript">
var accountHeadLedgerReport;
$(document).ready(function() {
	
	$('#start_date').datepicker({
        format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
	$('#ends_date').datepicker({
		format: "dd/mm/yyyy",
        orientation: "bottom"
	})

accountHeadLedgerReport = $('#account_head_ledger_report').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        bFilter:false,
        ordering:false,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#account_head_ledger_report').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.account_head.ledger.list') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray(),
                d.export=$('#export').val(),
                d.head_id  = $('#head').val(),
                d.label  = $('#label').val(),
                d.date  = $('#start_date').val(),
				d.end_date  = $('#ends_date').val(),
                d.branch  = $('#branch').val()
              
        }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'}, 
            // {data: 'sector', name: 'sector'}, 
            // {data: 'regan', name: 'regan'},
            // {data: 'zone', name: 'zone'}, 
            {data: 'type', name: 'type'}, 
            {data: 'description', name: 'description'},
            {data: 'amount', name: 'amount', 
                "render":function(data, type, row){
                        if ( row.amount>=0 ) {
                            return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        }else {
                            return "N/A";
                        }
                    }
                },
             {data: 'ac', name: 'ac'}, 
            {data: 'member_name', name: 'member_name'}, 
           // {data: 'associate_name', name: 'associate_name'},
            {data: 'payment_type', name: 'payment_type'}, 
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'voucher_no', name: 'voucher_no'}, 
            {data: 'voucher_date', name: 'voucher_date'},
            {data: 'cheque_no', name: 'cheque_no'}, 
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'utr_transaction_number', name: 'utr_transaction_number'}, 
            {data: 'transaction_date', name: 'transaction_date'},
            // {data: 'bank_name', name: 'bank_name'}, 
            // {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'received_bank', name: 'received_bank'}, 
            {data: 'received_bank_account', name: 'received_bank_account'},
            {data: 'date', name: 'date'},
        ],"ordering": false
    });
    $(accountHeadLedgerReport.table().container()).removeClass( 'form-inline' );
	
	accountHeadLedgerReport = $('#transaction_report').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#transaction_report').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.account_head.ledger.transaction_list') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray(),
                d.export=$('#export').val(),
                d.head_id  = $('#head').val(),
                d.label  = $('#label').val(),
                d.date  = $('#date').val(),
                d.end_date  = $('#ends_date').val(),
                d.branch  = $('#branch').val()
              
        }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'}, 
            {data: 'sector', name: 'sector'}, 
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'}, 
            {data: 'type', name: 'type'}, 
            {data: 'description', name: 'description'},
            {data: 'amount', name: 'amount', 
                "render":function(data, type, row){
                        if ( row.amount>=0 ) {
                            return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                        }else {
                            return "N/A";
                        }
                    }
                },
             {data: 'ac', name: 'ac'}, 
            {data: 'member_name', name: 'member_name'}, 
           // {data: 'associate_name', name: 'associate_name'},
            {data: 'payment_type', name: 'payment_type'}, 
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'voucher_no', name: 'voucher_no'}, 
            // {data: 'voucher_date', name: 'voucher_date'},
            {data: 'cheque_no', name: 'cheque_no'}, 
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'utr_transaction_number', name: 'utr_transaction_number'}, 
            // {data: 'transaction_date', name: 'transaction_date'},
            // {data: 'bank_name', name: 'bank_name'}, 
            // {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'received_bank', name: 'received_bank'}, 
            {data: 'received_bank_account', name: 'received_bank_account'},
            {data: 'date', name: 'date'},
        ],"ordering": false
    });
    $(accountHeadLedgerReport.table().container()).removeClass( 'form-inline' );
    
    
  /*  
 $('.export_report').on('click',function(){
     
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
           $('form#filter').attr('action',"{!! route('admin.account_head.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });
*/
$('.export_report').on('click',function(e){

		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}else{
			$('#export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.account_head.ledger.export') !!}");

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
            url :  "{!! route('admin.account_head.ledger.export') !!}",
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
	

    jQuery.fn.serializeObject = function(){
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
	/*
$('.export_report_trans').on('click',function(){
     
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
           $('form#filter').attr('action',"{!! route('admin.account_head_transcation.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });
*/
$('.export_report_trans').on('click',function(e){

		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExportk(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}else{
			$('#export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.account_head_transcation.ledger.export') !!}");

			$('form#filter').submit();
		}
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExportk(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.account_head_transcation.ledger.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExportk(start,limit,formData,chunkSize);
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
	

    jQuery.fn.serializeObject = function(){
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

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});



function searchForm()
{  
	$('#is_search').val("yes");

	var branch=$('#branch').val(); 
	var is_search=$('#is_search').val(); 
	var start_date=$('#start_date').val();
	var end_date=$('#ends_date').val(); 
	var queryParams = new URLSearchParams(window.location.search);
	 
	// Set new or modify existing parameter value. 
	queryParams.set("date", start_date);
	queryParams.set("end_date", end_date);
	queryParams.set("branch", branch);
	 
	// Replace current querystring with the new one.
	window.location.href = "{{url('/')}}/admin/account_head_ledger/{{$head}}/{{$label}}?"+queryParams;
}
function resetForm()
{ 
    $('#start_date').val('');
    $('#ends_date').val(''); 
    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/account_head_ledger/{{$head}}/{{$label}}";
}

function searchtransactionForm()
{  
    $('#is_search').val("yes");

    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();
    var end_date=$('#ends_date').val(); 
    var queryParams = new URLSearchParams(window.location.search);
     
    // Set new or modify existing parameter value. 
    queryParams.set("date", start_date);
    queryParams.set("end_date", end_date);
    queryParams.set("branch", branch);
     
    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/account_head_ledger/transaction/{{$head}}/{{$label}}?"+queryParams;
}
function resettransactionForm()
{ 
    $('#start_date').val('');
    $('#ends_date').val(''); 
    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/account_head_ledger/transaction/{{$head}}/{{$label}}";
}





</script>