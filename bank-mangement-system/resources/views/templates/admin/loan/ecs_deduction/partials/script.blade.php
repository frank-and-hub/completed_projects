<script type="text/javascript">
    $(document).ready(function()
    {
        var ecs_deduction = '';
        $('#searchForm').validate({
            rules:{
                loan_type:'required',
            },
            messages:{
                loan_type:'Please select the loan type.'
            }
        });

        $('#emi_due_date, #emi_due_to_date').datepicker({
            format: 'dd/mm/yyyy',
        });

        $('#reset_form').on('click',function()
        {
            $('#loan_type').find('option[value=""]').prop('selected',true);
            $('#ecs_type').find('option[value=""]').prop('selected',true);
            $('#company_id').val('');
            $('#emi_due_date').val('');
            $('#emi_due_to_date').val('');
            $('.data_div').addClass('d-none');
        });
        
        
        ecs_deduction = $('#ecs_deduction').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 20,
                lengthMenu: [10, 20, 40, 50, 100],
                ordering: false,
                searching: false,
                ajax: {
                    "url": "{!! route('admin.loan.ecsDeduction.listing') !!}",
                    "type": "POST",
                    "data":function(d){
                        d.searchData = $('#searchForm').serializeArray()
                    }
                },
                columns:[
                    {'data': 's_no','name':'s_no',},

                    {'data': 'regan','name':'regan',},

                    {'data':'branch_id','name':'branch_id'},// branch name

                    {'data':'customer_first_last_name','name':'customer_first_last_name'},// customer name

                    {'data':'account_number','name':'account_number'},// account no

                    {'data':'loan_type','name':'loan_type'}, //plan name 

                    {'data':'amount','name':'amount'}, //amount

                    {'data':'transfer_date','name':'transfer_date'},// Saction date

                    {'data':'emi_amount','name':'emi_amount'}, // emi amount

                    {'data':'emi_due_date','name':'emi_due_date'},//emidue date

                    {'data':'emi_mode','name':'emi_mode'}, //emimode

                    {'data':'mobile_no','name':'mobile_no'},

                    {'data':'ecs_ref_no','name':'ecs_ref_no'}, //ecs ref no 
                
                    {'data':'ecs_type','name':'ecs_type'}, //ecs type

                    //{'data':'associate_code','name':'associate_code'},

                    //{'data':'associate_first_last_name','name':'associate_first_last_name'},
                    

            ]});
        $(ecs_deduction.table().container()).removeClass( 'form-inline' );
        
        $('#search').on('click',function()
        {
            var loan_type = $('#loan_type').val();
            var ecs_type = $('#ecs_type').val();
            var date = $('#date').val();
            if($('#searchForm').valid())
            {
                $('.data_div').removeClass('d-none');
                ecs_deduction.draw();
            }
        });

        $('.export').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#ecs_deduction_export').val(extension);
            if(extension == 0)
            {
            var formData = jQuery('#ecs_deduction').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
            $("#cover").fadeIn(100);
            }
            else{
                $('#ecs_deduction_export').val(extension);

                $('form#ecs_deduction').attr('action',"{!! route('admin.loan.ecs_deduction.export') !!}");

                $('form#ecs_deduction').submit();
            }
        
        });
        
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
                
        // function to trigger the ajax bit
        function doChunkedExport(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.loan.ecs_deduction.export') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExport(start,limit,formData,chunkSize);
                        $(".loaders").text(response.percentage+"%");
                    }else{
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display","none");
                        $("#cover").fadeOut(100); 
                        window.open(csv,'_blank');
                    }
                }
            });
        }
        

        jQuery.fn.serializeObject = function(){
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };

        $('.bankExport').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extensions');
            $('#ecs_deduction_export').val(extension);
            if(extension == 1)
            {
                var formData = jQuery('#searchForm').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display","block");
                $(".loaders").text("0%");
                doChunkedExport_bank(0,chunkAndLimit,formData,chunkAndLimit);
                $("#cover").fadeIn(100);
            }
            else{
                $('#ecs_deduction_export').val(extension);
                $('form#ecs_deduction').attr('action',"{!! route('admin.loan.ecs_deduction.bankexport') !!}");
                $('form#ecs_deduction').submit();
            }
        
        });
        
                
        // function to trigger the ajax bit
        function doChunkedExport_bank(start,limit,formData,chunkSize){
            formData['start']  = start;
            formData['limit']  = limit;
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.loan.ecs_deduction.bankexport') !!}",
                data : formData,
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        doChunkedExport_bank(start,limit,formData,chunkSize);
                        $(".loaders").text(response.percentage+"%");
                    }else{
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display","none");
                        $("#cover").fadeOut(100); 
                        window.open(csv,'_blank');
                    }
                }
            });
        }
    });    
</script>