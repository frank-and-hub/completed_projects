<script type="text/javascript">
   var memberTable;
$(document).on('keyup','#associate_code',function(){

    $('#associate_detail').html('');
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.associate_dataGetAll_app') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'status'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#associate_detail').show();
                  $('#associate_detail').html(response.view);                             
                  
                }
                else
                {

                  if(response.msg_type=="error2")
                  { 
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate Blocked!</strong> </div>');
                  }
                  else
                  {
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>');
                  }
                  
                  
                }
              }
          });
    } 



  });

$(document).ready(function () {

memberTable = $('#member_listing').DataTable({
         processing: true,
         serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#member_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.app_inactive_associate_listing') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(),
                d.name=$('#name').val(),
                d.associate_code=$('#associate_code').val(),
                d.sassociate_code=$('#sassociate_code').val(),
                d.achieved=$('#achieved').val()
                d.is_search=$('#is_search').val()
                d.member_export=$('#export').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'join_date', name: 'join_date'},
            {data: 'branch', name: 'branch'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},

            {data: 'member_id', name: 'member_id'},
            
            {data: 'associate_no', name: 'associate_no'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email',orderable: true, searchable: true},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'status', name: 'status'}, 
            {data: 'app_status', name: 'app_status'},  
        ],"ordering": false,
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );
/*
$('.export').on('click',function(){
        var extension = $(this).attr('data-extension'); 
		console.log(extension,'extension')
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.app_inactive_associate.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	$('.export').on('click',function(e){
	
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

			$('form#filter').attr('action',"{!! route('admin.app_inactive_associate.export') !!}");

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
            url :  "{!! route('admin.app_inactive_associate.export') !!}",
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
 
 
 


$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


    function resetForm()
    {   var form = $("#filter1"),
        validator = form.validate();
        validator.resetForm();
        $('#associate_detail').hide();
        $('#associate_code').val('');
        $(".table-section").removeClass("show-table");
         $(".table-section").addClass("hide-table"); 
    }

</script>