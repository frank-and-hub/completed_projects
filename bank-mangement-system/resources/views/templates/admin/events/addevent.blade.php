@extends('templates.admin.master')

@section('content')
<link rel="stylesheet" href={{url('/')}}/asset/css/eventstyle.css"/>

<div class="content">
    <div class="row">  
        <div class="col-lg-12">   

            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Add Holidays</h6>
                </div>
            </div>             
        </div>
    </div>

    <div class="row" > 
        <div class="col-lg-12" id="print_passbook">                
            <div class="card bg-white shadow">
                <div class="card-body">

                    <form name="month-form" class="filter-form" id="month-form" action="{!! route('admin.holidays.export') !!}" method="post">
                        <h6 class="card-title font-weight-semibold">Export Holidays</h6>
                        @csrf
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">State<sup>*</sup></label>
                            <div class="col-lg-3">
                                <select name="exportstateid" id="exportstateid" class="form-control" title="Please select something!">
                                    <option value="0">All</option>
                                @foreach( App\Models\States::pluck('name', 'id') as $key => $val )
                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                @endforeach
                                </select>
                            </div>

                            <label class="col-form-label col-lg-2">Months<sup>*</sup></label>
                            <div class="col-lg-3">
                                <select name="monthid" id="monthid" class="form-control" title="Please select something!">
                                    <option value="0">All</option>
                                    @foreach($months as $key => $month)
                                        <option value="{{ $key }}"  >{{ $month }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-right col-lg-2">
                                <input type="submit" name="submitform" value="Export" class="btn btn-primary export">
                            </div>
                        </div> 
                    </form>

                    <form name="cal-month-form" class="calendar-month-form" id="cal-month-form" action="{!! route('admin.saveholidaysetting') !!}" method="post">
                        <h6 class="card-title font-weight-semibold">Select Holiday Settings</h6>
                        @csrf
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">State<sup>*</sup></label>
                            <div class="col-lg-3">
                                <select name="monthstateid" id="monthstateid" class="form-control" title="Please select something!">
                                    <option value="0">All</option>
                                @foreach( App\Models\States::pluck('name', 'id') as $key => $val )
                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            @foreach($months as $key => $month)
                              <label class="col-form-label col-lg-2">{{ $month }}</label>
                              <div class="col-lg-4">
                                <input type="checkbox" name="month[{{ $key }}]" id="month" value="{{ $month }}" @if(in_array($month,$narray)) checked @endif class="form-control {{ $month }}">
                              </div>
                            @endforeach
                        </div> 
                        <div class="text-right">
                            <input type="submit" name="submitform" value="Submit" class="btn btn-primary createMonth">
                        </div>   
                    </form>
                    
                    <h6 class="card-title font-weight-semibold">Create Event</h6>
                    <label class="col-form-label col-lg-2">State<sup>*</sup></label>
                    <div class="col-lg-10">
                        <select name="stateid" id="stateid" class="form-control" title="Please select something!">
                            <option value="0">All</option>
                        @foreach( App\Models\States::pluck('name', 'id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option> 
                        @endforeach
                        </select>
                    </div>
                    <div id="app"></div>                  
                </div>
            </div> 
        </div> 
    </div>
</div>

@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.events.partials.eventscript')
@endsection