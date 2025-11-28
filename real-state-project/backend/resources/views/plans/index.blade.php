@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Pricing Plans'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Plans
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                        <h4 class="card-title"></h4>
                    </div>
                    {{-- <div class="text-right">
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="Add New"
                            class="btn  btn-danger  mr-2 planmodel"><i class="fa fa-plus"> </i> Add New</a>
                    </div> --}}
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="planTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Plan Name</th>
                                        <th>Amount</th>
                                        <th>User Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="planmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="plan_form" id="plan_form">
                    <div class="modal-header plan_name">
                        <h5 class="modal-title" id="plan_name">Plan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="plan_id" id="plan_id">
                        {{-- <div class="form-group">
                            <label for="exampleInputName1">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="plan_name" id="plan_name"
                                placeholder="Enter Name">
                        </div> --}}
                        <div class="form-group">
                            <label for="exampleInputName1">Amount <span class="text-danger">*</span></label>
                            <input type="text" class="form-control number_with_decimal" name="plan_amount" id="plan_amount"
                                placeholder="Enter amount">
                        </div>
                    </div>
                    <div class="alert alert-success success_msg" role="alert"></div>
                    <div class="alert alert-danger error_msg" role="alert"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn theme_btn_1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('custom-script')
        <script type="text/javascript">
            var table = $('#planTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('plan_list') }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'plan_name',
                        name: 'plan_name'
                    }, {
                        data: 'amount',
                        name: 'amount'
                    }, {
                        data: 'type',
                        name: 'type'
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });

            $(document).ready(function () {
                $(document).on('click', '.planmodel', function () {
                    $('#planmodel').modal('show')
                })
                $("form[name='plan_form']").validate({
                    rules: {
                        plan_amount: {
                            required: true
                        },
                    },
                    messages: {
                        plan_amount: {
                            required: 'Enter Plan Amount'
                        },
                    },
                    submitHandler: function (form) {
                        $.ajax({
                            url: "{{ route('insert_plan') }}",
                            type: "POST",
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.status == 'success') {
                                    $("form[name='plan_form']").find(
                                        '.serverside_error').remove();
                                    $('.success_msg').html(response.msg);
                                    $('.success_msg').fadeIn();
                                    setTimeout(function () {
                                        $('.success_msg').fadeOut();
                                    }, 3000);
                                    $('#planTable').DataTable().ajax.reload();
                                    if ($('#plan_id').val() != '') {
                                        setTimeout(function () {
                                            $('#planmodel').modal('hide')
                                            $('#plan_form')[0].reset();
                                        }, 2000);
                                    } else {
                                        $('#plan_form')[0].reset();
                                    }
                                } else {
                                    $("form[name='plan_form']").find(
                                        '.serverside_error').remove();
                                    $('.error_msg').html(response.msg);
                                    $('.error_msg').fadeIn();
                                    setTimeout(function () {
                                        $('.error_msg').fadeOut();
                                    }, 3000);
                                }
                            },
                            error: function (xhr, status, error) {
                                handleServerError('plan_form', xhr.responseJSON.errors);
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.edit_plan', function () {
                var dataId = $(this).attr('data-id');
                $.ajax({
                    url: "{{ route('edit_plan') }}",
                    type: 'POST',
                    data: {
                        'dataId': dataId
                    },
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#plan_id').val(response.plan_id);
                            $('#plan_amount').val(response.plan_amount);
                            $('#plan_name').text(response.plan_name);
                            $('#planmodel').modal('show');
                        }
                    },
                });

            })
        </script>
    @endpush
@endsection
