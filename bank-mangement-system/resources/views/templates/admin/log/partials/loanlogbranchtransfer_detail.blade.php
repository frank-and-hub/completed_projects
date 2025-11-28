<?php

//print_r($loanData);
?>
    
 
                        <div class="col-lg-12">
							 
							
                            <div id="timeline-container">
                              <div class="inner-container">
							  @if(count($loanData)>0)
							 @foreach($loanData as  $val)
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
                     
    
	 
