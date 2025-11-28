<script type="text/javascript">
var rentLedger;
var rentLedgerReport;
var rentLedgerlib;
$(document).ready(function() {





    rentLedger = $('#rent-ledger-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.ledger.list') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.rent_month=$('#rent_month').val(),
                d.rent_year=$('#rent_year').val(), 
                d.is_search=$('#is_search').val(),
                d.status=$('#status').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'month', name: 'month'},
            {data: 'year', name: 'year'}, 
            {data: 'total_amount', name: 'total_amount', 
               "render":function(data, type, row){
                    if ( row.total_amount>=0 ) {
                        return row.total_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'tds_amount', name: 'tds_amount', 
               "render":function(data, type, row){
                    if ( row.tds_amount>=0 ) {
                        return row.tds_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },{data: 'payable_amount', name: 'payable_amount', 
               "render":function(data, type, row){
                    if ( row.payable_amount>=0 ) {
                        return row.payable_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'transferred_amount', name: 'transferred_amount', 
                "render":function(data, type, row){
                    if ( row.transferred_amount>=0 ) {
                        return row.transferred_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'transfer_charge', name: 'transfer_charge', 
                "render":function(data, type, row){
                    if ( row.transfer_charge>=0 ) {
                        return row.transfer_charge+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'neft', name: 'neft', 
                "render":function(data, type, row){
                    if ( row.neft>=0 ) {
                        return row.neft+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(rentLedger.table().container()).removeClass( 'form-inline' );




    rentLedgerReport = $('#rent-ledger-table-report').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.rent.leger-report-list') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.branch_id=$('#branch_id').val(), 
                d.is_search=$('#is_search').val(),
                d.status=$('#status').val(),
                d.export=$('#export').val()
                d.ledger_id=$('#ledger_id').val()
            
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
            {data: 'rent_type', name: 'rent_type'},
            {data: 'period_from', name: 'period_from'},
            {data: 'period_to', name: 'period_to'},
            {data: 'address', name: 'address'},
            {data: 'owner_name', name: 'owner_name'},
            {data: 'owner_mobile_number', name: 'owner_mobile_number'},
            {data: 'owner_pen_card', name: 'owner_pen_card'},
            {data: 'owner_aadhar_card', name: 'owner_aadhar_card'},
            {data: 'owner_ssb_account', name: 'owner_ssb_account'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'ifsc_code', name: 'ifsc_code'},
            {data: 'security_amount', name: 'security_amount', 
                "render":function(data, type, row){
                    if ( row.security_amount>=0 ) {
                        return row.security_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'yearly_increment', name: 'yearly_increment'},
            {data: 'office_area', name: 'office_area'},

            {data: 'rent', name: 'rent', 
                "render":function(data, type, row){
                    if ( row.rent>=0 ) {
                        return row.rent+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },

            {data: 'actual', name: 'actual', 
                "render":function(data, type, row){
                    if ( row.actual>=0 ) {
                        return row.actual+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
        
            {data: 'tds_amount', name: 'tds_amount', 
                "render":function(data, type, row){
                    if ( row.tds_amount>=0 ) {
                        return row.tds_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'transfer', name: 'transfer', 
                "render":function(data, type, row){
                    if ( row.transfer>=0 ) {
                        return row.transfer+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }
                }
            },
            {data: 'advance', name: 'advance', 
                "render":function(data, type, row){
                   
                    if ( row.advance>=0 ) {
                        return row.advance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    } else {
                        return "N/A";
                    }
                    
                }
            },
            {data: 'settle', name: 'settle', 
                "render":function(data, type, row){
                    if(row.settle>=0)
                    {
                        return row.settle+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }

                    
                }
            },

            {data: 'status', name: 'status'},
            
            {data: 'transfer_date', name: 'transfer_date'},
            {data: 'mode', name: 'mode'},
            {data: 'v_no', name: 'v_no'},
            {data: 'v_date', name: 'v_date'},
            {data: 'bank', name: 'bank'},
            {data: 'bank_ac', name: 'bank_ac'},
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'cheque', name: 'cheque'},
            {data: 'online_no', name: 'online_no'},
            {data: 'neft', name: 'neft', 
                "render":function(data, type, row){
                    if(row.neft>=0)
                    {
                        return row.neft+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return "N/A";
                    }

                }
            },

            {data: 'employee_code', name: 'employee_code'},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'employee_designation', name: 'employee_designation'},
            {data: 'mobile_number', name: 'mobile_number'}, 
        ]
    });
    $(rentLedgerReport.table().container()).removeClass( 'form-inline' );






    rentLedgerlib = $('#rent-ledgerLib-table-report').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.lib_ledger.list') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.liability=$('#liability').val()
            
            },              
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'date', name: 'date'},
        /*    {data: 'owner_name', name: 'owner_name'},
            {data: 'owner_mobile_number', name: 'owner_mobile_number'}, */
            {data: 'description', name: 'description'},
            {data: 'reference_no', name: 'reference_no'},
            {data: 'amount', name: 'amount', 
               "render":function(data, type, row){
                    if ( row.amount>0 ) {
                        return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return " ";
                    }
                }
            },
            // {data: 'deposit', name: 'deposit', 
            //     "render":function(data, type, row){
            //         if ( row.deposit>0 ) {
            //             return row.deposit+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            //         }else {
            //             return " ";
            //         }
            //     }
            // },
            // {data: 'opening_balance', name: 'opening_balance', 
            //     "render":function(data, type, row){
            //         if ( row.opening_balance>=0 ) {
            //             return row.opening_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            //         }else {
            //             return "N/A";
            //         }
            //     }
            // },
            {data: 'payment_type', name: 'payment_type',},
            {data: 'payment_mode', name: 'payment_mode',},
        ]
    });
    $(rentLedgerlib.table().container()).removeClass( 'form-inline' );


/*
$('.export').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	
	$('.export').on('click',function(e){
		
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExportm(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExportm(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.ledger.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExportm(start,limit,formData,chunkSize);
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
$('.export_report').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.ledger_report.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	
$('.export_report').on('click',function(e){
	
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#report_export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.ledger_report.export') !!}",
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






    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        rentLedger.draw();
    }
}
function resetForm()
{
    $('#is_search').val("no");
    $('#rent_year').val("");
    $('#rent_month').val('');
    $('#status').val('');
    $(".table-section").addClass("hideTableData");
    rentLedger.draw();
}

function searchFormReport()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        rentLedgerReport.draw();
    }
}
function resetFormReport()
{
    $('#is_search').val("no");
    $('#branch_id').val("");
    $('#status').val('');
    rentLedgerReport.draw();
}

function deleteLedger(id)
{
   date=$('#create_application_date').val();
   datetime=$('#created_at').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to delete ledger",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary confirm_delete",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger cancel_delete",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) 
                    {

                      $('.confirm_delete').attr('disabled',true);
                      $('.cancel_delete').attr('disabled',true);

                      $.ajax({
                          type: "POST",  
                          url: "{!! route('admin.rent.leger-delete') !!}",
                          dataType: 'JSON',
                          data: {'id':id,'date':date,'datetime':datetime},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                           // alert(response.msg);
                            if(response.data==1) 
                            {
                              rentLedger.draw();
                                swal("Success!", response.msg, "success");
                            }
                            else if(response.data==2) 
                            {
                              rentLedger.draw();
                                swal("Success!", response.msg, "success");
                            }
                            else
                            {
                                swal("Sorry!", response.msg, "error");
                            }

                          }
                        });
                      }
                  });
}


</script>