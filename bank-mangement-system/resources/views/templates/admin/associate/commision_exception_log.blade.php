@extends('templates.admin.master')

@section('content')

<div class="content"> 
  <div class="row"> 
    @if ($errors->any())
    <div class="col-md-12">
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif
    @if($detail)
    <div class="col-md-12">
            <div class="card"> 
              <div class="card-body">
                    <div class="row">
                    <div class="col-md-12">
                    <h6 class="card-title font-weight-semibold text-center">{{$detail->first_name }} {{$detail->last_name }} {{'('. $detail->associate_no.')'}}</h6> 
                                </div>                      
                                         
                        <div class="col-lg-12"> 
                          <div id="timeline-container">
                            <div class="inner-container">
                              @if($log)
                                 @foreach($log as $index => $record)
                                 <ul class="timeline">                                 
                                   <li class="timeline-item" data-date="{{date('d- M-Y H:i:s', strtotime($record->created_at))}}">
                                    <div class="main-box">
                                      <p>{{$record->description}}</p>                                      
                                    </div>
                                  </li>                      
                                </ul>
                                 @endforeach
                              @endif
                              </div>
                            </div>                            
                        </div>                    
                    </div>                    
                </div>                    
            </div>                    
        </div>
    @endif


    
    
</div>

@stop 