<script type="text/javascript">
var transfer_listing;
$(document).ready(function() {
    var investId='';
    transfer_listing = $('#transfer-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate.commission.leaserList') !!}",
            "type": "POST",          
            "data":function(d) {
                d.searchform=$('form#leaserFilter').serializeArray(),
                d.company_id=$('#company_id').val(),   
                d.commission_export=$('#leaser_export').val()
            },

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            
            {data: 'company_name', name: 'company_name'},
            {data: 'start', name: 'start'},
            {data: 'end', name: 'end'},
            {data: 'total', name: 'total',
                "render":function(data, type, row){
                 return row.total+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },

            {data: 'ledgerAmount', name: 'ledgerAmount',
                "render":function(data, type, row){
                 if(row.ledgerAmount)
                    {
                         return row.ledgerAmount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                    else
                    {
                        return '';
                    }
                }
              },

            {data: 'credit', name: 'credit',
            "render":function(data, type, row){
                if ( row.credit ) {
                    return row.credit+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
              {data: 'total_fuel', name: 'total_fuel',
                "render":function(data, type, row){
                 if(row.total_fuel)
                    {
                         return row.total_fuel+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                    else
                    {
                        return '';
                    }
                }
              },

              {data: 'credit_fuel', name: 'credit_fuel',
                "render":function(data, type, row){
                    if(row.credit_fuel)
                    {
                         return row.credit_fuel+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }
                    else
                    {
                        return '';
                    }
                
                }
              },
            {data: 'status', name: 'status'},
            {data: 'created', name: 'created'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
/*
    $('.leaser').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#leaser_export').val(extension);      
        $('form#leaserFilter').attr('action',"{!! route('admin.associate.leaserExport') !!}");
        $('form#leaserFilter').submit();
        return true;
    });

*/





    var investId='';
    transfer_listingDetail = $('#transfer-listing-detail').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.associate.leaserList') !!}",
            "type": "POST",
            "data":{'id':investId}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'start', name: 'start'},
            {data: 'end', name: 'end'},
            {data: 'total', name: 'total'},
            {data: 'credit', name: 'credit'},
            {data: 'status', name: 'status'},
            {data: 'created', name: 'created'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
/*
    $('.leaser').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#leaser_export').val(extension);      
        $('form#leaserFilter').attr('action',"{!! route('admin.associate.leaserExport') !!}");
        $('form#leaserFilter').submit();
        return true;
    });
	*/
	$('.leaser').on('click',function(e){
	
		e.preventDefault();
		var extension = $(this).attr('data-extension');
		
        $('#leaser_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#leaserFilter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#leaser_export').val(extension);

			$('form#leaserFilter').attr('action',"{!! route('admin.commission.associate.leaserExport') !!}");

			$('form#leaserFilter').submit();
		}
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
    
        formData['limit']  = limit;
	
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.commission.associate.leaserExport') !!}",
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
});
function leaserDelete(id)
{
   
    swal({
                    title: "Are you sure?",
                    text: "You want to delete Ledger",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary le_confirm_de",
                    confirmButtonText: "Yes, Delete",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger le_cancel_de",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) {

                        $('.le_confirm_de').attr('disabled',true);
                      $('.le_cancel_de').attr('disabled',true);


                          $.ajax({
                          type: "POST",  
                          url: "{!! route('admin.associate.laserdelete') !!}",
                          dataType: 'JSON',
                          data: {'id':id},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) {
                         // alert(response); 
                                if(response==0)
                                {
                                    transfer_listing.draw();
                                    swal("Success", "Ledger delete successfully!", "success");
                                }
                                else
                                {
                                      swal("Error", "Something went wrong.Try again!", "warning");
                                }

                            }
                          });
                    }
                  });
}


function searchForm()
{  
     
        transfer_listing.draw();
   
}

function resetForm()
  { 
    $('#company_id').val('');  
    transfer_listing.draw();
}
</script>

