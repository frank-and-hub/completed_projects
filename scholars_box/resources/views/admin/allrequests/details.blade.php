<!-- detail.blade.php -->

@extends('admin.layout.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
html {
    line-height: 1;
}

body {
    background-color: #fff;
    color: white;
    font: 16px arial, sans-serif;
}

* {
    margin: 0;
    padding: 0;
}

*,
*:after,
*:before {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

.container {
    margin: 0 auto;
    max-width: 1024px;
    padding: 0 20px;
    overflow: auto;
}

.container table {
    margin: 15px 0 0;
}

table {

    border-collapse: collapse;
    //border-spacing: 4px; /* border-spacing works only if border-collapse is separate */
    color: white;
    font: 15px/1.4 "Helvetica Neue", Helvetica, Arial, Sans-serif;
    width: 100%;
}

thead {
    background: #395870;
    -webkit-background: linear-gradient(#49708f, #293f50);
    -moz-background: linear-gradient(#49708f, #293f50);
    background: linear-gradient(#49708f, #293f50);
    color: white;
}

tbody tr:nth-child(even) {
    background: #f0f0f2;
}

/* borders cannot be applied to tr elements or table structure elements. we should follow like below code. */
tfoot tr:last-child td {
    //border-bottom: 0;
}

th,
td {
    //border: 2px solid #666;
    padding: 6px 10px;
    vertical-align: middle;
}

td {
    border-bottom: 1px solid #cecfd5;
    border-right: 1px solid #cecfd5;
}

td:first-child {
    border-left: 1px solid #cecfd5;
}

.book-title {
    color: white;
    //display: block;
}

.item-stock,
.item-qty {
    text-align: center;
}

.item-price {
    text-align: right;
}

.item-multiple {
    display: block;
}

/* task */
.task table {
    margin-bottom: 44px;
}

.task a {
    color: white;
    //text-decoration: none;
}

.task thead {
    background-color: #f5f5f5;
    -webkit-background: transparent;
    -moz-background: transparent;
    background: transparent;
    color: white;
}

.task table th,
.task table td {
    border-bottom: 0;
    border-right: 0;
}

.task table td {
    border-bottom: 1px solid #ddd;
}

.task table th,
.task table td {
    padding-bottom: 22px;
    vertical-align: top;
}

.task tbody tr:nth-child(even) {
    background: transparent;
}

.task table:last-child {
    //margin-bottom: 0;
}

.value {
    color: gray;
}
</style>
<style>
/* Style for the modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
}

/* Style for the modal content */
.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    text-align: center;
}

/* Style for the close button */
.close {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
}
</style>
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Applicant</a></li>
        <li class="breadcrumb-item active" aria-current="page">Applicant Details</li>
    </ol>
</nav>
<div class="row">
    <div class="col-md-12 stretch-card">
        <div class="card">

            <div class="col-4" style="padding:30px;">
                {{-- <select id="application_status" name="application_status" class="form-control" style="float: right;"
                    onchange="handleStatusChange(this.value, {{ $scholarship_id }}, {{ $applicantsDetail->id }})">
                    <option value="" >Select Mark Application Status</option>
                    @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_APPLICATION as $statusKey => $statusValue)
                    <option value="{{ $statusKey }}" {{ $applicantsDetails->status == $statusKey ? 'selected' : '' }}>
                        {{ $statusValue }}
                    </option>
                    @endforeach
                </select> --}}
                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Enter Custom Status</h5>
                                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button> --}}
                            </div>
                            <div class="modal-body">
                                <textarea id="custom_status_modal" name="custom_status_modal" class="form-control"
                                    placeholder="Enter custom status"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="saveCustomStatus()">Save</button>
                                {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- <h6 class="card-title">Applicant Details</h6> -->

                <div class="container">


                    <section class="task">
                        <table>



                            <tbody>

                                <tr>
                                    <h4 class="card-title">Request Details</h4>
                                    <th scope="row">

                                    </th>

                                </tr>
                                <tr>
                                    <th scope="row">

                                    </th>

                                    <td>

                                    </td>

                                    <td>

                                    </td>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">

                                    </th>
                                    <td>
                                        <h4>Name : </h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->name }}</h4>
                                    </td>

                                    <td>
                                        <h4>Email :</h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->email }}</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">

                                    </th>
                                    <td>
                                        <h4>category :</h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->category }}</h4>
                                    </td>
                                    <td>
                                        <h4>Phone Number :</h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->working_no }}</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">

                                    </th>
                                    <td>
                                        <h4>Alt Number :</h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->alternative_no }}</h4>
                                    </td>

                                    <td>
                                        <h4>message :</h4>
                                    </td>
                                    <td>
                                        <h4 class="value">{{ $students->message }}</h4>
                                    </td>
                                </tr>

                                <tr>
                                <th scope="row">

</th>


<td>
    <h4>subject :</h4>
</td>
<td>
    <h4 class="value">{{ $students->subject }}</h4>
</td>
                                   
                                </tr>

                               

                               


                                
                               
                                
                                 
                                </tbody>
                            <tfoot></tfoot>
                        </table>
                    </section>

                    
                    
                    
                    
                    
                                    
                </div>
            </div>
        </div>
    </div>
</div>
<div id="pdfModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <!-- Include an iframe for PDF rendering -->
        <iframe id="pdfIframe" width="100%" height="500px" frameborder="0"></iframe>
        <!-- Buttons for the modal -->

        <div class="row">
            <select class="form-control">
                <option>Select Status</option>
                <option>Blur</option>
                <option>Rejected</option>
                <option>Image not clear</option>
                <option>Not eligible</option>
            </select>
            <button class="btn btn-primary" onclick="closeModal()">Close</button>
            <button class="btn btn-info" onclick="downloadPDF()">Download PDF</button>
        </div>
    </div>
    <script>
    function openModal(pdfUrl) {
        // Get the modal and iframe elements
        var modal = document.getElementById('pdfModal');
        var iframe = document.getElementById('pdfIframe');

        // Set the source of the iframe to the PDF URL
        iframe.src = 'https://docs.google.com/gview?url=' + encodeURIComponent(pdfUrl) + '&embedded=true';

        // Display the modal
        modal.style.display = 'block';
    }

    function closeModal() {
        // Hide the modal
        document.getElementById('pdfModal').style.display = 'none';
        // Stop the PDF from loading when the modal is closed
        document.getElementById('pdfIframe').src = '';
    }

    function downloadPDF() {
        // You can implement the logic to trigger a download here
        // For example, you can redirect the user to the PDF URL for download
        window.location.href = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
    }
    </script>
    <script>
    function handleStatusChange(selectedValue, id, applicantId) {
        $('#myModal').modal('show');
    }



 function closeModal() {
        document.getElementById('pdfModal').style.display = 'none';
        document.getElementById('pdfIframe').src = '';
    }

    function downloadPDF() {
        window.location.href = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
    }

    function updateApplicationsStatus(customStatus, status, sch_id, student_id) {
        var url = "{{ route('admin.updateScholorshipStatus') }}";
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                customStatus: customStatus,
                status: status,
                sch_id: sch_id,
                student_id: student_id,
                _token: csrfToken,
            },
            success: function(data) {
                if (data.message == 'Success') {
                    toastr.success('Status updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });
                }
            },
            error: function(error) {
                console.error('Error updating status');
            }
        });
    }
    </script>
@endsection