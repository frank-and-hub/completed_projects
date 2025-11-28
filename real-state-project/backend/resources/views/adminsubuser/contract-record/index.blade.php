@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
@push('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <style>
        .iti {
            display: block;
        }

        .auto-password {
            float: right;
            cursor: pointer;
            margin-right: 10px;
            margin-top: -32px;
            color: #F30051;
        }

        .select2-selection--multiple {
            padding-top: 5px !important;
        }

        select {
            border-radius: 3.3rem !important;
        }
    </style>
@endpush
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">list</li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row d-flex justify-content-between align-items-center">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                    </div>
                </div>
                <div class="text-right d-flex">
                    <div class="text-right">
                    </div>
                </div>
            </div>
            <h4 class="card-title"></h4>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="ContractRecordTable" class="table">
                            <thead>
                                <tr>
                                    {{-- <th>Sr.No.</th> --}}
                                    <th>Created At</th>
                                    <th>Tenant</th>
                                    <th>Admin</th>
                                    <th>Phone</th>
                                    <th>Property</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-script')
<script type="text/javascript">
    $(document).ready(function() {
        "use strict";
        var table = $('#ContractRecordTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.location.href,
            columns: [
            {
                data: 'created_at',
                name: 'created_at',
                orderable: true,
                render: function (data, type, row) {
                    return dateF2(data);
                }
            },{
                data: 'tenant',
                name: 'tenant',
                orderable: false
            },{
                data: 'admin',
                name: 'admin',
                orderable: false
            }, {
                data: 'phone',
                name: 'phone',
                orderable: false
            }, {
                data: 'property',
                name: 'property',
                orderable: false
            }, {
                data: 'status',
                name: 'status',
                orderable: false
            },{
                data: 'action',
                name: 'action',
                orderable: false
            }],
            drawCallback: function(settings, json) {
                $('[data-toggle=tooltip]').tooltip();
            }
        });

    });
</script>
@endpush
