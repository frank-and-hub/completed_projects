<script type="text/javascript">
$(document).ready(function () {

  var id='@if($accountDetail) {{ $accountDetail->id  }} @endif';
  var code='{{ $code }}';
  var passbookTable;

  passbookTable = $('#listtansaction').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [10, 20, 40, 50, 100],
      aaSorting: false,
      ordering: false,
      "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
          var oSettings = this.fnSettings ();
          $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
          return nRow;
      },
        ajax: {
            "url": "{!! route('admin.investment.transaction_listing') !!}",
            "type": "POST",
            "data":{'id':id,'code':code}, 
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
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'tranid', name: 'tranid'},
			      {data: 'tran_by',name: 'tran_by'},
            {data: 'date', name: 'date'},
            {data: 'trans_date', name: 'trans_date'},
            {data: 'description', name: 'description'},
            {data: 'reference_no', name: 'reference_no'},
            {data: 'withdrawal', name: 'withdrawal',
                "render":function(data, type, row){
                if ( row.withdrawal ) {
                    return row.withdrawal+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
              }, 
            {data: 'deposit', name: 'deposit',
                "render":function(data, type, row){
                if ( row.deposit ) {
                    return row.deposit+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
              },
            {data: 'opening_balance', name: 'opening_balance',
                "render":function(data, type, row){
                if ( row.opening_balance ) {
                    return row.opening_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
              },
              {data: 'action', name: 'action'}, 
        ],"ordering": false
    });
    
    $(passbookTable.table().container()).removeClass( 'form-inline' );
  
   
$('#fillter').validate({
      rules: {
        transaction_id_from: {
            required: true,
            number : true,
          }, 
          transaction_id_to: {
            required: true,
            number : true,
          },  

      },
      messages: {
          
          transaction_id_from: {
            required: "Please enter Transaction ID.",
            number : "Please enter a valid number.",
          },
          transaction_id_to: {
            required: "Please enter Transaction ID.",
            number : "Please enter a valid number.",
          },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
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

      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

});

</script>