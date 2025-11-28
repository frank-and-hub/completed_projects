<div class="col-md-4">

	<div class="form-group row">

		<label class="col-form-label col-lg-12"> {{$dropDownCompanyTitle}} </label>

		<div class="col-lg-12 error-msg">

			<select class="form-control select" name="{{$companyFilterName}}" id="company" title="{{$companyFilterName}}">
			@foreach($branchCompany as $key => $val)
			<option value="{{$key}}" {{ isset($selectedValue) ? ( $selectedValue == $key ? 'selected' : '' ) : '' }} >{{$val}}</option>
			@endforeach
			</select>

		</div>

	</div>

</div>