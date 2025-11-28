@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@endpush
<div class="content-wrapper">
    <div class="page-header">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Contracts</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($contract) ? 'Edit' : 'Add' }}</li>
            </ol>
        </nav>
    </div>
    <style>
        .select2-selection--multiple {
            padding-top: 5px !important;
        }
    </style>
    <div class="row grid-margin">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ isset($contract) ? 'Update' : 'Create' }} Contracts</h4>
                    <form class="cmxform" id="add_contracts" name="add_contracts" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="property">Properties </label>
                            <select id="property" class="form-control property-select2" name="property[]" @if(isset($contract) && ($contract->property_ids)) disabled @endif>
                                <option value="" selected disabled >Select Property</option>
                                @forelse ($properties as $property)
                                    <option value="{{ $property->id }}"
                                        {{ isset($contract) ? (in_array($property->id, $contract->property_ids) ? 'selected' : '') : '' }}>
                                        {{ ucwords($property->title) }}</option>
                                @empty
                                    <option value="" disabled>No Properties Found!</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="summernote">Contracts Text <span class="text-danger">*</span></label>
                            <textarea id="summernote" class="form-control" rows="20" name="structure">{!! $contract_template !!}</textarea>
                        </div>
                        <div class="row">
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        <input type="submit" name="submit" value="{{ isset($contract) ? 'Update' : 'Submit' }}"
                            class="btn btn-primary mr-2" />
                        <input type="submit" name="submit" value="Preview" class="btn btn-primary mr-2" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg modal-dialog-scrollable" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="contract_preview">
        </div>
    </div>
</div>
<button type="button" class="btn btn-primary" style="visibility:hidden" data-toggle="modal" id="model_btn"
    data-target=".bd-example-modal-lg">Large modal</button>

@push('custom-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script type="text/javascript">
        const form = document.forms['add_contracts'];

        const GET_ALL_PRROPERTIES = `{{ route('adminSubUser.contract.get_all_properties') }}`;
        const EDIT_UPAATE_CONTRACT_URL =
            `{{ isset($contract) ? route('adminSubUser.contract.update', $contract->uuid) : route('adminSubUser.contract.store') }}`;
        const EDIT_UPAATE_CONTRACT_METHOD = `{{ isset($contract) ? 'PUT' : 'POST' }}`;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const structure = document.getElementById('summernote').value;
            if (!structure.trim()) {
                alert('Content is required');
                return;
            }
            submitHandler(e);
        });

        $(document).ready(function() {

            $('#property').select2();

            var selectedPropertyIds = @json($contract?->property_ids ?? '');

            if (selectedPropertyIds.length) {
                $('#property').val(selectedPropertyIds).trigger('change');
            }

            $('#property').on('select2:select', function(e) {
                result = e.params.data;
                $('.select2-search__field').css('width', '100%');
            });

            $('.select2-search__field').css('width', '100%');

            $('#summernote').summernote({
                height: 600,
                placeholder: 'Write something here...',
                tabsize: 2,
                minHeight: null,
                maxHeight: null,
                focus: true,
                lang: 'en-US',
                placeholder: 'Write your content here...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                    ['custom', ['inputbox']],
                ],
                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
                fontNamesIgnoreCheck: ['Arial', 'Arial Black'],
                fontSizeUnit: 'px',
                disableDragAndDrop: false, // Allow form elements to be editable
                callbacks: {
                    onInit: function() {
                        console.info('Summernote editor initialized');
                    },
                    onChange: function(contents, $editable) {
                        console.info('Content changed');
                    }
                }
            });

            var summernoteContent = $('#summernote').summernote('code');

            // Find and replace input field values in the content (ensure values are preserved)
            $(summernoteContent).find('input').each(function() {
                var inputField = $(this);
                inputField.attr('value', inputField.val()); // Set the value attribute to the current value
            });

            $('.property-select2').select2({
                ajax: {
                    url: GET_ALL_PRROPERTIES,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Search for a property',
                multiple: false,
                allowClear: true
            });
        });

        function submitHandler(e) {
            e.preventDefault();

            const formData = new FormData();

            let formDataSerialized = $('#add_contracts').serialize();
            const submitButton = e.submitter;
            formDataSerialized += '&submit=' + encodeURIComponent(submitButton.value);
            $.ajax({
                url: EDIT_UPAATE_CONTRACT_URL,
                method: EDIT_UPAATE_CONTRACT_METHOD,
                data: formDataSerialized,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true);
                },
                success: function(response) {
                    if (response.status === 'success') {
                        const successMsg = $('.success_msg');
                        if (successMsg.length) {
                            successMsg.html(response.msg).show();
                            setTimeout(function() {
                                successMsg.hide();
                            }, 5000);
                        }
                        $('#add_contracts')[0].reset();
                        if (submitButton.value === 'Preview') {
                            previewFile(response.path);
                        } else {
                            window.location.href = "{{ route('adminSubUser.contract.index') }}";
                        }
                    } else {
                        const errorMsg = $('.error_msg');
                        if (errorMsg.length) {
                            errorMsg.html(response.msg).show();
                            setTimeout(function() {
                                errorMsg.hide();
                            }, 5000);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('AJAX request failed with status: ' + xhr.status);
                    alert('Error occurred, please try again!');
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        }
    </script>
@endpush
@endsection
