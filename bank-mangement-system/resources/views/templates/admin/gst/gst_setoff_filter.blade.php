<div class="col-md-12">
    <!-- Basic layout-->
    <div class="card my-4">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
            <div class="card-body" id="bank-to-bank">
                @if(count($errors))
                    <div class="form-group">
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{$error}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                {{Form::open(['url'=>'#','method'=>'POST','id'=>'gst_setoff_filter','name'=>'gst_setoff_filter','enctype'=>'multipart/form-data'])}}
                       <div class="row ">
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                        {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control'])}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">To Date </label>
                                <div class="col-lg-12 error-msg">
                                        <div class="">
                                    {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control'])}}
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Company <sup>*</sup></label>
                                <div class="col-lg-12 error-msg">
                                        <div class="">
                                        <select class="form-control" id="company_id" name="company_id" required>
                                            <option value="">---- Please Select Company----</option>
                                            @foreach($company as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                    </select>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">State </label>
                                <div class="col-lg-12 error-msg">
                                        <div class="">
                                        <select class="form-control" id="state" name="state" >
                                            <option value="">---- Please Select ----</option>                                            
                                            @foreach($allState as $key => $val)
                                                <option value="{{ $key }}" >{{ $val[0]['state']['name'] . ' - ' . $key }}</option>
                                            @endforeach                                            
                                    </select>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Set-Off <sup>*</sup></label>
                                <div class="col-lg-12 error-msg">
                                        <div class="">
                                        <select class="form-control" id="setoff" name="setoff" required>
                                            <option value="">---- Please Select ----</option>                                           
                                            <option value="1" >Yes</option>
                                            <option value="0" >No</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>	
                    <div class="text-right">
                        {{Form::hidden('created_at','',['id'=>'created_at','class'=>'form-control created_at'])}}
                        {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                        <button type="submit" class=" btn bg-dark legitRipple" id="setofffiltersubmit">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="reset_setoff_form" onClick="resetForm()">Reset</button>
                    </div>
                {{Form::close()}}
            </div>
        <!-- /basic layout -->
    </div>
</div>