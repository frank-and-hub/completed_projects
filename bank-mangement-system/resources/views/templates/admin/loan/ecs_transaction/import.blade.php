@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="card py-5 px-3">
            <form action="{{ route('admin.import-csv') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <input type="file" required class="form-control" name="excel_file" accept=".xlxs,.xlsx, .csv">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary">Import file</button>
                    </div>
                    <div style="margin-left:30px" class="col-md-7">
                        <a href="javascript:void(0);" id="downloadExcel" title="A sample file" class="btn btn-dark">Download Sample File</a><small><b class="text-danger pl-2">Note :- You can upload any file containing the required fields as listed in the sample sheet.<sup>*</sup></b></small>
                    </div>
                </div>
            </form>
        </div>
        <div class="card py-5 px-3 d-none">
            <table class="table datatable-show-all" id="">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Loan Account Number</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Bank Account Number</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop
@section('script')
<script type="text/javascript">
$(document).ready(function () {
    $.ajax({
        type: "POST",
        url: "{!! route('admin.loan.ecs_import_listing') !!}",
        data: "data",
        dataType: "dataType",
        success: function (response) {
            
        }
    });

    $("#downloadExcel").click(function() {
        // Static data
        var data = [
            ['STATUS','POOLING ACCOUNT NUMBER','CREDIT DATE','TRANSACTION AMOUNT','Customer Transaction Ref No','IFSC/MICR CODE','DEBIT ACCOUNT NUMBER','CREDIT CONSOLIDATION NUMBER','UMRN NUMBER'],
            ["REALISED","920020034178187",  "25-02-2029" , "1","104980800001","PYTM0123456","12390100023374","220224666859","BARB7021801243004870"]
        ];

        // Convert data to CSV format
        var csvContent = "data:text/csv;charset=utf-8,";
        data.forEach(function(rowArray) {
            var row = rowArray.join(",");
            csvContent += row + "\r\n";
        });

        // Create a temporary anchor element
        var link = document.createElement("a");
        link.setAttribute("href", encodeURI(csvContent));
        link.setAttribute("download", "ecs_transaction.csv");
        link.style.display = "none";

        // Append the anchor element to the body and trigger the click event
        document.body.appendChild(link);
        link.click();

        // Cleanup
        document.body.removeChild(link);
    });
}); 
</script>
@endsection
