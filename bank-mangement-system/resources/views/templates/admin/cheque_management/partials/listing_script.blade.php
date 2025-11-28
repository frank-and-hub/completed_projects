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
            "url": "{!! route('admin.cheque_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            
            {data: 'cheque_create_date', name: 'cheque_create_date'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'account_no', name: 'account_no'},
            {data: 'cheque_no', name: 'cheque_no'},
            {data: 'use', name: 'use'},
            {data: 'status', name: 'status'},
            {data: 'cheque_delete_date', name: 'cheque_delete_date'},
            {data: 'cheque_cancel_date', name: 'cheque_cancel_date'},
            {data: 'remark_cancel', name: 'remark_cancel'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(chequeTable.table().container()).removeClass( 'form-inline' );

 
     /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#cheque_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.cheque.export') !!}");
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

			$('form#filter').attr('action',"{!! route('admin.cheque.export') !!}");

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
            url :  "{!! route('admin.cheque.export') !!}",
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
          cheque_no: { 
            number: true, 
          },
          company_id: { 
            required: true, 
          },
          bank_id: { 
            required: true, 
          },
          account_id: { 
            required: true, 
          },   
   

      },
       messages: {  
        
          cheque_no: { 
            number : "Please enter a valid number.",
          },
          company_id: { 
          required : "Please select company name.",
          },
          bank_id: { 
          required : "Please select bank name.",
          },
          account_id: { 
          required : "Please select account no.",
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
    $('#status').val('');
    $('#cheque_no').val('');
    $('#account_id').val('');
    $('#bank_id').val('');
    $('#end_date').val('');
    $('#start_date').val(''); 
    $('#company_id').val('0');
    $('#company_id').change();
    $(".table-section").addClass("hideTableData");
    chequeTable.draw();
}
 
 $(document).on('change','#bank_id',function(){ 
    var bank_id=$('#bank_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_account_list') !!}",
              dataType: 'JSON',
              data: {'bank_id':bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#account_id').find('option').remove();
                $('#account_id').append('<option value="">Select account number</option>');
                 $.each(response.account, function (index, value) { 
                        $("#account_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
                    }); 

              }
          });

  });
  $(document).on('change','#company_id',function(){ 
    $("#account_id").val('');
    var company_id=$('#company_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_list_by_company') !!}",
              dataType: 'JSON',
              data: {'company_id':company_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#bank_id').find('option').remove();
                $('#bank_id').append('<option value="">Select Bank</option>');
                $.each(response.bankList, function (index, value) { 
                      $("#bank_id").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                }); 

              }
          });

  });
</script>