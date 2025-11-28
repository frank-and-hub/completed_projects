@extends('templates.admin.master')
@php
    $dropDown = $company;
    $filedTitle = 'Company';
    $name = 'company_id';
@endphp
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">
                            @if ($type == 1)
                                Financial Year Filter
                            @else
                                Add Head Closing
                            @endif
                        </h6>
                    </div>
                    <?php
                    // $aaaaa = DB::select('call headSum(?,?,?,?,?,?,?,?,?)',["55","CR","01","01","2022","1","02","01","2022"]);
                    //   echo $aaaaa[0]->headAmount;die;
                    //  $aaaaa = DB::select('call getAllHead(?)',["1"]);
                    //    echo $aaaaa[0]->headVal;die;
                    ?>
                    <div class="card-body">
                        {{ Form::open(['url' => '#', 'method' => 'post', 'id' => 'getHeadList', 'name' => 'getHeadList', 'enctype' => 'multipart/form-data']) }}
                        <input type="hidden" class="form-control create_application_date" id="globalDate">
                        <div class="row">
                            @include('templates.GlobalTempletes.new_role_type', [
                                'dropDown' => $company,
                                'filedTitle' => 'Company',
                                'name' => 'company_id',
                                'value' => '',
                                'multiselect' => 'false',
                                'design_type' => 6,
                                'branchShow' => true,
                                'branchName' => 'branch_id',
                                'apply_col_md' => true,
                                'multiselect' => false,
                                'placeHolder1' => 'Please Select Company',
                                'placeHolder2' => 'Please Select Branch',
                                'selectedCompany' => 1,
                            ])
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Financial Year <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="financial_year" name="financial_year">
                                            <option value="">Select Financial Year </option>
                                            @foreach (getFinancialYear() as $key => $value)
                                                <option value="{{ $value }}">{{ $value }} </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="type_page" id="type_page" value="{{ $type }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            id="formgethead">Submit</button>
                                        <input type="hidden" name="export" id="export" value="">
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                            onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div class="col-md-12" id="head_closing_value_show">
            </div>
        </div>
    </div>
