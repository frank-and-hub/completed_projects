<script type="text/javascript">
  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    var memberTable;
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

      

 


    associateCommissionDetailTable = $('#associate-commission-detail').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#associate-commission-detail').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.loan_commission_list') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#commissionFilterDetail').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(), 
                d.is_search=$('#is_search').val(),
                d.commission_export=$('#commission_export').val(),
                d.id=$('#id').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'investment_account', name: 'investment_account'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'total_amount', name: 'total_amount',
                "render":function(data, type, row){
                 return row.total_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
            {data: 'commission_amount', name: 'commission_amount',
                "render":function(data, type, row){
                 return row.commission_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              }, 
            {data: 'percentage', name: 'percentage'},
            {data: 'carder_name', name: 'carder_name'},    
            {data: 'commission_type', name: 'commission_type'}, 
            {data: 'pay_type', name: 'pay_type'},
            {data: 'is_distribute', name: 'is_distribute'}, 
        ]
    });
    $(associateCommissionDetailTable.table().container()).removeClass( 'form-inline' );



    



     $('.exportcommissionDetail').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension); 
        $('form#commissionFilterDetail').attr('action',"{!! route('branch.loan.loanCommissionExport') !!}");
        $('form#commissionFilterDetail').submit();
        return true;
    });

 

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
          associate_code :{ 
            number : true,
          },  

      },
      messages: {  
          associate_code:{ 
            number: 'Please enter valid associate code.'
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

 



function searchCommissionDetailForm()
{  
    if($('#commissionFilterDetail').valid())
    {
        $('#is_search').val("yes");
        associateCommissionDetailTable.draw();
    }
}


 

 function resetCommissionDetailForm()
{
    $('#is_search').val("no");
    $('#end_date').val('');
    $('#start_date').val(''); 
    associateCommissionDetailTable.draw();
}
</script>