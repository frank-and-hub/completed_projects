<script type="text/javascript">

var empLedger;



$(document).ready(function() {



    empLedger = $('#emp_ledger').DataTable({

        processing: true,

        serverSide: true,

        pageLength: 100,

        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      

            var oSettings = this.fnSettings ();

            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

            return nRow;

        },

        ajax: {

            "url": "{!! route('admin.hr.employee.ledger_listing') !!}",

            "type": "POST",

            "data":function(d) {



                d.searchform=$('form#filter').serializeArray(), 

                d.liability=$('#liability').val()

            

            },              

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 

            //{data: 'company_name', name: 'company_name'},

            {data: 'date', name: 'date'},

        /*    {data: 'owner_name', name: 'owner_name'},

            {data: 'owner_mobile_number', name: 'owner_mobile_number'}, */

            {data: 'description', name: 'description'},

            {data: 'reference_no', name: 'reference_no'},

             {data: 'amount', name: 'amount', 
               "render":function(data, type, row){
                    if ( row.amount>0 ) {
                        return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                    }else {
                        return " ";
                    }
                }
            },
            // {data: 'deposit', name: 'deposit', 
            //     "render":function(data, type, row){
            //         if ( row.deposit>0 ) {
            //             return row.deposit+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            //         }else {
            //             return " ";
            //         }
            //     }
            // },
            // {data: 'opening_balance', name: 'opening_balance', 
            //     "render":function(data, type, row){
            //         if ( row.opening_balance>=0 ) {
            //             return row.opening_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
            //         }else {
            //             return "N/A";
            //         }
            //     }
            // },
            {data: 'payment_type', name: 'payment_type',},
            

            {data: 'payment_mode', name: 'payment_mode',},

        ],"ordering": false

    });

    $(empLedger.table().container()).removeClass( 'form-inline' );





    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });



    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });

});









</script>