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

        employeeTable = $('#emp_transfer_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#emp_transfer_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.hr.employee_transfer') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},
            {data: 'old_branch', name: 'old_branch'}, 
            {data: 'new_branch', name: 'new_branch'}, 
            {data: 'apply_date', name: 'apply_date'},
            {data: 'employee_code', name: 'employee_code'},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'old_designation', name: 'old_designation'},
            {data: 'old_category', name: 'old_category'}, 
            
            // {data: 'old_branch_code', name: 'old_branch_code'},
            // {data: 'old_sector', name: 'old_sector'},
            // {data: 'old_regan', name: 'old_regan'},
            // {data: 'old_zone', name: 'old_zone'},
            {data: 'rec_employee_name_old', name: 'rec_employee_name_old'},
            {data: 'transfer_date', name: 'transfer_date'}, 
            // {data: 'branch', name: 'branch'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector', name: 'sector'},
            // {data: 'regan', name: 'regan'},
            // {data: 'zone', name: 'zone'},
            {data: 'designation', name: 'designation'},
            {data: 'category', name: 'category'}, 
            {data: 'rec_employee_name', name: 'rec_employee_name'}, 
            {data: 'file', name: 'file',
                // "render":function(data, type, row){
                // if ( row.file ) {
                //     return " <a href='{{url('/')}}/asset/employee/transfer/"+row.file+"'  target='_blank' >"+row.file+" </a>";
                // } else {
                //     return "";
                // }
                // }
              }, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(employeeTable.table().container()).removeClass( 'form-inline' );

 
    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#emp_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.hr.employee_transfer_export') !!}");
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
	
	
	
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.hr.employee_transfer_export') !!}",
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

    $(document).on('change','#category',function(){ 
    var category=$('#category').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategory') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#designation').find('option').remove();
                $('#designation').append('<option value="">Select Designation</option>');
                 $.each(response.data, function (index, value) { 
                        $("#designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                    }); 

              }
          });

  });
  $(document).on('keyup','#employee_code',function(){ 
    var code=$('#employee_code').val();
    $('#employee_transfer_time').val('');
          $.ajax({
              type: "POST",  
              url: "{!! route('admin.trasnsferCount') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg==1)
                {
                    $('#employee_transfer_time').val(response.count);
                } 

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
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#end_date').val(''); 
    $('#category').val('');
    $('#designation').val('');
    $('#employee_name').val('');
    $('#employee_code').val('');
    $('#reco_employee_name').val('');
    $('#employee_transfer_time').val('');
    $(".table-section").addClass("hideTableData");
    employeeTable.draw();
}

</script>