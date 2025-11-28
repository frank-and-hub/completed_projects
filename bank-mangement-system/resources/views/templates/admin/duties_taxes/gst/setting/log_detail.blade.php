@extends('templates.admin.master')
@section('content')
<div class=""> 
    <div class="row"> 
          <div class="col-md-12">
              <div class="card">
                  <div class="card-header header-elements-inline">
                          <h6 class="card-title font-weight-semibold"></h6>
                      </div>
                  <div class="card-body">          
                      <div class="row">
                          <div class="col-lg-12"> 
                              <div id="timeline-container">
                                <div class="inner-container">
                                   @foreach($log as $index => $record)
                                    <ul class="timeline">
                                      <li class="timeline-item" data-date="{{date('d- M- Y', strtotime($record->created_at))}}">
                                        <div class="main-box">
                                          <h3 class="logs" data-id="{{$record->id}}" data-url="{{route('admin.duties_taxes.gst.setting.listing')}}" >{{ ucwords($record->title) }}</h3>
                                          <p>Created By : {{getAdminUsername($record->created_by_id)}}</p>
                                          <p>Created At : Admin</p>
                                          <p>{{ ucfirst($record->description) }}</p>
                                          <p>Created At : {{ date('d-m-Y H:i:s',strtotime($record->created_at)) }}</p>
                                        </div>
                                      </li>
                                    </ul>
                                   @endforeach
                                </div>
                              </div>
                          </div>
                      </div>
                  </div>                    
              </div>                    
          </div>    
  </div>
  @include('templates.admin.duties_taxes/model')
@stop