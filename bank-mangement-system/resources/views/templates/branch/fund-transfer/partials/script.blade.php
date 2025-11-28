<script type="text/javascript">
    var fundTransferReportTable;
    var branchToHoTable;
    $(document).ready(function() {
        var date = $('#date').val();
            /*
			$.ajax({
                type: "POST",  
                url: "{!! route('branch.fundTransfer.getloanmicroamount') !!}",
                dataType: 'JSON',
                data: {'date':date},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    $('#micro_daybook_amount').val(response.microDayBookCurrentAmount);
                    $('#loan_daybook_amount').val(response.loanDayBookCurrentAmount);
                }
            });
		*/
		var branch_id = $('input[name="branch_id"]').val();
		var date = $('input[name="date"]').val();
		// $.ajax({
		// 	type: "POST", 
		// 	url: "{!! route('branch.branchBankBalanceAmount') !!}",
		// 	dataType: 'JSON',
		// 	data: {'branch_id':branch_id,'entrydate':date},
		// 	headers: {
		// 		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 	},
		// 	success: function(response) { 
		// 		// alert(response.balance);
		// 		$('#micro_daybook_amount').val(response.balance);  
		// 	}
		// });
        $('#start_date,#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true,
            endDate: new Date(),
        });
        // $('#date').datepicker({
        //     format: "dd/mm/yyyy",
        //     orientation: "bottom",
        //     autoclose: true,
        //     endDate: new Date(),
        // });
        jQuery('#fund-transfer-head-office').validate({
            rules: {
                date:{ required: true },
                branch_id:{ required: true },
                branch_code:{ required: true },
                transfer_mode:{ required: true },
                company_id:{ required: true },
                transfer_amount: {
                    required:true,
                    number: true,
                    checkAmount:true,
                },
                conform_transfer_amount: {
                    required:true,
                    equalTo: "#transfer_amount"
                },
                bank:{ required: true },
                bank_slip:{ required: true,extension: "jpg|jpeg|png|pdf" }
            },
            submitHandler: function(form) {
                var submitButton = $(form).find('button[type="submit"]');
                submitButton.prop('disabled', true);
                var paymentModeVal = $( "#transfer_mode option:selected").val();
                var transferAmount = $( "#transfer_amount").val();
                if(paymentModeVal == 0){
                    var loanDaybookAmount = $( "#loan_daybook_amount").val();    
                    if(parseInt(transferAmount) > parseInt(loanDaybookAmount)){
                        swal("Error!", "Transfer Amount should be less than or equal to loan daybook amount!", "error");
                        return false;
                    }
                }if(paymentModeVal == 1){
                    var microDaybookAmount = $( "#micro_daybook_amount").val();    
                    if(parseInt(transferAmount) > parseInt(microDaybookAmount)){
                        swal("Error!", "Transfer Amount should be less than or equal to cash in hand!", "error");
                        return false;
                    }
                }
                return true;
            }
        });
        jQuery('#filter').validate({
            rules: {
                company_id:{ required: true },
            },
            messages: {
                company_id:{ required: 'Please select company' },
            }
        });

        jQuery.validator.addMethod("notEqual", function(value, element, param) {
                return this.optional(element) || value != $(param).val();
            },"Please select another bank");
        jQuery('#fund-transfer-bank').validate({
            rules: {
                from_bank:{ required: true, notEqual: "#to_bank" },
                to_bank:{ required: true, notEqual: "#from_bank" },
                from_Bank_account_no:{ required: true },
                to_Bank_account_no:{ required: true },
                from_cheque_number:{ required: true },
                to_cheque_number:{
                    required: true,
                    equalTo: "#from_cheque_number"
                },
                rtgs_neft_charge: {
                    required:true,
                    number: true,
                },
                bank_transfer_amount: {
                    required:true,
                    number: true,
                    checkAmount:true,
                },
                bank_receive_amount: {
                    required:true,
                    equalTo: "#bank_transfer_amount"
                },
                remark:{ required: true },
            },submitHandler: function(form) {
                var submitButton = $(form).find('button[type="submit"]');
                submitButton.prop('disabled', true);
            }
        });
        $(document).on('change','#fund_transfer',function(){
            var fundSection = $(this).val();
            console.log("FFFF", $(this).val());
            if (fundSection == 0) {
                $('#branch-to-ho').css("display", "block");
                $('#bank-to-bank').css("display", "none");
            } else {
                $('#branch-to-ho').css("display", "none");
                $('#bank-to-bank').css("display", "block");
            }
        });
        $(document).on('change','#from_bank', function () {
            var account = $('option:selected', this).val();
            $('#from_Bank_account_no').val('');
            $('.from-bank-account').hide();
            $('.'+account+'-from-bank-account').show();
        });
        $(document).on('change','#to_bank', function () {
            var account = $('option:selected', this).val();
            $('#to_Bank_account_no').val('');
            $('.to-bank-account').hide();
            $('.'+account+'-to-bank-account').show();
        });
        $(document).on('change','#bank', function () {
            var account = $('option:selected', this).val();
            $('#from_Bank_account_no').val('');
            $('.bank-account').hide();
            $('.'+account+'-bank-account').show();
        });
         branchToHoTable = $('#branch_to_ho_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#branch_to_ho_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.fundtransfer.branchtoholisting') !!}",
                "type": "POST",
                "data":function(d) {d.searchBranchToHo=$('form#filter').serializeArray()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            // "columnDefs": [{
            //     "render": function(data, type, full, meta) {
            //         return meta.row + 1; // adds id to serial no
            //     },
            //     "targets": 0
            // }],
            columns: [
                //{title: 'S/N'},
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'date', name: 'date'},
                // {data: 'company', name: 'company'},
                {data: 'branch', name: 'branch'},
                // {data: 'branch_code', name: 'branch_code'},
                {data: 'created_at', name: 'created_at'},
                {data: 'transfer_mode', name: 'transfer_mode'},
                {data: 'transfer_amount', name: 'transfer_amount', 
                "render":function(data, type, row){
                    return row.transfer_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }},
                {data: 'bank', name: 'bank'},
                {data: 'bank_account_number', name: 'bank_account_number'},
                {data: 'bank_slip', name: 'bank_slip'},
                {data: 'status', name: 'status'},
               /* {data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(branchToHoTable.table().container()).removeClass( 'form-inline' );
        var bankToBankTable = $('#bank_to_bank_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#bank_to_bank_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.fundtransfer.banktobranchlisting') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#filter').serializeArray()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [
                {title: 'S/N'},
                {data: 'from_bank', name: 'from_bank'},
                {data: 'bank_account_number', name: 'bank_account_number'},
                {data: 'transfer_mode', name: 'transfer_mode'},
                {data: 'to_bank', name: 'to_bank'},
                {data: 'bank_account_number', name: 'bank_account_number'},
                {data: 'transfer_amount', name: 'transfer_amount'},
                {data: 'remark', name: 'remark'},
                {data: 'status', name: 'status'},
               /* {data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(bankToBankTable.table().container()).removeClass( 'form-inline' );
        fundTransferReportTable = $('#fund_transfer_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "order": [[ 0, "asc" ]],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#fund_transfer_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.fund-transfer.report_lisiting') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#filter').serializeArray()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            // "columnDefs": [{
            //     "render": function(data, type, full, meta) {
            //         return meta.row + 1; 
            //     },
            //     "targets": 0
            // }],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'request_type', name: 'request_type'},
                {data: 'company', name: 'company'},
                {data: 'branch_name', name: 'branch_name'},
                {data: 'branch_code', name: 'branch_code'}, 
                // {data: 'loan_day_book_amount',name:'loan_day_book_amount', 
                //  "render":function(data, type, row){
                //      return row.loan_day_book_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                //  }},
               {data: 'micro_day_book_amount',name:'micro_day_book_amount', 
                 "render":function(data, type, row){
                     return row.micro_day_book_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                 }},
               {data: 'transfer_amount', name: 'transfer_amount', 
                "render":function(data, type, row){
                    return row.transfer_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }},
                {data: 'transfer_date_time', name: 'transfer_date_time'},
                {data: 'transfer_mode', name: 'transfer_mode'}, 
                {data: 'receive_amount', name: 'receive_amount'},
				{data: 'receive_bank_name', name: 'receive_bank_name'},
				{data: 'receive_bank_acc', name: 'receive_bank_acc'},
                {data: 'request_date', name: 'request_date'},
                {data: 'bank_slip', name: 'bank_slip'},
                //{data: 'approve_reject_date', name: 'approve_reject_date'},
                {data: 'status', name: 'status'}
            ],
        });
        $(fundTransferReportTable.table().container()).removeClass( 'form-inline' ); ;
        // $(document).on('change','#date', function () {
        //     var date = $('#date').val();
        //     $.ajax({
        //         type: "POST",  
        //         url: "{!! route('branch.fundTransfer.getloanmicroamount') !!}",
        //         dataType: 'JSON',
        //         data: {'date':date},
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(response) {
        //             $('#micro_daybook_amount').val(response.microDayBookCurrentAmount);
        //             $('#loan_daybook_amount').val(response.loanDayBookCurrentAmount);
        //         }
        //     });
        // });
        $(document).on('change','#transfer_mode', function () {
            var date = $('#date').val();
            if(date == ''){
                var branch = $('#transfer_mode').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false; 
            }
        });
        $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });
        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });
/*
        $('.export').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#report_export').val(extension);
            $('form#filter').attr('action',"{!! route('branch.fundTransfer.export') !!}");
            $('form#filter').submit();
            return true;
        });
		*/
		$('.export').on('click',function(e){
			e.preventDefault();
			var extension = $(this).attr('data-extension');
			$('#report_export').val(extension);
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
			var formData = jQuery('#filter').serializeObject();
			var chunkAndLimit = 50;
			$(".spiners").css("display","block");
			$(".loaders").text("0%");
			doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
			$("#cover").fadeIn(100);
		}else{
			$('#report_export').val(extension);
			$('form#filter').attr('action',"{!! route('branch.fundTransfer.export') !!}");
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
				url :  "{!! route('branch.fundTransfer.export') !!}",
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
		// A function to turn all form data into a jquery object
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
    });
// Filter
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        fundTransferReportTable.draw();
        $(".table-section").addClass("show-table");
    }
}
function searchBranchToHo()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        branchToHoTable.draw();
        $(".table-section").addClass("show-table");
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#end_date').val('');
    $('#branch_name').val('');
    $('#branch_code').val('');
    $('#category').val('');
    $('#status').val('');
    
    $('#company_id').val('');
    $(".table-section").removeClass("show-table");
  $(".table-section").addClass("hide-table");

}
function resetFormHo()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#company_id').val('');
    $(".table-section").removeClass("show-table");
  $(".table-section").addClass("hide-table");

}
</script>