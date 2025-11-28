<script type="text/javascript">
    var employeeTable;

   
    $(document).ready(function () {
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,  
            endDate: date, 
            autoclose: true
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true, 
            endDate: date,  
            autoclose: true
        });
     employeeTable = $('#salary_leaser').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#salary_leaser').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.hr.salary_leaser_listing') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(),
                d.company_id=$('#company_id').val(), 
                d.month=$('#month').val(),
                d.year=$('#year').val(), 
                d.status=$('#status').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
             {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
             {data: 'company_name', name: 'company_name'},
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
    $(employeeTable.table().container()).removeClass( 'form-inline' );

 /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#emp_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.hr.salary_ledger_export') !!}");
        $('form#filter').submit();
        return true;
    }); 
     */
	  $('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#emp_export').val(extension);
		
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
            url :  "{!! route('admin.hr.salary_ledger_export') !!}",
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
        company_id:"required",  

      },
       messages: {  
        company_id: "Please select company_name",
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

 

$(document).on('change','#category',function(){ 
    var category=$('#category').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategorySalary') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#designation').find('option').remove();
                $('#designation').append('<option value="">Select Designation</option>');
                $('#designation').append('<option value="all">All</option>');
                 $.each(response.data, function (index, value) { 
                        $("#designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                        
                    }); 

              }
          });

  });
 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        employeeTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
    $('#branch').val('');
    $('#category').val('');
    $('#designation').val('');
    $('#month').val('');
    $('#year').val('');
    $('#company_id').val(''); 
    $('#status').val('active');
    $(".table-section").addClass("hideTableData");
    employeeTable.draw();
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
            url: "{!! route('admin.hr.salary.salary_ledger_delete') !!}",
            dataType: 'JSON',
            data: {'id':id,'date':date,'datetime':datetime},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) { 
            // alert(response.msg);
            if(response.data==1) 
            {
                employeeTable.draw();
                swal("Success!", response.msg, "success");
            }
            else if(response.data==2) 
            {
                employeeTable.draw();
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

$("#year").change(function(){
    var thisYear = $(this).val();
    const months = ["none","January","February","March","April","May","June","July","August","September","October","November","December"];
    const d = new Date();//d.getMonth()
    $("#month").html('<option value="">Select  Month</option>');
        if(thisYear === '2021'){
            for(i = 4; i <= 12; i++){
                $("#month").append('<option value="'+i+'">'+months[i]+'</option>');
            }
        }else if(thisYear != ''){
            for(i = 1; i <= 12; i++){
                $("#month").append('<option value="'+i+'">'+months[i]+'</option>');
            }
        }
})
</script>