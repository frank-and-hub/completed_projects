<script type="text/javascript">
    var associateTable = '';
    associateTable='';
    $('.myopt').hide();
$(document).ready(function () {
    // $('#year').on('change', function() {
    //     $('#month').val('');
    //   var selectedYear = $(this).val(); 
    //   $('#month option.myopt').each(function() {
    //     var allowedYears = $(this).data('year'); 
    //     if (allowedYears && allowedYears.includes(Number(selectedYear))) {
    //       $(this).show(); 
    //     } else {
    //       $(this).hide();
    //     }
    //   });
    // });
    $("#year").trigger("change");
  $('#commissionFilter').validate({
      rules: {
        associate_code :{ 
            number : true,
        },  
        year :{ 
            required : true,
        },  
        month :{ 
            required : true,
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
     var date = new Date();
//    var Startdate = new Date();
//     Startdate.setMonth(Startdate.getMonth() - 3);
    $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        // startDate:Startdate,
        endDate: date,
        autoclose: true
    }).on("changeDate", function(e) {
        $('#end_date').datepicker('setStartDate', e.date, 'format',"dd/mm/yyyy");
    });
    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true
    });
      associateTable = $('#associate_listing').DataTable({
          processing: true,
          serverSide: true,
          pageLength: 20,
          lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.associater_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch', name: 'branch'},
            //  {data: 'branch_code', name: 'branch_code'},
			  {data: 'name', name: 'name'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'associate_no', name: 'associate_no'},
            {data: 'join_date', name: 'join_date'},
             {data: 'dob', name: 'dob'},
             {data: 'nominee_name',name:'nominee_name'},
			{data: 'relation',name:'relation'},
			{data:'nominee_age',name:'nominee_age'},
            {data: 'email', name: 'email'},
            {data: 'mobile_no', name: 'mobile_no'},
            /*{data: 'senior_code', name: 'senior_code'},
            {data: 'associate_name', name: 'associate_name'},*/
            {data: 'status', name: 'status'},
            {data: 'is_upload', name: 'is_upload'},
            // {data: 'achieved_target', name: 'achieved_target'},
            {data: 'address', name: 'address'},
            {data: 'state', name: 'state'},
            {data: 'district', name: 'district'},
            {data: 'city', name: 'city'},
            {data: 'village', name: 'village'},
            {data: 'pin_code', name: 'pin_code'},
            {data: 'firstId', name: 'firstId'},
            {data: 'secondId', name: 'secondId'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(associateTable.table().container()).removeClass( 'form-inline' );
    associateCommissionTable = $('#associate-commission-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#associate-commission-listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.associate.commissionlist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#commissionFilter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'branch_name', name: 'branch_name'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_carder', name: 'associate_carder'},            
            {data: 'commission_amount', name: 'commission_amount'}, 
            {data: 'collection_amount', name: 'collection_amount'}, 
            {data: 'collection_amount_all', name: 'collection_amount_all'}, 
            {data: 'senior_code', name: 'senior_code'},
            {data: 'senior_name', name: 'senior_name'},
            {data: 'senior_carder', name: 'senior_carder'},
        //    {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(associateCommissionTable.table().container()).removeClass( 'form-inline' );
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
            "url": "{!! route('branch.associate.commissionDetaillist') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#commissionFilterDetail').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex'
        },
        {
          data: 'month',
          name: 'month'
        },
        {
          data: 'account_number',
          name: 'account_number'
        },
        {
          data: 'plan_name',
          name: 'plan_name'
        },
        {
          data: 'total_amount',
          name: 'total_amount',
          "render": function(data, type, row) {
            return row.total_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'qualifying_amount',
          name: 'qualifying_amount',
          "render": function(data, type, row) {
            return row.qualifying_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'commission_amount',
          name: 'commission_amount',
          "render": function(data, type, row) {
            return row.commission_amount + " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
          }
        },
        {
          data: 'percentage',
          name: 'percentage',
          "render": function(data, type, row) {
            return row.percentage + "%";
          }
        },
        {
          data: 'carder_from',
          name: 'carder_from'
        },
        {
          data: 'carder_to',
          name: 'carder_to'
        },        
      ]
    });
    $(associateCommissionDetailTable.table().container()).removeClass( 'form-inline' );
    $('#associate-correction-form').validate({ // initialize the plugin
        rules: {
            'corrections' : 'required',
        },
    });
    $(document).on('click','.a-correction',function(){
      var mId = $(this).attr('data-id');
      var cStatus = $(this).attr('data-correction-status');
      var cId = $(this).attr('data-company');
      if(cStatus == '0'){     
        swal("Warning!", 'Correction request already submitted!', "warning");
      }
      $('#correction_type_id').val(mId);
      $('#companyid').val(cId);
    });
    /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
        $('form#filter').attr('action',"{!! route('branch.associate.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#member_export').val(extension);
			var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
		if((extension == 0))
		{
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
            if(datediff()){
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display","block");
                $(".loaders").text("0%");
                doChunkedExports(0,chunkAndLimit,formData,chunkAndLimit);
                $("#cover").fadeIn(100);
            }
		}else{
			$('#member_export').val(extension);
			$('form#filter').attr('action',"{!! route('branch.associate.export') !!}");
			$('form#filter').submit();
		}
	});
		  function doChunkedExports(start,limit,formData,chunkSize){
						formData['start']  = start;
						formData['limit']  = limit;
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url :  "{!! route('branch.associate.export') !!}",
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
   /*
    $('.exportcommission').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
        $('form#commissionFilter').attr('action',"{!! route('branch.associate.exportcommission') !!}");
        $('form#commissionFilter').submit();
        return true;
    });
	*/
	$('.exportcommission').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
			var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
			if(extension == 0)
		{
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
        var formData = jQuery('#commissionFilter').serializeObject();
       var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}else{
			$('#commission_export').val(extension);
			$('form#commissionFilter').attr('action',"{!! route('branch.associate.exportcommission') !!}");
			$('form#commissionFilter').submit();
		}
	});
	  function doChunkedExport(start,limit,formData,chunkSize){
						formData['start']  = start;
						formData['limit']  = limit;
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url :  "{!! route('branch.associate.exportcommission') !!}",
							   headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
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
    $('.exportcommissionDetail').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension); 
        $('form#commissionFilterDetail').attr('action',"{!! route('branch.associate.exportcommissionDetail') !!}");
        $('form#commissionFilterDetail').submit();
        return true;
    });
