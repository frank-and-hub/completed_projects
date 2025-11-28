@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
@push('custom-css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
@endpush
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Property {{ $active_page }}
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Property</a></li>
                <li class="breadcrumb-item active" aria-current="page">Property {{ $active_page }}</li>
            </ol>
        </nav>
    </div>
    <form enctype="multipart/form-data" id="add_contract" name="add_contract" onsubmit="submitHandler(this)">
        @csrf
        <textarea id="summernote" name="structure">{!! $data['contract_template'] !!}</textarea>
        <input type="submit" value="Submit" class="btn btn-primary mr-2" />
        <input type="submit" value="Preview" class="btn btn-primary mr-2" />
    </form>
    <div class="modal fade bd-example-modal-lg modal-dialog-scrollable" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="contract_preview">

            </div>
        </div>
    </div>
    <button type="button" class="btn btn-primary" style="visibility:hidden" data-toggle="modal" id="model_btn"
        data-target=".bd-example-modal-lg">Large modal</button>
</div>
@endsection
@push('custom-script')
<script type="text/javascript">
    const form = document.forms['add_contract'];
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Validate the structure field manually
        const structure = document.getElementById('summernote').value;
        if (!structure.trim()) {
            alert('Content is required'); // You can replace this with a custom error message
            return; // Stop form submission
        }

        submitHandler(e); // Call the submit handler after validation
    });

    $(document).ready(function() {
        $('form').on('keypress', function(e) {
            if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
        console.clear();
    });

    function previewFile(filePath) {
        document.getElementById('model_btn').click();
        const contractPreviewDiv = document.getElementById('contract_preview');

        // Clear any previous content inside the preview container
        contractPreviewDiv.innerHTML = '';

        // Create an object element to embed the PDF
        const pdfEmbed = document.createElement('object');
        pdfEmbed.data = filePath;
        pdfEmbed.type = 'application/pdf';
        pdfEmbed.style.width = '100%';
        pdfEmbed.style.height = '700px'; // You can adjust this height based on your needs

        // Append the object to the contract_preview div
        contractPreviewDiv.appendChild(pdfEmbed);
    }

    function submitHandler(e) {
        const formData = new FormData(e.target);
        const submitButton = e.submitter;
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('structure', document.getElementById('summernote').value);
        formData.append('submit', submitButton.value);

        const url = "{{ route('adminSubUser.contract.store', [$data['property']->id, $data['admin']->id, $data['user']->id]) }}";

        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.clear();
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        const successMsg = document.querySelector('.success_msg');
                        if (successMsg) {
                            successMsg.innerHTML = response.msg;
                            successMsg.style.display = 'block';
                            setTimeout(function() {
                                successMsg.style.display = 'none';
                            }, 5000);
                        }
                        form.reset();
                        if (submitButton.value == 'Preview') {
                            previewFile(response.msg);
                        } else {
                            window.location.href = response.msg;
                        }
                    } else {
                        const errorMsg = document.querySelector('.error_msg');
                        if (errorMsg) {
                            errorMsg.innerHTML = response.msg;
                            errorMsg.style.display = 'block';
                            setTimeout(function() {
                                errorMsg.style.display = 'none';
                            }, 5000);
                        }
                    }
                } else {
                    console.error('AJAX request failed with status: ' + xhr.status);
                    alert('Error occurred, please try again!');
                }
            }
        };

        xhr.send(formData);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
@endpush
