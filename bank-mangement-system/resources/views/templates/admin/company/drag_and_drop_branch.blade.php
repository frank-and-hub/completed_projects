<div class="form-group row">
	@if($valueArray=='branch')
    <div class="col-md-6">
        <strong class="text-center text-bold ">{{$labelOne}}</strong><br>
        <input placeholder="Search Account Head" type="search" id="{{$searchOne}}" name="{{$searchOne}}" class="w-100 p-1 my-3 form-control d-none" />
        <div class="h-50 w-100">
            <ul class="connectedSortable p-2" id="sortable1" style="max-height:300px; height:300px; overflow-y: scroll; overflow-x: hidden;">
                @foreach($$valueArray as $val)
                <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="hidden" id="{{$val->$value}}" value="{{$val->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
                            <label for="{{$val->$value}}" class="w-100">{{$val->$dataOne}}</label>
                        </div>
                        @if($dataTwo)
                        <div class="col-md-3">
                            <label class="w-100">({{$val->$dataTwo}})</label>
                        </div>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
	@else
	<div class="col-md-6">
        <strong class="text-center text-bold ">{{$labelOne}}</strong><br>
        <input placeholder="Search Account Head" type="search" id="{{$searchOne}}" name="{{$searchOne}}" class="w-100 p-1 my-3 form-control d-none" />
        <div class="h-50 w-100">
            <ul class="connectedSortable p-2" id="sortable1" style="max-height:300px; height:300px; overflow-y: scroll; overflow-x: hidden;">
                @foreach($$valueArray as $val)
				@if($val->companybranchs)
                <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="hidden" id="{{$val->companybranchs?$val->$value:''}}" value="{{$val->companybranchs?$val->$value:''}}" name="{{$valueArray}}[]" class="w-100 disable" />
                            <label for="{{$val->companybranchs?$val->$value:''}}" class="w-100">{{$val->companybranchs?$val->$dataOne:''}}</label>
                        </div>
                        @if($val->companybranchs?$val->$dataTwo:'')
                        <div class="col-md-3">
                            <label class="w-100">({{$val->companybranchs?$val->$dataTwo:''}})</label>
                        </div>
                        @endif
                    </div>
                </li>
				@endif
                @endforeach
            </ul>
        </div>
    </div>
	@endif
    <div class="col-md-6 ">
        <strong class="text-center text-bold">{{$labelTwo}}</strong><br>
        <input placeholder="Search Account Head" type="search" id="{{$searchTwo}}" class="w-100 p-1 my-3 form-control d-none" name="{{$searchTwo}}" />
        <div class="h-50 w-100">
            <ul class="connectedSortable p-2" id="sortable2" style="max-height:300px; height:300px; overflow-y: scroll; overflow-x: hidden;">
                @if($valueArray_opposite)
                @foreach($$valueArray_opposite as $val)   
			
                <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="hidden" id="{{$val->$value}}" value="{{$val->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
                            <label for="{{$val->$value}}" class="w-100">{{$val->$dataOne}}</label>
                        </div>
                        @if($val->$dataTwo)
                        <div class="col-md-3">
                            <label class="w-100">({{$val->$dataTwo}})</label>
                        </div>
                        @endif
                    </div>
                </li>

                @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>