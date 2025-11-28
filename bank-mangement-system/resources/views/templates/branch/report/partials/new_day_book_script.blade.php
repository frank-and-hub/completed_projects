<script type="text/javascript">
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
        $.validator.messages.currentdate = "To date must be greater than  from date.";
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
            // currentdate:function(element) {
            //   if ($( "#start_date" ).val()!='') {
            //     return true;
            //   } else {
            //     return false;
            //   }
            // }, 
      },
    company:'required',
    },
    messages:{
        start_date:{
            "required":"Please select date.",
        },
        end_date:{
            "required":"Please select date.",
        },
    }
  })
  // $('#print').on('click',function(){
  //    var branch=$('#branch_id').val(); 
  //       var start_date=$('#start_date').val();
  //       var end_date=$('#end_date').val();
  //    $.ajax({
  //             type: "POST",  
  //             url: "{!! route('branch.print.report.day_book') !!}",
  //             dataType: 'JSON',
  //             data: {'is_search':is_search,'start_date':start_date,'branch':branch,'end_date':end_date},
  //         });
  // })
  $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('branch.daybook.report.export') !!}");
        $('form#filter').submit();
    });
    var page = 0;
    $('#load_more').click(function(){
         page++;
        load_more(page);
    })
    $(document).ajaxStart(function() {
        $(".loader" ).show();
    });
    $(document).ajaxComplete(function() {
        $(".loader" ).hide();
    });
});
function printDiv (elem) {
        //Get the print button
        var printButton = document.getElementById("myPrntbtn");
            $("#"+elem).print({
                    //Use Global styles
                    globalStyles : true,
                    //Add link with attrbute media=print
                    mediaPrint : true,
                    //Custom stylesheet
                    stylesheet : "{{url('/')}}/asset/print.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top
                  //  prepend : "Hello World!!!<br/>",
                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: null,               // prefix to html
                  footer: null,  
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {    })
                });
    }
function searchForm()
{  
    if($('#filter').valid())
    {
       $('#is_search').val("yes")
        var branch=$('#branch_id').val(); 
        var is_search=$('#is_search').val(); 
        console.log(is_search);
        var start_date=$('#start_date').val();
        var end_date=$('#end_date').val();
        var company = $('#company_id').val();
        $('#filter_data').html(''); 
        $.ajax({
		  type: "POST",  
		  url: "{!! route('branch.report.day_booklisting') !!}",
		  dataType: 'JSON',
		  data: {'is_search':is_search,'start_date':start_date,'branch':branch,'end_date':end_date,'company':company},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) { 
			  $('.data').html(response.view);
			  var page = 0;   
			  load_more(page);                             
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
    $('#is_search').val("no");
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    location.reload();
}
  function load_more(page){
    var branch=$('#branch_id').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();
    var end_date=$('#end_date').val();
    var company=$('#company_id').val();
    var limit = 200
    if($('#index').val() == '')
    {
        var index = 0;
    }
    else{
        var index = $('#index').val();
    }
    var length = $('#td tr').length;
  $.ajax(
        {
            type: "POST",  
            url: "{!! route('branch.daybook.transaction_listing') !!}",
            dataType: 'JSON',
            data: {'is_search':is_search,'start_date':start_date,'branch':branch,'end_date':end_date,'page':page,'limit':limit,'index':index,'company':company},
            headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function()
            {
                $('.loader').show();
            }
        })
        .done(function(response)
        {
            if(response.msg == 'success')
            {
                $('#index').val(response.sno);
                if(response.data.length == 0){
                    $('.ajax-loading').html("No more records!");
                    $('#load_more').hide();
                    return false;
                }
                else{
                $('.ajax-loading').hide(); //hide loading animation once data is received
                    $("#t").append(response.data);
                    if(response.msg == 'success')
                    {
                       $('#load_more').show(); 
                    }  
                //append data into #results element    
                }      
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError)
        {
              console.log('No response from server');
        });
 }
</script>