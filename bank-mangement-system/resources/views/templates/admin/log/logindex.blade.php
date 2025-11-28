@extends('templates.admin.master')

@section('content')

<div class="content"> 
    <div class="row">  
    <div class="col-lg-12" > 
          @if (session('success')) 
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              
              <span class="alert-text"><strong>Success!</strong> {{ session('success') }} </span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          @endif
        </div> 
		<?php
		//dd($accountbrabnch);
	     
		?>
           <div class="col-md-12">
            <div class="card"> 
                <div class="card-body">
        
								 
								
                    <div class="row">
                        <div class="col-lg-12"> 
                            <div id="timeline-container">
                              <div class="inner-container">
							  @if(count($accountbrabnch)>0)
							  @foreach($accountbrabnch as  $val)
                                <ul class="timeline">
                                 
                                  <li class="timeline-item" data-date="{{date('d- M- Y H:i:s', strtotime($val->created_at))}}">
                                    <div class="main-box">
                                     <h3>{{getBranchDetail($val->old_branch_id)->name}} To {{getBranchDetail($val->new_branch_id)->name}}</h3>
                                      <p>Branch Transferred By ({{getAdminUsername($val->created_by_id)}})</p>
                                      
                                    </div>
                                  </li>
                               </ul>
                                @endforeach
								@else
									<h6 style="text-align:center;">No Logs Available</h6>
								@endif
								
                              </div>
                            </div>                            
                        </div>                    
                    </div>                    
                </div>                    
            </div>                    
        </div>
    </div>
</div>

@stop