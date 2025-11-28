<script type="text/javascript">
    var maturityTable;
$(document).ready(function () {
  var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    endDate: date, 
    autoclose: true
  });

  $('#end_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
    endDate: date,  
    autoclose: true
  });
     maturityTable = $('#maturity_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#maturity_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.report.associate_business_summary') !!}",
            "type": "POST",
            "data":function(d) {

                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(), 
                d.is_search=$('#is_search').val(),
                d.export=$('#export').val()
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'branch', name: 'branch'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'designation_name', name: 'designation_name'},
            {data: 'category_name', name: 'category_name'},            
            {data: 'month', name: 'month'},
            {data: 'year', name: 'year'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(maturityTable.table().container()).removeClass( 'form-inline' );

 
    
     

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


    $('#filter11').validate({
      rules: {
      //  status:"required",  

      },
       messages: {  
     //     status: "Please select status",
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

 

 
});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        maturityTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error"); 
 
    $('#branch').val('');
    $('#category').val('');
    $('#designation').val('');
    $('#month').val('');
    $('#year').val(''); 
    $('#status').val('active');

    maturityTable.draw();
}

</script>