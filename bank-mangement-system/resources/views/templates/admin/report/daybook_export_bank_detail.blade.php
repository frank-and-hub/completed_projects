<div class="">
                 <h3 class="card-title font-weight-semibold">Bank</h3>
                <div class="">
                    <table border="1" width="100%" style="border-collapse: collapse;font-size:12px;">
                       <thead>
                                 <tr>
                                   <th style="font-weight: bold;">Bank Name</th>
                                   <th style="font-weight: bold;">Account Number</th>
                                   <th style="font-weight: bold;">Opening</th>
                                   <th style="font-weight: bold;">Receiving</th>
                                   <th style="font-weight: bold;">Payment</th>
                                   <th style="font-weight: bold;">Closing</th>
                                  
                                   <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
                                 </tr>
                               </thead>
                               <tbody>
                               @foreach($bank as $value)
                                 
                                 <tr>
                                  <td>{{$value->bank_name}}</td>
                                  <td>{{$value['bankAccount']->account_no}}</td>
                                   <td>{{number_format((float)getbankopeningBalance($startDate,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankreceivedBalance($startDate,$endDate,$branch_id,$value->id), 2, '.', '')}}</td>
                                    <td>{{number_format((float)getbankpaymentBalance($startDate,$endDate,$branch_id,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankclosingBalance($endDate,$value->id), 2, '.', '')}}</td>
                                 </tr>
                                 @endforeach
                               </tbody>
                    </table>     
                 </div>   
            </div>   
