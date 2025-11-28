@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
@endpush

<style>
    /* Add padding and increase the font size for the container */
    .container {
        padding: 20px;
        font-size: 18px;
    }

    /* Add padding and increase the font size for form elements */
    .form-group {
        padding: 10px;
    }
</style>

@section('content')
    <div class="container">
        <div class="form-group">
            <label for="type">Select Type:</label>
            <div>
                <input type="radio" name="type" value="radio" id="radio" onclick="showFields('radio_fields')"> Radio
                <input type="radio" name="type" value="checkbox" id="checkbox" onclick="showFields('checkbox_fields')"> Checkbox
                <input type="radio" name="type" value="text" id="text" onclick="showFields('text_fields')"> Text
                <input type="radio" name="type" value="document" id="document" onclick="showFields('document_fields')"> Document

            </div>
        </div>

        <div id="radio_fields" style="display: none;">
            <form action="{{ route('admin.scholarship.application.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="radio_text">Question:</label>
                    <textarea class="form-control" rows="5" placeholder="Enter Your Question" name="question" onkeyup="removeSpecialCharacters(this)" ></textarea>
                </div>
                <input type="hidden" name="type" value="radio">
                <input type="hidden" name="scholarship_id" value="{{$id}}">
                <div class="form-group">
                    <label for="radio_option1">Option 1:</label>
                    <input type="text" name="option_1" id="radio_option1">
                </div>
                <div class="form-group">
                    <label for="radio_option2">Option 2:</label>
                    <input type="text" name="option_2" id="radio_option2">
                </div>
                <div class="form-group">
                    <label for="radio_option3">Option 3:</label>
                    <input type="text" name="option_3" id="radio_option3">
                </div>
                <div class="form-group">
                    <label for="radio_option4">Option 4:</label>
                    <input type="text" name="option_4" id="radio_option4">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>

        <div id="checkbox_fields" style="display: none;">
            <form action="{{ route('admin.scholarship.application.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="checkbox_text">Question:</label>
                    <textarea class="form-control" rows="5" placeholder="Enter Your Question" name="question" onkeyup="removeSpecialCharacters(this)"></textarea>
                </div>
                <input type="hidden" name="type" value="checkbox">
                <input type="hidden" name="scholarship_id" value="{{$id}}">

                <div class="form-group">
                    <label for="option1">Option 1:</label>
                    <input type="text" name="option_1" id="option1">
                </div>
                <div class="form-group">
                    <label for="option2">Option 2:</label>
                    <input type="text" name="option_2" id="option2">
                </div>
                <div class="form-group">
                    <label for="option3">Option 3:</label>
                    <input type="text" name="option_3" id="option3">
                </div>
                <div class="form-group">
                    <label for="option4">Option 4:</label>
                    <input type="text" name="option_4" id="option4">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>

        <div id="text_fields" style="display: none;">
            <form action="{{ route('admin.scholarship.application.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="text_input1">Question:</label>
                    <textarea class="form-control" rows="5" placeholder="Enter Your Question" name="question" onkeyup="removeSpecialCharacters(this)"></textarea>
                </div>
                <input type="hidden" name="scholarship_id" value="{{$id}}">
                <input type="hidden" name="type" value="text">

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <div id="document_fields" style="display: none;">
        <form action="{{ route('admin.scholarship.application.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="document_text">Question:</label>
                <input type="text" class="form-control" rows="5" placeholder="Enter Document Name" name="question" onkeyup="removeSpecialCharacters(this)" >
            </div>
            <input type="hidden" name="type" value="document">
            <input type="hidden" name="scholarship_id" value="{{$id}}">
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <script>
        function removeSpecialCharacters(input) {
            // input.value = input.value.replace(/[^\w\s/]/gi, ''); 
            input.value = input.value.replace(/[^\w\s\-(:)?,\%/]/gi, ''); // replace(/[^\w\s\-()?%/,]/gi, '');
        }
        function showFields(selected) {
    document.getElementById('radio_fields').style.display = 'none';
    document.getElementById('checkbox_fields').style.display = 'none';
    document.getElementById('text_fields').style.display = 'none';
    document.getElementById('document_fields').style.display = 'none';

    document.getElementById(selected).style.display = 'block';
}
    </script>
@endsection
