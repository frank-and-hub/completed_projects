<script type="text/javascript">
    var memberTable;
$(document).ready(function () {

    var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    endDate: date, 
    autoclose: true,
    orientation:'bottom',
  });

  $('#end_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
    endDate: date,  
    autoclose: true,
     orientation:'bottom',
  });

  //Required filed astrisk symbol color declare
  $('.required').css('color','red');

     memberTable = $('#member_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
         searching:true,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#member_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},   
            {data: 'branch', name: 'branch'}, 
			{data: 'name', name: 'name'},
			{data: 'member_id', name: 'member_id',
                "render":function(data, type, row) {
                    var accountNumber = row.reinvest_old_account_number;
                    if ( accountNumber ) {
                        return 'R-' + row.member_id;
                    } else {
                        return row.member_id;
                    }
                }
            },
            {data: 'customer_id', name: 'customer_id'},
			{data: 'join_date', name: 'join_date'},
			{data: 'dob', name: 'dob'}, 
            {data: 'gender', name: 'gender'},
			{data: 'mobile_no', name: 'mobile_no'},
			 {data: 'state', name: 'state'},
            {data: 'district', name: 'district'},
            {data: 'city', name: 'city'},
			{data: 'address', name: 'address'},
			{data: 'village', name: 'village'},
			{data: 'pin_code', name: 'pin_code'},
			{data: 'firstId', name: 'firstId'},
            {data: 'secondId', name: 'secondId'},
			{data: 'nominee_name',name:'nominee_name'},
			{data:'nominee_age',name:'nominee_age'},
			{data: 'relation',name:'relation'},
			 {data:'nominee_gender',name:'nominee_gender'},
			{data: 'associate_name', name: 'associate_name'},
			{data: 'associate_code', name: 'associate_code'},
			{data: 'status', name: 'status'},
            {data: 'ssb_account', name: 'ssb_account',orderable: true, searchable: true},
            {data: 'is_upload', name: 'is_upload'},           
            
            
            

            
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

    memberInvestmentPaymentTable = $('#member_investment_payment_listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
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
             {data: 's-branch', name: 's-branch'},
             {data: 'branch_code', name: 'branch_code'},
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'amount', name: 'amount'},
            {data: 'transaction_date', name: 'transaction_date'},
            {data: 'cheque_date', name: 'cheque_date'},
            {data: 'cheque_number', name: 'cheque_number'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false,
    });
    $(memberInvestmentPaymentTable.table().container()).removeClass( 'form-inline' );
    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.member.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	 $('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
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
			
			
			$('#member_export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.member.export') !!}");

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
            url :  "{!! route('admin.member.export') !!}",
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
 /*
    $('.export-tds').on('click',function(){

        var extension = $(this).attr('data-extension');

        var url = $('form#membertdsfileter').attr('action');

        $('#m_investment_tds_export').val(extension);

        $('form#membertdsfileter').attr('action',"{!! route('admin.member.exportinteresttdslisting') !!}");

        $('form#membertdsfileter').submit();

        $('form#membertdsfileter').attr('action',url);

        return true;

    });
	*/
	

$('.export-tds').on('click',function(e){
	
	
		e.preventDefault();
		var extension = $(this).attr('data-extension');
		  //var url = $('form#membertdsfileter').attr('action');
        $('#m_investment_tds_export').val(extension);
		 if(extension == 0)
        {
		var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
		
				
				if( startdate =='')
				{
					swal("Error!", "Please select start date, you can export last three months data!", "error");
					return false;	
				}
			
				if( enddate =='')
				{
					swal("Error!", "Please select end date, you can export last three months data!", "error");
					return false;
				}
			
		
        var formData = jQuery('#membertdsfileter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExports(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
    }
    else
    {
        $('#cheque_export').val(extension);

            $('form#membertdsfileter').attr('action',"{!! route('admin.member.exportinteresttdslisting') !!}");

            $('form#membertdsfileter').submit();
    }
	});
		  function doChunkedExports(start,limit,formData,chunkSize){
						formData['start']  = start;
						formData['limit']  = limit;
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url :  "{!! route('admin.member.exportinteresttdslisting') !!}",
							   headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
							data : formData,
							success: function(response) {
								console.log(response);
								if(response.result=='next'){
									start = start + chunkSize;
									doChunkedExports(start,limit,formData,chunkSize);
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
	
    $(document).on('click','#reset_form',function(){
        $('#is_search').val('no');
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
          company_id :{ 
            number : true,
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
        $(".table-section").removeClass('hideTableData');
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
    $('#company_id').val('0');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#associate_code').val(''); 
    $('#status').val(''); 
    $(".table-section").addClass("hideTableData");
   // memberTable.draw();
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