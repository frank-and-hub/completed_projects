<h6 class="card-title font-weight-semibold">Loan From Bank Ledger List - {{$detail->bank_name}} ({{$detail->loan_account_number}})
</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                <th >S.No</th>                                         
                                        <th >Type</th> 
                                        <th >Description</th>
                                        <th >Credit(CR)</th> 
                                        <th >Debit(DR)</th> 
                                        <th >Interest Amount</th> 
                                        <th >Balance</th> 
                                        <th >Payment Type</th>
                                        <th >Payment Mode</th>  
                                        <th> Bank</th>
                                        <th> Bank Account</th>
                                        <th>Created Date</th>
                            </tr>
</thead>
<tbody>
  <?php $balance=0;?>
@foreach($data as $index => $row)  
<?php
            if($row->payment_type=='DR')
                {
                    $balance=$balance-$row->amount;
                }
                else{
                      $balance=$balance+$row->amount;
                }
                if($row->type == 17)
                {
                     if($row->sub_type == 171)
                    {
                        $type = 'Create Loan';
                    }
                     elseif($row->sub_type == 172)
                    {
                        $type = 'Emi Payment';
                    }
                }
                $payment_type = 'N/A';
                if($row->payment_type=='DR')
                {
                    $payment_type = 'Debit';
                }
                if($row->payment_type=='CR')
                {
                    $payment_type = 'Credit';
                }

                $payment_type = 'N/A'; 
                if($row->payment_mode==0)
                {
                    $payment_mode = 'Cash';
                }
                if($row->payment_mode==1)
                {
                    $payment_mode = 'Cheque';
                }
                if($row->payment_mode==2)
                {
                    $payment_mode = 'Online Transfer';
                }
                if($row->payment_mode==3)
                {
                    $payment_mode = 'SSB Transfer Through JV';
                }
                if($row->payment_mode==4)
                {
                    if($row->payment_type=='CR')
                    {
                        $payment_mode =  "Auto Credit";
                    }
                    else
                    {
                        $payment_mode = "Auto Debit";
                    }
                }

                if($row->payment_mode==4)
                {
                     
                      $bank_id = $row->amount_from_id;

                       $bankDtail=getSamraddhBank($bank_id);
                        

                      $bank_name= $bankDtail->bank_name; 
                      
                      $bankAcDetail= App\Models\SamraddhBankAccount::where('bank_id',$bank_id)->first();

                      $bank_ac=$bankAcDetail->account_no;

                     
               } 
               if($row->payment_mode==2)
                {
                     
                      $transction_bank_to_name = $row->transction_bank_to_name;
                      $bank_name=$transction_bank_to_name;
                      $bank_ac = $row->transction_bank_to_ac_no;
                     
                 }
                 $cr='';
                 $dr='';
                 $emi_interest_rate=\App\Models\AllHeadTransaction:: where('head_id',97)->where('daybook_ref_id',$row->daybook_ref_id)->first();
                 if(isset($emi_interest_rate->amount))
                 {
                    $interestAmount = $emi_interest_rate->amount; 
                 }
                 else{
                    $interestAmount = 0; 
                 }

                 if($row->payment_type=='CR')
                {
               $cr= number_format((float)$row->amount, 2, '.', '');
             }
             if($row->payment_type=='DR')
                {
               $dr= number_format((float)$row->amount, 2, '.', '');
             }
                
?>
  <tr>
    <td>{{ $index+1 }}</td> 
    <td>{{ $type }}</td> 
    <td>{{ $row->description }} </td>
    <td>{{ $cr }} </td> 
    <td>{{ $dr }} </td> 
     <td>{{ $interestAmount }} </td> 
    <td>{{ $balance }} </td> 
    <td>{{ $payment_type }} </td> 
    <td>{{ $payment_mode }} </td> 
    <td>{{ $bank_name }} </td> 
    <td>{{ $bank_ac }} </td> 
    <td>{{ date("d/m/Y", strtotime(convertDate($row->entry_date))) }} </td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
