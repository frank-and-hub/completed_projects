@extends('layouts/branch.dashboard')

@section('content')

    <div class="loader" style="display: none;"></div>

    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">{{$title}}</h3>
                            <!--<a href="{!! route('branch.fundtransfer.createbanktobank') !!}" style="float:right" class="btn btn-secondary">Add</a>-->
                            <!-- Validate error messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                        @endif
                        <!-- Validate error messages -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="bank_to_bank_listing" class="table table-flush">
                                        <thead class="">
                                        <tr>
                                            <th>S/N</th>
                                            <th>From Bank</th>
                                            <th>Bank A/C</th>
                                            <th>Transfer Mode</th>
                                            <th>To Bank</th>
                                            <th>Bank A/C</th>
                                            <th>Transfer Amount</th>
                                            <th>Remark</th>
                                            <th>Status</th>
                                            {{--<th>Action</th>--}}
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('templates.branch.fund-transfer.partials.script')
@stop