<script type="text/javascript">
    var voucher_listing;
$(document).ready(function () {
  var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    endDate: date, 
    orientation:"bottom",
    autoclose: true
  });

  $('#end_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
    endDate: date,
    orientation:"bottom",
    autoclose: true
  });
     voucher_listing = $('#voucher_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#voucher_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.voucher.lists') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(), 
                d.month=$('#month').val(),
                d.year=$('#year').val(), 
                d.status=$('#status').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
             {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
             {data: 'company_name', name: 'company_name'},
            {data: 'branch', name: 'branch'},
             
            // {data: 'sector', name: 'sector'}, 
            // {data: 'regan', name: 'regan'},
            // {data: 'zone', name: 'zone'}, 
            {data: 'date', name: 'date'}, 
            {data: 'rv_mode', name: 'rv_mode'}, 

            {data: 'rv_amount', name: 'rv_amount', 
                "render":function(data, type, row){
                     if ( row.rv_amount>=0 ) {
                         return row.rv_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                         return "N/A";
                     }
                 }
             },

            {data: 'account_head', name: 'account_head'}, 
            {data: 'account_sub_head', name: 'account_sub_head'}, 
            {data: 'director', name: 'director'},
            {data: 'shareholder', name: 'shareholder'}, 
            {data: 'employee_code', name: 'employee_code'}, 
            {data: 'employee_name', name: 'employee_name'},
            {data: 'bank_name', name: 'bank_name'}, 
            {data: 'bank_account_number', name: 'bank_account_number'}, 
            {data: 'eli_loan', name: 'eli_loan'},
            // {data: 'day_book', name: 'day_book'}, 
            {data: 'cheque_no', name: 'cheque_no'}, 
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'utr_transaction_number', name: 'utr_transaction_number'}, 
            {data: 'transaction_date', name: 'transaction_date'}, 
            {data: 'party_bank_name', name: 'party_bank_name'},
            {data: 'party_bank_account', name: 'party_bank_account'}, 
            {data: 'received_bank', name: 'received_bank'}, 
            {data: 'received_bank_account', name: 'received_bank_account'},
            {data: 'bank_slip', name: 'bank_slip'},  
            //{data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],'ordering':false
    });
    $(voucher_listing.table().container()).removeClass( 'form-inline' );

 /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.voucher.exportLists') !!}");
        $('form#filter').submit();
        return true;
    }); 
   */
$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		
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
            url :  "{!! route('admin.voucher.exportLists') !!}",
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

 


 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        $(".table-section").removeClass('hideTableData');
        voucher_listing.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");  
    $('#start_date').val('');
    $('#company_id').val('0').trigger('change');
    $('#end_date').val('');
    $('#payment_type').val('');
    $('#account_head').val('');
    $('#is_search').val('no');   
    $(".table-section").addClass("hideTableData");
    voucher_listing.draw();
}

</script>