<form id="company_register_fa_code" name="company_register_fa_code" class="row" style="display: none;" method="POST">
<div class="col-12">
	<h3 class="text-center">Step - 3 FA	Code </h3>
	<hr>
</div>
    @csrf
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Passbook<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="passbook_fa_code" class="form-control" id="passbook_fa_code" readonly value="{{$fa_code[0]->code ?? old('passbook_fa_code')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Member Id<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="member_id_fa_code" class="form-control" id="member_id_fa_code" readonly value="{{$fa_code[1]->code ?? old('member_id_fa_code')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Associate Code<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="associate_code_fa_code" class="form-control" id="associate_code_fa_code" readonly value="{{$fa_code[2]->code ?? old('associate_code_fa_code')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">SSb<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="SSb_code_fa_code" class="form-control" id="SSb_code_fa_code" readonly value="{{$fa_code[3]->code ?? old('SSb_code_fa_code')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="fa_code" value="" id="fa_code"/>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Certificate<sup class="text-danger" >*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="certificate_fa_code" class="form-control" id="certificate_fa_code" readonly value="{{$fa_code[4]->code ?? old('certificate_fa_code')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    @if($company->id)
    <input type="hidden" value="{{$company->id}}" name="company_id_fa"/>
    @endif
    <div class="row col-md-12">
        <div class="col-md-6">
            <div class="text-start">
                <button class="btn btn-primary " id="prev_two">Previous</button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="text-right">
                <button class="btn btn-primary " type="submit">Next</button>
            </div>
        </div>
    </div>
</form>