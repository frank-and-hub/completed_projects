<script type="text/javascript">
    $(document).on('change','#loan_category',function(){ 
        $("#plan").val('');
        var category=$('#loan_category').val();
        var company_id=$('#company_id').val();
            $.ajax({
            type: "POST",  
            url: "{!! route('admin.planByLoanCategory') !!}",
            dataType: 'JSON',
            data: {'category':category,'company_id':company_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function(response) { 
            $('#plan').find('option').remove();
            $('#plan').append('<option value="">----Select Plan----</option>');
            
                $.each(response.data, function (index, value) { 
                    $("#plan").append("<option value='"+value.id+"'>"+value.name+"</option>");
                    
                }); 

            }
        });
    });


    $(document).ready(function () {

        var date = new Date();
        const currentDate = $('#adm_report_currentdate').val();
        
        $('#loanpayment_start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date, 
            autoclose: true,
            orientation:'bottom'
        }).datepicker('setDate', currentDate).datepicker('fill');

        $('#loanpayment_end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true, 
            endDate: date,  
            autoclose: true,
            orientation:'bottom'
        }).datepicker('setDate', currentDate).datepicker('fill');
        $('#filter').validate({
            rules: {
                
                loan_category: {
                    required: true,
                },
                company_id: {
                    required: true,
                },
               
            },
            messages: {
               
                loan_category: {
                    required: 'Please select loan category',
                },
                company_id: {
                    required: 'Please select loan category',
                },
                
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });
        
        loanissueReport = $('#loan_issued_list').DataTable({
            processing: true,
            serverSide: true,
            bFilter: false,
            ordering: false,
            pageLength: 20,
            lengthMenu: [20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                var oSettings = this.fnSettings ();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },

            ajax: {
                "url": "{!! route('admin.report.loanissuelist') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'branch', name: 'branch'},
                {data: 'customer_id', name: 'customer_id'},  
                {data: 'member_id', name: 'member_id'}, 
                {data: 'member_name', name: 'member_name'},
                {data: 'account_number', name: 'account_number'},
                {data: 'application_date', name: 'application_date'},
                {data: 'loan_issue_date', name: 'loan_issue_date'},
                {data: 'loan_type', name: 'loan_type'},
                {data: 'tenure', name: 'tenure'},
                {data: 'loan_mode', name: 'loan_mode'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'transfer_amount', name: 'transfer_amount'},
                {data: 'total_recovery_amount', name: 'total_recovery_amount'},
                {data: 'due_amount', name: 'due_amount'},
                {data: 'status', name: 'status'},
                {data: 'closing_date', name: 'closing_date'},
                {data: 'last_date_of_emi', name: 'last_date_of_emi'},
                {data: 'associate_code', name: 'associate_code'},
                {data: 'associate_name', name: 'associate_name'},

            ],
            "bDestroy": true,
        });
        $(loanissueReport.table().container()).removeClass( 'form-inline' );



        $('.export-loan').on('click',function(e){
            
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            //$('#report_export').val(extension);
            var formData = {}
            formData['loanpayment_start_date'] = jQuery('#loanpayment_start_date').val();
            formData['loanpayment_end_date'] = jQuery('#loanpayment_end_date').val();
            formData['loan_category'] = jQuery('#loan_category').val();
            formData['plan'] = jQuery('#plan').val();
            formData['loan_status'] = jQuery('#loan_status').val();
            formData['branch_id'] = jQuery('#branch').val();
            formData['company_id'] = jQuery('#company_id').val();
            formData['application_number'] = jQuery('#application_number').val();
            formData['member_id'] = jQuery('#member_id').val();
            formData['member_name'] = jQuery('#member_name').val();
            formData['account_number'] = jQuery('#account_number').val();
            formData['is_search'] = jQuery('#is_search').val();
            formData['export'] = jQuery('#export').val();
            var chunkAndLimit = 50;
            $(".spiners").css("display","block");
            $(".loaders").text("0%");
            doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit,1);
            $("#cover").fadeIn(100);
            
        });
    

        // function to trigger the ajax bit
        function doChunkedExport(start,limit,formData,chunkSize,page){
            formData['start']  = start;
            formData['limit']  = limit;
            
            formData['page']  = page;
            
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url :  "{!! route('admin.loanissuelist.report.export') !!}",
                data : formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if(response.result=='next'){
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExport(start,limit,formData,chunkSize,page);
                        
                        $(".loaders").text(response.percentage+"%");
                    }else{
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display","none");
                        $("#cover").fadeOut(100); 
                        window.open(csv, '_blank');
                    }
                }
            });
        }
    
        $('#filter').validate({
            rules:{
                application_number:{
                number:true,
                },
                member_id:{
                number:true,
                },
            },
        }) 

        $( document ).ajaxStart(function() {
            $( ".loader" ).show();
        });
        $( document ).ajaxComplete(function() {
            $( ".loader" ).hide();
        });
    });  

    
    // search Filter and Data

    function searchForm()
        {  
            if($('#filter').valid())
            {
                $('#is_search').val("yes");
                $(".table-section").removeClass('datatable');
                loanissueReport.draw();
            }
        }

    // Form Reset Function start
    function resetForm() {
        var form = $("#filter");
        // validator = form.validate();
        // validator.resetForm();
        form.find(".error").removeClass("error");
        $('.table-section').addClass('datatable');
        
        $('#is_search').val('no'); 
        $('#loanpayment_start_date').val('');
        $('#loanpayment_end_date').val('');
        $('#loan_category').val('');
        $('#plan').val('');
        $('#branch').val('');
        $('#member_id').val('');
        $('#company_id').val('0');
        $('#company_id').trigger("change"); 
        $('#member_name').val('');
        $('#account_number').val('');
        // $(".table-section").removeClass("show-table");
        // $(".table-section").addClass("hide-table");
        loanissueReport.draw();
        
        
    }
        // Form Reset Function end
</script>