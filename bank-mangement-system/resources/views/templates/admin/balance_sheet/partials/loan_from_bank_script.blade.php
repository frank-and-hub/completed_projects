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


 detailList = $('#loan_asset_report').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings ();

                $('html, body').stop().animate({

                    scrollTop: ($('#loan_asset_report').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.balance-sheet.loan_from_bank_detail_ledger') !!}",

                "type": "POST",

                "data":function(d,oSettings) {
                    let totalAmount;
                    if(oSettings.json != null)
                    {
                        totalAmount = oSettings.json.total;
                        // var total = oSettings.json.total;
                    }
                    else{
                        totalAmount = 0;
                    }
                    var page = ($('#loan_asset_report').DataTable().page.info());
                    var currentPage  = page.page+1;
                    d.pages = currentPage,
                	d.searchform=$('form#filter').serializeArray(),
                	d.head= $('#head_id').val(),
                	d.label= $('#label').val(),
                    d.date= $('#start_date').val(),
					d.end_date = $('#ends_date').val(),
                    d.total = totalAmount


            	},

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

            },

            columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'type', name: 'type'},
            {data: 'description', name: 'description'},
            {data: 'received_bank', name: 'received_bank'},
            {data: 'account_number', name: 'account_number'},
             {data: 'payment_bank', name: 'payment_bank'},
            {data: 'payment_account_number', name: 'payment_account_number'},

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


function searchForm()
{

    if($('#filter').valid())

    {

        $('#is_search').val("yes");

        detailList.draw();

    }
}

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
window.location.href = "{{url('/')}}/admin/balance-sheet/detail_ledger?"+queryParams;

}

function resetbanktransaction()
{
    location.reload();
    $('#is_search').val("no");
    $('#branch').val("");

    var branch=$('#branch').val();
    var is_search=$('#is_search').val();
    var default_start_date=$('#default_date').val();
      var default_end_date=$('#default_end_date').val();
    var queryParams = new URLSearchParams(window.location.search);

// Set new or modify existing parameter value.
queryParams.set("date", default_start_date);
queryParams.set("end_date", default_end_date);

// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/detail_ledger?"+queryParams;
}


</script>
