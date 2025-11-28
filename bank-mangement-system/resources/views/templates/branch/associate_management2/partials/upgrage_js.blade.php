<script type="text/javascript">
$(document).on('keyup','#associate_code',function(){
    $('#associate_detail').html('');
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associter_dataGet') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'upgrade'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#associate_detail').show();
                  $('#associate_detail').html(response.view);
                  if(response.carder>=3)
                  {
                    $('#hide_associate').hide();
                  }
                  else
                  {
                    $('#hide_associate').show();
                  }
                  $.ajax({
                          type: "POST",  
                          url: "{!! route('branch.getCarderForUpgrade') !!}",
                          dataType: 'JSON',
                          data: {'id':response.carder,'type':'upgrade'},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                            $('#upgrade_carder').find('option').remove();
                            $('#upgrade_carder').append('<option value="">Select Carder</option>');
                             $.each(response.carde, function (index, value) { 
                              if(value.id<=3)
                              {
                                    $("#upgrade_carder").append("<option value='"+value.id+"'>"+value.name+"("+value.short_name+")</option>");
                                  }
                                }); 

                          }
                      });
                  
                  
                }
                else
                {
                  
                  if(response.msg_type=="error1")
                  { 
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate Inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
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
         $('#upgrade_carder').find('option').remove();
         $('#upgrade_carder').append('<option value="">Select Carder</option>');
    }
</script>