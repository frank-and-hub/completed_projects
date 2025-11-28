<script type="text/javascript">

 $('document').ready(function(){
$('#date').datepicker({
        format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
$('#to_date').datepicker({
        format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
$.validator.addMethod("dateDdMm", function(value, element,p) {
     
      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
      {
        $.validator.messages.dateDdMm = "";
        result = true;
      }else{
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;  
      }
    
    return result;
  }, "");

$('#filter').validate({
      rules: {
        date:{ 
           required: true,
            dateDdMm : true,
          }, 
          to_date :{ 
           required: true,
           dateDdMm : true,
          }, 
         /* branch :{ 
           required: true,
          },*/  

      },
      messages: {  
          date:{ 
            required: "Please enter date.",
          },
        
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
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

   $('.export').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('admin.profit-loss.rent.export') !!}");
  $('form#filter').submit();
  });
 detailList = $('#rent').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings ();

                $('html, body').stop().animate({

                    scrollTop: ($('#rent').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.rofit-loss.rent_list') !!}",

                "type": "POST",

                "data":function(d) {
                    d.searchform=$('form#filter').serializeArray(),
                    d.head= $('#head_id').val(),
                    d.label= $('#label').val(),
                    d.date= $('#date').val(),
                    d.branch_id= $('#branch_id').val(),
                    d.to_date= $('#to_date').val()
                 
                        
                },

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

            }, 
             "columnDefs": [{
                "render": function(data, type, full, meta) {
                   
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,
                
            }],
            columns: [

                {data: 'DT_RowIndex', name: 'DT_RowIndex'},

                {data: 'date', name: 'date'},
                {data: 'owner_name', name: 'owner_name'},
                {data: 'rent_type', name: 'rent_type'},
                {data: 'cr', name: 'dr'},
                {data: 'dr', name: 'dr'},
                {data: 'balance', name: 'balance'},

            ]

        });

        $(detailList.table().container()).removeClass( 'form-inline' );  


$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});


 function searchrentForm()
    {  
      $('#is_search').val("yes");

      var is_search=$('#is_search').val(); 
      var date=$('#date').val(); 
      var to_date=$('#to_date').val(); 
      var queryParams = new URLSearchParams(window.location.search);
 
  // Set new or modify existing parameter value. 
      queryParams.set("date", date);
      queryParams.set("to_date", to_date);
        
      // Replace current querystring with the new one.
      window.location.href = "{{url('/')}}/admin/profit-loss/detailed/rent?"+queryParams;
      }

</script>
