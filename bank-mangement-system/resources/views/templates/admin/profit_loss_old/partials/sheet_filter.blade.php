<?php

$income = array(3);
 $subHeadsIDS = App\Models\AccountHeads::where('head_id',3)->where('status',0)->pluck('head_id')->toArray();
  if( count($subHeadsIDS) > 0 ){
    $head_ids=  array_merge($income,$subHeadsIDS);
      $return_array= get_change_sub_account_head($income,$subHeadsIDS,true);
    }
    foreach ($return_array as $key => $value) {
     $ids[] = $value;
    }
$expense = array(4);
 $subHeadsIDS = App\Models\AccountHeads::where('head_id',4)->where('status',0)->pluck('head_id')->toArray();
  if( count($subHeadsIDS) > 0 ){
    $head_ids=  array_merge($expense,$subHeadsIDS);
      $return_array= get_change_sub_account_head($expense,$subHeadsIDS,true);
    }
    foreach ($return_array as $key => $value) {
     $expenseIds[] = $value;
    } 
     if(isset($branch_id))
   {
    $branch_id = $branch_id;
   }
   else{
    $branch_id ='';
   }   
?>
  <div class="row">

                <div class="col-md-6">

                  <div class="d-flex justify-content-between mx-2 mb-2">

                    <span class="font-weight-bold">Income</span>

                    <span class="font-weight-bold">Amount</span>

                  </div>

                  <div class="card">

                    <div class="">

                    <table id="direct_income" class="table  datatable-show-all">

                        @foreach ($incomeHead as $val) 

                          <thead >

                            <tr >
                
                              <th><a href="{{ URL::to('admin/profit-loss/detail/'.$val->head_id.'/'.$val->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{$val->sub_head}} </a> </th>

                              <th></th>
                              @php
                                  $headAmount = getHeadClosingNew($val->head_id,$date);
                              @endphp
                              <th>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew($val->head_id,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</th>

                            </tr>  

                          </thead> 

                          @php  $incomeHeadThree = getHead($val->head_id,3); @endphp

                          @if(count($incomeHeadThree)>0)

                              @foreach ($incomeHeadThree as $valincome)  
                               <?php  $countExpense = getHead($valincome->head_id,4);
                              
                               ?>



                              <tbody>

                                <tr style="background-color: #dff9fb;">
                                  @if(count($countExpense)>0 )
                                   <th><a href="{{ URL::to('admin/profit-loss/detail/'.$valincome->head_id.'/'.$valincome->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper($valincome->sub_head)}} </a> </th>
                                  @else
                                   
                                    <th><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$valincome->head_id.'/'.$valincome->labels.'?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{$valincome->sub_head}}</a></th>
                                   
                                 @endif
                                   <td style="padding-right:150px"></td>
                                   @php
                                  $headAmount = getHeadClosingNew($val->head_id,$date);
                              @endphp
                                  <td>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew($valincome->head_id,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</td>
                                
                                  
                                </tr>    

                              </tbody> 

                            @endforeach


                          @endif

                        @endforeach  

                      </table>

                    </div>

                  </div>

                </div> 

                  <div class="col-md-6">

                    <div class="d-flex justify-content-between mb-2">

                      <span class="font-weight-bold">Expenses</span>

                      <span class="font-weight-bold">Amount</span>

                    </div>

                    <div class="card">

                      <div class="">

                         <table id="current_asset" class="table  datatable-show-all">



                            @foreach ($expenseHead as $val) 
                            <?php  $countExpense = getHead($val->head_id,4);?>

                            <thead >

                              <tr >
                               
                                  <th><a href="{{ URL::to('admin/profit-loss/detail/'.$val->head_id.'/3/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{$val->sub_head}} </a> </th>
                                
                                <th></th>
                                @php
                                  $headAmount = getHeadClosingNew(4,$date);
                              @endphp
                                <th>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew($val->head_id,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</th>
                                

                              </tr>  

                            </thead> 

                            @php  $expenseHeadThree = getHead($val->head_id,3); @endphp

                            @if(count($expenseHeadThree)>0)

                              @foreach ($expenseHeadThree as  $index => $valexpense)  
                                @php  $CountexpenseHeadThree = getHead($valexpense->head_id,4);  @endphp 

                                <tbody>
                                
                                 
                                  <tr style="background-color: #dff9fb;">
                                  @if(count($CountexpenseHeadThree) > 0 )
                                    <th><a href="{{ URL::to('admin/profit-loss/detail/'.$valexpense->head_id.'/'.$valexpense->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper(($valexpense->sub_head))}} </a> </th>
                                  @elseif(in_array(86,$expenseIds) && in_array(40,$expenseIds) == false  && in_array(53,$expenseIds) == false  && in_array(87,$expenseIds) == false  && in_array(88,$expenseIds) == false)
                                      <th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$valexpense->head_id.'/'.$valexpense->labels.'?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper(($valexpense->sub_head))}}</a></th> 
                                  @elseif($valexpense->head_id ==87)
                                      <th><a href="{{ URL::to('admin/profit-loss/detailed/commission/'.'?head='.$valexpense->head_id.'&date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{$valexpense->sub_head}}</a></th>    
                                  @elseif($valexpense->head_id ==40)
                                      <th><a href="{{ URL::to('admin/profit-loss/detailed/depreciation/'.'?head='.$valexpense->head_id.'&date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper($valexpense->sub_head)}}</a></th> 
                                   
                                  
                                    @elseif($valexpense->head_id ==46)
                                      <th><a href="{{ URL::to('admin/profit-loss/detail/'.$valexpense->head_id.'/'.$valexpense->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper(($valexpense->sub_head))}} </a> </th>  
                                    @elseif($valexpense->head_id ==97)
                                      <th><a href="{{ URL::to('admin/profit-loss/detailed/interest_on_loan_taken/'.$valexpense->head_id.'/'.$valexpense->labels.'?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper($valexpense->sub_head)}}</a></th> 
                                    @elseif($valexpense->head_id ==88)
                                      <th><a href="{{ URL::to('admin/profit-loss/detailed/fuel_charge/'.'?head='.$valexpense->head_id.'&date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper($valexpense->sub_head)}}</a></th>  
                                  @else
                                  <td><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$valexpense->head_id.'/'.$valexpense->labels.'?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">{{strtoupper(($valexpense->sub_head))}}</a></td>
                                  @endif
                                   @if(!is_null(Auth::user()->branch_ids))
                                  <td>&#X20B9; {{ number_format((float)headTotalNew($valexpense->head_id,$start_date,$to_date,$branch_id), 2, '.', '')}}</td>
                                  <td style="padding-right:150px"></td>
                                
                                  @else
                                  <td style="padding-right:150px"></td>
                                  @php
                                      $headAmount = getHeadClosingNew(3,$start_date);
                                  @endphp
                                  <td>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew($valexpense->head_id,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</td>
                                                             
                                  @endif 
                                 
                                </tbody> 
                                
                              @endforeach

                            @endif

                          @endforeach    

                        </table>

                      </div>

                    </div>

                  </div> 

              </div> 

              <div class="row d-flex align-items-center">

                  <div class="col-md-6">

                      <div class="card">

                        <div class="">

                          <table id="total_liability" class="table datatable-show-all">

                              <thead>

                                 <tr class="d-flex justify-content-between">

                                  <th>Total Income</th>

                                  <th></th>
                                  @php
                                      $headAmount = getHeadClosingNew(3,$start_date);
                                  @endphp
                              <th>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew(3,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</th>

                                 </tr>  

                              </thead> 

                          </table>

                        </div>

                      </div>

                   </div> 

                   <div class="col-md-6 ">

                      <div class="card">

                        <div class="">

                          <table id="total_assets" class="table datatable-show-all">

                            <thead>

                              <tr class="d-flex justify-content-between">

                              <th>Total Expenses</th>

                              <th></th>
                              @php
                                      $headAmount = getHeadClosingNew(4,$date);
                                  @endphp
                              <th>&#X20B9; {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew(4,$start_date,$to_date,$branch_id,1), 2, '.', '')}}</th>

                             </tr>  

                            </thead> 

                          </table>

                        </div>

                      </div>

                    </div> 

              </div>

            </div>


     
