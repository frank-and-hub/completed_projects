<script type="text/javascript">
    var investTable;
    var ssbTable;
    var otherTable;
    $(document).ready(function () {
    var date = new Date();
    const currentDate = $("#associate_report_currentdate").val();
    $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,  
        endDate: date, 
        autoclose: true,
        orientation:'bottom'
    }).datepicker('setDate', currentDate).datepicker('fill');

    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true, 
        endDate: date,  
        autoclose: true,
        orientation:'bottom'
    }).datepicker('setDate', currentDate).datepicker('fill');

    investTable = $('#investment_list').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#investment_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.report.transactionDetail') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(),
                d.payment_type=$('#payment_type').val(),  
                d.payment_mode=$('#payment_mode').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},  
            {data: 'member_id', name: 'member_id'},            
            {data: 'member_name', name: 'member_name'},
            {data: 'account_number', name: 'account_number'}, 
            {data: 'plan', name: 'plan'},
            {data: 'tag', name: 'tag'},
            {data: 'amount', name: 'amount'}, 
            {data: 'mode', name: 'mode'},
            {data: 'type', name: 'type'}, 
            {data: 'is_eli', name: 'is_eli'},
            {data: 'created_at', name: 'created_at'} 
        ]
    });
    $(investTable.table().container()).removeClass( 'form-inline' );
/*
$('.export_invest').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.report.transactionDetailExport') !!}");
        $('form#filter').submit();
        return true;
    }); 
*/
$('.export_invest').on('click',function(e){
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
            url :  "{!! route('admin.report.transactionDetailExport') !!}",
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

    
    ssbTable = $('#ssb_list').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#ssb_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.report.transactionDetailSSB') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(),
                d.payment_type=$('#payment_type').val(),  
                d.payment_mode=$('#payment_mode').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},  
            {data: 'member_id', name: 'member_id'},            
            {data: 'member_name', name: 'member_name'},
            {data: 'account_number', name: 'account_number'},  
            {data: 'amount', name: 'amount'}, 
            {data: 'mode', name: 'mode'},
            {data: 'type', name: 'type'},  
            {data: 'created_at', name: 'created_at'}, 
        ]
    });
    $(ssbTable.table().container()).removeClass( 'form-inline' );

/*
    $('.export_ssb').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.report.transactionDetailSsbExport') !!}");
        $('form#filter').submit();
        return true;
    });
*/
    $('.export_ssb').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport2(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport2(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.report.transactionDetailSsbExport') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport2(start,limit,formData,chunkSize);
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


    otherTable = $('#other_list').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#other_list').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.report.transactionDetailOther') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(),
                d.payment_type=$('#payment_type').val(),  
                d.payment_mode=$('#payment_mode').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},  
            {data: 'member_id', name: 'member_id'},            
            {data: 'member_name', name: 'member_name'},
            {data: 'account_number', name: 'account_number'},  
            {data: 'amount', name: 'amount'}, 
            {data: 'mode', name: 'mode'},
            {data: 'type', name: 'type'},  
            {data: 'created_at', name: 'created_at'}, 
        ]
    });
    $(otherTable.table().container()).removeClass( 'form-inline' );
/*
 $('.export_other').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.report.transactionDetailOtherbExport') !!}");
        $('form#filter').submit();
        return true;
    }); 
    
 */
  $('.export_other').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport3(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExport3(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.report.transactionDetailOtherbExport') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport3(start,limit,formData,chunkSize);
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


    $('#filter11').validate({
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
        $(".table-section").removeClass('datatable');
        investTable.draw();
        ssbTable.draw();
        otherTable.draw();

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
    $('#start_date').val(currentDate);
    $('#end_date').val(currentDate);
    $('#payment_mode').val('0');
    $('#payment_type').val('CR');
   

    investTable.draw();
    ssbTable.draw();
    otherTable.draw();
    $(".table-section").addClass("datatable");
}

</script>