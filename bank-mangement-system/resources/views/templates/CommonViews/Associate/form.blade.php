<div class="card-body">
    {!! Form::open(['url' => null,'class'=>'row','id' => 'filter']) !!}
    <div class="col-md-4">
        <div class="col-md-12">
            {!! Form::label('start_date', 'Start Date', ['class' => 'col-form-label']) !!}
        </div>
        <div class="col-md-12">
            {!! Form::text('start_date', null, ['class' => 'form-control','id' => 'start_date', 'readonly' => 'readonly']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="col-md-12">
            {!! Form::label('end_date', 'End Date', ['class' => 'col-form-label']) !!}
        </div>
        <div class="col-md-12">
            {!! Form::text('end_date', null, ['class' => 'form-control','id' => 'end_date', 'readonly' => 'readonly']) !!}
        </div>
    </div> 
    {!!$companyView!!}
    <div class="col-md-4">
        <div class="col-md-12">
            {!! Form::label('associate', 'Associate Code', ['class' => 'col-form-label']) !!}
        </div>
        <div class="col-md-12">
            {!! Form::text('associate', null, ['class' => 'form-control','id' => 'associate']) !!}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-lg-12 text-right">
                {!! Form::button('Submit', ['class' => $btn, 'onclick' => 'searchForm()']) !!}
                {!! Form::button('Reset', ['class' => 'btn btn-gray legitRipple', 'onclick' => 'resetForm()']) !!}
                {!! Form::hidden('is_search', 'no', ['id' => 'is_search']) !!}
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</div>