
<style type="text/css">
    #expense{
            height: 42rem;
            overflow-x: hidden;
            overflow-y: auto;
            text-align:justify;
    }
</style>

           
        
            <div class="row">

         <div class="mb-4">
                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>

                                    <th></th>
                                    <th colspan="2" scope="colgroup">NCC(NEW BUSINESS)</th> 
                                    <th  colspan="2" scope="colgroup">RENEWAL</th>
                                    <th  colspan="2" scope="colgroup">TOTAL CASH COLLECTION</th>
                                    <th>NEW FW JOINING </th>
                                    <th  colspan="3" scope="colgroup">NEW A/C THROUGH NEW WORKER</th>                                    
                                    <th>TOTAL INVOLVE FW</th>
                                    <th>TOTAL PAYMENTS</th> 
                                         
                                </tr>
                            </thead>  
                            <tbody>
                                <tr>
                                    <td colspan="1">MODE</td>
                                    <td>NO. OF A/C</td>
                                    <td>DENO.</td>
                                    <td>NO. OF A/C</td>
                                    <td>AMT.</td>
                                    <td>NO. OF A/C</td>
                                    <td>AMT.</td>
                                    <td></td>
                                    <td>MODE</td>
                                     <td>NO. OF A/C</td>
                                    <td>AMT.</td>
                                    <td></td>
                                    <td></td>
                                   
                                    
                                </tr>    
                                <tr>
                                    <td>DAILY</td>
                                    <td>{{$data['current_daily_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_daily_new_deno_ac'], 2, '.', '')}}</td>
                                    <td>{{$data['current_daily_renew_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_daily_renew_amount_sum'], 2, '.', '')}}</td>
                                    <td>{{$data['total_case_current_daily_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['total_case_current_daily_new_deno_ac'], 2, '.', '')}}</td>
                                    <td>{{$data['totalFWbranchwise']}}</td>
                                    <td>DAILY</td>
                                      <td>{{$data['fw_daily_count']}}</td>
                                    <td>&#x20B9;{{ number_format((float)$data['fw_daily'], 2, '.', '')}}</td>
                                    <td>{{$data['totalFW']}}</td>
                                    <td>&#x20B9;{{ $total_final_payments_Amounts = number_format((float)$data['total_final_payments'], 2, '.', '')}}</td>
                                </tr>
                                <tr>    
                                    <td>MONTHLY</td>
                                   <td>{{$data['current_monthly_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_monthly_deno_sum'], 2, '.', '')}}</td>
                                    <td>{{$data['current_monthly_renew_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_monthly_renew_amount_sum'], 2, '.', '')}}</td>
                                    <td>{{$data['total_case_current_monthly_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['total_case_current_monthly_deno_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td>MONTHLY</td>
                                    <td>{{$data['fw_month_count']}}</td>
                                    <td>&#x20B9;{{ number_format((float)$data['fw_month'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>FD</td>
                                     <td>{{$data['current_fd_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_fd_deno_sum'], 2, '.', '')}}</td>
                                    <td>{{$data['current_fd_renew_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['current_fd_renew'], 2, '.', '')}}</td>
                                    <td>{{$data['total_case_current_fd_new_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['total_case_current_fd_deno_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td>FD</td>
                                      <td>{{$data['fw_fd_count']}}</td>
                                    <td>&#x20B9;{{ number_format((float)$data['fw_fd'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>LOAN RECOVERY</td>
                                     <td>N/A</td>
                                    <td>N/A</td>
                                    <td>{{$data['total_loan_recovery_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['total_loan_recovery_amt'], 2, '.', '')}}</td>
                                    <td>{{$data['total_loan_recovery_ac_cashmode']}}</td>
                                    <td>&#x20B9;{{$data['total_loan_recovery_amt_cashmode']}}</td>
                                    <td></td>
                                    <td>LOAN RECOVERY</td>
                                     <td>{{$data['fw_loan_recovery_count']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['fw_loan_recovery'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>FILE CHARGE </td>
                                   
                                    <td>{{$data['file_chrg_total_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['file_chrg_amount_total'], 2, '.', '')}}</td>
                                     <td>N/A</td>
                                    <td>N/A</td>
                                    <td>{{$data['file_chrg_total_ac_case_mode']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['file_chrg_amount_total_cash_mode'], 2, '.', '')}}</td> 
                                    <td></td>
                                    <td>FILE CHARGE</td> 
                                    <td>{{$data['fw_filecharge']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['fw_filecharge_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>OTHER</td>
                                    <td>{{$data['Other_ncc_total']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['Other_ncc_sum'], 2, '.', '')}}</td>
                                     <td>N/A</td>
                                    <td>N/A</td>
                                    <td>{{$data['Other_ncc_total']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['Other_ncc_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td>OTHER</td>
                                      <td>{{$data['fw_other']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['fw_other_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>TOTAL</td>
                                   <td>{{($data['current_daily_new_ac']) + ($data['current_monthly_new_ac']) + ($data['current_fd_new_ac']) + ($data['file_chrg_total_ac']) + ($data['Other_ncc_total']) }}</td>
                                    <td>&#x20B9;{{ $totalNEWBusinessAmount = number_format((float)($data['current_daily_new_deno_ac']) + ($data['current_monthly_deno_sum']) + ($data['current_fd_deno_sum']) + ($data['file_chrg_amount_total']) , 2, '.', '') + (number_format((float)$data['Other_ncc_sum'], 2, '.', '')) }}</td>
                                     <td>{{($data['current_daily_renew_ac']) + ($data['current_monthly_renew_ac']) + ($data['current_fd_renew_ac']) + ($data['total_loan_recovery_ac'])  }}</td>
                                     <td>&#x20B9;{{ $totalNewRenewalAmount = number_format((float)($data['current_daily_renew_amount_sum']) + ($data['current_monthly_renew_amount_sum']) + ($data['current_fd_renew']) + ($data['total_loan_recovery_amt']) , 2, '.', '') }}</td>
                                     <td>{{($data['total_case_current_daily_new_ac']) + ($data['total_case_current_monthly_new_ac']) + ($data['total_case_current_fd_new_ac']) + ($data['total_loan_recovery_ac_cashmode']) + ($data['file_chrg_total_ac_case_mode']) + ($data['Other_ncc_total']) }}</td>
                                    <td>&#x20B9;{{ $totalCaseInHandAmount = (number_format((float)$data['total_case_current_daily_new_deno_ac'], 2, '.', '')) + (number_format((float)$data['total_case_current_monthly_deno_sum'], 2, '.', '')) + (number_format((float)$data['total_case_current_fd_deno_sum'], 2, '.', '')) + (number_format((float)$data['total_loan_recovery_amt_cashmode'], 2, '.', '')) + (number_format((float)$data['file_chrg_amount_total_cash_mode'], 2, '.', '')) + (number_format((float)$data['Other_ncc_sum'], 2, '.', '')) }}</td>
                                    <td></td>
                                    <!-- <td>{{($data['total_cash_account'])}}</td>
                                    <td>{{($data['total_cash_amount'])}}</td>
                                   <td>{{($data['totalFWbranchwise'])}}</td> -->
                                    <td>TOTAL</td>
                                     <td>{{$data['fw_total']}}</td>
                                    <td>&#x20B9;{{ number_format((float)$data['fw_total_sum'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    
                                </tr>
                            </tbody>
                                    
                        </table>
                    </div>

                </div>
            </div>
            <div class="row">
             <div class="col-lg-8">
                <div class="card">
                     <h3 class="text-center"> SCHEME WISE DETAIL OF OPENDED A\C  AND DENOMINATION</h3>
                    <div class="table-responsive">
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="3" scope="colgroup">DAILY/MONTHLY N.I.</th>
                                    <th colspan="3" scope="colgroup">F.D. N.I.</th>
                                    <th colspan="3" scope="colgroup">LOAN</th>
                                </tr>
                            </thead>  
                            <tbody>
                                <tr>
                                    <td colspan="1">PERIOD</td>
                                    <td>NO. A/C</td>
                                    <td>AMT.</td>
                                   <td colspan="1">PERIOD</td>
                                    <td>NO. A/C</td>
                                    <td>AMT.</td>
                                    <td colspan="1">PERIOD</td>
                                    <td>NO. A/C</td>
                                    <td>AMT.</td>
                                    
                                </tr>    
                                <tr>
                                    <td>12 DAILY</td>
                                    <td>{{$data['current_daily_new_ac_tenure12']}}</td>
                                    <td>&#x20B9;{{$data['current_daily_new_ac_amt_sum_tenure12']}}</td>
                                    <td>12 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure12']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure12'], 2, '.', '')}}</td>
                                    <td>PERSONAL LOAN</td>
                                    <td>{{$data['personal_loan_total_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['personal_loan_total_amt'], 2, '.', '')}}</td>
                                </tr>
                                <tr>    
                                     <td>24 DAILY</td>
                                    

                                    <td>{{$data['current_daily_new_ac_tenure24']}}</td>
                                    <td>&#x20B9;{{$data['current_daily_new_ac_amt_sum_tenure24']}}</td>
                                    <td>18 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure18']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure18'], 2, '.', '')}}</td>
                                    <td>GROUP LOAN</td>
                                    <td>{{$data['grp_loan_total_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['grp_loan_total_amt'], 2, '.', '')}}</td>
                                </tr>
                                <tr>    
                                    <td>36 DAILY </td>
                                    <td>{{$data['current_daily_new_ac_tenure36']}}</td>
                                    <td>&#x20B9;{{$data['current_daily_new_ac_amt_sum_tenure36']}}</td>
                                    <td>48 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure48']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure48'], 2, '.', '')}}</td>
                                    <td>LOAN AGAINST INVESTMENT</td>
                                    <td>{{$data['loan_against_investment_total_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['loan_against_investment_total_amt'], 2, '.', '')}}</td>
                                </tr>
                                <tr>    
                                    <td>60 DAILY </td>
                                    <td>{{$data['current_daily_new_ac_tenure60']}}</td>
                                    <td>&#x20B9;{{$data['current_daily_new_ac_amt_sum_tenure60']}}</td>
                                    <td>60 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure60']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure60'], 2, '.', '')}}</td>
                                    <td>STAFF LOAN</td>
                                    <td>{{$data['staff_loan_total_ac']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['staff_loan_total_amt'], 2, '.', '')}}</td>
                                </tr>
                                <tr>    
                                    <td>12 Month </td>
                                    <td>{{$data['monthly_new_ac_tenure12']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenure12']}}</td>
                                    <td>72 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure72']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure72'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>36 Month </td>
                                    <td>{{$data['monthly_new_ac_tenure36']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenure36']}}</td>
                                    <td>96 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure96']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure96'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>60 Month </td>
                                    <td>{{$data['monthly_new_ac_tenure60']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenure60']}}</td>
                                    <td>120 Month</td> 
                                    <td>{{$data['monthly_new_fd_ac_tenure120']}}</td>
                                    <td>&#x20B9;{{number_format((float)$data['monthly_new_fd_sum_ac_tenure120'], 2, '.', '')}}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>84 Month </td>
                                    <td>{{$data['monthly_new_ac_tenure84']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenure84']}}</td>
                                    <td></td> 
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>    
                                    <td>120 Month</td>
                                    <td>{{$data['monthly_new_ac_tenure120']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenure120']}}</td>
                                    <td></td> 
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                
                                <tr>
                                  <td>Kanyadan</td>
                                    <td>{{$data['monthly_new_ac_tenurekanyadan']}}</td>
                                    <td>&#x20B9;{{$data['monthly_new_ac_amt_sum_tenurekanyadan']}}</td>
                                    <td></td> 
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>   
                                </tr>
                            </tbody>
                                    
                        </table>
                    </div>
                   
                </div>
            </div>
            <div class="col-md-4 col-sm-8" >
                <div class="card"  id="expense">
                    <h3 class="text-center">TOTAL EXPENSES REPORT</h3>
                    <div class="table-responsive">
                        
                        <table  class="table table-flush">
                            <thead>
                                <tr>
                                  
                                    <th>A/C Head.</th>
                                    <th>AMT.</th>
                                </tr>
                            </thead>  
                            <tbody>
                                @foreach($account_head as $index => $head )
                               
                               <tr>    
                                
                                    <td>{{$head->sub_head}}</td>
                                    <td>&#x20B9;{{number_format((float)headTotalNew($head->head_id,$start_date,$end_date,$branch_id), 2, '.', '')}}</td>
                                    
                                </tr>
                                <?php  $arrayCategories = array();
                                $data=getFixedAsset($head->id); 
                                ?>
                                @if(count($data)>0) 

                                @foreach($data as $ci =>$child_asset)

                                <tr>
                                   
                                    <td>{{$child_asset->sub_head}}</td>
                                    <td>&#x20B9;{{number_format((float)headTotalNew($child_asset->head_id,$start_date,$end_date,$branch_id), 2, '.', '')}}</td>
                                </tr>
                               
                             <?php
                                $sub_child=getsubChildFixedAsset($child_asset->id);  
                                ?>
                                @if(count($sub_child) > 0)
                                     @foreach($sub_child as $in=> $sub_child_asset)
                                      <tr>

                                    

                                    <td>{{$sub_child_asset->sub_head}}</td>
                                    
                                     <td>&#x20B9;{{number_format((float)headTotalNew($sub_child_asset->head_id,$start_date,$end_date,$branch_id), 2, '.', '')}}</td>
                                </tr>
                                <?php
                                $sub_child_sub_asset=getsubChildsubAssetFixedAsset($sub_child_asset->id);  
                                ?>
                                @if(count($sub_child_sub_asset)>0)
                                @foreach($sub_child_sub_asset as $i => $asset ) 
                                   <tr>
                                   
                                    <td>{{$asset->sub_head}}</td>
                                    <td>&#x20B9;{{number_format((float)headTotalNew($asset->head_id,$start_date,$end_date,$branch_id), 2, '.', '')}}</td>
                                </tr>


                                @endforeach
                                @endif 
                                @endforeach
                                @endif
                                 @endforeach
                            @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="">
                        <h3 class="text-center">FUND SEND H.Q. BY  CASH (BANK DEPOSIT)</h3>
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="3" scope="colgroup">SAMRADDH (Micro)</th>
                                    <th colspan="3" scope="colgroup">LOAN</th>
                                    <th rowspan="2" scope="rowgroup"></th>
                                </tr>
                            </thead>  
                            <tbody>
                                <tr>    
                                    <td>DATE</td>
                                    <td>BANK NAME</td>
                                    <td>AMOUNT</td>
									
                                    <td>DATE</td>
                                    <td>BANK NAME</td>
                                    <td>AMOUNT</td>
									
                                    <td>TOTAL SENT</td>
                                    
                                </tr>
								
								<?php if( (count($micros) > 0) || (count($loans) > 0) ) { 
									$microCount = count($micros);
									$loansCount = count($loans);
									$maxValue = max($microCount, $loansCount);
									
									for($i=0; $i<$maxValue; $i++){
								?>
									<tr>
										
										
                                        <td> <?php if(isset($micros[$i])) { echo date("d/m/Y", strtotime(convertDate($micros[$i]->day))); } ?></td>
                                        <td> <?php if(isset($micros[$i])) { echo $micros[$i]->bank_name; } ?></td>
                                        <td> <?php if(isset($micros[$i])) { echo "&#x20B9;".number_format((float)$micros[$i]->amount, 2, '.', ''); } ?></td>
                                        <td> <?php if(isset($loans[$i])) { echo date("d/m/Y", strtotime(convertDate($loans[$i]->day))); } ?></td>
                                        <td> <?php if(isset($loans[$i])) { echo $loans[$i]->bank_name; } ?></td>
                                        <td> <?php if(isset($loans[$i])) { echo "&#x20B9;".number_format((float)$loans[$i]->amount, 2, '.', ''); } ?></td>
										<?php if($i == 0) {?>
											<td><?php echo "&#x20B9;".number_format((float)$total_amounts, 2, '.', ''); ?></td>
										<?php } else { ?>
											<td></td>
										<?php }	?>
									<tr/>
									
								<?php } } ?>
          
							    

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="">
                        <h3 class="text-center">RECEIVED CHEQUES </h3>
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="3" scope="colgroup">SAMRADDH (Micro)</th>
                                    <th colspan="3" scope="colgroup">LOAN</th>
                                    <th rowspan="2" scope="rowgroup"></th>
                                </tr>
                            </thead>  
                            <tbody>
                                <tr>    
                                    <td>DATE</td>
                                    <td>BANK NAME</td>
                                    <td>AMOUNT</td>
                                     <td>DATE</td>
                                    <td>BANK NAME</td>
                                    <td>AMOUNT</td>
                                    <td>TOTAL RECEIVED</td>
                                </tr>
                               
								<?php if( (count($receivedChequeMicro) > 0) || (count($receivedChequeLoan) > 0) ) { 
									$receivedChequemicroCount = count($receivedChequeMicro);
									$receivedChequeloansCount = count($receivedChequeLoan);
									$maxValue = max($receivedChequemicroCount, $receivedChequeloansCount);
									
									for($j=0; $j<$maxValue; $j++){
								?>
									<tr>
										<td> <?php if(isset($receivedChequeMicro[$j])) { echo date("d/m/Y", strtotime(convertDate($receivedChequeMicro[$j]->day))); } ?></td>
										<td> <?php if(isset($receivedChequeMicro[$j])) { echo $receivedChequeMicro[$j]->bank_name; } ?></td>
										<td> <?php if(isset($receivedChequeMicro[$j])) { echo "&#x20B9;".number_format((float)$receivedChequeMicro[$j]->amount, 2, '.', ''); } ?></td>
										<td> <?php if(isset($receivedChequeLoan[$j])) { echo date("d/m/Y", strtotime(convertDate($receivedChequeLoan[$j]->day))); } ?></td>
										<td> <?php if(isset($receivedChequeLoan[$j])) { echo $receivedChequeLoan[$j]->bank_name; } ?></td>
										<td> <?php if(isset($receivedChequeLoan[$j])) { echo "&#x20B9;".number_format((float)$receivedChequeLoan[$j]->amount, 2, '.', ''); } ?></td>
										<?php if($j == 0) {?>
											<td><?php echo "&#x20B9;".number_format((float)$total_received_cheque_amount, 2, '.', ''); ?></td>
										<?php } else { ?>
											<td></td>
										<?php }	?>
									<tr/>
									
								<?php } } ?>
							   
                                <tr>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex col-md-12">
                <div class="">
                    <div class=" col-md-12">
                        <div class="card">
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>OPENING CASH IN HAND RS.</th>
                                    <td>&#x20B9;{{ number_format((float)$totalCaseInHandAmount, 2, '.', '') }}</td>
                                </tr>
                                 <tr>
                                    <th>(+) TOTAL RECEIVING RS.</th>
                                    <td>&#x20B9;{{ number_format((float)$totalNEWBusinessAmount, 2, '.', '') +  number_format((float)$totalNewRenewalAmount, 2, '.', '') }}</td>
                                </tr>
                                 <tr>
                                    <th>TOTAL RS.</th>
                                    <td>&#x20B9;{{ number_format((float)$totalNEWBusinessAmount, 2, '.', '') +  number_format((float)$totalNewRenewalAmount, 2, '.', '') + number_format((float)$totalCaseInHandAmount, 2, '.', '') }}</td>
                                </tr>
                                 <tr>
                                    <th>(-) TOTAL PAYMENTS</th>
                                    <td>&#x20B9;{{ number_format((float)$total_final_payments_Amounts, 2, '.', '') }}</td>
                                </tr>
                                <tr>
                                    <th>ACTUAL CASH IN HAND</th>
                                    <td>&#x20B9;{{ number_format((float)$totalNEWBusinessAmount, 2, '.', '') +  number_format((float)$totalNewRenewalAmount, 2, '.', '') + number_format((float)$totalCaseInHandAmount, 2, '.', '') - number_format((float)$total_final_payments_Amounts, 2, '.', '')  }}</td>
                                </tr>
                            </thead>  

                        </table>
                    </div>
                </div>
                <div class=" col-md-12">
                        <div class="card">
                            <div class="">
                               
                                <table id="" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>CLOSING CASH IN HAND SAMRADDH</th>
                                            <td>&#x20B9;{{number_format((float)$closing_cash_in_hand_samraddh_micro, 2, '.', '')}}</td>
                                        </tr>
                                         <!-- <tr>
                                            <th>CLOSING CASH IN HAND LOAN</th>
                                            <td>&#x20B9;{{number_format((float)$closing_cash_in_hand_samraddh_loan, 2, '.', '')}}</td>
                                        </tr> -->
                                         
                                    </thead>  
                                   
                                </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        @include('templates.branch.report.partials.branch_business_script')
