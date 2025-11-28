<script type="text/javascript">



$(document).ready(function() {


    
    /**
     * Summary Report Gst Ajax
     * @param start
     */

    gstsummary = $('#gstsummary').DataTable({

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

            "url": "{!! route('admin.gstsummary.listing') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#application_filter_report').serializeArray()}, 

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex',sortable:true},

            {data: 'nature_of_doc', name: 'nature_of_doc',sortable:true},

            {data: 'sr_from', name: 'sr_from'},

            {data: 'sr_to', name: 'sr_to'},

            {data: 'total_number', name: 'total_number'},

            {data: 'cancelled', name: 'cancelled'},

            {data: 'net_issued', name: 'net_issued'},

         

        ],"ordering": false

    });

    $(gstsummary.table().container()).removeClass( 'form-inline' );

      /**
     * Summary Report Gst Ajax
     * @param start
     */
    

       /**
     *CR DR NOTE REport 
     * @param start
     */

    gstCRDRNote = $('#cr_dr_note').DataTable({

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

        "url": "{!! route('admin.gst_cr_dr_note.listing') !!}",

        "type": "POST",

        "data":function(d) {d.searchform=$('form#application_filter_report').serializeArray()}, 

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        },

    },

    columns: [

        {data: 'DT_RowIndex', name: 'DT_RowIndex'},

        {data: 'name_of_recipient', name: 'name_of_recipient'},

        {data: 'gst_in', name: 'gst_in'},

        {data: 'state_name', name: 'state_name'},

        {data: 'pos', name: 'pos'},

        {data: 'invoice_no', name: 'invoice_no'},

        {data: 'invoice_date', name: 'invoice_date'},

        {data: 'invoice_value', name: 'invoice_value'},

        {data: 'invoice_sac', name: 'invoice_sac'},

        {data: 'invoice_good_service_desc', name: 'invoice_good_service_desc'},

        {data: 'invoice_taxable_value', name: 'invoice_taxable_value'},

        {data: 'qty_qunatity', name: 'qty_qunatity'},

        {data: 'qty_unit', name: 'qty_unit'},


        {data: 'igst_rate', name: 'igst_rate'},

        {data: 'igst_amount', name: 'igst_amount'},

        {data: 'cgst_rate', name: 'cgst_rate'},

        {data: 'cgst_amount', name: 'cgst_amount'},

        {data: 'sgst_rate', name: 'sgst_rate'},

        {data: 'sgst_amount', name: 'sgst_amount'},

        {data: 'cess_rate', name: 'cess_rate'},

        {data: 'cess_amount', name: 'cess_amount'},

        
        {data: 'reverse_charge', name: 'reverse_charge'},

        {data: 'ecommerce_op_name', name: 'ecommerce_op_name'},

        {data: 'gst_ecommerce_op_name', name: 'gst_ecommerce_op_name'},

        {data: 'shipping_export_type', name: 'shipping_export_type'},

        {data: 'shipping_no', name: 'shipping_no'},

        {data: 'shipping_date', name: 'shipping_date'},

        {data: 'shipping_part_code', name: 'shipping_part_code'},

        {data: 'receipent_category', name: 'receipent_category'},

        {data: 'item_type', name: 'item_type'},

    

    ],"ordering": false

    });

    $(gstCRDRNote.table().container()).removeClass( 'form-inline' );
/**
 * End CRDRNOTE Report
 * 
 */



   

    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });

    /**
     * Export Summary Report
     */
    
	$('.export_summary').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#export_summary_extension').val(extension);
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
            url :  "{!! route('admin.gst_summary_report.export') !!}",
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

</script>