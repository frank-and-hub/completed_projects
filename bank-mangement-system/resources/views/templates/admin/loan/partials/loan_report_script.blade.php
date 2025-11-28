<script type="text/javascript">
$(document).ready(function() {
    var today = new Date() 
    var fdate = new Date(new Date().setDate(new Date().getDate() + 7));
    $('.from_date,.outstanding_as_on_date,.to_date').hover(function() {
        var EndDate = $('.create_application_date').val();

        $(this).datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: EndDate
        })
    })

    
      
    
    
    // Loan Repayment Chart
    $('body').delegate('.repayment','click',function(ev){
        var loanId ='';
        loanId=  $(this).attr('data-loan-id');
        loanType=  $(this).attr('data-type');
        var URL;
       $.ajax({
            "url": "{!! route('admin.loan.repayment_chart') !!}",
            "type": "POST",
            "data":{ "searchform" : $('form#filter').serializeArray(),
                "loanId" :loanId,
                "loanType":loanType
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(response){
                $('#exampleModalLong').modal('show');
                obj = response;
                $('#datarow').html('');
                $('table.pageee').attr('id',loanId);
                $("#inputid").val(loanId);
                $.each(obj,function(i,val){
                    $('#datarow').append('<tr><td>'+val.DT_RowIndex+'</td><td>'+val.emi_date+'</td><td>'+val.emi_amount+'</td><td>'+val.roi+'</td><td>'+val.principal_amount+'</td><td>'+val.outstanding+'</td></tr>');
                })
                // paginationLength(loanId);
                // $('table.pageee').removeAttr('id');
            }
       })
    })
          paginationLength = (loanId) => {
        $("#"+loanId).dataTable({ 
				"pageLength": 10 , 
				columnDefs: [{  targets: [2] }]
				});
    }
    $('.prnt_modal').on('click',function(){
        var loanId=  $('#inputid').val();
        idPrint(loanId);
    })
    $('.export-repayment').on('click',function(e){
     e.preventDefault();
     var extension = $(this).attr('data-extension');
     $('#export_repayment').val(extension);
     if(extension == 0)
     {
     var formData = jQuery('#filter_repayment').serializeObject();
     var loanId=  $('#inputid').val();
     formData['loanId'] = loanId;
     formData['title']= $('#report_title').val()
     var chunkAndLimit = 1000;
     $(".spiners").css("display","block");
     $(".loaders").text("0%");
     doChunkedExportmt2(0,chunkAndLimit,formData,chunkAndLimit);
     $("#cover").fadeIn(100);
     }else{
         $('#export_repayment').val(extension);
     $('form#filter_repayment').attr('action',"{!! route('admin.loan.repayment.export') !!}");
         $('form#filter_repayment').submit();
     }
 });
// function to trigger the ajax bit
 function doChunkedExportmt2(start,limit,formData,chunkSize){
     formData['start']  = start;
     formData['limit']  = limit;
     jQuery.ajax({
         type : "post",
         dataType : "json",
         url :  "{!! route('admin.loan.repayment.export') !!}",
         data : formData,
         success: function(response) {
             if(response.result=='next'){
                 start = start + chunkSize;
                doChunkedExportmt2(start,limit,formData,chunkSize);
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


 $('#company_id').on('change',function(){
    $('#loan_type').val('');
    $('#loan_plan').empty();
 })
    // End
    // loanReport = $('#loan_report').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     pageLength: 20,
    //     "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
    //         var oSettings = this.fnSettings ();
    //         $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
    //         return nRow;
    //     },
    //     ajax: {
    //         "url": "{!! route('admin.loan.reportList') !!}",
    //         "type": "POST",
    //         "data":function(d) {d.searchform=$('form#filter').serializeArray(),
    //                             d.title = $('#report_title').val()
    //         }, 
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //     },
    //     columns: [
    //         {data: 'DT_RowIndex', name: 'DT_RowIndex'},
    //         {data: 'created_date', name: 'created_date'},
    //         {data: 'branch', name: 'branch'},
    //         {data: 'branch_code', name: 'branch_code'},
    //         {data: 'account_number', name: 'account_number'},
    //         {data: 'tenure', name: 'tenure'},
    //         {data: 'member_name', name: 'member_name'},
    //         {data: 'member_id', name: 'member_id'},
    //         {data: 'loan_type', name: 'loan_type'},
    //         {data: 'loan_amount', name: 'loan_amount'},
    //         {data: 'outstanding_amount', name: 'outstanding_amount'},
    //         {data: 'total_due_amount', name: 'total_due_amount'},
    //         {data: 'no_of_due_emi', name: 'no_of_due_emi'},
    //         {data: 'total_deposite_emi', name: 'total_deposite_emi'},
    //         {data: 'no_of_deposite_emi', name: 'no_of_deposite_emi'},
    //     ],
    //     // "columnDefs": [
    //     //                 {
    //     //                 'targets':[10,11,12,13],
    //     //                 'render': function (data, type, row, meta){
    //     //                     var title = $("#report_title").val();
    //     //                     if(title=='Loan Outstanding'){
    //     //                         loanRecoveryTable.columns([10]).visible(false);
    //     //                         loanRecoveryTable.columns([11]).visible(false);
    //     //                         loanRecoveryTable.columns([12]).visible(false);
    //     //                         loanRecoveryTable.columns([13]).visible(false);
    //     //                     }else{
    //     //                     }
    //     //                 }
    //     //             },
    //     //     ]
    // });
    // $(loanReport.table().container()).removeClass( 'form-inline' );
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    $('.export-outstanding').on('click',function(e){
        e.preventDefault();
        var extension = $(this).attr('data-extension');
		var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text(Math.floor(Math.random() * 10));
        doChunkedExportmt(0, chunkAndLimit, formData, chunkAndLimit, 1);
        $("#cover").fadeIn(100);
    });
    // function to trigger the ajax bit
    function doChunkedExportmt(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.loan.outStanding.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExportmt(start,limit,formData,chunkSize);
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
        $('#filter').validate({
            rules: {
                loan_type: {
                    required: true,
                },
                loan_account_number : {number: true},
                member_id : {number: true},
                associate_code : {number: true},
            },
            messages: {
                loan_type: {
                    required: 'Please select loan type',
                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });
    //Get Loan type onchange get
        $('#loan_type').on('change',function(){
            loanType = $('#loan_type option:selected').val();
            var company_id = $('#company_id').val();

            $('#loan_plan') .empty();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.loan.getplanlist') !!}",
                dataType: 'JSON',
                data: {'loan_type':loanType,'company_id': company_id,
},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loan_plan').find('option').remove();
                    $('#loan_plan').append('<option value="">------- Select---- </option>');
                    $.each(response, function (index, value) {
                            $("#loan_plan").append("<option value='"+value.id+"'>"+value.name+" ("+value.code+")</option>");
                        });
                }
            })
		if(loanType != 'G'){
			$('.group_loan_common').hide();
			$('.group_loan_common').val('');
		}else(
			$('.group_loan_common').show()
		)
        })
});

let loanRecoveryTable;
const dataTable = () => {
        loanRecoveryTable = $('#loan_report_outstanding').DataTable({
        processing: true,
        paging: true,
        serialize: true,
        serverSide :true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.loan.reportList') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter').serializeArray(),
                d.title = $('#report_title').val()
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},
            {data: 'created_date', name: 'created_date'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'account_number', name: 'account_number'},
            {data: 'group_loan_common_id', name: 'group_loan_common_id'},
            {data: 'customer_id', name: 'customer_id'},
            {data: 'member_name', name: 'member_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'loan_type', name: 'loan_type'},
            {data: 'emi_type', name: 'emi_type'},
            {data: 'emi_period', name: 'emi_period'},
            // {data: 'roi', name: 'roi'},
            {data: 'total_deposit', name: 'total_deposit'},
            {data: 'loan_amount', name: 'loan_amount'},
            {data: 'outstanding_amount', name: 'outstanding_amount'},
            {data: 'action', name: 'action'},
        ],"ordering": false,
    });
    $(loanRecoveryTable.table().container()).removeClass( 'form-inline' );

    }
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");

        $(".table-section").removeClass('hideTableData');

        (!loanRecoveryTable ||  !$.fn.DataTable.isDataTable('#loan_report_outstanding') ) ?  dataTable() : loanRecoveryTable.ajax.reload();
       
    }
    
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('.from_date').val('');
    $('.to_date').val('');
    $('#company_id').val('');
    $('#branch').val('');
    $('#customer_id').val('');

    $('#date').val('');
    $('#loan_account_number').val('');
     $('#branch_id').val('');
    $('#member_name').val('');
    $('#member_id').val('');
    $('#associate_code').val('');
    $('#loan_type').val(''); 
    $('#loan_plan').val(''); 
    $('#emi_option').val('');
    $('#status').val('');
    $('#emi_option').val('');
	$('#group_loan_common_id').val('');	
    $(".table-section").addClass("hideTableData");
    loanRecoveryTable.draw();
}
// function loanSearchForm()
// {  
//     if($('#loan-filter').valid())
//     {
//         $('#is_search').val("yes");
//         loanRequestTable.draw();
//     }
// }
// function loanResetForm()
// {
//     var form = $("#loan-filter"),
//     validator = form.validate();
//     validator.resetForm();
//     form.find(".error").removeClass("error");
//     $('#is_search').val("no");
//     $('#date').val('');
//     $('.from_date').val('');
//     $('.to_date').val('');
//     $('#branch_id').val('');
//     $('#application_number').val('');
//     $('#member_name').val('');
//     $('#member_id').val('');
//     $('#associate_code').val('');
//     $('#plan').val(''); 
//     $('#status').val(''); 
//     loanRequestTable.draw();
// }
// function searchGroupLoanForm()
// {  
//     if($('#grouploanfilter').valid())
//     {
//         $('#is_search').val("yes");
//         groupLoanRecoveryTable.draw();
//     }
// }
// function resetGroupLoanForm()
// {
//     var form = $("#grouploanfilter"),
//     validator = form.validate();
//     validator.resetForm();
//     form.find(".error").removeClass("error");
//     $('#is_search').val("no");
//     $('#date').val('');
//     $('.from_date').val('');
//     $('.to_date').val('');
//     $('#loan_account_number').val('');
//     $('#member_name').val('');
//     $('#member_id').val('');
//     $('#associate_code').val('');
//     $('#plan').val(''); 
//     $('#status').val(''); 
//     groupLoanRecoveryTable.draw();
// }
// function groupLoanSearchForm()
// {  
//     if($('#group-loan-filter').valid())
//     {
//         $('#is_search').val("yes");
//         groupLoanRequestTable.draw();
//     }
// }
// function groupLoanResetForm()
// {
//     var form = $("#group-loan-filter"),
//     validator = form.validate();
//     validator.resetForm();
//     form.find(".error").removeClass("error");
//     $('#is_search').val("no");
//     $('#branch_id').val('');
//     $('#date').val('');
//     $('.from_date').val('');
//     $('.to_date').val('');
//     $('#loan_account_number').val('');
//     $('#application_number').val('');
//     $('#member_name').val('');
//     $('#member_id').val('');
//     $('#associate_code').val('');
//     $('#plan').val(''); 
//     $('#status').val(''); 
//     groupLoanRequestTable.draw();
// }
function idPrint(elem) {
	document.getElementById(elem).style.marginTop = "0px";
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
                   header: false,               // prefix to html
                  footer: false,  
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {  })
                });
}
</script>