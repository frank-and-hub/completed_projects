<script type="text/javascript">
   
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
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#name').val('');
    $('#member_id').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');
    $('#associate_code').val(''); 

    memberTable.draw();
}

 
 
</script>