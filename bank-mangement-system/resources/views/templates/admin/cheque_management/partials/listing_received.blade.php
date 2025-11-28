<script type="text/javascript">
    var chequeTable;
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

  

  // $('.cheque_date').datepicker({
  //   dateFormat: "dd/mm/yyyy",
  //   todayHighlight: true,  
  //   endDate: date, 
  //   autoclose: true
  // });

     chequeTable = $('#cheque_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#cheque_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.received.cheque_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},           
            // {data: 'branch_name', name: 'branch_name'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'cheque_create_date', name: 'cheque_create_date'},
            {data: 'cheque_no', name: 'cheque_no'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'bank_branch_name', name: 'bank_branch_name'},
            {data: 'account_holder_name', name: 'account_holder_name'},
            {data: 'cheque_account_no', name: 'cheque_account_no'},
            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
              },
            {data: 'cheque_deposit_date', name: 'cheque_deposit_date'},
            {data: 'deposit_bank_id', name: 'deposit_bank_id'},
            {data: 'deposit_account_id', name: 'deposit_account_id'},
            {data: 'used_date', name: 'used_date'},
            {data: 'clearing_date', name: 'clearing_date'},
            {data: 'status', name: 'status'}, 
            {data: 'remark', name: 'remark'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(chequeTable.table().container()).removeClass( 'form-inline' );

 

/*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#cheque_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.received.cheque.export') !!}");
        $('form#filter').submit();
        return true;
    }); 
*/
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#cheque_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#cheque_export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.received.cheque.export') !!}");

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
            url :  "{!! route('admin.received.cheque.export') !!}",
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


$.validator.addMethod("dateDdMm", function(value, element,p) {
     
      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
      {
        $.validator.messages.dateDdMm = "";
        result = true;
      }else{
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;  
      }
    
    return result;
  }, "");

$('#filter').validate({
      rules: {
        start_date:{ 
            dateDdMm : true,
            
          },
          end_date:{
            dateDdMm : true,
          },
          company_id:{
            required : true,
          },

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
  //alert('s');
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        chequeTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#status').val(1); 
    $('#branch').val('');
    $('#company_id').val('0');
    $('#end_date').val('');
    $('#start_date').val('');
    $(".table-section").addClass("hideTableData");
    chequeTable.draw();
}
function approve(id)
{
   date=$('#create_application_date').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to approve cheque",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary confirm_approve",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger cancel_approve",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) {

                        $('.confirm_approve').attr('disabled',true);
                        $('.cancel_approve').attr('disabled',true);


                          
                          window.location.href = "{{url('admin/received/cheque/approved')}}/"+id+"?date="+date;
                    }
                  });
}

function setDate(id)
{   
    date=$('#create_application_date').val();
    jQuery(".sweet-alert .form-group .sa-input-error").css("display","none");
    swal({
          title: "Are you sure?",
          text: "You want to approve cheque",
          type: "warning",
          content: "input",
          showCancelButton: true,
          confirmButtonClass: "btn-primary confirm_approve",
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          cancelButtonClass: "btn-danger cancel_approve",
          closeOnConfirm: false,
          animation: "slide-from-top",
          inputPlaceholder: "dd/mm/yyyy"
        },
        function(isConfirm) {
           var inputValue = $('#mydate').val();
            if (isConfirm && inputValue === "") {
                jQuery(".sweet-alert .form-group .sa-input-error").css("display","block");
                jQuery(".sa-help-text").css("color","red");
                swal.showInputError("You need to add cheque clear date!");

                return false
              }
            if (isConfirm) {
                $('.confirm_approve').attr('disabled',true);
                $('.cancel_approve').attr('disabled',true);
                jQuery.ajax({
                    type : "post",
                    dataType : "json",
                    url :  "{!! route('admin.received.cheque_approved_custom') !!}",
                    data : {"input" : inputValue, "id" : id, "date" : date },
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        jQuery(".sweet-alert .form-group").css("display","none");
                        jQuery(".sweet-alert .form-group .sa-input-error").css("display","none");
                        jQuery(".sweet-alert .form-group input.form-control").removeAttr("id");
                        if(response.msg == 'success'){
                            swal("Success!", "Cheque Clear date: " + response.clear_date, "success");
                        }else{
                            swal("Fail!", "Cheque Clear date: " + response.clear_date, "error");
                        }
                    }
                });
            }
            chequeTable.draw();
            
    });
    jQuery(".sweet-alert .form-group").css("display","block");
    
    jQuery(".sweet-alert .form-group input.form-control").attr("id","mydate");
    jQuery("#mydate").attr("placeholder","Select date here");

    var date = new Date();
       $('#mydate').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true, 
        minDate: date, 
        autoclose: true
  });

}


    

 function delete_cheque(id)
{
   date=$('#create_application_date').val();
    swal({
                    title: "Are you sure?",
                    text: "You want to delete cheque",
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


                          
                          window.location.href = "{{url('admin/received/cheque/delete')}}/"+id+"?date="+date;
                    }
                  });
}
</script>