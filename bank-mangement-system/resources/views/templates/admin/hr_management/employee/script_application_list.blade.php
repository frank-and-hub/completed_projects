<script type="text/javascript">
    var empApplicationTable;
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

      empApplicationTable = $('#emp_application_listing').DataTable({
        processing: true,
       
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#emp_application_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.hr.employee_application') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},
            {data: 'application_type', name: 'application_type'},
            {data: 'designation', name: 'designation'},
            {data: 'category', name: 'category'},
            {data: 'branch', name: 'branch'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector', name: 'sector'},
            // {data: 'regan', name: 'regan'},
            // {data: 'zone', name: 'zone'},
            {data: 'rec_employee_name', name: 'rec_employee_name'}, 
            {data: 'employee_name', name: 'employee_name'},
            {data: 'dob', name: 'dob'},
            {data: 'gender', name: 'gender'},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'email', name: 'email'},
            {data: 'guardian_name', name: 'guardian_name'},
            {data: 'guardian_number', name: 'guardian_number'},
            {data: 'mother_name', name: 'mother_name'},
            {data: 'pen_card', name: 'pen_card'},
            {data: 'aadhar_card', name: 'aadhar_card'},
            {data: 'voter_id', name: 'voter_id'},
            {data: 'esi', name: 'esi'},
            {data: 'pf', name: 'pf'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(empApplicationTable.table().container()).removeClass( 'form-inline' );

 
    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#emp_application_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.hr.employee_application_export') !!}");
        $('form#filter').submit();
        return true;
    }); 
*/
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#emp_application_export').val(extension);
		
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
            url :  "{!! route('admin.hr.employee_application_export') !!}",
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

});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        empApplicationTable.draw();
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
    $('#branch').val('');
    $('#company_id').val('0');
    $('#category').val('');
    $('#designation').val('');
    $('#employee_name').val(''); 
    $('#reco_employee_name').val('');
    $('#status').val(0);
    $('#app_type_resign').prop('checked', false);
    $('#app_type_register').prop('checked', false);
    $(".table-section").addClass("hideTableData");
    empApplicationTable.draw();
}
function deleteApplication(id,type)
{
   date=$('#create_application_date').val();
   datetime=$('#created_at').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to delete application",
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
                          url: "{!! route('admin.hr.delete_employee_application') !!}",
                          dataType: 'JSON',
                          data: {'id':id,'type':type,'date':date,'datetime':datetime},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                           // alert(response.msg);
                            if(response.data==1) 
                            {
                              empApplicationTable.draw();
                                swal("Success!", "Application Deleted Successfully.", "success");
                            }
                            else
                            {
                                swal("Sorry!", "Something Went Wrong! Try Again.", "error");
                            }

                          }
                        });
                      }
                  });
}
function applicationApproved(id,type)
{
   date=$('#create_application_date').val();
   datetime=$('#created_at').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to Approve application",
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
                          url: "{!! route('admin.hr.approve_employee_application') !!}",
                          dataType: 'JSON',
                          data: {'id':id,'type':type,'date':date,'datetime':datetime},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                           // alert(response.msg);
                            if(response.data==1) 
                            {
                              empApplicationTable.draw();
                                swal("Success!", "Application Approved Successfully.", "success");
                            }
                            else
                            {
                                swal("Sorry!", "Something Went Wrong! Try Again.", "error");
                            }

                          }
                        });
                      }
                  });
}
function rejectApplication(id)
{
   date=$('#create_application_date').val();
   datetime=$('#created_at').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to reject application",
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
                          url: "{!! route('admin.hr.reject_employee_application') !!}",
                          dataType: 'JSON',
                          data: {'id':id,'date':date,'datetime':datetime},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                           // alert(response.msg);
                            if(response.data==1) 
                            {
                              empApplicationTable.draw();
                                swal("Success!", "Application Rejected Successfully.", "success");
                            }
                            else
                            {
                                swal("Sorry!", "Something Went Wrong! Try Again.", "error");
                            }

                          }
                        });
                      }
                  });
}
</script>