 <!--{{-- {!! Form::open(['url'=>'#','method'=>'POST','id'=>'company_default_settings' ,'name'=>'company_default_settings','class'=>'row','style'=>'display: none;']) !!}
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Name<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="settings_name" class="form-control" id="settings_name" value="{{ $default_settings ? $default_settings->name : old('settings_name')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Short name<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="settings_short_name" class="form-control" id="settings_short_name" readonly value="{{ $default_settings ? $default_settings->short_name : old('settings_short_name')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Effective from<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="settings_effective_from" class="form-control create_application_date" id="settings_effective_from" readonly value="{{ $default_settings ? $default_settings->effective_from : old('settings_effective_from')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    {!! Form::hidden('system_date','',['id'=>'system_date','class'=>'created_at']) !!}
    {!! Form::hidden('default_settings_id',$default_settings->id??old('default_settings_id')) !!}
    {!! Form::hidden('user_id',Auth::user()->id,['id'=>'user_id']) !!}
    {!! Form::hidden('company_id_default_settings',$company->id??old('company_id_default_settings')) !!}
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Amount<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="settings_amount" class="form-control" id="settings_amount" value="{{ $default_settings ? $default_settings->amount : old('settings_amount')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="row col-md-12">
        <div class="col-md-6">
            <div class="text-start">
                <button class="btn btn-primary " id="prev_three">Previous</button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="text-right">
                <button class="btn btn-primary " type="submit">Next</button>
            </div>
        </div>
    </div>
 {!! Form::close() !!} --}}-->