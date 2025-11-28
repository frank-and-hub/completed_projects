@extends('admin.layout.master')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @push('plugin-styles')
        <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
        <!-- Add Select2 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @endpush

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Applicant</a></li>
            <li class="breadcrumb-item active" aria-current="page">Applicant List</li>
        </ol>
    </nav>
<form action="#" method="POST" name="DisbursedForm" id="DisbursedForm" enctype="multipart/form-data"> @csrf
    <table class="table" id="dataTable">
        <thead>
            <tr>
                <th scope="col">Amount Disbursed *</th>
                <th scope="col">Account Number *</th>
                <th scope="col">Account Holder Name *</th>
                <th scope="col">Receipt *</th>
                <th scope="col">Response</th>
                <th scope="col">Acknowledge</th>
                <input type="hidden" name="user_id" value="{{ $user_id }}" /> 
                <input type="hidden" name="sch_id" value="{{ $scholarship_id }}" /> 
            </tr>
        </thead>
        <tbody>
            @foreach ($distribution as $row)
                <tr>
                    <td><input type="text" name="amount[]" value="{{ $row->amount }}" required></td>
                    <td><input type="text" name="account_number[]" value="{{ $row->account_number }}" required></td>
                    <td><input type="text" name="account_holder_name[]" value="{{ $row->account_holder_name }}" required></td>
                    <td><input type="file" name="receipt[]" required><input type="hidden" name="hidden_receipt_file[]" required></td>
                    <td>@if($row->ack == 1) <p>Acknowledged</p> @else <p>No Response </p> @endif</td>
                    <td><button class="btn btn-danger removeRowBtn" type="button">Remove</button></td>
                </tr>
            @endforeach
            <!--<tr>-->
            <!--    <td><input type="text" name="amount[]" required></td>-->
            <!--    <td><input type="text" name="account_number[]" required></td>-->
            <!--    <td><input type="text" name="account_holder_name[]" required></td>-->
            <!--    <td><input type="file" name="receipt[]" required><input type="hidden" name="hidden_receipt_file[]" required></td>-->
            <!--    <td><button class="btn btn-danger removeRowBtn" type="button">Remove</button></td>-->
            <!--</tr>-->
        </tbody>
    </table>

    <button id="addRowBtn" type="button" class="btn btn-primary" style="float: right;">Add Row</button>
    <button id="saveBtn" type="button" class="btn btn-success">Save</button>
</form>
@endsection

@push('custom-scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
  $(document).ready(function () {
    // Populate the form fields with distribution data
    @foreach ($distribution as $index => $row)
        $('input[name="amount[]"]').eq({{ $index }}).val('{{ $row->amount }}');
        $('input[name="account_number[]"]').eq({{ $index }}).val('{{ $row->account_number }}');
        $('input[name="account_holder_name[]"]').eq({{ $index }}).val('{{ $row->account_holder_name }}');
        $('input[name="hidden_receipt_file[]"]').eq({{ $index }}).val('{{ $row->receipt }}');
    @endforeach

    $("#addRowBtn").click(function () {
        var newRow =
            '<tr>' +
            '<td><input type="text" name="amount[]" required></td>' +
            '<td><input type="text" name="account_number[]" required></td>' +
            '<td><input type="text" name="account_holder_name[]" required></td>' +
            '<td><input type="file" name="receipt[]" required><input type="hidden" name="hidden_receipt_file[]" required></td>' +
            '<td><button class="btn btn-danger removeRowBtn" type="button">Remove</button></td>' +
            '</tr>';

        $("#dataTable tbody").append(newRow);
    });

    // Handle remove button click
    $("#dataTable").on("click", ".removeRowBtn", function () {
        $(this).closest("tr").remove();
    });

    $("#saveBtn").click(function () {
        var isValid = true;
        $('#dataTable tbody tr').each(function() {
            var receiptInput = $(this).find('input[name="receipt[]"]');
            if (receiptInput.length && !receiptInput.val()) {
                isValid = false;
                receiptInput.addClass('is-invalid'); // Add invalid class for visual feedback
            } else {
                receiptInput.removeClass('is-invalid'); // Remove invalid class if valid
            }
        });

        if (!isValid) {
            toastr.error('Please fill all required fields', 'Error', {
                positionClass: 'toast-top-right',
                timeOut: 3000,
            });
            return;
        }

        // Send data to the server using Ajax
        var formData = new FormData($('#DisbursedForm')[0]);

        $.ajax({
            type: 'POST',
            url: '{{ route('admin.disbursed') }}',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                toastr.success('Data updated successfully', 'Success', {
                    positionClass: 'toast-top-right',
                    timeOut: 3000,
                });
            },
            error: function (error) {
                console.error('Error saving data:', error);
            }
        });
    });
});

</script>
@endpush
