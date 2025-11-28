@forelse($data->subcategory as $val)
    <li class="ui-state-default" style="list-style-type: none; cursor: -webkit-grab; cursor: grab">
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" id="{{$val->$value}}" value="{{$val->$value}}" name="{{$valueArray}}[]" class="w-100 disable" />
                <label for="{{$val->$value}}" class="w-100 text-{{$color[$a]}} pl-5">{{$val->$dataOne}}</label>
            </div>
            @if($dataTwo)
            <div class="col-md-12">
                <label class="w-100">({{$val->$dataTwo}})</label>
            </div>
            @endif
        </div>
    </li>
    @include('templates.admin.company.heads',['data' => $val,'dataOne' => 'dataOne','dataTwo' => 'dataTwo','value' => 'value','a'=>$a+1,'color' => $color])
@empty
@endforelse