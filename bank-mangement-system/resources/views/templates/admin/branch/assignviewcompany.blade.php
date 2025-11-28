@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                
                <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                         <strong class="">Branch Name:</strong>
                                        </div>
                                        <div class="col-md-6">
                                         <span class="mr-5" style="">{{$branch->name}}</span>
                                        </div>
                                    </div>
                            </div>
                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                             <strong>Cash In Hand :</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->cash_in_hand}}</span>
                                        </div>
                                    </div>
                               
                            </div>
                            <div class="col-lg-4">   
                                    <div class="row">
                                        <div class="col-md-6">
                                          <strong>Country:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{ \App\Http\Controllers\Admin\BranchController::CompanyLocation($branch->country_id,'country')}}</span>
                                        </div>
                                    </div>        
                            </div>
                        </div>

                        <div class="form-group row">
                            
                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>State :</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{ \App\Http\Controllers\Admin\BranchController::CompanyLocation($branch->state_id,'state')}}</span>
                                        </div>
                                    </div>   
                            </div>

                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>City :</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{ \App\Http\Controllers\Admin\BranchController::CompanyLocation($branch->city_id,'city')}}</span>
                                        </div>
                                    </div>   
                            </div>

                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Sector:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->sector}}</span>
                                        </div>
                                    </div>  
                            </div>
                            
                        </div>

                        <div class="form-group row">
                           
                                    
                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Region:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->regan}}</span>
                                        </div>
                                    </div>  
                            </div>

                            <div class="col-lg-4">
                                     <div class="row">
                                        <div class="col-md-6">
                                            <strong>Zone:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->zone}}</span>
                                        </div>
                                    </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="row">
                                        <div class="col-md-6">
                                            <strong>Postal code:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->pin_code}}</span>
                                        </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            
                           

                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Address:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->address}}</span>
                                        </div>
                                    </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="row">
                                        <div class="col-md-6">
                                            <strong>Branch Phone Number:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->phone}}</span>
                                        </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Email id:</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="mr-5" style="">{{$branch->email}}</span>
                                        </div>
                                    </div>
                            </div>
                           
                        </div>

                </div>
                
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Assigned Companies</h6>
                </div>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">S.NO.</th>
                            <th scope="col">Company</th>
                            <th scope="col">Old Business</th>
                            <th scope="col">New Business</th>
                            <th scope="col">Primary</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @if(!empty($branch->companies_branch))
                        @foreach($branch->companies_branch as $key => $combranch)
                       
                        <tr>
                            <th scope="row">
                               {{$key+1}}
                            </th>
                            <td>{{$combranch->get_company->name}}</td>
                            <td>
                                @if($combranch->is_old_business == 1)
                                    <span class="text-success">Active</span>
                                @else 
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($combranch->is_new_business == 1)
                                    <span class="text-success">Active</span>
                                @else 
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($combranch->is_primary == 1)
                                    <span class="text-success">Active</span>
                                @else 
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.branch.partials.branch-script')
@stop