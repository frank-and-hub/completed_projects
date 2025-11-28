<script>
    $(document).ready(function(){
        var company_associate_setting;
        $('.select2').select2();
        $('#company_associate_setting_form').validate({
            rules: {
                company_id: {
                    required: true,
                },
            },
            messages: {
                company_id: {
                    required: "Please select Company Name",
                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(this).removeClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
       
        company_associate_setting = $('#CompanyAssociatesListing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            sorting: false,
            bFilter: false,
            ordering: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#CompanyAssociatesListing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },

            ajax: {
                "url": "{!! route('admin.companies.companyAssociatesListing') !!}",
                "type": "POST",
                "data": function(d) {
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company_id',
                    name: 'company_id'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status'
                },
            ],"ordering": false
        });
        $('#submit_associate_setting_form').on('click',function() {
            if($('#company_associate_setting_form').valid()){
                var company_associate_setting_form = new FormData(document.forms['company_associate_setting_form']);
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.companies.associate_store') !!}",
                    dataType: 'JSON',
                    data: company_associate_setting_form,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var data = JSON.parse(JSON.stringify(response));
                        if (data.message == 'create' && data.status > 0) {
                            company_associate_setting.draw();
                            swal('Success','Associate Setting Updated successfully','success');
                        }else if(data.message == 'change'){
                            company_associate_setting.draw();
                            swal('Success','Associate Setting Updated Successfully','success');
                        }else{
                            company_associate_setting.draw();
                            swal('Error','Company Associate Setting Not Updated ! ','error');
                        }
                    },
                });
            }
        });
    });
</script>