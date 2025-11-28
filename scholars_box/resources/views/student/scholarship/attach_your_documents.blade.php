<form id="userDocumentUpdateFormDiv" enctype="multipart/form-data" name="userDocumentUpdateFormDiv">
    <div class="row">
        @if(isset($scholarships[0]->apply_now->select_a_document_h_s) && ($scholarships[0]->apply_now->select_a_document_h_s == '1'))
        <div class="col-md-6">
            <div class="form-box-one">
                <label>Select a Document {{($scholarships[0]->apply_now->select_a_document_r == '1') ? '*' : '' }}</label>
                <select name="document_type" class="form-input-one" {{($scholarships[0]->apply_now->select_a_document_r == '1') ? 'required=true' : '' }}>
                    <option value="">Select Document Type</option>
                    @foreach (\App\Models\Document::$documentTypes as $key => $value)
                        <option value="{{ $key }}" >
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        
        @if(isset($scholarships[0]->apply_now->upload_your_document_h_s) && ($scholarships[0]->apply_now->upload_your_document_h_s == '1'))
        <div class="col-md-6">
            <div class="form-box-one">
                <label>Upload Your Document{{$scholarships[0]->apply_now->upload_your_document_r == '1' ? '*' : '' }}</label>
                <input type="file" name="document" class="" id="fileInput">
            </div>
        </div>
        @endif
        
        <div class="col-md-0" style="margin-top: 21px">
            <div class="form-box-one mb-0">
                <button id="userDocumentUpdateBtn" class="sec-btn-one d-none" type="button" style=" padding: 12px 20px; height: 49px" >Upload</button>
            </div>
        </div>
        
        
        <div id="documentList">
        </div>

    </div>
