@extends('layouts.app')

@section('content')
@include('admin.filter')
<?php
$status = [
    0 => 'pending',
    1 => 'send',
    2 => 'feild',
    3 => 'pending'
];
?>
<div class="card">
    <div class="card-header">{{ __('Email Report List') }}</div>
        <div class="card-body">
            <div class="row">
                <div class="form-row">
                    <div class="container">
                        <table class="table" id="send_email_list_table">
                            <thead>
                                <tr>
                                    <th scope="col-2" >S. No.</th>
                                    <th scope="col-2" >Subject</th>
                                    <th scope="col-2" >Totla Email</th>
                                    <th scope="col-2" >Send Email</th>
                                    <th scope="col-2" >CSV</th>
                                    @if(Auth::user()->role == '1')
                                    <th scope="col-2" >Email Id</th>
                                    @endif
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

<div class="modal fade" id="largeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Email</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="model_email">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            {{--<button type="button" class="btn btn-primary">Save changes</button>--}}
        </div>
        </div>
    </div>
</div>
@include('admin.script')
@endsection
