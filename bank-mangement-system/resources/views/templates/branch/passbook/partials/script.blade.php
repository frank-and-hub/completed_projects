

<script type="text/javascript">
$(document).ready(function () {

  var date = new Date();
  $('#start_date').datepicker({
    format: "mm/dd/yyyy",
    todayHighlight: true,
    endDate: date,
    autoclose: true
  });

  $('#end_date').datepicker({
    format: "mm/dd/yyyy",
    todayHighlight: true,
    endDate: date,
    autoclose: true
  });
  });


$('#member-correction-form').validate({
            rules:{
                corrections:{
                    required:true,
                },

            },
            messages:{
                corrections:{
                    "required":"Please enter description."
                },

            }
        })

function printDiv(elem,id) {

   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
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
                    deferred: $.Deferred().done(function() {  coverprint(id); })
                });
}
function printDivDublicate(elem,id) {
   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
                    //Custom stylesheet
                    stylesheet : "{{url('/')}}/asset/print.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top

                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: null,               // prefix to html
                  footer: null,
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {  coverprintDublicate(id);   })
                });
}
function coverprintDublicate(id)
{
  swal("Success!", "Passbook cover page print sucessfully!", "success");
  location.reload();
}
function coverprint(id)
{

  $.ajax({
              type: "POST",
              url: "{!! route('branch.cover_print') !!}",
              dataType: 'JSON',
              data: {'id':id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if(response.msg_type=="success")
                {
                  $('#printButton').hide();
                  $('#printButtonPay').show();
                  $('#showDuplicate').css('visibility', 'visible');
                  swal("Success!", "Passbook cover page print sucessfully!", "success");
                  location.reload();

                }
              }
          });
}


 function printDivTran(elem,no) {
   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
                    //Custom stylesheet
                    stylesheet : "{{url('/')}}/asset/print.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top
               //     prepend : "<span class='tran_account_number' style='padding-left: 40px;line-height: 50px;'>A/C No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+no+"</span>",
                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: null,               // prefix to html
                  footer: null,
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() { console.log('Printing done', arguments);})
                });
}

 function pay_print(elem,id) {
  $.ajax({
              type: "POST",
              url: "{!! route('branch.pay_print') !!}",
              dataType: 'JSON',
              data: {'id':id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if(response.msg_type=="success")
                {
                  printDivDublicate(elem,id);

                }
                else
                {
                  swal("Error!", "Somthing wrong. Try again!", "error");
                }
              }
          });
  }


  function printDivcer(elem,id) {
    $('.remove_lable').css("visibility", "hidden");

    $('.tr_remove').css("visibility", "hidden");
   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
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
                    deferred: $.Deferred().done(function() { certificateprintDublicate(id);  $('.remove_lable').css("visibility", "visible");  $('.tr_remove').css("visibility", "visible"); console.log('Printing done', arguments); })
                });
}
 function certificate_pay_print(elem,id) {
  $.ajax({
              type: "POST",
              url: "{!! route('branch.Certificatepay_print') !!}",
              dataType: 'JSON',
              data: {'id':id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if(response.msg_type=="success")
                {
                  printDivcer(elem,id);

                }
                else
                {
                  swal("Error!", "Somthing wrong. Try again!", "error");
                }
              }
          });
  }
function certificateprintDublicate(id)
{

  $.ajax({
              type: "POST",
              url: "{!! route('branch.certificate_print') !!}",
              dataType: 'JSON',
              data: {'id':id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                console.log(response);
                if(response.msg_type=="success")
                {
                  $('#printButton').hide();
                  $('#printButtonPay').show();
                  $('#showDuplicate').css('visibility', 'visible');
                  swal("Success!", "Passbook Certificate page print sucessfully!", "success");
                  location.reload();

                }
              }
          });
}
</script>
