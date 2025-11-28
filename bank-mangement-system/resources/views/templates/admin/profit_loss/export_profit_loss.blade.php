<?php
  if(isset($data['branch_id']))
  {
    $branchId = $data['branch_id'];
  }
  else{
    $branchId = '';
  }

?>
  <table id="direct_income" class="table  datatable-show-all">
                        @foreach ($data['incomeHead'] as $val) 
                          <thead >
                            <tr>
						
                              <th>{{$val->sub_head}}</th>

                              <th></th>

                              @php 
                                $headAmount = getHeadClosingNew($val->head_id,$data['date']);
                              @endphp
                              <th>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($val->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</th>

                            </tr>  

                          </thead> 
<?   $incomeHeadThree = 0; ?>
                          <?php   $incomeHeadThree = getHead($val->head_id,3); ?>

                          @if(count($incomeHeadThree)>0)

                            @foreach ($incomeHeadThree as $valincome)  
                             <?  $CountincomeHeadThree = getHead($valincome->head_id,4); ?>
                              
                                
                                
                              <tbody>
                                @if($valincome->head_id != 32 )
                                <tr>

                                   @if(count($CountincomeHeadThree) > 0 )
                                    <th>{{strtoupper(($valincome->sub_head))}}</th>

                                  @else
                                  <td>{{strtoupper(($valincome->sub_head))}}</td>
                                  @endif
                              
                                  
                                  @if(!is_null(Auth::user()->branch_ids))
                                  
                                  <td>{{ number_format((float)headTotalNew($valincome->head_id,$data['date'],$data['to_date'],$branchId), 2, '.', '')}}</td>
                                  <td></td>
                                 
                                  @else
                                  @php 
                                    $headAmount = getHeadClosingNew($valincome->head_id,$data['date']);
                                  @endphp
                                  <td>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($valincome->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</td>
                              
                                <td></td>
                                  @endif

                                </tr>    
                                @endif
                              </tbody> 

                            @endforeach

                          @endif

                        @endforeach  

                      </table>

                          <table>



                            @foreach ($data['expenseHead'] as $val) 

                            <thead >

                              <tr >

                                <th>{{$val->sub_head}}</th>

                                <th></th>
                                @php 
                                    $headAmount = getHeadClosingNew($val->head_id,$data['date']);
                                @endphp
                                @if($val->head_id ==86)

                                 <th>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($val->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</th>
                                
                                @else
                                <th>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($val->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</th>
                                @endif

                              </tr>  

                            </thead> 

                            <?  $expenseHeadThree = getHead($val->head_id,3); ?>

                            @if(count($expenseHeadThree)>0)

                              @foreach ($expenseHeadThree as  $index => $valexpense)  
                               <?  $CountexpenseHeadThree = getHead($valexpense->head_id,4);  ?> 

                                <tbody>
                                
                                
                                  <tr>
                                  @if(count($CountexpenseHeadThree) > 0 )
                                    <th>{{strtoupper(($valexpense->sub_head))}}  </th>
                                  @elseif($valexpense->head_id ==38 || $valexpense->head_id ==39 || $valexpense->head_id ==41 || $valexpense->head_id ==42 || $valexpense->head_id ==43 || $valexpense->head_id ==44 || $valexpense->head_id ==45 ||  $valexpense->head_id ==47 || $valexpense->head_id ==48 || $valexpense->head_id ==49 || $valexpense->head_id ==50 || $valexpense->head_id ==51 || $valexpense->head_id ==52 || $valexpense->head_id == 111 )
                                      <th>{{strtoupper(($valexpense->sub_head))}}</th> 
                                  @elseif($valexpense->head_id ==87)
                                      <th>{{$valexpense->sub_head}}</th>    
                                  @elseif($valexpense->head_id ==40)
                                      <th>{{strtoupper($valexpense->sub_head)}}</th> 
                                  @elseif($valexpense->head_id ==87)
                                      <th>{{strtoupper($valexpense->sub_head)}}</th>          
                                   @elseif($valexpense->head_id ==37)
                                      <th>{{strtoupper(($valexpense->sub_head))}}  </th> 
                                    @elseif($valexpense->head_id ==46)
                                      <th>{{strtoupper(($valexpense->sub_head))}}  </th>  
                                    @elseif($valexpense->head_id ==97)
                                      <th>{{strtoupper($valexpense->sub_head)}}</th> 
                                    @elseif($valexpense->head_id ==88)
                                      <th>{{strtoupper($valexpense->sub_head)}}</th>  
                                  @else
                                  <td>{{strtoupper(($valexpense->sub_head))}}</td>
                                  @endif
                                  @php 
                                      $headAmount = getHeadClosingNew($valexpense->head_id,$data['date']);
                                  @endphp
                                   @if(!is_null(Auth::user()->branch_ids))
                                  <td>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($valexpense->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</td>
                                  <td></td>
                               
                                  @else
                                  <td>{{ ($headAmount != null ) ? number_format((float)$headAmount, 2, '.', '') : number_format((float)headTotalNew($valexpense->head_id,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</td>
                                  <td></td>
                                
                                  @endif
                               
                                     
                                 </tr>
                                </tbody> 
                                
                              @endforeach

                            @endif

                          @endforeach    

                        </table>

                         <table>

                              <thead>

                                 <tr class="d-flex justify-content-between">

                                  <th>Total Income</th>

                                  <th></th>

                                  @php
                                      $headAmount = getHeadClosingNew(3,$data['date']);
                                  @endphp
                                  <th>&#X20B9;  {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew(3,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</th>

                                 </tr>  

                              </thead> 

                          </table>

                         <table>

                              <thead>

                                 <tr class="d-flex justify-content-between">

                                  <th>Total Expenses</th>

                                  <th></th>

                                  @php
                                      $headAmount = getHeadClosingNew(4,$data['date']);
                                  @endphp

                                  <th>&#X20B9;  {{ ( $headAmount != null ) ? number_format((float)$headAmount,2,'.', '') : number_format((float)headTotalNew(4,$data['date'],$data['to_date'],$branchId,1), 2, '.', '')}}</th>

                                 </tr>  

                              </thead> 

                          </table>