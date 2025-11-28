<script type="text/javascript">
    // --------------------------------------document ready function start --------------------------------------------------
    var investment_plans_tenure;
    $(document).ready(function() {

        //set the css on required file 
        $('.required').css('color','red');

        //------------------------------------this is validation js that our months always less then or equal to tenure field || start
        $.validator.addMethod('less', function(value, element, param) {
            return this.optional(element) || Number(value) <= Number($(param).val());
        }, 'Months are less then or equal to Tenure');
        $('[name="tenure"]').on('change blur keyup', function() {
            $('[name="months"]').valid(); // <- trigger a validation test
        });
        //--------------------------------------this is validation js that our months always less then or equal to tenure field || end
        //----------------------------------- Show loading animation while ajax start --------------------------------------------------------------
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        //----------------------------------- Hide loading animation while ajax complete --------------------------------------------------------------
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        var plantable;

        // ----------------------------------custom validation function for min amount and max amount -------------------------------------
        $.validator.addMethod('le', function(value, element, param) {
            return this.optional(element) || Number(value) <= Number($(param).val());
        }, 'Minimum Amout is less then or equal to Max Amount');
        $('input[name="max_amount"]').on('change blur keyup', function() {
            $('input[name="min_amount"]').valid(); // <- trigger a validation test
        });

        //-----------------------------------From validation start --------------------------------------------------------------

        $('#planform').validate({
            rules: {
                // plan_code: {
                //     required: true,
                //     number: true,
                // },
                multiple_deposit: {
                    required: true,
                    number: true,
                },
                min_amount: {
                    le: '#max_amount',
                    required: true,
                    number: true,
                },
                max_amount: {
                    required: true,
                    number: true,
                },
                short_name: {
                    required: true,
                    maxlength: 10
                },
                name: 'required',
                plan_category: 'required',
                company: 'required',
                prematurity: 'required',
                load_against_deposit: 'required',
                death_help: 'required',
                ssb_required:'required',
                death_help:"required",
                

            },
            messages: {
                multiple_deposit: {
                    required: "Multiple Deposit is required",
                    number: "Plase enter numbers only",
                },
                max_amount: {
                    required: "Maximum amount  is required",
                    number: "Plase enter numbers only",
                },
                min_amount: {
                    required: "Minimum amount  is required",
                    number: "Plase enter numbers only",
                },
                short_name: {
                    required: "Short name is required",
                    maxlength: "Max length is {0}",
                },
                death_help: {
                    required:true ,
                   
                },
                name: "Name is required",
                plan_code: 'Plan code is required',
                company: "Company name is required",
                prematurity: "Prematurity is required",
                plan_category: "Plan category is required",
                death_help: "Death Help Deposit is required",
                load_against_deposit: "Loan Against Deposit is required",
                death_help:"Death help required",
                ssb_required:" Select Ssb required",

            },
        });

        //-----------------------------------From validation end --------------------------------------------------------------

        //-----------------------------------Data Table Start------------------------------------------------------------------
        $(document).on('click', '#companyplanfilter', function(e) {
            e.preventDefault();
            // plancompanyid = $('#company_id').val();
            var plantable = $('.plantablecc').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 20,
                ordering: false,
                sorting: false,
                searching: false,
                lengthMenu: [10, 20, 40, 50, 100],
                // "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                //     var oSettings = this.fnSettings();
                //     $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                //     return nRow;
                // },
                ajax: {
                    "url": "{!! route('investment.plan_list') !!}",
                    "type": "POST",
                    data: {
                        'company_id': $('#company_id').val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'plan_code',
                        name: 'plan_code'
                    },
                    {
                        data: 'interest_head_id',
                        name: 'interest_head_id'
                    },
                    {
                        data: 'deposit_head_id',
                        name: 'deposit_head_id'
                    },
                    {
                        data: 'plan_category_code',
                        name: 'plan_category_code'
                    },
                    {
                        data: 'min_deposit',
                        name: 'min_deposit'
                    },
                    {
                        data: 'multiple_deposit',
                        name: 'multiple_deposit'
                    },
                    {
                        data: 'max_deposit',
                        name: 'max_deposit'
                    },
                    {
                        data: 'effective_from',
                        name: 'effective_from'
                    },
                    {
                        data: 'effective_to',
                        name: 'effective_to'
                    },
                    {
                        data: 'company_id',
                        name: 'company_id'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false,
                        "render": function(data, type, row) {
                            if (row.status == 0) {
                                return "<span class='badge badge-danger'>Disabled</span>";
                            } else {
                                return "<span class='badge badge-success'>Active</span>";
                            }
                        }
                    },
                    {
                        data: 'created_by_id',
                        name: 'created_by_id'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "bDestroy": true,
            });
        });
        //-------------------------------------------- Data Table End ------------------------------------------------------------------------
        // $(plantable.table().container()).removeClass('form-inline');

        //----------------------------------- Making slug with name field --------------------------------------------------------------
        $("#name").keyup(function() {
            var Text = $(this).val();
            Text = Text.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
            $("#slug").val(Text);
        });

        

        //----------------------------------- Status change with ajax = start --------------------------------------------------------------
        $(document).on('click', '.sbutton', function() {
            let slug = $(this).data('slug');
            var reload_path = $(this).parent().parent().parent().parent().parent().parent();
            //----------------------------------sweet alert ---------------------------------------------------------
            swal({
                    title: "Are you sure?",
                    text: "Do you want to change the status?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary ",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger ",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        let gdate = $('.gdate').text();
                        $.ajax({
                            url: "{{ route('admin.plan.status') }}",
                            type: "post",
                            data: {
                                'slug': slug,
                                'gdate': gdate
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                if(data.response > 0)
                                {
                                    swal({
                                        title:"Success",
                                        text:"Status changed Successfully!!",
                                        type: "success",
                                    });
                                    $('#companyplanfilter').click();
                                }
                                else
                                {
                                    swal({
                                        title:"Warning",
                                        text:"Saving Plan status can't change!!",
                                        type: "warning",
                                    });
                                    $('#companyplanfilter').click();
                                }
                            },
                            error: function() {
                                alert("error");
                            }
                        });
                    }
                }
            );
        });
        investment_plans_tenure = $('#investment_plans_tenure').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({ scrollTop: ($('#investment_plans_tenure').offset().top)}, 10);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                'url': "{!! route('admin.py-plans.tenure.plan_tenure_listing') !!}",
                'type': "POST",
                'datatype': "JSON",
                "data": function(d) {
                    let totalAmount = (d.json != null) ? d.json.total : 0;
                    let page = ($('#investment_plans_tenure').DataTable().page.info());
                    let currentPage = page.page + 1;
                    d.pages = currentPage;
                    d.searchform = $('form#tenure_form').serializeArray();
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,
            }],
            'columns': [
                {data:'DT_RowIndex'},
                // {data:'plan_code'},
                {data:'plan'},
                {data:'tenure'},
                {data:'roi'},
                {data:'spl_roi'},
                {data:'compounding'},
                {data:'status'},
                {data:'effective_from'},
                {data:'action'},
            ],
        });


        // $(investment_plans_tenure.table().container()).removeClass('form-inline');
        var plantable = $('#commissionLoanDetails_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            ordering: false,
            sorting: false,
            searching: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{{-- route('admin.loan.commission.listing') --}}",
                "type": "post",
                "data": {
                    'id': $("#loan_type_id").val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'tenure_type',
                    name: 'tenure_type'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'status',
                    name: 'status',
                    "render": function(data, type, row) {
                        if (row.status == 0) {
                            return "<span class='badge badge-danger'>Inactive</span>";
                        } else {
                            return "<span class='badge badge-success'>Active</span>";
                        }
                    }
                },
                // {data: 'head_id', name: 'head_id'},
                {
                    data: 'effective_from',
                    name: 'effective_from'
                },
                {
                    data: 'effective_to',
                    name: 'effective_to'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ]
        });
        $(plantable.table().container()).removeClass('form-inline');

        $(document).on('click','.status_btn',function()
        {
            let gdate = $('.gdate').text();
            var id = $(this).data('id');
            swal({
                title: 'Status Change',
                text: 'Are you sure want to change the status?',
                type: 'warning',
                confirmButtonText: 'Yes',
                showCancelButton: true,
                cancelButtonClass: 'btn-danger',
            },
            function(isConfirm){
                if(isConfirm)
                {
                    $.ajax({
                        url: "{{ route('admin.py-plans.tenure.status') }}",
                        type: "post",
                        data: {
                            'date': gdate,
                            'id': id
                        },
                        success: function(data)
                        {
                            if(data.response > 0)
                            {
                                swal({
                                    title: 'Status Changed',
                                    text: 'Status Changed Successfully.',
                                    type: 'success',
                                });
                                window.location.reload();
                            }
                        },
                        error: function()
                        {
                            alert('error');
                        }
                    });
                }
            });
        });
    });
    //----------------------------------- Status change with ajax = end ------------------------------

    // ----------------------------------jquery document ready function end ----------------------------------------------------------

    // ------------------ validator for more then greaterThanZero value only -------------------
    jQuery.validator.addMethod("greaterThanZero", function(value, element) {
        return this.optional(element) || (parseFloat(value) > 0);
    }, "Amount must be greater than Zero");
    // ------------------ validator for more then greaterThanZero value only ENd -------------------
    $.validator.addMethod("dateDdMm", function(value, element, p) {
        if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g
            .test(value) == true) {
            $.validator.messages.dateDdMm = "";
            result = true;
        } else {
            $.validator.messages.dateDdMm = "Please enter valid Date.";
            result = false;
        }
        return result;
    }, "");


    jQuery.validator.addMethod("greater", function(value, element, parm) {
        return this.optional(element) || (Number(value) >= Number($(parm).val()));
    }, "Amount must be greater than Zero");

    // $('#effective_from').on('click',function(){
    var date = new Date();
    $('#effective_from').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        // endDate: date,
        autoclose: true,
        orientation: "bottom",
    });
    // });
    // form validation for tenure
    $('#tenure_form').validate({
        rules: {
            'tenure': {
                required: true,
                number: true,
                greaterThanZero: 0,
            },
            'month_from': {
                less: '#tenure',
                required: true,
                number: true,
                greaterThanZero: 0,
            },
            'month_to': {
                required: true,
                number: true,
                greaterThanZero: 0,
                greater: '#month_from',
            },
            'roi': {
                required: true,
                number: true,
                greaterThanZero: 0,
            },
            'spl_roi': {
                required: true,
                number: true,
                greaterThanZero: 0,
            },
            'effective_from': {
                required: true,
                dateDdMm: true,
            },
        },
        messages: {
            'tenure': {
                greaterThanZero: 'Tenure is must be greater then 0',
            },
            'month_from': {
                greaterThanZero: 'Months from is must be greater then 0',
            },
            'month_to': {
                greaterThanZero: 'Months to is must be greater then 0',
                greater: 'Months to is must be greater then months from',
            },
        }
    });
    // form validation for tenure end
    
    $(document).on('click', ".close",function() {
        $('#modelDiv').html('');
        });

        $(document).on('click','.view_data', function() {
            let Id = $(this).data('id');
            let tenureId = $(this).data('tenure');
            let planId = $(this).data('plan_id');
          $.ajax({
            type: "POST",
            url: "{{route('admin.investment.plan.commissionPercentage.modelShow')}}",
            data: {
                planId: planId,
              tenureId : tenureId,
              id : Id,
            },
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            dataType: "JSON",
            success: function (response) {
                $('#modelDiv').html(response.view);
            }
          });
        })
</script>