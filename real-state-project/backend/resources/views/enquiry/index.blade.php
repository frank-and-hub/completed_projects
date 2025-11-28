@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Enquiries'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Enquiry
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">list</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"></h4>
                <form name="enquiry_email">
                    <div class="row">
                        <input type="hidden" id="email_id" name="email_id" value="{{ $enquiry_email->id }}">
                        <div class="col-8">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" class="form-control" name="email" type="text"
                                    value="{{ $enquiry_email->email }}">
                            </div>
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <div class="form-group">
                                <button type="submit" class="btn theme_btn_1">Update</button>
                            </div>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h4 class="card-title"></h4>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="enquiryTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('custom-script')
        <script type="text/javascript">
            $(document).ready(function () {
                var table = $('#enquiryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('enquiry_list') }}",
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'email',
                            name: 'email',
                            render: function (data, type, row, meta) {
                                return data.length > 30 ?
                                `<span class="short-text">${data.substring(0, 30)}...</span>
                                    <span class="full-text text-lowercase" style="display:none; line-height:18px;">${data}</span>
                                    <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` : `<span class="text-lowercase">${data}</span>`;
                            }
                        }, {
                            data: 'subject',
                            name: 'subject',
                            render: function (data, type, row, meta) {
                                return data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                             <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                             <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data;
                            }
                        }, {
                            data: 'message',
                            name: 'message',
                            render: function (data, type, row, meta) {
                                return data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                             <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                             <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data;
                            }
                        }],
                    drawCallback: function (settings, json) {
                        $('#enquiryTable').off('click', '.view-more').on('click', '.view-more', function () {
                            var $shortText = $(this).siblings('.short-text');
                            var $fullText = $(this).siblings('.full-text');

                            if ($shortText.is(':visible')) {
                                $shortText.hide();
                                $fullText.show();
                                $(this).text('View Less');
                            } else {
                                $shortText.show();
                                $fullText.hide();
                                $(this).text('View More');
                            }
                        });
                        $('[data-toggle=tooltip]').tooltip();
                    }
                });

                $("form[name='enquiry_email']").validate({
                    rules: {
                        email: {
                            required: true,
                            email_rule: true
                        },
                    },
                    messages: {
                        email: {
                            required: 'Enter eamil'
                        },
                    },
                    submitHandler: function (form) {
                        $.ajax({
                            url: "{{ route('email_update') }}",
                            type: "POST",
                            data: new FormData(form),
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                if (response.status == 'success') {
                                    ToastAlert(msg = response.msg, cls = "success");
                                } else {
                                    ToastAlert(msg = response.msg, cls = "error");
                                }
                            },
                            error: function (xhr, status, error) {
                                handleServerError('enquiry_email', xhr.responseJSON.errors);
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
