<form id="company_associate_setting_form" name="company_associate_setting_form" class="row form-control">
    @csrf
    <input type="hidden" name="created_at" class="created_at" id="created_at">
    <input type="hidden" name="globalDate" class="create_application_date" id="globalDate">
    <div class="col-md-12">
        <div class="form-group row">
            <la class="col-form-label col-lg-12">Assign Company to Associates<span>*</span></strong>
            <div class="col-lg-12 error-msg">
                <select class="select2 form-control" name="company_id" id="company_id">
                    @foreach($company as $key => $val)
                    <option value="{{$key}}" {{$company_associate ? $company_associate->company_id == $key ? 'selected': '' : '' }} >{{$val}}</option>
                    @endforeach
                </select>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="text-right">
            <button class="btn btn-primary" type="button" id="submit_associate_setting_form" >Submit</button>
        </div>
    </div>
</form>
@include('templates.admin.company.partials.associate_script')