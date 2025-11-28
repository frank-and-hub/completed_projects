@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="card p-3 d-flex" style="flex-direction: row; justify-content: space-between;">
            <span class="mx-3 h6"><b>Total Realised Amount :   {{$total_realised_amount}} &#8377</b></span>
            <span class="mx-3 h6"><b>Total Number of Record :   {{$total_record}}</b></span>
            <span class="mx-3 h6"><b>Total Number of Realised Record :   {{$total_realised_record}}</b></span>
            <span class="mx-3 h6"><b>Total Number of Returned Record :   {{$total_returned_record}}</b></span>
        </div>
        <div class="card pt-3 pb-3 px-3">
            <div class="table-responsive">
                <table id="" class="table table-flush">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Loan Type</th>
                        <th>Company Name</th>
                        <th>Loan Account Number</th>
                        <th>Amount</th>
                        <th>Branch</th>
                        <th>Associate</th>
                        <th>Date</th>
                        <th>Payment Mode</th>
                        <th>Payment Status</th>
                        <th>Company Bank Account</th>
                        <th>Company Bank Account Number</th>
                        <th>Customer Bank Account Number</th>
                        <th>Reference/UTR no</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['dataarray'] as $key => $value)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $value['loan_type'] }}</td>
                            <td>{{ $value['company_name'] }}</td>
                            <td>{{ $value['account_number'] }}</td>
                            <td>{{ $value['amount'] }}</td>
                            <td>{{ $value['branch_name'] }}</td>
                            <td>{{ $value['associate_no'] }}</td>
                            <td>{{ $value['date'] }}</td>
                            <td>Online</td>
                            <td>{{ $value['transaction_status'] }}</td>
                            <td>{{ $value['bank_name'] }}</td>
                            <td>{{ $value['account_no'] }}</td>
                            <td>{{ $value['customer_account_number'] }}</td>
                            <td>{{ $value['utr_transaction_number'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
            <div class="row justify-content-end mr-3 mt-3">
                <button class="btn btn-primary" id="submit_btn">submit</button>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
    $('#submit_btn').click(function(e) {
        e.preventDefault();
        $(this).attr('disabled', true);
        submitFormData();
    });

    function submitFormData() {
        $.ajax({
            type: "POST",
            url: "{!! route('admin.loan.import_data') !!}",
            data: {
                data: {!! json_encode($important) !!}
            },
            dataType: "json",
            success: function(response) {
                handleSuccess(response);
            },
            error: function(xhr, status, error) {
                handleErrors(xhr, status, error);
            }
        });
    }

    function handleSuccess(response) {
        swal({
            title: 'Success!',
            text: 'Emi transaction successfully imported',
            type: "success"
        }, function(result) {
            if (result) {
                window.location.href = "{{ route('admin.loan.importView') }}";
            }
        });
    }

    function handleErrors(xhr, status, error) {
        if (status === 'error') {
            swal({
                title: 'Warning!',
                text: xhr.responseJSON,
                type: "warning"
            }, function(result) {
                if (result) {
                    window.location.href = "{{ route('admin.loan.importView') }}";
                }
            });
        }
    }
});

    </script>
@endsection
