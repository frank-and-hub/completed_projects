<form id="company_register_form" name="company_register_form" class="row" method="POST" enctype="multipart/form-data">
<div class="col-12">
	<h3 class="text-center">{{ ($title == 'Company | Edit Company Details') ? 'Edit' : 'Step - 1'}} Company Basic Details </h3>
	<hr>
</div>
    @csrf
    <input type="hidden" name="created_at" class="created_at" id="created_at">
    <input type="hidden" name="globalDate" class="create_application_date" id="globalDate">
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Comany Name<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" name="name" class="form-control" id="name"value="{{$company->name ?? old('name')}}">
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    @if($company->id)
    <input type="hidden" value="{{$company->id}}" name="company_id"/>
    @endif
    @if($company->status)
    <input type="hidden" value="{{$company->status}}" name="company_status"/>
    @endif
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Short Name<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg" >
                <input type="text" class="form-control" name="short_name" id="short_name" value="{{$company->short_name ?? old('short_name')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        {{--
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Company Image<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg justify-content-center row" >
                <img src="{{ asset( $company->image ?? 'asset/company/company-logo-placeholder-image.png' ) }}" alt="Company Image" class="text-center" id="preview" style="height:5rem; cursor:pointer;"/>
                <input type="file"  class="form-control pt-0 " name="company_image" id="company_image" value=""/>
                <div class="input-group">
                </div>
            </div>
        </div>
        --}}
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Company Image<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg justify-content-center row" >
                @php
                $CompanyImage = (($company->image) ? (ImageUpload::generatePreSignedUrl("company/".$company->image)) : (asset('asset/company/company-logo-placeholder-image.png'))) ;
                @endphp
                <img src="{{ $CompanyImage }}" alt="{{$company->short_name ?? old('short_name')}} Company Image" class="text-center" id="preview" style="height:5rem; cursor:pointer;"/>
                <input type="file"  class="form-control pt-0 d-none" name="company_image" id="company_image" value=""/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    {!! Form::hidden('hidden_image',$company->image??'') !!}
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Mobile Number<sup class="text-danger">*</sup> </label>
            <div class="col-lg-12 error-msg">
                <input class="form-control numberonly" id="mobile_no" name="mobile_no" type="mobile_no" value="{{$company->mobile_no ?? old('mobile_no')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">address<sup class="text-danger">*</sup> </label>
            <div class="col-lg-12 error-msg">
                <input class="form-control" id="address" name="address" type="address" value="{{$company->address ?? old('address')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="{{Auth::user()->id}}" name="user_id" id="user_id"/>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Email Address<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="email" class="form-control" name="email" id="email" value="{{$company->email ?? old('email')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">FA Code From<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" class="form-control numberonly" maxlength="4" name="fa_code_from" id="fa_code_from" value="{{$company->fa_code_from ?? old('fa_code_from')}}" {{$title != 'Company | New Company Register' ? 'readonly' : '' }}/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">FA Code to<sup class="text-danger">*</sup></label>
            <div class="col-lg-12 error-msg">
                <input type="text" class="form-control numberonly" maxlength="4" name="fa_code_to" id="fa_code_to" value="{{$company->fa_code_to ?? old('fa_code_to')}}" readonly />
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">TIN Number</label>
            <div class="col-lg-12 error-msg">
                <input type="text" class="form-control" name="tin_no" id="tin_no" value="{{$company->tin_no ?? old('tin_no')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">PAN Number</label>
            <div class="col-lg-12 error-msg">
                <input type="text" class="form-control" name="pan_no" id="pan_no" value="{{$company->pan_no ?? old('pan_no')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">CIN Number</label>
            <div class="col-lg-12 error-msg">
                <input type="text" class="form-control" name="cin_no" id="cin_no" value="{{$company->cin_no ?? old('cin_no')}}"/>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
	@if($title!='Company | Edit Company Details')
    <div class="col-md-12">
        <div class="text-right">
            <button class="btn btn-primary" id="disablebtn" type="submit" >Next</button>
        </div>
    </div>
	@else
	<div class="col-md-12">
        <div class="text-right">
            <button class="btn btn-primary " type="button" id="update_company" >Update</button>
        </div>
    </div>
	@endif
</form>