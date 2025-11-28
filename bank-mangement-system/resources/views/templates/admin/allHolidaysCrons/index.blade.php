@extends('templates.admin.master')

@section('content')
@section('css')

@endsection

<style>
.error-message{
    color:red;
}
    </style>

<div class="content">
    <div class="row">
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Crons List</h6>
                    <!-- add condition in allholiday crom also  -->
                    <button style="float: right;" type="button" class="btn btn-primary" onclick="addNewHolidayCron()">Add New Holiday Cron</button>
                </div>
                <div class="">
                    <table id="cron_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Title</th>

                                <th>Cron Name</th>
                                <th>Cron Date</th>
                                <th>Template Id</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $key=>$value)

                    
                            <tr>
                                <th scope="row">{{++$key}}</th>
                                <td>{{$value->title}}</td>
                                <td>{{$value->cron_name}}</td>
                                <td>{{ date('d/m/Y', strtotime($value->cron_date)) }}</td>
                                <td>{{$value->templateId}}</td>
                                <td>{{$value->created_by}}</td>
                                <td>{{$value->updated_by}}</td>
                                <td>
                                    @php
                                        $buttonClass = $value->status == 1 ? 'btn-success' : 'btn-danger';
                                        $buttonText = $value->status == 1 ? 'Active' : 'InActive';
                                    @endphp
                                    <button type="button" onclick="updateNewHolidayCronStatus('{{ $value->id }}')" class="btn {{ $buttonClass }} btn-sm">{{ $buttonText }}</button>
                                </td>

                               
                                <th>
                                    <a class="btn bg-dark legitRipple" onclick="updateNewHolidayCron('{{ $value->id }}', '{{ $value->title }}', '{{ $value->cron_date }}', '{{ $value->templateId }}','{{$value->cron_name}}','{{$value->message}}')" title="Edit Cron">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a class="btn bg-dark legitRipple" href="{{route('admin.check.holiday.cron.logs', $value->id)}}" title="View Logs">
                                        <i class="fa fa-eye"></i>
                                    </a>
                            
                                </th>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scrollable modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New Holiday Cron</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="HolidaysCron" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="corn_title">Title<sup class="error"> *</sup></label>
                        <input type="text" name="corn_title" id="corn_title" class="form-control" required>
                        <span class="error-message" id="corn_title_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="cron_name">Cron Name<sup class="error"> *</sup></label>
                        <input type="text" name="cron_name" id="cron_name" class="form-control" required>
                        <span class="error-message" id="cron_name_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="templateId">Template Id<sup class="error"> *</sup></label>
                        <input type="text" name="templateId" id="templateId" class="form-control" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <span class="error-message" id="templateId_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="templateId">Message<sup class="error"> *</sup></label>
                        <textarea class="form-control" name="message" id="message"></textarea>
                        <span class="error-message" id="message_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="effective_to_model">Date<sup class="error"> *</sup></label>
                        <input type="text" name="effective_to_model" id="effective_to_model" class="form-control effective_to" required>
                        <span class="error-message" id="effective_to_model_error"></span>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" onclick="submitUpdateEffectiveToDate();" class="btn btn-primary">Submit</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- update -->
<div class="modal fade" id="myModalupdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update New Holiday Cron</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="HolidaysCron" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="corn_title">Title<sup class="error"> *</sup></label>
                        <input type="text" name="corn_title" id="corn_title_update" value="" class="form-control" readonly>
                        <div class="error-message" id="corn_title_error"></div>
                    </div>
                    <div class="form-group">
                        <label for="cron_name">Cron Name<sup class="error"> *</sup></label>
                        <input type="text" name="cron_name" id="cron_name_update" class="form-control" readonly required>
                        <div class="error-message" id="cron_name_error"></div>
                    </div>
                    <div class="form-group">
                        <label for="templateId">Template Id<sup class="error"> *</sup></label>
                        <input type="text" name="templateId" id="templateId_update" class="form-control" readonly required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <div class="error-message" id="templateId_error"></div>
                    </div>
                    <div class="form-group">
                        <label for="templateId">Message<sup class="error"> *</sup></label>
                        <textarea class="form-control" name="message_update" id="message_update" readonly></textarea>
                        <div class="error-message" id="templateId_error"></div>
                    </div>
                    <input type="hidden" id="modelid" value="">
                    <div class="form-group">
                        <label for="effective_to_model">Date<sup class="error"> *</sup></label>
                        <input type="text" name="effective_to_model" id="effective_to_model_update" class="form-control effective_to" required>
                        <div class="error-message" id="effective_to_model_error"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="updateCronUpdate();" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('templates.admin.allHolidaysCrons.particals.script')

@stop