@stop
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('click', '#settlebalancesheet', function() {
                const url = "{!! route('admin.balance.closed_balanceSheet') !!}";
                const financialYear = $('#financial_year option:selected').val();
                const branch_id = $('#branch option:selected').val();
                const libiliyAmount = $('#LIABILITY').attr('data-amount');
                const assetAmount = $('#ASSETS').attr('data-amount');
                const incomeAmount = $('#INCOMES').attr('data-amount');
                const expenseAmount = $('#EXPENSES').attr('data-amount');
                const libiliyHeadId = $('#LIABILITY').attr('data-head');
                const assetHeadId = $('#ASSETS').attr('data-head');
                const incomeHeadId = $('#INCOMES').attr('data-head');
                const expenseHeadId = $('#EXPENSES').attr('data-head');
                console.log(financialYear, branch_id, libiliyAmount, assetAmount, incomeAmount,
                    expenseAmount, libiliyHeadId, assetHeadId, incomeHeadId, expenseHeadId);
                $.post(url, {
                    financialYear: financialYear,
                    branch_id: branch_id,
                    libiliyAmount: libiliyAmount,
                    assetAmount: assetAmount,
                    incomeAmount: incomeAmount,
                    expenseAmount: expenseAmount,
                    libiliyHeadId: libiliyHeadId,
                    assetHeadId: assetHeadId,
                    incomeHeadId: incomeHeadId,
                    expenseHeadId: expenseHeadId,
                }, function(response) {
                    (response == 200) ? location.reload(): swal('Warning', 'SomeThing Went Wrong!',
                        'warning');
                })
            })
            $(document).on('click', '.export', function(e) {
                e.preventDefault();
                var extension = $(this).attr('data-extension');
                console.log(extension);
                $('#export').val(extension);
                if (extension == 1) {
                    var formData = jQuery('#getHeadList').serializeObject();
                    var chunkAndLimit = 50;
                    $(".spiners").css("display", "block");
                    $(".loaders").text("0%");
                    doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                    $("#cover").fadeIn(100);
                } else {
                    $('#export').val(extension);
                    $('form#getHeadList').attr('action', "{!! route('admin.balance_sheet.head_closing.export') !!}");
                    $('form#getHeadList').submit();
                }
            });
            // function to trigger the ajax bit
            function doChunkedExport(start, limit, formData, chunkSize) {
                formData['start'] = start;
                formData['limit'] = limit;
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: "{!! route('admin.balance_sheet.head_closing.export') !!}",
                    data: formData,
                    success: function(response) {
                        console.log(response);
                        if (response.result == 'next') {
                            start = start + chunkSize;
                            doChunkedExport(start, limit, formData, chunkSize);
                            $(".loaders").text(response.percentage + "%");
                        } else {
                            var csv = response.fileName;
                            console.log('DOWNLOAD');
                            $(".spiners").css("display", "none");
                            $("#cover").fadeOut(100);
                            window.open(csv, '_blank');
                        }
                    }
                });
            }
            jQuery.fn.serializeObject = function() {
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
            // $(document).on('keyup','.aa',function(event){
            //     if( $.isNumeric($(this).val()) == true){
            //         $(this).next('.pl-2').html("");
            //     }else{
            //         $(this).next('.pl-2').html("Please Enter Only Number");
            //     }
            //     $('.aa').each(function() {
            //         if($(this).val()){
            //             if($(this).val().split('-')[0] == ""  ){
            //                 $(this).css("border", "2px solid red");
            //             }else{
            //                 if($(this).val() != "0.00" && $.isNumeric($(this).val()) == true){
            //                     $(this).css("border", "2px solid  green");
            //                 }else{
            //                     $(this).removeAttr("style");
            //                 }
            //             }
            //             if( $.isNumeric($(this).val()) == true){
            //                 $(this).next('.pl-2').html("");
            //             }
            //         }else{
            //             $(this).removeAttr("style");
            //             $(this).next('.pl-2').html("Please Enter Value");
            //         }
            //     });
            // })
            // $(document).on('click','.export',function(){
            //   var extension = $(this).attr('data-extension');
            //   if($('#getHeadList').valid()){
            //       $('#export').val(extension);
            //       $('form#getHeadList').attr('action',"{!! route('admin.head_closing.export') !!}");
            //       $('form#getHeadList').submit();
            //     }else{
            //       $('#export').val('');
            //     }
            // });
            $(document).on('click', '#myformsubmit', function(e) {
                e.preventDefault();
                var myarray = [];
                $('.aa').each(function() {
                    if (!$(this).val()) {
                        $(this).next('.pl-2').html("Please Enter Value");
                        myarray = "error";
                    } else if ($.isNumeric($(this).val()) == false) {
                        $(this).next('.pl-2').html("");
                        $(this).next('.pl-2').html("Please Enter Only Number");
                        myarray = "error";
                    }
                });
                if (myarray == '' && myarray != "error") {
                    var dropdwn = $("#financial_year").val();
                    const branchId = $('#branch option:selected').attr('data-name');
                    const branch_id = $('#branch option:selected').val();
                    const endDate = $('#globalDate').val();
                    const branchName = (branchId == 'all' || typeof(branchId) == 'undefined') ? 'All' :
                        branchId;
                    swal({
                            title: "Are you sure?",
                            text: "Do you want to Add Head Closing Amount ?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-primary",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            cancelButtonClass: "btn-danger",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                    type: 'POST',
                                    url: "{!! route('admin.closing_head.save') !!}",
                                    dataType: 'JSON',
                                    data: $("#myform").serialize(),
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(response) {
                                        $("#financial_year").val('');
                                        if (response.msg_type == "success") {
                                            swal('success', 'Amount Updated Successfully!',
                                                'success');
                                            $('html, body').animate({
                                                scrollTop: $(
                                                        "#head_closing_value_show")
                                                    .offset().top
                                            }, 2000);
                                            location.reload();
                                        } else {
                                            swal('warning', 'Something went wrong!',
                                                'warning');
                                            location.reload();
                                        }
                                    }
                                });
                            }
                        });
                    //var myform = $("#myform").serialize();
                    // $.ajax({
                    //         type: "POST",  
                    //         url: "{!! route('admin.closing_head.save') !!}",
                    //         data: $("#myform").serialize(),
                    //         headers: {
                    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    //         },
                    //         success: function(response) { 
                    //             $("#financial_year").val('');                        
                    //             if(response.msg_type=="success")
                    //             {
                    //                 $('#head_closing_value_show').html('<div class="alert alert-success alert-block"><strong>Amount successfully added </strong></div>');  
                    //                 $('html, body').animate({
                    //                 scrollTop: $("#head_closing_value_show").offset().top 
                    //                 }, 2000);
                    //             }
                    //             else
                    //             {                     
                    //                 $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>'+response.vew+' </strong></div>');
                    //             }
                    //         }
                    // });
                }
            })
        })
        function resetForm() {
            $('#getHeadList')[0].reset();
            $("#head_closing_value_show").html('');
        }
        function myFormreset() {
            $('#myform')[0].reset();
            $('.aa').each(function() {
                $(this).val('0.00');
                $(this).removeAttr("style")
            });
            $('html, body').animate({
                scrollTop: $("#head_closing_value_show").offset().top
            }, 2000);
        }
        function resetFinanceHead() {
            var financial_year = $('#financial_year').val();
            if (financial_year != '') {
                swal({
                        title: "Are you sure?",
                        text: "Do you want to Remove Head Closing Amount ?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-primary",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        cancelButtonClass: "btn-danger",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },function(isConfirm) {
                        if (isConfirm) {
                            $.ajax({
                                type: "POST",
                                url: "{!! route('admin.reset-closing_head') !!}",
                                dataType: 'JSON',
                                data: {
                                    'financial_year': financial_year
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.msg_type == "success") {
                                        $('#head_closing_value_show').html(
                                            '<div class="alert alert-success alert-block">  <strong>Head closing amount removed successfully.</strong> </div>'
                                        );
                                    } else if (response.msg_type == "error") {
                                        $('#head_closing_value_show').html(
                                            '<div class="alert alert-danger alert-block">  <strong>Something went worng!</strong> </div>'
                                        );
                                    }
                                },
                                error: function() {
                                    $('#head_closing_value_show').html(
                                        '<div class="alert alert-danger alert-block">  <strong>Something went worng!</strong> </div>'
                                    );
                                }
                            });
                        }
                    });
            } else {
                $('#head_closing_value_show').html(
                    '<div class="alert alert-danger alert-block">  <strong>Plese select financial year!</strong> </div>'
                );
            }
            // $('html, body').animate({
            //     scrollTop: $("#head_closing_value_show").offset().top
            // }, 2000);
        }
    </script>
    @include('templates.admin.head_closing.partials.script')
@stop