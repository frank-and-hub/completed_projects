@extends('templates.admin.master')
@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Commission | Daily Account Setting</h6>
                </div>
                <div class="card-body">
                    <form action="{!! route('admin.dailyaccount.setting_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="row">
                                    <label class="col-lg-3">Min. Days</label><label class="col-lg-1">:</label>
                                    <div class="col-lg-5 error-msg">
                                        <input class="form-control" type="text" name="min_days" id="min_days"  class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="row">
                                    <label class="col-lg-3">Max. Days</label><label class="col-lg-1">:</label>
                                    <div class="col-lg-5 error-msg">
                                        <input class="form-control" type="text" name="max_days" id="max_days"   class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="row">
                                    <button type="Submit" class="btn bg-dark legitRipple">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-header header-elements-inline"></div>
                            <div class="">
                                <table id="daily_account_listing" class="table datatable-show-all">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Created Date</th>
                                            <th>Min. Days</th>
                                            <th>Max. Days</th>
                                            <th>Created by</th>
                                            <th>User Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    @foreach($dailyaccountsettings as $sno=> $dailyaccountsetting)
                                    <tr>
                                      
                                        <td>{{$sno+1}}</td>
                                        <td>{{date("d/m/Y", strtotime($dailyaccountsetting->created_at))}}</td>
                                        <td>{{$dailyaccountsetting->min_days}}</td>
                                        <td>{{$dailyaccountsetting->max_days}}</td>
                                           @php
                                                $createdbyname = $dailyaccountsetting->created_by;
                                           
                                                if($dailyaccountsetting->created_by == 1)
                                                {
                                                    $createdbyname = "Admin";
                                                }else{
                                                    $createdbyname = "Branch";
                                                }
                                            
                                                $val['created_by'] = $createdbyname ; 
                                           @endphp 
                                        <td>{{$createdbyname }}</td>
                                        @php
                                        if($dailyaccountsetting->created_by == 1)
                                            {
                                            $createdbyname = \App\Models\Admin::where('id',$dailyaccountsetting->created_by_id)->first('username');
                                            $createdbyname = $createdbyname->username;
                                            }
                                            else{
                                                $createdbyname = \App\Models\Branch::where('id',$dailyaccountsetting->created_by_id)->first('name');
                                               
                                                $createdbyname = $createdbyname->name;
                                            }   
                                        @endphp        
                                        <td>{{$createdbyname }}</td>

                                        @php
                                        $status ='';
                                         if($dailyaccountsetting->status==1){
                                            $status = 'Active';
                                         }else{
                                            $status = 'Inactive';
                                         }
                                        @endphp
                                        <td>{{ $status}}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.associate.partials.daily_account_setting_js') @stop
