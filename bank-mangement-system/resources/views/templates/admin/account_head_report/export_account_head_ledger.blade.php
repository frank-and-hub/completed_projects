<h6 class="card-title font-weight-semibold">Account Head Report</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
                                 <th >S.No</th> 
                                        <th >BR Name</th> 
                                        <th >BR Code</th>
                                        <th >SO Name</th>
                                        <th >RO Name</th>
                                        <th >ZO Name</th>
                                        <th >Type</th> 
                                        <th >Description</th>
                                        <th >Amount</th>
                                        <th >Account No</th>
                                        <th >Member Name</th>  
                                      <!--  <th >Associate Name</th>   -->
                                        <th >Payment Type</th>
                                        <th >Payment Mode</th>
                                        <th >Voucher No.</th>
                                        <th>Voucher Date</th>  
                                        <th >Cheque No.</th>   
                                        <th >Cheque Date</th>
                                        <th >Transaction Number</th>
                                        <th>Transaction Date</th>  
                                        <th>Receive Bank</th>
                                        <th>Receive Bank Account</th>
                                        <th>Created Date</th>
                            </tr>
</thead>
<tbody>
    @foreach($data as $index => $row)
    <?php
         if($row->type == 2 || $row->type ==3)
                {
                    $account_number = getInvestmentDetails($row->type_id);
                   $account_no = $account_number->account_number;
                }

                if($row->type == 4)
                {
                    $account_number = getSavingAccountMemberId($row->type_id);
                    if( $account_number)
                    {
                         $account_no= $account_number->account_no;
                    }
                   else{
                       $account_no = 'N/A';
                   }
                } 
                if($row->type == 5)
                {
                    $account_number = getLoanDetail($row->type_id);
                    if($account_number)
                    {
                        $account_no= $account_number->account_number;
                    }
                    else{
                       $account_no = 'N/A';
                    }
                } 
                
                if($row->type == 13)
                {
                    $v_no = \App\Models\DemandAdvice::where('id',$row->type_id)->first();
                   $account_no = $v_no->account_number;
                }
                 if($row->v_no)
                {
                    $v_no = $row->v_no;
                   $voucher_number =  $v_no;
                }
                if($row->type == 13)
                {
                    $v_no = \App\Models\DemandAdvice::where('id',$row->type_id)->first();
                   $voucher_number = $v_no->voucher_number;
                }
                else
                {
                   $voucher_number = "N/A";
                }
                 if($row->transction_bank_to)
               {
                  $transction_bank_to_name = getSamraddhBank($row->transction_bank_to);
                    $receive_bank =  $transction_bank_to_name->bank_name;
               }
               else{
               $receive_bank  =  "N/A";
               }
                if($row->transction_bank_to)
                {
                    $transction_bank_to_ac_no = getSamraddhBankAccountId($row->transction_bank_to);
                    $receive_bank_ac =  $transction_bank_to_ac_no->account_no;
                }
                else{
                $receive_bank_ac =  "N/A";
               }
    ?>

   <tr> 
    <td>{{$index+1}}</td>
    <td>@if( $row['branch']){{ $row['branch']->name}}@endif</td>
    <td>@if( $row['branch']){{ $row['branch']->branch_code}}@endif</td>
    <td>@if( $row['branch']){{ $row['branch']->sector}}@endif</td>
    <td>@if( $row['branch']){{ $row['branch']->regan}}@endif</td>
    <td>@if( $row['branch']){{ $row['branch']->zone}}@endif</td>
    <td>@if($row->type == 16)
       ShareHolder
        @elseif($row->type == 15)
        Director
        @elseif($row->type == 17)
        Loan From Bank
        @else
    @endif</td>
    <td>{{$row->description}}</td>
    <td>{{ number_format((float)$row->amount, 2, '.', '')}}</td>
    <td>@if($row->type == 2 || $row->type ==3)
            {{$account_no}}
        @elseif($row->type == 4)
            {{$account_no}}
        
        @elseif($row->type == 5)
            {{$account_no}}
        @elseif($row->type == 13)
            {{$account_no}}
        @endif
    </td>

    <td>@if($row->member_id){{getMemberData($row->member_id)->first_name.' '.getMemberData($row->member_id)->last_name}}@endif</td>
   <!-- <td>@if($row->associate_id){{ $row->associate_id}}@endif</td>-->
    <td>@if($row->payment_type){{$row->payment_type}}@endif</td>
    <td>@if($row->payment_mode == 0)
        Cash
        @elseif($row->payment_mode == 1)
        Cheque
        @elseif($row->payment_mode == 2)
        Online Transfer
        @elseif($row->payment_mode == 3)
        SSB Transfer
        @elseif($row->payment_mode == 4)
       Auto Transfer
       @else
       @endif</td>
     <td>
        
        {{$voucher_number}}
        </td>
    <td>@if($row->v_date){{date("d/m/Y", strtotime(convertDate($row->v_date)))}}@endif</td>
    <td>@if( $row->cheque_no){{ $row->cheque_no}}@endif</td>
    <td>@if($row->cheque_date){{date("d/m/Y", strtotime(convertDate($row->cheque_date)))}}@endif</td>
    <td>@if($row->transction_no){{$row->transction_no}}@endif</td>

       <td>@if($row->transction_date){{$row->transction_date}}@endif</td>
    <td>{{$receive_bank}}</td>
    <td>{{$receive_bank_ac}}</td>
    <td>@if($row->entry_date){{ date("d/m/Y", strtotime(convertDate($row->entry_date)))}}@endif</td>   </tr>
    @endforeach
</tbody>
</table>
