<script type="text/javascript">

var loanReport;

$(document).ready(function () {

    var date = new Date();

    $('#start_date').datepicker({

        format: "dd/mm/yyyy",

        todayHighlight: true,  

        endDate: date, 

        autoclose: true,
        orientation:'bottom'
    });

    $('#end_date').datepicker({

        format: "dd/mm/yyyy",

        todayHighlight: true, 

        endDate: date,  

        autoclose: true,
        orientation:'bottom'
    });

    loanReport = $('#group_loan_list').DataTable({

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

            "url": "{!! route('admin.report.grouploanlist') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},

            {data: 'status', name: 'status'},

            {data: 'applicant_name', name: 'applicant_name'},

            {data: 'applicant_phone_number', name: 'applicant_phone_number'},

            {data: 'membership_id', name: 'membership_id'},

            {data: 'account_number', name: 'account_number'},  

            {data: 'branch', name: 'branch'},  

            {data: 'sector', name: 'sector'},   

            {data: 'member_id', name: 'member_id'},          

            {data: 'sanctioned_amount', name: 'sanctioned_amount'},

            {data: 'sanctioned_date', name: 'sanctioned_date'}, 

            {data: 'emi_rate', name: 'emi_rate'},

            {data: 'no_of_installement', name: 'no_of_installement'}, 

            {data: 'loan_mode', name: 'loan_mode'},

            {data: 'loan_type', name: 'loan_type'}, 

            {data: 'loan_issue_date', name: 'loan_issue_date'},

            {data: 'loan_issue_mode', name: 'loan_issue_mode'}, 

             {data: 'cheque_no', name: 'cheque_no'},

            {data: 'total_recovery_amount', name: 'total_recovery_amount'},

            {data: 'total_recovery_emi_till_date', name: 'total_recovery_emi_till_date'},

            {data: 'closing_amount', name: 'closing_amount'},

            {data: 'balance_emi', name: 'balance_emi'},       

            {data: 'emi_should_be_received_till_date', name: 'emi_should_be_received_till_date'},            

            {data: 'future_emi_due_till_date', name: 'future_emi_due_till_date'},     

            {data: 'date', name: 'date'},                 

            {data: 'co_applicant_name', name: 'co_applicant_name'},

            {data: 'co_applicant_number', name: 'co_applicant_number'}, 

            {data: 'gurantor_name', name: 'gurantor_name'},

            {data: 'gurantor_number', name: 'gurantor_number'}, 

            {data: 'applicant_address', name: 'applicant_address'},

            {data: 'first_emi_date', name: 'first_emi_date'}, 

            {data: 'loan_end_date', name: 'loan_end_date'},

            {data: 'total_deposit_till_date', name: 'total_deposit_till_date'}, 

        ]

    });
    $(loanReport.table().container()).removeClass( 'form-inline' );

    $('.export-loan').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.grouploan.report.export') !!}");
        $('form#filter').submit();
    }); 

    $('#filter').validate({

      rules:{

        application_number:{

          number:true,

        },

        member_id:{

          number:true,

        },

      },
    }) 

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});



function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        loanReport.draw();
    }
}

function resetForm()

{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
    $('#branch_id').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    $('#plan').val('');
    $('#status').val('');
    $('#application_number').val('');
    $('#member_id').val('');
    $('#is_search').val("yes");
    loanReport.draw();
}
</script>