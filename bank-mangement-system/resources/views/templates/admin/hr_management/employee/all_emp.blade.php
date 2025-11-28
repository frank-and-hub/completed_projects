@extends('templates.admin.master')

@section('content')
    <style type="text/css">

    </style>

    <div class="content">

        <div class="row" id="application_details">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">s.no</th>
                        <th scope="col">Name</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Status</th>
                        <th scope="col">Photo</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $i = 1;
                    @endphp
                    @foreach ($data as $details)
                        <tr> 
                            <td>{{ $i++ }}</td>
                            <?php
                            $folderName = 'employee/' . $details->photo;
                            if (ImageUpload::fileExists($folderName) && $details->photo != '') {
                                $photo_url = ImageUpload::generatePreSignedUrl($folderName);
                            } else {
                                $photo_url = url('/') . '/asset/images/user.png';
                            }
                            if ($details->status == 0) {
                                $status = 'Inactive';
                            } elseif ($details->status == 1 ) {
                                $status = 'Active';
                            } elseif ($details->status == 9 ) {
                                $status = 'Deleted';
                            } else {
                                $status = 'N/A';
                            }
                            ?>
                            <td>{{ $details->employee_name }}</td>
                            <td>{{ $details->mobile_no }}</td>
                            <td>{{$status }}</td>
                            <td><img alt="Image placeholder" id="photo-preview" src="{{ $photo_url }}" width="150">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-lg-6 text-right">
                                <button type="submit" class="btn btn-primary" onclick="printDiv('application_details');">
                                    Print<i class="icon-paperplane ml-2"></i></button>
                            </div>
                            {{-- <div class="col-lg-6 text-left">
                                <form action="{!! route('admin.hr.employee_application_export_pdf') !!}" method="post" enctype="multipart/form-data"
                                    id="employee_register" name="employee_register">
                                    @csrf
                                    <input type="hidden" name="id" id="id" value="{{ $employee->id }}">
                                    <button type="submit" class="btn btn-primary">Download<i
                                            class="icon-download4 ml-2"></i></button>
                                </form>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script type="text/javascript">
        function printDiv(elem) {
            $("#" + elem).print({
                //Use Global styles
                globalStyles: true,
                //Add link with attrbute media=print
                mediaPrint: true,
                //Custom stylesheet
                stylesheet: "{{ url('/') }}/asset/print.css",
                //Print in a hidden iframe
                iframe: false,
                //Don't print this
                noPrintSelector: ".avoid-this",
                //Add this at top
                //  prepend : "Hello World!!!<br/>",
                //Add this on bottom
                // append : "<span><br/>Buh Bye!</span>",
                header: null, // prefix to html
                footer: null,
                //Log to console when printing is done via a deffered callback
                deferred: $.Deferred().done(function() {})
            });
        }
    </script>
@stop
