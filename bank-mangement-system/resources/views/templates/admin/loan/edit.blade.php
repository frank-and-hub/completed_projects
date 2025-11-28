@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Edit</h6>
                </div>
            </div> -->
                <form action="{{route('admin.loan.plan.edit')}}" method="post" name="loanplanform" id="loanplanform">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-danger"></p>
                        
                                @csrf
                                <input type="hidden" name="created_at" class="created_at">
                                <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                <input type="hidden" name="plan_id" class="plan_id"  value="{{$plan->id}}">
                                <div class="form-group row col-sm-12">
                                    <div class="col-sm-6">
                                        
                                        <label class="col-form-label col-lg-2">Company:</label>
                                        <div class="col-lg-12">
                                        <input type="text" name="company_id" id="company_id" class="form-control" autocomplete="off" value="{{$company_name->name}}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        
                                        <label class="col-form-label col-lg-2">Type:</label>
                                        <div class="col-lg-12">
                                        <select class="form-control" id="loan_type" name="loan_type" {{($resourceType == "plan") ? 'disabled' : "" }} > 
                                            <option value=""  >----Select----</option> 
                                            <option value="L"  @if($plan->loan_type == 'L') selected @endif>Loan</option> 
                                            <option value="G" @if($plan->loan_type == 'G') selected @endif>Group Loan</option> 
                                        </select>
                                        </div>
                                    </div>
                                    
                                </div>



                                <div class="form-group row col-sm-12">
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Plan Name:</label>
                                        <div class="col-lg-12">
                                            <input type="text" name="name" id="name" class="form-control" autocomplete="off" value="{{$plan->name}}" {{($resourceType == "plan") ? 'readonly' : "" }}>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Code:</label>
                                        <div class="col-lg-12">
                                            <input type="text" name="code" id="code" class="form-control" autocomplete="off" value="{{$plan->code}}" {{($resourceType == "plan") ? 'readonly' : "" }} readonly>
                                        </div>
                                    </div>
                                    
                                </div>


                                <div class="form-group row col-sm-12">
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Category:</label>
                                        <div class="col-lg-12">
                                            <select class="form-control loan_category" name="loan_category" id="loan_category" {{($resourceType == "plan") ? 'disabled' : "" }}>
                                                <option value="">--Please Category -- </option>
                                                @foreach($loans as $loanData)
                                                    <option value="{{$loanData->id}}" @if($plan->loan_category == $loanData->id) selected @endif class="{{($loanData->loan_type == 'L') ? 'loan_cat' : 'grploan_cat' }}"  @if($loanData->loan_type != $plan->loan_type) style="display:none;" @endif>{{$loanData->name}}</option>  
                                                @endforeach
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Minimum amount:</label>
                                        <div class="col-lg-12">
                                            <input type="number" name="min_amount" id="min_amount" min="0" autocomplete="off" class="form-control" value="{{$plan->min_amount}}" {{($resourceType == "plan") ? 'readonly' : "" }}>
                                        </div>
                                    </div>                                  
                                </div>

                                <div class="form-group row col-sm-12">
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Maximum amount:</label>
                                        <div class="col-lg-12">
                                            <input type="number" step="any" name="max_amount" id="max_amount" autocomplete="off" min="0" class="form-control" value="{{$plan->max_amount}}" {{($resourceType == "plan") ? 'readonly' : "" }}>
                                            <span id="warning-msg" class="text-danger"></span>
                                        </div>

                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-form-label col-lg-6">Effective From</label>
                                        <div class="col-lg-12">
                                            <input readonly type="text" name="effective_from" id="effective_from" autocomplete="off " class="form-control effective_from" value="{{date('d/m/Y',strtotime($plan->effective_from))}}" {{($resourceType == "plan") ? 'disabled' : "" }}>
                                        </div>
                                    </div>
                                </div>
                               
                                @if($resourceType != "plan")
                                <div class="text-right">
                                    <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                                @endif    
                        </div>    
                        </div>
                       
                    </div>
               
                   
                    
                  
                </form>
                @include('templates.admin.loan.loan_tenure',['loanTenure' =>$loan,'type' => 'editForm','record'=>$record,'old'=> old(),'resourceType' => $resourceType])
                @if ($resourceType == "plan" && $plan->loan_category != 4)
                @include('templates.admin.loan.loan_file_charge',['fileCharge' =>$fileCharge,'type' => 'editForm','record'=>$recordFile,'old'=> old() ,'resourceType' => $resourceType])
                @include('templates.admin.loan.loan_insurance_charge',['insCharge' =>$insCharge,'type' => 'editForm','record'=>$recordIns,'old'=> old(), 'resourceType' => $resourceType])
                @elseif($resourceType != "plan")
                @include('templates.admin.loan.loan_file_charge',['fileCharge' =>$fileCharge,'type' => 'editForm','record'=>$recordFile,'old'=> old() ,'resourceType' => $resourceType])
                @include('templates.admin.loan.loan_insurance_charge',['insCharge' =>$insCharge,'type' => 'editForm','record'=>$recordIns,'old'=> old(), 'resourceType' => $resourceType])
                @endif
               
             
        </div>
    </div>
</div>
@include('templates.admin.loan.partials.edit_settingscript')
@stop