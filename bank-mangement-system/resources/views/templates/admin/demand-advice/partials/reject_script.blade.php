<script type="text/javascript">

    $(document).ready(function() {
        var today=$('.create_application_date').val();
        $('.date-from,.date-to').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom",
        autoclose: true,
        endDate: today,
        maxDate: today,
        startDate: '01/04/2021',


          })

         //Export Reject Redemand Report

    $('.export_reject_report').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#demand_advice_report_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter_report').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#demand_advice_report_export').val(extension);

			$('form#filter_report').attr('action',"{!! route('admin.demandadvice.reject_export') !!}");

			$('form#filter_report').submit();
		}

	});

    $('.required').css('color','red');

    //Company Name on change event for Search Form
        $(document).on('change','#company_id',function(){ 
            var company_id=$('#company_id').val();

                $.ajax({
                    type: "POST",  
                    url: "{!! route('admin.demand-advice.report_reject.fetch.branch') !!}",
                    dataType: 'JSON',
                    data: {'company_id':company_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) { 
                        $('#filter_branch').find('option').remove();
                        $('#filter_branch').append('<option value="">---- Select Branch ----</option>');
                        $.each(response.bankList, function (index, value) { 
                                $("#filter_branch").append("<option value='"+value.branch.id+"'>"+value.branch.name+"</option>");
                            }); 

                    }
                });
        });

    //Form Validation 
    $('#filter_report').validate({
        rules:{
            company_id : 'required',
        },
        messages:
        {
            company_id: 'Select the company.'
        }
    });


	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.demandadvice.reject_export') !!}",
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

    //
        demandAdviceApplicationTable = $('#demand-advice-reject-table').DataTable({

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

            "url": "{!! route('admin.demandadvice.rejectReport.reportlist') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#filter_report').serializeArray()

        },

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},

            {data: 'company_id', name: 'company_id'},

            {data: 'branch_name', name: 'branch_name'},

            {data: 'reason', name: 'reason'},

            {data: 'date', name: 'date'},

            {data: 'account_number', name: 'account_number'},

            {data: 'member_name', name: 'member_name'},

            {data: 'associate_code', name: 'associate_code'},

            {data: 'associate_name', name: 'associate_name'},

            {data: 'ac_opening', name: 'ac_opening'},

            {data: 'advice_type', name: 'advice_type'},

            {data: 'expense_type', name: 'expense_type'},

            

            {data: 'voucher_number', name: 'voucher_number'},

            {data: 'total_amount', name: 'total_amount'},

            {data: 'final_amount', name: 'final_amount'},

            {data: 'tds_amount', name: 'tds_amount'},

            {data: 'maturity_payment_mode', name: 'maturity_payment_mode'},

            // {data: 'interest_amount', name: 'interest_amount'},
            
            

            {data: 'status', name: 'status'},

            // {data: 'action', name: 'action'},

        ],"ordering": false,

        });

        $(demandAdviceApplicationTable.table().container()).removeClass( 'form-inline' );


        $( document ).ajaxStart(function() {

            $( ".loader" ).show();

            });



            $( document ).ajaxComplete(function() {

            $( ".loader" ).hide();

            })
    })

    $(document).on('click','.re_demNS',function(){
    var id = $(this).attr("data-row-id");
    var msg = "Are you sure, you want to re-demand this record?";

    swal({
        title: "Are you sure?",
        text: msg,
        type: "warning",
        buttons: true,
        showCancelButton: true,
    },
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                type: "POST",
                url: "{!! route('admin.demand.re_demand') !!}",
                dataType: 'JSON',
                data: {'id':id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if(response.status == "success"){
                        swal("Success!", response.msg, response.status);
                        demandAdviceApplicationTable.draw();
                    } else {
                        swal("Warning!", response.msg, 'warning');
                        return false;
                    }
                }
            });
        }
    });
});

function searchReportForm()
    {
        if($('#filter_report').valid())
        {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            demandAdviceApplicationTable.draw();
        }
    }



    function resetReportForm()

    {

        var form = $("#filter_report"),

        validator = form.validate();

        validator.resetForm();

        form.find(".error").removeClass("error");

        $('.date-from').val('');

        $('#company_id').val('');

        $('.date-to').val('');

        $('#filter_branch').val('');

        $('#advice_type').val('');

        $('#expense_type').val('');

        $('.advice-type').hide();

        $('#voucher_number').val('');

        $('#is_search').val("no");

        $(".table-section").addClass("hideTableData");
        
        demandAdviceApplicationTable.draw();

    }

</script>
