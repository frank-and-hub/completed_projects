<script type="text/javascript">
    let memberTable;
$(document).ready(function () {


    $('#start_date').datepicker({
        format:'d/m/yyyy',
        orientation:'bottom',
        autoclose:true,
    })
     memberTable = $('#investment_report_isting').DataTable({
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
            "url": "{!! route('branch.investement.dailyReportListing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray(),d.slug = $('#slug').val()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'opening_date', name: 'opening_date'},
            {data: 'current_date', name: 'current_date'},
            // {data: 'so_name', name: 'so_name'},
            // {data: 'ro_name', name: 'ro_name'},
            // {data: 'zo_name', name: 'zo_name'},
            {data: 'member', name: 'member'},
            {data: 'member_id', name: 'member_id'},
            {data: 'mobile_no', name: 'mobile_no'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'account_no', name: 'account_no'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
            {data: 'deno_amount', name: 'deno_amount'},
            {data: 'due_emi', name: 'due_emi'},
            {data: 'due_emi_amount', name: 'due_emi_amount'},
           
        ]
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

    $('.export').on('click',function(e){
        e.preventDefault();
        var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
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
            url :  "{!! route('branch.investement_report.export') !!}",
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
    $('#is_search').val("yes");
   
    $('#plan').val('');
    $('#scheme_account_number').val('');
    $('#associate_code').val('');
    memberTable.draw();
}

 
</script>