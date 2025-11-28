<!-- <script type="text/javascript">
    $(document).ready(function(){
        $('#company_id').on('change',function(){
            const companyId = $('#company_id option:selected').val();
            const companyData = {!! $companyBranch !!};
            var appendData = '';

            $('#branch').find('option').remove();
            $('#branch').append('<option value="">--Please Select Branch --</option>');

            $.each(companyData[companyId],function(index,value){
               if(value?.branch?.name )
               {
                $("#branch").append("<option data-val=" + value?.branch?.state_id + "  data-code="+value?.branch?.branch_code+" value=" + value?.branch_id + ">" + value?.branch?.name + "</option>");
               }


            });


        });

        $('#company_id').val("{{$selectedCompany}}");
        $('#company_id').trigger("change");
        $('#branch').val("{{$selectedBranch}}");

    });

</script> -->
<script type="text/javascript">
    $(document).ready(function(){
        $('#company_id').on('change',function(){
            const companyId = $('#company_id option:selected').val();
            const companyData = {!! $companyBranch !!};
            var appendData = '';
            $('#branch').find('option').remove();
            $('#branch').append('<option value="">--Please Select Branch --</option>');

            if(companyId == '0')
            {
                $('#branch').append('<option value="0">All Branch</option>');
            }
            else{


                @if(isset($all)&&($all!=false))
                    $.each(companyData[companyId],function(index,value){
                        if(value?.branch?.name ){
                            $("#branch").append("<option data-val=" + value?.branch?.state_id + "  data-code="+value?.branch?.branch_code+" value=" + value?.branch_id + ">" + value?.branch?.name + " - "+value?.branch?.branch_code+"</option>");
                        }
                    });
                @else
                    $.each(companyData[companyId],function(index,value){
                        if(value?.branch?.name ){
                                $("#branch").append("<option data-val=" + value?.branch?.state_id + "  data-code="+value?.branch?.branch_code+" value=" + value?.branch_id + ">" + value?.branch?.name + " - "+value?.branch?.branch_code+"</option>");
                        }
                    });
                @endif
            }
        });
        $('#company_id').val("{{$selectedCompany}}");
        $('#company_id').trigger("change");
        $('#branch').val("{{$selectedBranch}}");

    });

</script>
