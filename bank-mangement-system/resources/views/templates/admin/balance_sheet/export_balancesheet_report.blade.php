<?php

  $subHeadsIDS = App\Models\AccountHeads::select('id','head_id','child_head')->where('head_id',1)->where('status',0)->first();
  $Assetids =($subHeadsIDS->child_head);

 ?>


                   <table id="capital" class="table  datatable-show-all">
                      @foreach ($libalityHead as $lib)
                        <?php
                          $countHead = getHead($lib->head_id,3);
                         ?>
                        <thead >
                         <!-- main Head  -->
                            <tr>
                            
                                <th class="text_upper"> {{$lib->sub_head}}  </th>


                            
                             @php $headAmount = getHeadClosingNew($lib->head_id,$startDate
                                  );@endphp
                                <th style="padding-right:150px"></th>
                                @if($lib->head_id == 6)
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)($profit_loss), 2, '.', '') }}</th>
                                @else
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew($lib->head_id,$startDate,
                                          $endDate,$branch_id), 2, '.', '')}}</th>
                                @endif


                          </tr>




                          <!-- Sub Head -->
                        </thead>
                         <?php  $libalityHeadThree = getHead($lib->head_id,3);?>
                        @if(count($libalityHeadThree)>0)
                          @foreach ($libalityHeadThree as $valLib)
                           <?php  $countChildLia = getHead($valLib->head_id,4);?>
                            <tbody>
                              <tr>
                             
                                   <td class="text_upper">{{$valLib->sub_head}}</td>

                              


                                @php $headAmount = getHeadClosingNew($valLib->head_id,$startDate
                                  );@endphp
                                <th style="padding-right:150px"></th>
                                @if($valLib->head_id == 17)
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)($profit_loss), 2, '.', '') }}</th>
                                @else
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew($valLib->head_id,$startDate,
                                          $endDate,$branch_id), 2, '.', '')}}</th>
                                @endif


                              </tr>
                            </tbody>
                          @endforeach
                        @endif
                      @endforeach

                    </table>


                      <table id="current_asset" class="table  datatable-show-all">
                        @foreach ($assestHead as $assest)
                          <?php
                            $assetCount = getHead($assest->head_id,3);

                          ?>
                        <thead >
                          <tr >
                           
                              <th class="text_upper">{{$assest->sub_head}}</th>

                        

                            @php $headAmount = getHeadClosingNew($assest->head_id,$startDate
                                  );@endphp
                                <th style="padding-right:150px"></th>
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew($assest->head_id,$startDate,
                                          $endDate,$branch_id), 2, '.', '')}}</th>
                          </tr>
                        </thead>
                        <?php  $assestHeadThree = getHead($assest->head_id,3);?>
                        @if(count($assestHeadThree)>0)
                          @foreach ($assestHeadThree as $val1)
                             <?php  $countChild = getHead($val1->head_id,4);?>

                            <tbody>
                             <tr @if($val1->status==1) class="child_inactive" @endif style="background-color: #dff9fb;">

                           

                                  <td class="text_upper">{{$val1->sub_head}}</td>



                                @php $headAmount = getHeadClosingNew($val1->head_id,$startDate
                                  );@endphp
                                <th style="padding-right:150px"></th>
                                <th class="test" >&#X20B9;{{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew($val1->head_id,$startDate,
                                          $endDate,$branch_id), 2, '.', '')}}</th>
                              </tr>
                            </tbody>
                          @endforeach
                        @endif
                      @endforeach
                      </table>


                        <table id="total_liability" class="table datatable-show-all">
                            <thead>
                               <tr>
                                <th class="text_upper">Total Liabilities</th>
                                <th></th>
                                @php $headAmount = getHeadClosingNew(1,$startDate
                                  );@endphp
                                  <td>&#X20B9; {{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew(1,$startDate,
                                          $endDate,$branch_id), 2, '.', '') + $profit_loss}}</td>
                               </tr>
                            </thead>
                        </table>

                        <table id="total_assets" class="table datatable-show-all">
                          <thead>
                            <tr>
                            <th class="text_upper">Total Assets[E]</th>
                            <th></th>
                            @php $headAmount = getHeadClosingNew(2,$startDate
                                  );@endphp
                                  <td>&#X20B9; {{ ($headAmount  ) ? number_format((float)$headAmount, 2, '.', '') :  number_format((float)headTotalNew(2,$startDate,
                                          $endDate,$branch_id), 2, '.', '')}}</td>


                           </tr>
                          </thead>
                        </table>





