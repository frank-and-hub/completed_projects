<script type="text/javascript">
    var designationTable;
$(document).ready(function () {
 

     designationTable = $('#designation_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#designation_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.hr.designation_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'designation_name', name: 'designation_name'},
            {data: 'category', name: 'category'},
            {data: 'gross_salary', name: 'gross_salary'},
            {data: 'basic_salary', name: 'basic_salary'},
            {data: 'daily_allowances', name: 'daily_allowances'},
            {data: 'hra', name: 'hra'}, 
            {data: 'hra_metro_city', name: 'hra_metro_city'},
            {data: 'uma', name: 'uma'},
            {data: 'convenience_charges', name: 'convenience_charges'},
            {data: 'maintenance_allowance', name: 'maintenance_allowance'},
            {data: 'communication_allowance', name: 'communication_allowance'},
            {data: 'prd', name: 'prd'},
            {data: 'ia', name: 'ia'},
            {data: 'ca', name: 'ca'},
            {data: 'fa', name: 'fa'},
            {data: 'pf', name: 'pf'},
            {data: 'tds', name: 'tds'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(designationTable.table().container()).removeClass( 'form-inline' );

 
    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#designation_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.hr.designation_export') !!}");
        $('form#filter').submit();
        return true;
    }); 
*/
 $('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#designation_export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	

    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.hr.designation_export') !!}",
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


    $('#filter').validate({
      rules: {
      //  status:"required",  

      },
       messages: {  
     //     status: "Please select status",
      },
        
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
  });
 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        designationTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#status').val('');

    designationTable.draw();
}
function deleteDesignation(id)
{
   date=$('#create_application_date').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to delete this designation",
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
                    if (isConfirm) {

                        $('.confirm_delete').attr('disabled',true);
                      $('.cancel_delete').attr('disabled',true);


                          
                          window.location.href = "{{url('admin/hr/designation/delete/')}}/"+id;
                    }
                  });
}
</script>