@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                {{ Form::open(['url' => '#', 'method' => 'POST', 'name' => 'loancommissionform', 'id' => 'loancommissionform']) }}
                <div class="card">
                    <div class="card-body">
                        <div class="form-group row col-sm-12 col-lg-12">
                            <div class="col-sm-6 col-lg-4">
                                {{ Form::hidden('plan_id', $plan_type_id) }}
                                <label for="tenure_type">Tenure Type</label>
                                <select class="form-control" id="tenure_type" name="tenure_type">
                                    <option value="">Please select tenure type</option>
                                    <option value="0">Year</option>
                                    <option value="1">Month</option>
                                </select>
                            </div>

                            <div class="col-sm-6 col-lg-4">
                                {{ Form::label('tenure', 'Tenure') }}
                                {{ Form::text('tenure', $data->tenure, ['placeholder' => 'Enter your tenure', 'id' => 'tenure', 'class' => 'form-control']) }}
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                {{ Form::label('effect_from', 'Effective From') }}
                                {{Form::text('effect_from',null,['id'=>'effect_from','class'=>'form-control effect_from','readonly'=>true])}}
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                {{ Form::label('tenure_to', 'Tenure To') }}
                                {{Form::text('tenure_to',null,['id'=>'tenure_to','class'=>'form-control','required'=>true])}}
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                {{ Form::label('tenure_from', 'Tenure From') }}
                                {{Form::text('tenure_from',null,['id'=>'tenure_from','class'=>'form-control','required'=>true])}}
                            </div>
                        </div>
                        <div class="text-right">
                            {{Form::hidden('plan_id',$data->plan_id,['id'=>'plan_id','class'=>'plan_id'])}}
                            {{Form::hidden('created_at','',['id'=>'created_at','class'=>'created_at'])}}
                            {{ Form::button('NEXT', ['class' => 'btn bg-dark', 'id' => 'next']) }}
                        </div>
                    </div>
                </div>
                <div class="card d-none" id="collectorPercentageForm">
                    <div class="card-body">
                        <div class="form-group row col-sm-12 col-lg-12">
                            <div class="col-sm-6 col-lg-12">
                                <table class="col-12">
                                    <tr>
                                        <th>S. No.</th>
                                        <th>Carder</th>
                                        <th>Collector Per.</th>
                                        <th>Associate Per.</th>
                                    </tr>
                                    @php $s = 1; @endphp
                                    @foreach ($Carder as $value)
                                        <tr>
                                            <td>
                                                {{ $s }}
                                            </td>
                                            <td>{{ $value->name }}
                                                {{ Form::hidden('carder[' . $value->id . ']', $value->id, ['class' => 'form-control']) }}
                                            </td>
                                            <td> 
                                                {{ Form::text('collector[' . $value->id . ']', old('collector[' . $value->id . ']')??'', ['class' => 'form-control','required','placeholder'=>'0.000']) }}
                                            </td>
                                            <td> 
                                                {{ Form::text('associate[' . $value->id . ']', old('associate[' . $value->id . ']')??'', ['class' => 'form-control','required','placeholder'=>'0.000']) }}
                                            </td>
                                        </tr>
                                        @php $s++; @endphp
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="text-right">
                            {{ Form::submit('SUBMIT', ['class' => 'btn bg-dark', 'id' => 'submit']) }}
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    @include('templates.admin.py-scheme.partials.scriptcommisssion')
@stop
