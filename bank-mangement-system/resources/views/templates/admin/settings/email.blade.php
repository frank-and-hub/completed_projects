<!doctype html>
<html class="no-js" lang="en">
<head>
</head>
<body>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Hi Admin</h6>
                </div>

                <div class="form-group row"> New member registered successfully</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>

                        <tr>
                            <th>MEMBER ID</th>
                            <th>MEMBER NAME</th>
                            <th>MEMBER MOBILE NO.</th>
                            <th>MEMBER BRANCH CODE</th>
                            <th>BRANCH MI</th>
                            <th>ASSOCIATE ID</th>

                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td> {{ $bodyMessage->member_id }} </td>
                            <td> {{ $bodyMessage->first_name }} {{ $bodyMessage->last_name }} </td>
                            <td> {{ $bodyMessage->mobile_no }} </td>
                            <td> {{ $bodyMessage->branch_code }} </td>
                            <td> {{ $bodyMessage->associate_senior_code }} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    Thanks,
                    S.B.Micro Finance Association,
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<footer>
</footer>
</html>