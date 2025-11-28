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
        start_date:{ 
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
          start_date:{ 
            required: "Please enter date.",
          },
          branch:{ 
            required: 'Please select branch.'
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
   $('#type').val(1);
  $('form#filter').attr('action',"{!! route('admin.profit-loss.panel.report.export') !!}");
  $('form#filter').submit();
  });
  
 detailList = $('#penal_list').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings ();

                $('html, body').stop().animate({

                    scrollTop: ($('#penal_list').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

                return nRow;

            },

            ajax: {

                "url": "{!! route('admin.profit-loss.penal_interest_list') !!}",

                "type": "POST",

                "data":function(d) {
                    var page = ($('#penal_list').DataTable().page.info());
                    var currentPage  = page.page+1;
                    d.pages = currentPage,
                    d.searchform=$('form#filter').serializeArray(),
                    d.head= $('#head_id').val(),
                    d.label= $('#label').val(),
                     d.date= $('#date').val(),
                     d.to_date= $('#to_date').val(),
                      d.branch_id= $('#branch_id').val()
                   
                        
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
                {data: 'employee_code', name: 'employee_code'},
                {data: 'employee_name', name: 'employee_name'},
                 {data: 'type', name: 'type'},
                {data: 'cr', name: 'cr'},
                {data: 'dr', name: 'dr'},
                {data: 'balance', name: 'balance'},
                //{data: 'amount', name: 'amount'},
            ],"ordering": false

        });

        $(detailList.table().container()).removeClass( 'form-inline' );  


$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});

function searchpenalIntersetForm ()
    {  
      detailList.ajax.reload();
  }
   
function resetForm()
{
    location.reload();
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
  
    var is_search=$('#is_search').val();
     var start_date=$('#default_date').val(); 
     var end_date=$('#default_end_date').val(); 
     var head= $('#head_id').val();
     var branch_id= $('#branch_id').val()
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("to_date", end_date);
queryParams.set("head", head);
queryParams.set("branch_id", branch_id);
 
// Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/profit-loss/detailed/penal?"+queryParams;
}

</script>