<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<thead>
    <tr>
         <th>S/N</th> 
            <th>Date Time</th>
            <th>BR Name</th> 
            <th>BR Code</th> 
            <th>SO Name</th> 
            <th>RO Name</th> 
            <th>ZO Name</th>
            <th>Member Name</th>
            <th>Member Id</th> 
            <th>Type</th> 
            <th> A/c No</th>
            <th>Amount</th> 
            <th>Description</th>
            <th>Payment Type</th> 
            <th>Payment Mode</th> 
    </tr>
</thead>
<tbody>
        @foreach($data as $index=>$row)
        <?php $member =  App\Models\Member::where('id',$row->applicant_id)->first(['id','associate_code','first_name','last_name']);
        $branch = App\Models\Branch::where('id',$row->branch_id)->first()->name ;
          $type = '';
                       if($row->type == 1)
                {
                    if($row->sub_type==11)
                    {
                        $type ="Member - MI";
                    }
                    elseif($row->sub_type==12)
                    {
                        $type = "Member - STN ";
                    }
                }
                if($row->type == 2)
                {
                    if($row->sub_type == 21)
                    {
                        $type = 'Associate - Associate Commission';
                    }
                }

                if($row->type == 3)
                {
                    if($row->sub_type == 30)
                    {
                        $type = 'Investment - ELI';
                    }
                    elseif ($row->sub_type == 31) {
                        $type = 'Investment - Register';
                    }
                    elseif ($row->sub_type == 32) {
                          $type = 'Investment - Renew';
                    }
                    elseif ($row->sub_type == 33) {
                          $type = 'Investment - Passbook Print';
                    }
                }

                if($row->type == 4)
                {
                    if($row->sub_type == 41)
                    {
                        $type = "SSB - Register";
                    }
                    elseif ($row->sub_type == 42) {
                        $type = 'SSB - Renew(Deposit)';
                    }
                    elseif ($row->sub_type == 43) {
                          $type = 'SSB - Withdraw';
                    }
                    elseif ($row->sub_type == 44) {
                          $type = 'SSB - Passbook Print';
                    }
                    elseif ($row->sub_type == 45) {
                        $type = 'SSB - Commission';
                    }
                    elseif ($row->sub_type == 46) {
                          $type = 'SSB - Fule';
                    }
                    elseif ($row->sub_type == 47) {
                          $type = 'SSB - Transfer To Investment';
                    }
                    elseif ($row->sub_type == 48) {
                        $type = 'SSB - Transfer To loan';
                    }
                    elseif ($row->sub_type == 49) {
                          $type = 'SSB - Rent Transfer';
                    }
                    elseif ($row->sub_type == 410) {
                          $type = 'SSB - Salary Transfer';
                    }
                }

                if($row->type == 5)
                {
                     if($row->sub_type == 51)
                    {
                        $type = "Loan ";
                    }
                    elseif ($row->sub_type == 52) {
                        $type = 'Loan - Emi';
                    }
                    elseif ($row->sub_type == 53) {
                          $type = 'Loan - Panelty';
                    }
                    elseif ($row->sub_type == 54) {
                          $type = 'Loan - Group Loan';
                    }
                    elseif ($row->sub_type == 55) {
                        $type = 'Loan - Group Loan Emi';
                    }
                    elseif ($row->sub_type == 56) {
                          $type = 'Loan - Group Loan Panelty';
                    }
                    elseif ($row->sub_type == 57) {
                          $type = 'Loan - File Charge';
                    }
                    elseif ($row->sub_type == 58) {
                        $type = 'Loan - Group Loan File Charge';
                    }
                }

                if($row->type == 6)
                {
                    if($row->sub_type == 61)
                    {
                        $type = "Employee - Salary";
                    }
                }

                if($row->type == 7)
                {
                    
                  $type = "Transferred Branch To Bank ";
                    
                }

                if($row->type == 8)
                {
                    $type = "Transferred Bank To Bank ";
                    
                }

                if($row->type == 9)
                {
                    if($row->sub_type == 90)
                    {
                        $type = "Tds - Commission";
                    }
                }

                if($row->type == 10)
                {
                    if($row->sub_type == 101)
                    {
                        $type = "Rent - Ledger";
                    }
                    elseif ($row->sub_type == 102) {
                        $type = 'Rent - Payment';
                    }
                    elseif ($row->sub_type == 103) {
                          $type = 'Rent - Security';
                    }
                    elseif ($row->sub_type == 104) {
                          $type = 'Rent - Advance';
                    }
                }

                if($row->type == 11)
                {
                    $type ="Demand";
                }

                 if($row->type ==12)
                {
                    if($row->sub_type == 121)
                    {
                        $type = "Salary - Ledger";
                    }
                    elseif ($row->sub_type == 122) {
                        $type = 'Salary - Transfer';
                    }
                    elseif ($row->sub_type == 123) {
                          $type = 'Salary - Advance';
                    }
                }

                if($row->type ==13)
                {
                    if($row->sub_type == 131)
                    {
                        $type = "Demand Advice - Fresh Expense";
                    }
                    elseif ($row->sub_type == 132) {
                        $type = 'Demand Advice - Ta Advance';
                    }
                    elseif ($row->sub_type == 133) {
                          $type = 'Demand Advice - Maturity';
                    }
                    elseif ($row->sub_type == 134) {
                          $type = 'Demand Advice - Prematurity';
                    }
                    elseif ($row->sub_type == 135) {
                        $type = 'Demand Advice - Death Help';
                    }
                    elseif ($row->sub_type == 136) {
                          $type = 'Demand Advice - Death Claim';
                    }
                    elseif ($row->sub_type == 137) {
                          $type = 'Demand Advice - EM';
                    }
                }

                if($row->type == 14)
                {
                    if($row->sub_type == 141)
                    {
                        $type = "Voucher - Director ";
                    }
                    elseif ($row->sub_type == 142) {
                        $type = 'Voucher  - ShareHolder';
                    }
                    elseif ($row->sub_type == 143) {
                          $type = 'Voucher  - Penal Interest';
                    }
                    elseif ($row->sub_type == 144) {
                          $type = 'Voucher  - Bank';
                    }
                    elseif ($row->sub_type == 145) {
                        $type = 'Voucher  - Eli Loan';
                    }
                }

                if($row->type == 15)
                {
                    if($row->sub_type == 151)
                    {
                        $type = 'Director - Deposit';
                    }

                    elseif($row->sub_type == 152)
                    {
                        $type = 'Director - Withdraw';
                    }
                }

                if($row->type == 16)
                {
                     if($row->sub_type == 161)
                    {
                        $type = 'ShareHolder - Deposit';
                    }
                     elseif($row->sub_type == 162)
                    {
                        $type = 'ShareHolder - Transfer';
                    }
                }
                
                if($row->type == 17)
                {
                     if($row->sub_type == 171)
                    {
                        $type = 'Loan From Bank  - Create Loan';
                    }
                     elseif($row->sub_type == 171)
                    {
                        $type = 'Loan From Bank  - Emi Payment';
                    }
                }

                $account = getSsbAccountNumber($row->type_id);

                    ?>?>
    <tr>
        <td>{{$index+1}}</td>
        <td>{{  date("d/m/Y", strtotime($row->entry_date))}}</td>
        <td>@if($row->memberTransactionBranch){{$row->memberTransactionBranch->name}}@endif</td>
         <td>@if($row->memberTransactionBranch){{$row->memberTransactionBranch->branch_code}}@endif</td>
        <td>@if( $row->memberTransactionBranch){{ $row->memberTransactionBranch->sector}}@endif</td>
        <td>@if($row->memberTransactionBranch){{$row->memberTransactionBranch->regan}}@endif</td>
        <td>@if($row->memberTransactionBranch){{$row->memberTransactionBranch->zone}}@endif</td>
        <td>@if($row->memberTransaction){{$row->memberTransaction->member_id}}@endif</td>
   
        <td>@if($row->memberTransaction){{ $row->memberTransaction->first_name.' '.$row->memberTransaction->last_name}}@endif</td>
        
         <td>{{ $type}}</td>
        <td>@if($row->type==3)
                {{getInvestmentDetails($row->type_id)->account_number}} 
            @endif
            @if($row->type==4)
                @if($account)
                    {{$account->account_no}}
                @endif
            @endif
            @if($row->type==5)
                @if($row->sub_type==54 || $row->sub_type==55 || $row->sub_type==56 || $row->sub_type==58)
                 {{getGroupLoanDetail($row->type_id)->account_number}}
                @elseif($row->sub_type==51 || $row->sub_type==52 || $row->sub_type==53 || $row->sub_type==57){
                      {{getLoanDetail($row->type_id)->account_number}}
                 @endif 
             @endif 
               </td>
               <td>{{$row->amount}}</td>
               <td>{{$row->description}}</td>
               <td>@if($row->payment_type=='DR')
                    Debit
                    @endif
                    @if($row->payment_type=='CR')
                    Credit
                    @endif
                </td>
                <td>
                    @if($row->payment_mode==0)
                        Cash
                    @endif
                    @if($row->payment_mode==1)
                        Cheque
                    @endif
                
                    @if($row->payment_mode==2)
                        Online Transfer
                    @endif
                    @if($row->payment_mode==3)
                        SSB Transfer Through 
                    @endif
                    @if($row->payment_mode==4)
                    
                        @if($row->payment_type=='CR')
                           Auto Credit
                        @else
                            Auto Debit
                        @endif
                    @endif    
                
                </td>
        
       
         
    </tr>

    @endforeach
</tbody>                                    
</table>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
window.onafterprint = window.close;
window.print();
</script>