<script type="text/javascript">


 $('document').ready(function(){
$('#start_date').datepicker({
        //format: "dd/mm/yyyy",
			format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
	
	$('#ends_date').datepicker({
        //format: "dd/mm/yyyy",
			format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
	
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
           // required: true,
            dateDdMm : true,
          }, 
         /* branch :{ 
           required: true,
          },*/  

      },
      messages: {  
          start_date:{ 
            required: "Please enter date.",
          },
          branch:{ 
            required: 'Please select branch.'
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

// $('.submit').on('click',function(){
//   location.reload();
// })
$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

  
 detailList = $('#bank_wise_detail').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings ();

                $('html, body').stop().animate({

                    scrollTop: ($('#bank_wise_detail').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.balance-sheet.bank_wise_transaction_list') !!}",

                "type": "POST",

                "data":function(d,oSettings) {
					if(oSettings.json != null)
				   {
					$('#total_balance').val(oSettings.json.total);
					// var total = oSettings.json.total;
				   }
				   else{
					 $('#total_balance').val(0);
				   }
				   
				   var page = ($('#bank_wise_detail').DataTable().page.info());
					var currentPage  = page.page+1;
					d.pages = currentPage,
								   
                	d.searchform=$('form#filter').serializeArray(),
                	d.head= $('#head_id').val(),
                	d.branch= $('#branch_filter').val(),
                	d.label= $('#label').val(),
                    d.date= $('#start_date').val(),
					d.end_date = $('#ends_date').val()
					d.total=$('#total_balance').val()
                		
            	},

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

            }, 

            columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'member_id', name: 'member_id'},
            {data: 'member_name', name: 'member_name'},
            
            {data: 'account_number', name: 'account_number'},
            {data: 'transaction_type', name: 'transaction_type'},  
            {data: 'cr', name: 'cr'}, 
            {data: 'dr', name: 'dr'},  
			{data: 'balance', name: 'balance'},  
            ],"ordering": false,

        });

        $(detailList.table().container()).removeClass( 'form-inline' );  
});



$('.export_report').on('click',function(){
	var extension = $(this).attr('data-extension');
	$('#export').val(extension);
	$('form#filter').attr('action',"{!! route('admin.balance_sheet.bank_wise.export') !!}");
	$('form#filter').submit();
	return true;
});




function searchbanktransaction()
{  
    
        $('#is_search').val("yes");

    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
	var end_date = $('#ends_date').val();  
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/bank_wise/{{$head}}/{{$label}}?"+queryParams;
    
}

function resetbanktransaction()
{
    location.reload();
    $('#is_search').val("no");  
    $('#branch').val(""); 
    
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#default_start_date').val();  
	var end_date = $('#default_end_date').val();  
	//console.log(start_date);
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/bank_wise/{{$head}}/{{$label}}?"+queryParams;
}


</script>