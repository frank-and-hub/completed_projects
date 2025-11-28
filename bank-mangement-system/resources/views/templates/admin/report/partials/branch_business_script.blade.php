<script type="text/javascript">

   

$(document).ready(function () {
  var date = new Date();
  const currentDate = $("#associate_report_currentdate").val();
  $('.start_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true,  

    endDate: date, 

    autoclose: true,
    orientation: 'bottom'

  }).datepicker('setDate', currentDate).datepicker('fill');



  $('.end_date').datepicker({

    format: "dd/mm/yyyy",

    todayHighlight: true, 

    endDate: date,  

    autoclose: true,
    orientation: 'bottom'

  }).datepicker('setDate', currentDate).datepicker('fill');

 
$.validator.addMethod("currentdate", function(value, element,p) {

  
     moment.defaultFormat = "DD/MM/YYYY HH:mm";

     var f1 = moment($('#start_date').val()+' 00:00', moment.defaultFormat).toDate();

     var f2 = moment($('#end_date').val()+' 00:00', moment.defaultFormat).toDate();



      var from = new Date(Date.parse(f1));

      var to = new Date(Date.parse(f2));





     if(to>=from)

      {

        $.validator.messages.currentdate = "";

        result = true;

      }else{

        $.validator.messages.currentdate = "To date must be greater than current from date.";

        result = false;  

      }

      

    

    return result;

  }, "")



  $.validator.addMethod("dateDdMm", function(value, element,p) {

     

      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)

      {

        $.validator.messages.dateDdMm = "";

        result = true;

      }else{

        $.validator.messages.dateDdMm = "Please enter valid date.";

        result = false;  

      }

    

    return result;

  }, "");

  $('#filter').validate({
    rules:{
      start_date:{
        required:true,
        dateDdMm:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }
        },
        
            currentdate:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 
      },
      end_date:{
        required:true,
        dateDdMm:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }
        },     
            currentdate:function(element) {

              if ($( "#start_date" ).val()!='') {

                return true;

              } else {

                return false;

              }

            }, 
      },
      company_id:{
        required:true,
      },
    },
    messages:{
        start_date:{
            "required":"Please select date.",
        },
        end_date:{
            "required":"Please select date.",
        }
    }
  })



$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
});

$('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.branch_business.report.export') !!}");
        $('form#filter').submit();
    });
});



function searchForm()

{ 
    if($('#filter').valid())
    {
    var start_date=$('#start_date').val(); 
		var end_date=$('#end_date').val(); 
		var branch=$('#branch').val();
    var company_id=$('#company_id').val();
			$('#is_search').val("yes");
			var is_search=$('#is_search').val(); 

		
			$.ajax({
			  type: "POST",  
			  url: "{!! route('admin.report.branch_business_listing') !!}",
			  dataType: 'JSON',
			  data: {'is_search':is_search,'start_date':start_date,'branch':branch,'end_date':end_date,'company_id':company_id},
			  headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			  success: function(response) { 
          $("#start_date").removeClass("start_date");
          $("#end_date").removeClass("end_date");
          $("#start_date").addClass("startdate");
          $("#end_date").addClass("enddate");
          $('.startdate').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
            orientation: 'bottom'
            }).datepicker('setDate', start_date);

            $('.enddate').datepicker({
              format: "dd/mm/yyyy",
              todayHighlight: true,  
              autoclose: true,
              orientation: 'bottom'
              }).datepicker('setDate', end_date);
              $('.det').html(''); 

              $('.det').append(response.view);
			  }
		  });	
		}
   
}

function resetForm()

{
    
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
    const currentDate = $("#associate_report_currentdate").val();
    
    $('#is_search').val("yes");
    $('#branch_id').val('');
    $('#end_date').val(currentDate);
    $('#start_date').val(currentDate);
	  $('#filter_data').html(''); 
	  $('.container').html(''); 

}



 

 

</script>