<script type="text/javascript">
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

     memberTable = $('#member_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#member_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('reinvest.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'join_date', name: 'join_date'},
            {data: 'account_number', name: 'account_number'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'member_id', name: 'member_id'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email',orderable: true, searchable: true},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

    memberInvestmentPaymentTable = $('#member_investment_payment_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#chequefilter').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member.investmentchequepaymentlisting') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#chequefilter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'amount', name: 'amount'},
            {data: 'transaction_date', name: 'transaction_date'},
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'cheque_number', name: 'cheque_number'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberInvestmentPaymentTable.table().container()).removeClass( 'form-inline' );
    
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.member.export') !!}");
        $('form#filter').submit();
        return true;
    });

    $('.exportchequelisting').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#cheque_export').val(extension);
        $('form#chequefilter').attr('action',"{!! route('admin.member.exportinvestmentchequelistin') !!}");
        $('form#chequefilter').submit();
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
          member_id :{ 
            number : true,
          },
          associate_code :{ 
            number : true,
          },  

      },
      messages: { 
          member_id:{ 
            number: 'Please enter valid member id.'
          },
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

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        memberTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#name').val('');
    $('#member_id').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#associate_code').val(''); 

    memberTable.draw();
}

function searchCheckForm()
{  
    if($('#chequefilter').valid())
    {
        $('#is_search').val("yes");
        memberInvestmentPaymentTable.draw();
    }
}

function resetCheckForm()
{
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#branch_id').val('');
    $('#status').val(''); 
    memberInvestmentPaymentTable.draw();
}
 
</script>