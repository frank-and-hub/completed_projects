<div class="card">
    <div class="card-header">{{ __('Email Filter') }}</div>
    <div class="card-body">
        {{Form::open(['url' => '#', 'methord' => 'POST', 'id' => 'filter', 'name' => 'filter', 'enctype' => 'multipart/form-data'])}}
        <div class="row mb-3">
            <label for="inputDate" class="col-sm-2 col-form-label">Date</label>
            <div class="col-sm-10">
                {{Form::date('date', \Carbon\Carbon::now(), ['class' => 'form-control', 'id' => 'date'])}}
            </div>
        </div>
        @if(Auth::user()->role == '1')
            {{Form::hidden('users', 'all', ['id' => 'users', 'class' => 'form-control'])}}
        @else
            {{Form::hidden('users', auth()->user()->id, ['id' => 'users', 'class' => 'form-control'])}}
        @endif
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label"></label>
            <div class="col-sm-10">
                {{Form::submit('search', ['id' => 'searchSubmit', 'class' => 'btn btn-primary'])}}
            </div>
        </div>
        {{Form::close()}}
    </div>
</div>
</div>
