<div class="col-md-12">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
        <div class="card-body">
            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                @csrf
                <input type="hidden" class="form-control created_at" name="created_at" id="created_at" value="">
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Branch</label>
                            <div class="col-lg-12 error-msg">
                                <div class="">
                                    <select class="form-control"
                                        name="branch_id" id="branch_id"
                                        title="Please Select Branch">
                                        <option value="">---Please Select Branch---</option>                                        
                                        @foreach ($Allbranch as $item)
                                        <option value="{{ $item->branch->id }}" {{($item->branch->id == $branch_id) ? 'selected' : ''}}>{{ $item->branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">From Date </label>
                            <div class="col-lg-12 error-msg">
                                 <div class="input-group">
                                     <input type="text" class="form-control  " name="start_date" id="start_date" value="{{$start_date}}" readonly>  
                                   </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">To Date </label>
                            <div class="col-lg-12 error-msg">
                                 <div class="input-group">
                                     <input type="text" class="form-control end_date" name="end_date" id="end_date" value="{{$end_date}}" readonly> 
                                   </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="export_title" value="Profit & Loss -  ">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-lg-12 text-right" >
                                @php
                                $arrayhidden = [
                                    'head_id'=>$head_id,
                                    'label'=>$label,
                                    'company'=>$company_id,
                                    'is_search'=>'no',
                                    'label'=>$label,
                                ];
                                @endphp

                                @foreach($arrayhidden as $key => $value)
                                    <input type="hidden" name="{{$key}}" id="{{$key}}" value="{{$value}}"/>
                                @endforeach                                
                                
                                <input type="hidden" name="export" class="export" id="export"/>
                                <button type="button" class=" btn bg-dark legitRipple" onClick="searchtdsForm()" >Submit</button>
                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resettdsForm()" >Reset </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
