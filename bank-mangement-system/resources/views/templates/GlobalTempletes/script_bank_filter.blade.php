<script type="text/javascript">
    $(document).ready(function(){

        $('#company_id').on('change',function(){
            alert("ghjh");
            const companyId = $('#company_id option:selected').val();
            const companyData = {!! $companyBank !!};
            var appendData = '';
          
            $('#bank').find('option').remove();
            $('#bank').append('<option value="">--Please Select Branhc --</option>');
            $.each(companyData[companyId],function(index,value){
                console.log(value);
               if(value?.bank_name )
               {
                $("#bank").append("<option  value=" + value?.id + ">" + value?.bank_name + "</option>");
               }
                
               
            });
           

        });
       
    });

</script>