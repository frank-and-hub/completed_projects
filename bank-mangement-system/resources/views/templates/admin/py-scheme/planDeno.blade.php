@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="modal fade" id="plandenomodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="plandenomodelLabel">Plan Denomination</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form name="plan_deno_form" id="plan_deno_form" action="#">
                                    @csrf
                                    <div class="form-group row tenureData">
                                        {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                        <label class="col-form-label col-lg-3">Plan</label>
                                        <div class="col-lg-9">
                                            @foreach($fetch as $value)
                                            <input type="text" class="form-control" name="plan" id="plan" value="{{$value->name}}" readonly>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group row tenureData">
                                        <label class="col-form-label col-lg-3">Plan code</label>
                                        <div class="col-lg-9">
                                            @foreach($fetch as $value)
                                            <input type="number" id="plan_code" name="plan_code" class="form-control"
                                                value="{{$value->plan_code}}" id="plan_code" readonly>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group row tenureData">
                                        <label class="col-form-label col-lg-3">Tenure</label>
                                        <div class="col-lg-9">
                                            <select name="tenure" class="form-control" id="tenure">
                                                @foreach($fetch_tenures as $tenures)
                                                <option>{{$tenures->tenure}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row tenureData">
                                        <label class="col-form-label col-lg-3">Denomination</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="denomination" id="denomination" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row tenureData">
                                        <label class="col-form-label col-lg-3">Effective From</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="effective_from" id="effective_from" class="form-control effective_from" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row tenureData">
                                        <label class="col-form-label col-lg-3">Effective To</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="effective_to" id="effective_to" class="form-control effective_to" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="text-right">

                                        @foreach($fetch as $value)
                                        <input type="hidden" name="plan_id" value="{{$value->id}}">
                                        @endforeach
                                        
                                        <button type="submit" class="btn bg-dark legitRipple" id="insertPlanDeno">Submit<i class="icon-paperplane ml-2"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-white">
            <div class="card-body">
                <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Plan Denomination</h3>
                <div class="table-responsive py-4 overflow-hidden">
                    <div class="">
                        <div class="form-group row tenureData">
                            <label class="col-form-label col-lg-1">Plan</label>
                            <div class="col-lg-3">
                                @foreach($fetch as $value)
                                <input type="text" class="form-control" name="plan" id="plan" value="{{$value->name}}" readonly>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group row tenureData">
                            <label class="col-form-label col-lg-1">Plan Code</label>
                            <div class="col-lg-3">
                                @foreach($fetch as $value)
                                <input type="text" class="form-control" value="{{$value->plan_code}}" readonly>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="">
                        
                        
                    <table class="table datatable-show-all refresh" id="file_charge_d">
                        <thead class="thead-light">
                            <tr>
                                <th>S. No.</th>
                                <th>Tenure</th>
                                <th>Denomination</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                                <th>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#plandenomodel" title="Add Plan Deno"> <i class="fa fa-plus"></i></button>
                                </th>
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
    @include('templates.admin.py-scheme.partials.planDenoScript');
@stop
