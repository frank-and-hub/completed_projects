@extends('layouts.app')
@section('content')
@include('admin.show_filter',['email'=>$email])

<div class="card">
    <div class="card-header">{{ __('Report List') }}</div>
        <div class="card-body">
            <div class="row">
                <div class="form-row">
                    <div class="container">    
                        <table class="table" id="show_table_csv">
                            <thead>
                                <tr>
                                    <th scope="col-2">S. No.</th>
                                    <th scope="col-2">Email</th>
                                    <th scope="col-2">Name</th>
                                    <th scope="col-2">Company Name</th>
                                    <th scope="col-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function () {
        send_email_{{$email->id}}();
    });
        function send_email_{{$email->id}}() {
            var formData = $('#start_email_sending_report').serializeArray();
            var chunkAndLimit = 1;
            doChunkedEmailsTransactions_{{$email->id}}(0, chunkAndLimit, formData, chunkAndLimit, '');
        }
        function doChunkedEmailsTransactions_{{$email->id}}(start, limit, formData, chunkSize, fileName) {
            $.post("{{route('user.send.email')}}", { 'form': formData, "_token": csrfToken , 'start' : start , 'limit':limit}, function (r) {
                if (r.result == 'next') {
                    start = start + chunkSize;
                    console.log(start);
                    fileName = r.tokenName;
                    setTimeout(() => {
                        doChunkedEmailsTransactions_{{$email->id}}(start, limit, formData, chunkSize, fileName);
                    }, 5000); 
                    csvfetchUpdatedUserData_{{$email->id}}();
                    getEmailCount_{{$email->id}}();
                } else if (r.result == 'error'){
                    alert(r.msg);
                } else if (r.result == 'completed'){
                    // alert(r.msg);
                } else {
                    alert('All emails sent sucessfully !');
                }
            }, "JSON");
        }
        csvfetchUpdatedUserData_{{$email->id}}();
        function csvfetchUpdatedUserData_{{$email->id}}() {
            var formData = $('form#start_email_sending_report').serialize(); // Serialize the form data
            $.ajax({
                url: "{{ route('user.show.list.csv') }}",
                method: 'POST',
                data: formData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $('#show_table_csv tbody').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching updated data:', error);
                }
            });
        }
        function getEmailCount_{{$email->id}}(){
            var id = '{{$email->id}}';
            $.post("{{route('user.count.emails')}}",{'id':id,'_token':csrfToken},function(r){
                $('#totalemailswent').text(r);
            })
        }
</script>
@endsection