*/
$('.exportcommissionDetail').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#commission_export').val(extension);
			var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
			if(extension == 0)
		{
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
        var formData = jQuery('#commissionFilterDetail').serializeObject();
         var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExporte(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}else{
			$('#commission_export').val(extension);
			$('form#commissionFilterDetail').attr('action',"{!! route('branch.associate.exportcommissionDetail') !!}");
			$('form#commissionFilterDetail').submit();
		}
	});
	  function doChunkedExporte(start,limit,formData,chunkSize){
						formData['start']  = start;
						formData['limit']  = limit;
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url :  "{!! route('branch.associate.exportcommissionDetail') !!}",
							   headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
							data : formData,
							success: function(response) {
								console.log(response);
								if(response.result=='next'){
									start = start + chunkSize;
									doChunkedExporte(start,limit,formData,chunkSize);
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
    function resetForm()
    {
        $('#is_search').val("no");
        $('#listing_table').hide();
        $('#member_name').val('');
        $('#name').val('');
        $('#associate_code').val('');
        $('#branch_id').val('');
        $('#end_date').val('');
        $('#customer_id').val('');
        $('#start_date').val('');
        $('#sassociate_code').val('');
        $('#achieved').val('');
        associateTable.draw();
    }
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    $.validator.addMethod("datediff", function(value, element, p) {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f1 = moment($('#start_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var f2 = moment($('#end_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var from = new Date(Date.parse(f1));
        var to = new Date(Date.parse(f2));
        var threeMonthsLater = moment(from).add(3, 'months');
        if (to < threeMonthsLater) {
            $.validator.messages.datediff = "";
            result = true;
        } else {
            $.validator.messages.datediff = "The date difference should not be more than 3 months.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("currentdate", function(value, element, p) {
      moment.defaultFormat = "DD/MM/YYYY HH:mm";
      var f1 = moment($('#start_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var f2 = moment($('#end_date').val() + ' 00:00', moment.defaultFormat).toDate();
      var from = new Date(Date.parse(f1));
      var to = new Date(Date.parse(f2));
      if (to >= from) {
        $.validator.messages.currentdate = "";
        result = true;
      } else {
        $.validator.messages.currentdate = "End date must be greater than current from date.";
        result = false;
      }
      return result;
    }, "");
    $('#filter').validate({
        rules: {
            start_date:{
                dateDdMm : true,
                // required: true,
            },
            end_date:{
                dateDdMm : true,
                // datediff : true,
                // required: true,
                // currentdate : function (e){
                //         if ($('#end_date').val() == '' ) {
                //             return false;
                //         } else if($('#start_date').val() == '') {
                //             return false;
                //         } else{
                //             return true;
                //         }
                //     },
            },
            associate_code :{
                number : true,
            },
            sassociate_code :{
                number : true,
            }
        },
        messages: {
            associate_code:{
                number: 'Please enter valid associate code.'
            },
            sassociate_code:{
                number: 'Please enter valid senior code.'
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
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        associateTable.draw();
        $('#listing_table').show();
    }
}
function searchCommissionForm()
{  
    if($('#commissionFilter').valid())
    {
        $('#is_search').val("yes");
        associateCommissionTable.draw();
    }
}
function searchCommissionDetailForm()
{  
    if($('#commissionFilterDetail').valid())
    {
        $('#is_search').val("yes");
        associateCommissionDetailTable.draw();
    }
}
function resetCommissionForm()
{
    $('#is_search').val("no");
    $('#member_name').val('');
    $('#name').val('');
    $('#associate_code').val('');
    $('#branch_id').val('');
    $('#year').val('');
    $('#year').trigger('change');
    $('#company_id').val('0');
    $('#sassociate_code').val('');
    $('#associate_name').val('');
    $('#achieved').val('');
    associateCommissionTable.draw();
}
 function resetCommissionDetailForm()
{
    $('#is_search').val("no");
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    associateCommissionDetailTable.draw();
}
function resetForm()
    {
        $('#is_search').val("no");
        $('#member_name').val('');
        $('#listing_table').hide();
        $('#name').val('');
        $('#associate_code').val('');
        $('#end_date').val('');
        $('#end_date').val('');
        $('#customer_id').val('');
        $('#start_date').val('');
        $('#sassociate_code').val('');
        $('#achieved').val('');
        associateTable.draw();
    }
    function datediff() {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f1 = moment($('#start_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var f2 = moment($('#end_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var from = new Date(Date.parse(f1));
        var to = new Date(Date.parse(f2));
        var threeMonthsLater = moment(from).add(3, 'months');
        if(!(to < threeMonthsLater)) {
            swal('Error','The date difference should not be more than 3 months.','error');
            return false;
        }else{
            return true;
        }
    };
    $('#year').on('change', function() {
    $('#month').val($('#month_set').val());
    $('#month_set').val("");
    var selectedYear = $(this).val();
    $('#month option.myopt').each(function() {
      var allowedYears = $(this).data('year');
      if (allowedYears && allowedYears.includes(Number(selectedYear))) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
</script>