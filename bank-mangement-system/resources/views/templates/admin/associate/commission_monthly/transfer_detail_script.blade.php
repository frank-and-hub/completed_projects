<script type="text/javascript">
var transfer_listing; 
$(document).ready(function() {


    var investId='{{ $detail->id }}';
    transfer_listing = $('#transfer-listing-detail').DataTable({
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
            "url": "{!! route('admin.associate.commission.leaserDetailList') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#leaserDetailFilter').serializeArray(),
                d.associate_code=$('#associate_code').val(), 
                d.company_id=$('#company_id').val(), 
                d.is_search=$('#is_search').val(),
                d.commission_export=$('#leaserDetail_export').val(),
                d.id=$('#id').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'code', name: 'code'},
            {data: 'name', name: 'name'},
            {data: 'carder', name: 'carder'},
            {data: 'pan', name: 'pan'},
            {data: 'amount_tds', name: 'amount_tds',
                "render":function(data, type, row){
                 return row.amount_tds+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
            {data: 'tds', name: 'tds',
                "render":function(data, type, row){
                 return row.tds+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
              {data: 'total', name: 'total',
                "render":function(data, type, row){
                 return row.total+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
              {data: 'collection', name: 'collection',
                "render":function(data, type, row){
                 return row.collection+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
              
            {data: 'fuel', name: 'fuel',
                "render":function(data, type, row){
                 return row.fuel+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },

            {data: 'account', name: 'account'},
            {data: 'status', name: 'status'},
            {data: 'created', name: 'created'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
/*
    $('.leaserDetail').on('click',function(){
        var extension = $(this).attr('data-extension');
     //   alert(extension);
        $('#leaserDetail_export').val(extension);      
        $('form#leaserDetailFilter').attr('action',"{!! route('admin.associate.leaserDetailExport') !!}");
        $('form#leaserDetailFilter').submit();
        return true;
    });
	*/
	$('.leaserDetail').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#leaserDetail_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#leaserDetailFilter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#leaserDetail_export').val(extension);

			$('form#leaserDetailFilter').attr('action',"{!! route('admin.associate.commission.leaserDetailExport') !!}");

			$('form#leaserDetailFilter').submit();
		}
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.associate.commission.leaserDetailExport') !!}",
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



function searchCommissionDetailForm()
{  
    if($('#leaserDetailFilter').valid())
    {
        $('#is_search').val("yes");
        transfer_listing.draw();
    }
}
function resetCommissionDetailForm()
  {
    $('#is_search').val("yes");
    $('#associate_code').val('');  
    transfer_listing.draw();
}
</script>

