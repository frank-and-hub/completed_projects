<style type="text/css">
    #expense{
            height: 44.5rem;
            overflow-x: hidden;
            overflow-y: auto;
            text-align:justify;
    }
</style>
<?php


?>
<input type="hidden" name="index" id="index">
 <div class="container">
              <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
            </div>   
            <div class="col-md-12">
                <h4 class="text-center font-weight-semibold">Samraddh Bestwin Micro Finance Association</h4>
                <h6 class="text-center mx-auto" style="width:50%;">Corp. Office: 114-115, Pushp Enclave, Sector -5, Pratap Nagar, Tonk Road Jaipur- 302033 Info.: Phone No.: +91 9829861860 | Web: www.samraddhbestwin.com | Mail Us: info@samraddhbestwin.com</h6>
            </div>
            <div class="col-md-12 mt-4">
                <h4 class="font-weight-semibold text-center">DAYBOOK</h4>
            </div>
            <div class="d-flex my-4 col-md-12">
            <div class="col-md-4">
                <h3 class="card-title font-weight-semibold">Cash Allocation</h3>
                <div class="card">
                     <?php 
                    $getBranchOpening_cash =getBranchOpeningDetail($branch_id);
                    $balance_cash =0;
                    $C_balance_cash =0;
                    $currentdate = date('Y-m-d');
                    if($getBranchOpening_cash->date==$start_date)
                    {
                      $balance_cash =$getBranchOpening_cash ->total_amount;
                      if($end_date == '')
                          {
                            $end_date=$currentdate;
                          }
                    }
                    if($getBranchOpening_cash->date<$start_date)
                    {
                        if($getBranchOpening_cash->date != '')
                        {
                              $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($start_date,$getBranchOpening_cash->date,$getBranchOpening_cash->total_amount,$branch_id);
                        }
                        else{
                             $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($start_date,$currentdate,$getBranchOpening_cash->total_amount,$branch_id);
                            
                        }
                     
                      $balance_cash =$getBranchTotalBalance_cash;
                      if($end_date == '')
                          {
                            $end_date=$currentdate;
                          }
                    }
                    $getTotal_DR=getBranchTotalBalanceAllTranDR($start_date,$end_date,$branch_id);
                     $getTotal_CR=getBranchTotalBalanceAllTranCR($start_date,$end_date,$branch_id);
                     $totalBalance=$getTotal_CR-$getTotal_DR;
                     $C_balance_cash = $balance_cash+$totalBalance;
                     $clBalance=$cashInhand['CR']-$cashInhand['DR']+$cashInhandOpening;
                    ?>
                    <table class="table table-flush">
                       <tr>
                         <th colspan="2">Opening</th>
                          <td></td>
                          <td>{{number_format((float)$balance_cash, 2, '.', '')}}</td> 
                         </tr>
                         <tr>
                          <th colspan="2"></th>
                          <th>Received</th>
                          <th>Payment</th> 
                         </tr>
                         <tr>
                          <th colspan="2">Cash</th>
                          <td>{{number_format((float)$cashInhand['CR'], 2, '.', '')}}</td>
                          <td>{{number_format((float)$cashInhand['DR'], 2, '.', '')}}</td> 
                         </tr>
                         <tr>
                          <th colspan="2">Closing</th>
                          <td></td>
                          <!--<td>{{number_format((float)$cashInhandclosing, 2, '.', '')}}</td> -->
                          <td>{{number_format((float)$C_balance_cash, 2, '.', '')}}</td> 
                         </tr>  
                    </table>     
                 </div>   
            </div>    
            <div class="col-md-4">
                 <h3 class="card-title font-weight-semibold">Cheque</h3>
                <div class="card ">
                    <table class="table table-flush">
                        <tr>
                          <th colspan="2">Opening</th>
                          <td></td>
                          <td>{{number_format((float)getchequeopeningBalance($start_date,$branch_id), 2, '.', '')}}</td> 
                         </tr>
                         <tr>
                          <th colspan="2"></th>
                          <th>Received</th>
                          <th>Payment</th> 
                         </tr>
                         <tr>
                          <th colspan="2">Cheque</th>
                          <td>{{number_format((float)$cheque['CR'], 2, '.', '')}}</td>
                          <td>{{number_format((float)$cheque['DR'], 2, '.', '')}}</td> 
                         </tr>
                         <tr>
                          <th colspan="2">Closing</th>
                          <td></td>
                          <td>{{number_format((float)getchequeclosingBalance($end_date,$branch_id), 2, '.', '')}}</td> 
                         </tr>  
                    </table>     
                 </div>   
            </div> 
            
            </div>

            <div class="col-md-12">
                 <h3 class="card-title font-weight-semibold">Bank</h3>
                <div class="card">
                    <table class="table table-flush">
                       <thead>
                                 <tr>
                                   <th>Bank Name</th>
                                   <th>Account Number</th>
                                   <th>Opening</th>
                                   <th>Receiving</th>
                                   <th>Payment</th>
                                   <th>Closing</th>
                                  
                                   <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
                                 </tr>
                               </thead>
                               <tbody>
                                @foreach($samraddh as $value)
                               
                                 <tr>
                                  <td>{{$value->bank_name}}</td>
                                  <td>{{$value['bankAccount']->account_no}}</td>
                                   <td>{{number_format((float)getbankopeningBalance($start_date,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankreceivedBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                    <td>{{number_format((float)getbankpaymentBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankclosingBalance($end_date,$value->id), 2, '.', '')}}</td>
                                 </tr>
                                 @endforeach
                               </tbody>
                    </table>     
                 </div>   
            </div>   
            <div class="col-md-12" id="ad">
                <div class="card" id="expense">
                    <div class="table-responsive" >
                       <table class="table table-flush" id="t">
                          
                                 <thead>
                                 <tr>
                                  <th>Tr.No</th> 
                                   <th>Tr.Date</th>
                                   <th>Receipt.No</th>
                                   <th>Tr.By</th>
                                   <th>Account Number</th>
                                   <th>Plan Name </th>
                                   <th>Member/Employee/Owner</th>
                                   <th>Associate</th>
                                   <th>Narration</th>
                                   <th>CR Narration</th>
                                   <th>DR Narration</th>
                                   <th>CR Amount</th>
                                   <th>DR Amount</th>
                                   <th>Balance</th>
                                   <th>Ref Id</th>
                                   <th>Payment Type</th>
                                   <th>Tag</th>
                                   <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
                                 </tr>
                               </thead>
                               <tbody>
                                 
                               </tbody>
                           </tr> 

                       </table> 

                    </div>
                     <button class="btn btn-primary" id="load_more" style="display:none;float: center;">Load More...</button>
            </div>
          </div>
         
        <div class="card-body ">
                <div class="row">
              <div class="col-lg-12 text-center">
              <a href="{{ URL::to('admin/print/report/day_book/?from_date='.$start_date.'&to_date='.$end_date.'&branch='.$branch_id)}}"  target="_blank" type="submit" class="btn btn-primary" >View Print<i class="icon-paperplane ml-2" ></i></a >
            </div> 
          </div>

          @include('templates.admin.report.partials.new_day_book_script')
