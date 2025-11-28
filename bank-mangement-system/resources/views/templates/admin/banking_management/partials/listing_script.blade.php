<script type="text/javascript">

var memberTable;

$(document).ready(function () {

    designationTable = $('#designation_listing').DataTable({

        processing: true,

        serverSide: true,

         pageLength: 20,

         lengthMenu: [10, 20, 40, 50, 100],

        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      

            var oSettings = this.fnSettings ();

            $('html, body').stop().animate({

            scrollTop: ($('#designation_listing').offset().top)

        }, 1000);

            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

            return nRow;

        },

        ajax: {

            "url": "{!! route('admin.jv.listing') !!}",

            "type": "POST",

            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

        },

        columns: [

            {data: 'DT_RowIndex', name: 'DT_RowIndex'},

            {data: 'journal', name: 'journal'},

            {data: 'branch', name: 'branch'},

            {data: 'reference_number', name: 'reference_number'},

            // {data: 'status', name: 'status'},

            {data: 'debit', name: 'debit'},

            {data: 'credit', name: 'credit'},

            {data: 'created', name: 'created'}, 

            {data: 'action', name: 'action',orderable: false, searchable: false},

        ],"ordering": false

    });

    $(designationTable.table().container()).removeClass( 'form-inline' );

    $(document).on('click', '.delete-jv-journal', function(e){

        var url = $(this).attr('href');

        e.preventDefault();

        swal({

          title: "Are you sure, you want to delete this entry?",

          text: "",

          icon: "warning",

          buttons: [

            'No, cancel it!',

            'Yes, I am sure!'

          ],

          dangerMode: true,

        }).then(function(isConfirm) {

          if (isConfirm) {

            location.href = url;

          } 

        });

    })

    $( document ).ajaxStart(function() {

        $( ".loader" ).show();

    });

    $( document ).ajaxComplete(function() {

        $( ".loader" ).hide();

    });
	
	
	
	$('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.jv.list.export') !!}");
        $('form#filter').submit();
    });
	
	

});

</script>