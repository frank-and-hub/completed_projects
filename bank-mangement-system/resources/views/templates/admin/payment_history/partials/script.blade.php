<script type="text/javascript">

	$(document).ready(function(){
		var date = new Date();
		// Start date
		$('#start_date').datepicker({
			format:'dd/mm/yyy',
			orientation:'bottom auto',
			todayHighlight:true,
			autoclose:true,
			endDate:date
		})

		// End date
		$('#end_date').datepicker({
			format:'dd/mm/yyy',
			orientation:'bottom auto',
			todayHighlight:true,
			autoclose:true,
			endDate:date
		})

		// Datatable Listing 

		paymentListing = $('#payment_listing').DataTable({
			processing:true,
			serverSide:true,
			pageLength:20,
			lengthMenu:[10,20,40,50,100],
			"fnRowCallBack":function(nRow,aData,iDisplayIndex)
			{
				var oSettings = this.fnSettings();
				$('html,body').stop().animate({
					scrollTop:($('#payment_listing').offset().top)
				},1000);
				$('td:nth-child(1)',nRow).html(oSettings._iDisplayStart+iDisplayIndex+1);
				return nRow;
			},
			ajax: {
	            "url": "{!! route('admin.payment.list_detail') !!}",
	            "type": "POST",
	            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
	            headers: {
	                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	            },
	        },
	        columns: [
	            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
	            {data: 'date', name: 'date'}, 
	            {data: 'payment', name: 'payment'}, 
	            {data: 'reference', name: 'reference'}, 
	            {data: 'v_name', name: 'v_name'}, 
	            {data: 'bill', name: 'bill'}, 
	            {data: 'mode', name: 'mode'},             
	            {data: 'amount', name: 'amount'},             
	            {data: 'action', name: 'action',orderable: false, searchable: false},
	        ],"ordering": false
	    });

  		$(paymentListing.table().container()).removeClass( 'form-inline' );
	})
function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
       	paymentListing.draw();
      
    }
}




function resetForm()
{
    $('#is_search').val("yes");
    $('#start_date').val('');
    $('#end_date').val('');
    $('#type').val('0');
    $('#category').val('');
    $('#rent_type').val('');
    $('#status').val(''); 

    $('#vendor_div').show();
    $('#vendor_div1').hide();
    $('#vendor_div3').hide();
    $('#vendor_div2').hide();;
    $('#vendor_div4').hide();
    $('#r_type').hide();
    $('#cat_div').show();


          vendor_listing.draw();
}
function printDiv(elem) {
   $("#"+elem).print({
                    //Use Global styles
        globalStyles : true,
        //Add link with attrbute media=print
        mediaPrint : true,
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
        deferred: $.Deferred().done(function() {    })
    });
}




var a = ['','one ','two ','three ','four ', 'five ','six ','seven ','eight ','nine ','ten ','eleven ','twelve ','thirteen ','fourteen ','fifteen ','sixteen ','seventeen ','eighteen ','nineteen '];
var b = ['', '', 'twenty','thirty','forty','fifty', 'sixty','seventy','eighty','ninety'];

function inWords (num) {
    if ((num = num.toString()).length > 9) return 'overflow';
    n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return; var str = '';
    str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
    str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
    str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
    str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
    str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'only ' : '';
    return str;
}


$(document).on('click','.printBillPayement',function(){
	var bill_payment_id = $(this).attr("data-row-id");
	$.ajax({
		
		type: "POST",  
		data: {'bill_payment_id':bill_payment_id},
		url: "{!! route('admin.payment.getPaymentBillDetails') !!}",
		dataType: 'JSON',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (data) {
			if(data.bill_records.id > 0){
				
				$("#payment_id,#paymentDate,#reference_number,#payment_mode,#paidToVendor,#amountInWord").val("");
				$(".pcs-total,.over_payment,.bill_number,.bill_date,.bill_amount,.payable_amount").text("");
				
				$("#payment_id").val(data.bill_records.id);
				$("#paymentDate").val(data.bill_records.payment_date);
				$("#reference_number").val("none");
				
				if(data.bill_records.payment_mode == "0"){
					$("#payment_mode").val("Cash");
				}
				if(data.bill_records.payment_mode == "1"){
					$("#payment_mode").val("Cheque");
				}
				if(data.bill_records.payment_mode == "2"){
					$("#payment_mode").val("Online");
				}
				if(data.bill_records.payment_mode == "3"){
					$("#payment_mode").val("ssb");
				}
				
				if(data.bill_records.vendor_detail.name){
					$("#paidToVendor").val(data.bill_records.vendor_detail.name);
				}
				
				if(data.bill_records.payment_type == "CR"){
					$(".pcs-total").text(data.bill_records.deposit);
					$(".over_payment").text(data.bill_records.deposit);
					var paidAmount = inWords(parseInt(data.bill_records.deposit));
					$("#amountInWord").val(paidAmount);
				}
				if(data.bill_records.payment_type == "DR"){
					$(".pcs-total").text(data.bill_records.withdrawal);
					$(".over_payment").text(data.bill_records.withdrawal);
					var paidAmount = inWords(parseInt(data.bill_records.withdrawal));
					$("#amountInWord").val(parseInt(paidAmount));
				}
				
				if(data.bill_records.created_by){
					$("#paymentThrough").val(data.bill_records.created_by);
				}
				
				
				if(data.bill_records.bill_detail!= null && data.bill_records.bill_detail!= ""){
					$(".bill_number").text(data.bill_records.bill_detail.bill_number);
					$(".bill_date").text(data.bill_records.bill_detail.bill_date);
					$(".bill_amount").text(data.bill_records.bill_detail.balance);
					$(".payable_amount").text(data.bill_records.bill_detail.payble_amount);
					
					$(".payment_for_div").css("display","revert");
					$(".detailtable").css("display","revert");
					$(".over_payment_div").css("display","none");
					
				} else {
					$(".payment_for_div").css("display","none");
					$(".detailtable").css("display","none");
					$(".over_payment_div").css("display","revert");
				}
				
			}

		}
	});  
});
</script>