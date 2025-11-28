<script type="text/javascript">
$(document).on('keyup','#associate_code',function(){

    $('#associate_detail').html('');
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associate_dataGetAll') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'status'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#associate_detail').show();
                  $('#associate_detail').html(response.view);   
                  if(response.carder>3)
                  {
                    $('#hide_associate').hide();
                  }
                  else
                  {
                    $('#hide_associate').show();
                  }                          
                  
                }
                else
                {
                  
                  if(response.msg_type=="error2")
                  { 
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate Blocked!</strong> </div>');
                  }
                  else
                  {
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>');
                  }
                }
              }
          });
    } 
    
  });

$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
function resetForm()
    {
        $('#associate_detail').hide();
        $('#associate_code').val('');
        
    }
</script>