</form>
<input type="hidden" id="requiredDoc"  value="{{($scholarships[0]->apply_now->docs)}}"/>
<input type="hidden" name="textingvalidation"  value=""/>
<input type="hidden" id="d_t"  value="{{ ($scholarships[0]->apply_now->select_a_document_r == '1') ? 'true' : 'false' }}"/>
<input type="hidden" id="r_v"  value="{{ ($scholarships[0]->apply_now->upload_your_document_r == '1') ? 'true' : 'false' }}"/>
<!-- Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel"></h5>
                <button type="button" id="closeModalButton" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="documentImage" src="" style="max-width: 100%;">
                <!-- Add more content related to the document here -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#userDocumentUpdateFormDiv').validate({
            rules: {
                document_type: {
                    required: function() {
                        return ($('#d_t').val() == 'true') ? true : false;
                    }
                }
            },
            messages: {
                
            }
        });
        updateValidationRules();
        function updateValidationRules(){
            if ($('#r_v').val() == 'true' || $('#d_t').val() == 'true') {
                $('input[name="document"]').rules('add', {
                    reqValid: true,
                    // required:true
                });
            } else {
                $('input[name="document"]').rules('add', {
                    reqValid: false,
                    // required:false
                });
            }
        }
        $.validator.addMethod("reqValid", function(value, element) {
            return testValidation();
        }, "");

        function testValidation(){
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var id = $('input[name="scholarship_id"]').val();
            var reqDoc = document.getElementById('requiredDoc').value;
            var isValid = false;
            $.post("{!! route('Student.scholarship.questions.doc') !!}", { 'id' : id, '_token' : csrfToken ,'reqDoc' : reqDoc }, (res) => {
                if((!res.data) && (res.msg != '')){
                    new Noty({type: 'error',text: res.msg }).show();
                    $('#d_t').val('true');
                    $('#r_v').val('true');
                }else{
                    $('#d_t').val('false');
                    $('#r_v').val('false');
                }
                fetchAndDisplayDocuments();
                updateValidationRules();
                isValid = res.data;
                console.log(isValid,"< - isValid - >");
            }, 'JSON');
           
            return isValid;
        }
        $('input[name="document"] , input[name="document_type"]').on('change',function(){
            // if($('#userDocumentUpdateFormDiv').valid()){
                $('#userDocumentUpdateBtn').click();
            // }
        });
        $('#userDocumentUpdateBtn').click(function (event) {
            event.preventDefault();
            // Validate the form here
            var documentType = $('select[name="document_type"]').val();
            var documentFile = $('input[type="file"]')[0].files[0];

            if (!documentType || !documentFile) {
                new Noty({
                    type: 'error',
                    text: 'Please fill in all required fields.'
                }).show();
                return;
            }
            var sch_id = $('input[name="scholarship_id"]').val();
            // If the form is valid, proceed to AJAX
            var formData = new FormData();
            formData.append('document_type', documentType);
            formData.append('document', documentFile);
            formData.append('sch_id', sch_id);

            console.log(sch_id,"sch_id");
            // return false;

            // var formData = new FormData('#userDocumentUpdateFormDiv');
                $.ajax({
                    url: "{{ route('Student.updateDocument') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (response) {
                        fetchAndDisplayDocuments();
                        new Noty({
                            text: 'Documents updated successfully!'
                        }).show();
                    },
                    error: function (jqXHR) {
                        var response = $.parseJSON(jqXHR.responseText);
                        if (response && response.errors && response.errors.document) {
                            var errorMessage = response.errors.document[0];
                            new Noty({
                                type: 'error',
                                text: errorMessage
                            }).show();
                        } else {
                            new Noty({
                                type: 'error',
                                text: 'An unexpected error occurred.'
                            }).show();
                        }
                    }
                });
            // }
        });

        function fetchAndDisplayDocuments() {
            $.ajax({
                type: 'GET',
                url: "{{ route('Student.getUserDocuments') }}",
                success: function (documents) {
                    var documentList = $('#documentList');
                    documentList.empty();

                    $.each(documents, function (index, document) {
                        var documentListItem = $('<div>', {
                            'class': 'row document-list'
                        }).append($('<div>', {
                            'class': 'col-md-6',
                            'text': document.humanReadableType
                        })).append($('<div>', {
                            'class': 'col-md-6 document-btn'
                        }).append($('<a>', {
                            'class': 'sec-btn-one',
                            'style': 'margin-right: 5px; margin-bottom: 7px;',
                            // 'data-bs-toggle': 'modal',
                            // 'data-bs-target': '#documentModal',
                            // 'data-document-type': document.humanReadableType,
                            'target': '_blank',
                            // 'data-document-url': document.document,
                            'href': document.document,
                            'html': '<i class="fa fa-eye"></i> View'
                        })).append($('<a>', {
                            'class': 'sec-btn-one delete-document',
                            'style': 'margin-right: 5px; margin-bottom: 7px;',
                            'data-document-id': document.id,
                            'html': '<i class="fa fa-trash"></i> Delete'
                        })));

                        documentList.append(documentListItem);
                    });
                },
                error: function (jqXHR) {
                    // Handle error
                }
            });
        }

        
        $('#documentModal').on('show.bs.modal', function (event) {

            var button = $(event.relatedTarget); // Button that triggered the modal
            var documentType = button.data('document-type');
            var documentUrl = button.data('document-url'); // Assuming this is the URL generated by PHP

            var modal = $(this);
            modal.find('.modal-title').text(documentType);

            if (documentUrl.toLowerCase().endsWith('.pdf')) {
                // Load PDF using pdf.js
                modal.find('.modal-body').html('<iframe src="' + documentUrl +
                    '" width="100%" height="500px"></iframe>');
            } else if (documentUrl.toLowerCase().endsWith('.docx')) {
                // Load DOCX using Microsoft Office Online Viewer
                modal.find('.modal-body').html(
                    '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=' +
                    encodeURIComponent(documentUrl) +
                    '" width="100%" height="500px"></iframe>');
            }
        });

        // Close the modal when the close button is clicked
        $('#closeModalButton').on('click', function () {  
            $('#documentModal').modal('hide');
        });

        // Call the function initially to load documents
        fetchAndDisplayDocuments();

        // Delete document event listener
        $(document).on('click', '.delete-document', function () {
            var documentId = $(this).data('document-id');
            testValidation();

            $('#r_v').val('true');$('#d_t').val('true');
            // Make AJAX call to delete the document by ID
            $.ajax({
                type: 'DELETE',
                data: {
                    id: documentId
                },
                url: "{{ route('Student.destroyUserDocument', ['id' => '__id__']) }}".replace(
                    '__id__', documentId),
                success: function (response) {

                    

                    new Noty({
                        text: 'Document deleted successfully!'
                    }).show();

                    
                    updateValidationRules();
                    fetchAndDisplayDocuments();
                    
                    
                },
                error: function (jqXHR) {
                    var response = $.parseJSON(jqXHR.responseText);

                    if (response && response.errors && response.errors.error) {
                        var errorMessage = response.errors.error;

                        // new Noty({
                        //     type: 'error',
                        //     text: errorMessage
                        // }).show();
                    } else { }
                }
            });
        });
    });
</script>