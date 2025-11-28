<script type="text/javascript">
	$(document).ready(function(){
		
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
            "url": "{!! route('admin.rent.payment_leger-report-list') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.branch_id=$('#branch_id').val(), 
                d.company_id=$('#company_id').val(), 
                d.month=$('#month').val(), 
                d.year=$('#year').val(), 
                d.rent_type=$('#rent_type').val(), 
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
            {data: 'rentCompany', name: 'rentCompany'}, 
            {data: 'branch', name: 'branch'}, 
             {data: 'month', name: 'month'},
              {data: 'year', name: 'year'},
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
                    if(row.tds_amount>=0)
                    {
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
    $(rentLedgerReport.table().container()).removeClass( 'form-inline' )

    $('.export').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.payment.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });
/*
$('.export_report').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.payment.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });
*/

$('.export_report').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
		
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
            url :  "{!! route('admin.payment.ledger.export') !!}",
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

    
	})

function searchFormReport()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        rentLedgerReport.draw();
    }
}
function resetFormReport()
{
    $('#is_search').val("no");
    $('#company_id').val('0').trigger('change');
    $('#status').val('');
    $('#month').val("");
    $('#year').val('');
    $(".table-section").addClass("hideTableData");
    rentLedgerReport.draw();
}
</script>