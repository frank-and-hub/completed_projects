@php
   $types = 'Tenure';
   @endphp
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="col-md-12">
<div class="card bg-white">
    <div class="card-body">
        <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Loan Tenure</h3>
        <div class="table-responsive py-4">
        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                <thead class="thead-light">
                    <tr>
                        <th style="border: 1px solid #ddd; width:20%;">Emi Option</th>
                        <th style="border: 1px solid #ddd; ">Tenure</th>
                        <th style="border: 1px solid #ddd;">ROI </th>
                        <th style="border: 1px solid #ddd;">Effective From </th>
                        <th style="border: 1px solid #ddd;">Effective To</th>
                        @if($type == 'editForm' && $resourceType != "plan")<th style="border: 1px solid #ddd; "> <button type="button" class="btn btn-primary tenureM" data-toggle="modal" data-target="#tenureModel" data-OldData = "{{json_encode($old,true)}}"  data-type="{{$types}}" data-title = "Edit Tenure" >   <i class="icon-add"></i></button></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @if($type == 'editForm')
                    @foreach($loanTenure as $value)
                    @php 
                        if($value->emi_option == 1)
                        {
                            $label = 'Monthly';
                        }
                        elseif($value->emi_option == 2){
                            $label = 'Weekly';
                        }
                        elseif($value->emi_option == 3){
                            $label = 'Daily';
                        }
                    @endphp
                    <input type="hidden" name="tenure_ids" value="{{$value->id}}" >
                    <input type="hidden" name="loan_id" value="{{$value->loan_id}}" id="loan_id" >
                    <tr >
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;" >
                            <input type="text" name="emi_option"  class="form-control emi_option_{{$value->id}}" autocomplete="off" data-value= "{{$value->emi_option}}" value="{{$label}}" readonly>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure"  class="form-control tenure_{{$value->id}}" autocomplete="off" value="{{$value->tenure}}" readonly>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="roi"  min="0" autocomplete="off" class="form-control roi_{{$value->id}}" value="{{$value->ROI}}" readonly>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure_effective_from"  autocomplete="off" class="form-control effective_from tenure_effective_from_{{$value->id}}" value="@if(isset($value->effective_from)){{date('d/m/Y',strtotime($value->effective_from))}} @endif" disabled>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure_effective_to"  autocomplete="off" class="form-control effective_from tenure_effective_to_{{$value->id}}"value="@if(isset($value->effective_to)){{date('d/m/Y',strtotime($value->effective_to))}} @endif" disabled>
                        </td>
                        @if($resourceType != "plan")
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;display:flex;justify-content: space-between;">
                            @if(!empty($record[$value->tenure]))
                                @if(count($record[$value->tenure]) == 0)
                                    <button type="button" class="btn btn-danger add_qualification_trash" id="add_qualification_trash" data-Id="{{$value->id}}"><i class="icon-trash"></i></button>
                                @endif
                            @else
                                <button type="button" class="btn btn-danger add_qualification_trash" id="add_qualification_trash" data-Id="{{$value->id}}"><i class="icon-trash"></i></button>
                            @endif
                            <button type="button" class="btn btn-primary tenureM ml-2" data-toggle="modal" data-target="#tenureModel"  data-type="{{$types}}" data-title = "Edit Tenure" data-Id = "{{$value->id}}" data-OldData = "{{json_encode($old,true)}}"  >   <i class="icon-pencil"></i></button>
                            @if ($value->status == 1)
                                <button class="btn btn-success status_button ml-2" data-id="{{$value->id}}"><i class="fa fa-check-circle"></i></button>
                            @else
                                <button class="btn btn-danger status_button ml-2" data-id="{{$value->id}}"><i class="fa fa-ban"></i></button>
                            @endif
                        </td>
                        @endif
                    </tr>  
                    @endforeach
                    @else
                    <tr>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                            <div class="col-lg-12">
                                <select class="form-control select" name="emi_option" id="emi_option">
                                    <option value="">Select Emi Option</option>
                                    <option value="1">Monthly
                                    </option>
                                    <option value="2">Weekly
                                    </option>
                                    <option value="3">Daily
                                    </option>
                                </select>
                            </div>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure" id="tenure" class="form-control" autocomplete="off" reqiured>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="roi" id="roi" min="0" autocomplete="off" class="form-control">
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure_effective_from" id="tenure_effective_from" autocomplete="off" class="form-control effective_from" disabled>
                        </td>
                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                            <input type="text" name="tenure_effective_to" id="tenure_effective_to" autocomplete="off" class="form-control effective_to">
                        </td>
                        <!-- <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                            <button type="button" class="btn btn-primary" id="add_qualification"><i class="icon-add"></i></button>
                        </td> -->
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@if($type == 'editForm')                        
@include("templates.admin.loan.tenure_modal")
@endif