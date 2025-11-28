<div class="form-group row">
	<div class="col-md-12 text-center row justify-content-around">
		<div><span class="text-success">Green</span> - label 1 </div>
		<div><span class="text-primary">Blue</span> - label 2 </div>
		<div><span class="text-warning">Red</span> - label 3 </div>
		<div><span style="color:Orange;" >Orange</span> - label 4 </div>
		<div><span style="color:Violet;" >Violet</span> - label 5 </div>
		<div><span class="text-secondary">Black</span> - label 6 </div>
	</div>
	<div class="col-12 text-center"> 
	<hr>
	</div>
    <div class="col-md-6"> 
        <strong class="text-center text-bold ">{{$labelOne}}</strong><br>
        <input placeholder="Search Account Head" type="search" id="{{$searchOne}}" name="{{$searchOne}}" class="w-100 p-1 my-3 form-control d-none" />
        <div class="h-50 w-100">
            <ul class="connectedSortable p-2" id="sortable1" style="max-height:300px; height:300px; overflow-y: scroll; overflow-x: hidden;">
                @foreach($$valueArray as $valu)
                <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" id="{{$valu->$value}}" value="{{$valu->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
                            <label for="{{$valu->$value}}" class="w-100 text-success pl-0">{{$valu->$dataOne}}</label>
						</div>
						@if($dataTwo)
                        <div class="col-md-12">
                            <label class="w-100">({{$valu->$dataTwo}})</label>
                        </div>
                        @endif
                    </div>
				</li>
					@if(isset($valu->subcategory))
					@foreach($valu->subcategory as $val)
					<li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
						<div class="row">
							<div class="col-md-12">
								<input type="hidden" id="{{$val->$value}}" value="{{$val->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
								<label for="{{$val->$value}}" class="w-100 text-primary pl-1">{{$val->$dataOne}}</label>
							</div>
							@if($dataTwo)
							<div class="col-md-12">
								<label class="w-100">({{$v->$dataTwo}})</label>
							</div>
							@endif
						</div>
					</li>
						@if(isset($val->subcategory))
						@foreach($val->subcategory as $va)
						<li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
							<div class="row">
								<div class="col-md-12">
									<input type="hidden" id="{{$va->$value}}" value="{{$va->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
									<label for="{{$va->$value}}" class="w-100 text-warning pl-2">{{$va->$dataOne}}</label>
								</div>
								@if($dataTwo)
								<div class="col-md-12">
									<label class="w-100">({{$va->$dataTwo}})</label>
								</div>
								@endif
							</div>
						</li>	
							@if(isset($va->subcategory))
							@foreach($va->subcategory as $v)
							<li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
								<div class="row">
									<div class="col-md-12">
										<input type="hidden" id="{{$v->$value}}" value="{{$v->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
										<label for="{{$v->$value}}" style="color:Orange;" class="w-100 pl-3">{{$v->$dataOne}}</label>
									</div>
									@if($dataTwo)
									<div class="col-md-12">
										<label class="w-100">({{$v->$dataTwo}})</label>
									</div>
									@endif
								</div>
							</li>
								@if(isset($v->subcategory))
								@foreach($v->subcategory as $a)
								<li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
									<div class="row">
										<div class="col-md-12">
											<input type="hidden" id="{{$a->$value}}" value="{{$a->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
											<label for="{{$a->$value}}" style="color:Violet;" class="w-100 pl-4">{{$a->$dataOne}}</label>
										</div>
										@if($dataTwo)
										<div class="col-md-12">
											<label class="w-100">({{$a->$dataTwo}})</label>
										</div>
										@endif
									</div>
								</li>
									@if(isset($a->subcategory))
									@foreach($a->subcategory as $b)
									<li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
										<div class="row">
											<div class="col-md-12">
												<input type="hidden" id="{{$b->$value}}" value="{{$b->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
												<label for="{{$b->$value}}" class="w-100 text-secondary pl-5">{{$b->$dataOne}}</label>
											</div>
											@if($dataTwo)
											<div class="col-md-12">
												<label class="w-100">({{$b->$dataTwo}})</label>
											</div>
											@endif
										</div>
									</li>							
									@endforeach
									@endif	
								@endforeach
								@endif							
							@endforeach
							@endif						
						@endforeach
						@endif
					@endforeach
					@endif                       
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-md-6 ">
        <strong class="text-center text-bold">{{$labelTwo}}</strong><br>
        <input placeholder="Search Account Head" type="search" id="{{$searchTwo}}" class="w-100 p-1 my-3 form-control d-none" name="{{$searchTwo}}" />
        <div class="h-50 w-100">
            <ul class="connectedSortable p-2" id="sortable2" style="max-height:300px; height:300px; overflow-y: scroll; overflow-x: hidden;">
                @if($valueArray_opposite)
                @foreach($$valueArray_opposite as $valu)		
                <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="hidden" id="{{$valu->$value}}" value="{{$valu->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
                            <label for="{{$valu->$value}}" class="w-100 text-secondary pl-0">{{$valu->$dataOne}}</label>
                        </div>
                        @if($dataTwo)
                        <div class="col-md-3">
                            <label class="w-100">({{$valu->$dataTwo}})</label>
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