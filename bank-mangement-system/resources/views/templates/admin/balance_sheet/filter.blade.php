<div class="col-md-12">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
        <div class="card-body">
                {{Form::open(['url'=>'#','method'=>'post','class'=>'','id'=>'filter','name'=>'filter','enctype'=>'multipart/form-data']) }}
                <div class="row">
                    {{--
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Branch</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    <select class="form-control"
                                        name="branch_id" id="branch_id"
                                        title="Please Select Branch">
                                        <option value="">---Please Select Branch---</option> 
                                                                               
                                        @foreach ($Allbranch as $key=>$item)
                                                <option value="{{ $item->branch->id }}" {{($item->branch->id  == $branch_id) ? 'selected' : ''}}>{{ $item->branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>   
                    --}}    
                    
                    @include('templates.GlobalTempletes.new_role_type', [
                        'dropDown' => $AllCompany,
                        'filedTitle' => 'Company',
                        'name' => 'company_id',
                        'value' => '',
                        'selectedCompany' => $company_id,
                        'design_type' => 4,
                        'branchShow' => true,
                        'branchName' => 'branch_id',
                        'selectedBranch' => $branch_id,
                        'apply_col_md' => true,
                        'multiselect' => false,
                        'placeHolder1' => 'Please Select Company',
                        'placeHolder2' => 'Please Select Branch',
                    ])
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">From Date </label>
                            <div class="col-lg-12 error-msg">
                                 <div class="input-group">
                                    {{Form::text('start_date',$start_date,['id'=>'start_date','class'=>'form-control','readonly'=>true])}}
                                     {{-- <input type="text" class="form-control  " name="start_date" id="start_date" value="{{$start_date}}" readonly>   --}}
                                   </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">To Date </label>
                            <div class="col-lg-12 error-msg">
                                 <div class="input-group">
                                    {{Form::text('end_date',$end_date,['id'=>'end_date','class'=>'form-control','readonly'=>true])}}
                                    {{-- <input type="text" class="form-control end_date" name="end_date" id="end_date" value="{{$end_date}}" readonly>  --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-lg-12 text-right" >
                                @php
                                $arrayhidden = [
                                    'head_id'=>$head_id,
                                    'label'=>$label,
                                    'is_search'=>'no',
                                    'label'=>$label,
                                    'export'=>'',
                                    'created_at'=>'',
                                    'export_title'=>'Balance Sheet - ',
                                ];
                                @endphp

                                @foreach($arrayhidden as $key => $value)
                                    {{Form::hidden($key,$value,['id'=>$key,'class'=>$key])}}
                                @endforeach
                                
                                <button type="button" class=" btn bg-dark legitRipple" onClick="searchtdsForm()" >Submit</button>
                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resettdsForm()" >Reset </button>
                            </div>
                        </div>
                    </div>
                </div>
            {{Form::close()}}
        </div>
    </div>
</div>
