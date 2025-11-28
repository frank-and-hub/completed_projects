@extends('templates.admin.master')
@section('content')
    <div class="loader" style="display: none;"></div>
    <div class="content">
        <div class="row card p-2">
            <div class="col-md-12">
                <form action="#" method="post" enctype="multipart/form-data" id="addrequest" name="fillter">
                    <input type="hidden" name="create_application_date" class="create_application_date"
                        id="create_application_date">
                        <center>
                        <h1>The Below button will make saving accounts for all members who have running loan but not having saving account</h1>
                    {{-- <div class="form-group">
                        <label class="col-form-label col-lg-2">Customer Id<sup>*</sup></label>
                        <div class="col-lg-4">
                            <input type="text" name="memberid" id="memberid" class="form-control">
                        </div>
                        <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
                        <div class="col-lg-4">
                            <input type="text" name="form_number" id="form_number" class="form-control">
                        </div>
                    </div> --}}
                        <button type="submit" id="cmsubmit"
                        class="btn btn-danger text-right btn-lg px-5 mr-2 my-3">Run</button>
                    </center>
                </form>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        $(document).ready(function() {
            $('#addrequest').validate({
                rules: {
                    // 'memberid': {
                    //     required: true
                    // },
                    // 'form_number': {
                    //     required: true
                    // }
                },
                submitHandler: function(form) {
                    // Prevent the form from submitting via the browser
                    event.preventDefault();
                    // Serialize the form data
                    var formData = $(form).serialize();
                    swal({
            title: "Are you sure?",
            text: "Do you want to run this code?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary delete_cheque",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            cancelButtonClass: "btn-danger delete_cancel",
            closeOnConfirm: false,
            closeOnCancel: true
          },
          function(result) {
            if (result) {
                    $.ajax({
                        type: 'POST',
                        url: "{!! route('admin.registerSSbRequiredData.data') !!}",
                        data: formData,
                        success: function(response) {
                            console.log(response);
                            if (response.status == 'true' || response.status == true) {
                                swal({
                                    title: 'Success!',
                                    text: 'Great saving account ' + response.data
                                        .account_no + ' created successfully',
                                    type: "success"
                                });
                            } else if (response.status == "Error") {
                                swal({
                                    title: 'Warning!',
                                    text: 'Error ' + response.message+ ' ',
                                    type: "warning"
                                });
                            } else {
                                swal('Warning!', "Error", 'warning');
                            }

                        },
                        error: function(xhr, status, error) {
                            // Handle the error case if needed
                            swal('Warning!', "Selected Details Not Found", 'warning');
                        }
                    });
                }
          });
                }
            });
        });
    </script>
@stop
