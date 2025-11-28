<script type="text/javascript">


  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
$(document).ready(function(){
        
        $('#getHeadList').validate({
            rules:{
                financial_year:{
                    required:true,
                }, 
            },
            messages:{
              financial_year:{
                "required":"Please select financial year."
              }, 
            },
        })

  
        $(document).on('click','.export',function(){
        var extension = $(this).attr('data-extension');
        if($('#getHeadList').valid()){
            $('#export').val(extension);
            $('form#getHeadList').attr('action',"{!! route('admin.head_closing.export') !!}");
            $('form#getHeadList').submit();
          }else{
            $('#export').val('');
          }
      });

    $('#formgethead').on('click',function(){
      $('#head_closing_value_show').html(' '); 
       if($('#getHeadList').valid())
        {
          	var financial_year = $('#financial_year').val();
            var type_page = $('#type_page').val();  
            var company_id = $('#company_id').val();  
            var branch_id = $('#branch').val();  

            
          	$.ajax({
                      type: "POST",  
                      url: "{!! route('admin.get.closing_head_list') !!}",
                      dataType: 'JSON',
                      data: {'financial_year':financial_year,'type':type_page,'company_id':company_id,'branch_id':branch_id},
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      },
                       success: function(response) {      				
                          
                          if(response.msg_type=="success")
                          {
                            $('#head_closing_value_show').html(response.view); 
                          }
                          else if(response.msg_type=="closing_add")
                          {
                            $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>Amount already added in selected financial year!</strong> </div>');  
                          }
                          else
                          { 
                            $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>Record not found!</strong> </div>');
                            
                          }



                      }
                  });
          }
   }) 

   
    
		
})
</script>