
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

    <thead>

    <tr>
        <th>#</th>
        <th>BR Name</th>
        <th>BR Code</th>
        <th>SO Code</th>
        <th>RO Code</th>
        <th>ZO Code</th>
        <th>Date</th>
         <th>A/C Opening Date</th>
         <th>Account Number</th>
         <th>SSB Account Number</th>
        <th>Member Name</th>
        <th >Associate Code</th>
        <th>Associate Name</th>
        <th>Is Loan</th>
        <th>Total Amount</th>
         <th>TDS Amount</th>
        <th >Total Amount With Interest</th>
        <th>Advice Type</th>
        <th>Expense Type</th>
        <th>Voucher No</th>
        <th>Payment Mode</th>
         <th>RTGS Charge</th>
        
        <th>Bank Account Number</th>
        <th>IFSC Code</th>
        <th>Print</th>
        <th>Status</th>



    
    </tr>

    </thead>

    <tbody>

			<?php 
            $sno = 1;
            foreach ($data as $row)
            {
                  $isLoan = 'No';
            $branch_id = $row['branch']['id'];
            if($row->investment_id)
            {
                 $member_id = $row['investment']->member_id; //getInvestmentDetails($row->investment_id)->member_id;

                 $associate_id = $row['investment']->associate_id; //getInvestmentDetails($row->investment_id)->associate_id;
                 $detail =App\Models\Memberloans::where('applicant_id',$member_id)->where('status',4)->exists();
                 if($detail)
                 {
                    $isLoan = 'Yes';
                 }
                 else{
                    $isLoan = 'No';
                 }
            }
            else{
                $member_id='';
                $associate_id='';
            }
             $opening_date='N/A';
                if(isset($row->payment_type))
                {
                    
                       if($row->investment_id)
                        {
                         $date = $row['investment']; //getInvestmentDetails($row->investment_id);
                         if($date)
                         {
                            $opening_date = date("d/m/Y", strtotime( $date->created_at));   
                         }
                        else
                        {
                             $opening_date = 'None';
                        }
                        }
                    

                    
                }
               
                else{
                    if($row->opening_date)
                    {
                        $opening_date = date("d/m/Y", strtotime( $row->opening_date));
    
                    }
                    else{
                       $opening_date = "N/A";                  
                    }
                }
            if($row->investment_id)
                {
                    $member_id = $row['investment']->member_id; //getInvestmentDetails($row->investment_id)->member_id;
                    
                    $ac = $row['investment']['ssb'];//App\Models\SavingAccount::where('member_id',$member_id)->first();
                    if($ac){
                    $ac = $ac->account_no;
                    }
                    else{
                        $ac = 'N/A';
                    }
                }
                else{
                    $ac = "N/A";
                }

                 if($row->bank_account_number)
                {
                    $bank =  $row->bank_account_number;
                }
                else{
                    $bank =  "N/A";
                }
                if($row->bank_ifsc)
                {
                    $bank_ifsc =  $row->bank_ifsc;
                }
                else{
                    $bank_ifsc =  "N/A";
                }
                if($row->id)
                {
                    if($row->payment_mode == 2 )
                    {
                        $transaction = App\Models\AllHeadTransaction::where('head_id',92)->where('type_id',$row->id)->first();;

                        if($transaction)
                        {                     
                                  
                            $neft  =round($transaction->amount);
                        }                    
                         else{
                             $neft  ='N/A';
                         }
                    }
                    else{
                         $neft  ='N/A'; 
                    }
                    
                }
                else{
                    $neft ='N/A'; 
                }
                
                

                if($row->is_print == 0){
                    $print = 'Yes';
                }else{
                    $print = 'No';        
                }
                 if($row->maturity_amount_payable){          
                    $total_payable_interest =  round($row->maturity_amount_payable+$row->tds_amount);
                }else{
                     $total_payable_interest =  $row['expenses']->sum('amount');
                }
                
            ?> 

        <tr>

            <td>{{ $sno }}</td>
        	<td>{{ $row['branch']['name'] }}</td>
        	<td>{{ $row['branch']['branch_code'] }}</td>
        	<td>{{ $row['branch']['sector'] }}</td>
        	<td>{{ $row['branch']['regan'] }}</td>
        	<td>{{ $row['branch']['zone'] }}</td>
            <td>{{ date("d/m/Y", strtotime( $row->date)) }}</td>
            <td> @if($opening_date)
                {{$opening_date}}
                @endif
            </td>
            @php
            if($row->payment_type == 4)
            {
                if($row->investment_id)
                {
                    $data = $row['investment']; 
                    $aNumber = $data->account_number;     
                }
            }else{
                if($row->account_number){
                    $aNumber = $row->account_number;
                }else{
                    $aNumber = 'N/A';
                } 
            }
            @endphp
            <td>{{ $aNumber }}</td>    
            <td>{{ $ac }}</td>
             <td>@if($row->investment_id!='')
               {{ $row['investment']['member']->first_name.' '.$row['investment']['member']->last_name }}
                @else
                 @endif
            </td>
             @if($associate_id !='')
                <td>{{ $row['investment']['associateMember']->associate_no   }}</td>
            @else
                <td></td>
            @endif
            <td>
                @if($row->investment_id !='')
                   @if(isset($row['investment']['member']->first_name))  
                   {{$row['investment']['member']->first_name.' '.$row['investment']['member']->last_name }}
                   @else
                   @endif
                @else
                @endif
            </td>
            
            <td>{{  $isLoan }}</td>
           
            @php
            if($row->investment_id){
                $investmentAmount = App\Models\Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
            }else{
                $investmentAmount = 'N/A';
            }
            @endphp
            <td>{{ $investmentAmount }}</td>
            <td>{{round($row->tds_amount)}}</td>
            <td>{{$total_payable_interest}}</td>
            @php
                if($row->payment_type == 0){
                    $payment_type = 'Expenses';
                }elseif($row->payment_type == 1){
                    $payment_type = 'Maturity';
                }elseif($row->payment_type == 2){
                    $payment_type = 'Prematurity';
                }elseif($row->payment_type == 3){
                    if($row->sub_payment_type == '4'){
                        $payment_type = 'Death Help';
                    }elseif($row->sub_payment_type == '5'){
                        $payment_type = 'Death Claim';
                    }
                }elseif($row->payment_type == 4)
                {
                    $payment_type = "Emergency Maturity";
                }
                if($row->sub_payment_type == '0'){
                    $sub_payment_type = 'Fresh Exprense';
                }elseif($row->sub_payment_type == '1'){
                    $sub_payment_type = 'TA Advanced';
                }elseif($row->sub_payment_type == '2'){
                    $sub_payment_type = 'Advanced salary';
                }elseif($row->sub_payment_type == '3'){
                    $sub_payment_type = 'Advanced Rent';
                }elseif($row->sub_payment_type == '4'){
                    $sub_payment_type = 'N/A';
                }elseif($row->sub_payment_type == '5'){
                    $sub_payment_type = 'N/A';
                }else{
                    $sub_payment_type = 'N/A';
                }
            @endphp

            <td>{{ $payment_type }}</td>
            <td>{{ $sub_payment_type }}</td>

           

            <td>{{ $row['voucher_number'] }}</td>
            <td>@if($row)
                
                    @if($row->payment_mode == 0)
                    
                         Cash
                    
                    @elseif($row->payment_mode == 1)
                    
                         Cheque
                    
                    @elseif($row->payment_mode == 2)
                    
                     Online Transfer
                    
                    @elseif($row->payment_mode == 3)
                    
                         SSB Transfer
                         @else
                    @endif
                @endif
                
                </td>
            <td>{{ $neft }}</td>
             <td> {{$bank}}</td>  
            <td> {{$bank_ifsc}}</td>  
            <td>{{$print}}</td> 
            @if($row['status'] == 0)
                <td> Pending</td>
            @else
                 <td> Approved</td>    
            @endif
        </tr>

	<?php 
        $sno = $sno + 1;	
        } 
    ?>
</tbody>

</table>

