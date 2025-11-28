<h6 class="card-title font-weight-semibold">Report Management | Loan Report</h6>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

    <thead>

        <tr>

            <th>S/N</th>

            <th>Staus</th>

            <th>Applicant Name</th>

            <th>Applicant Phone Number</th>

            <th>Membership ID</th>

            <th>Account No.</th> 

            <th>Branch</th>   

            <th>Sector Branch</th>                                    

            <th>Sanctioned Amount</th>

            <th>Sanctioned Date</th>

            <th>EMI Rate</th>

            <th>No. of Installments</th>

            <th>Loan Mode</th> 

            <th>Loan Type</th>     

            <th>Loan Issued Date</th>

            <th>Loan Issued Mode</th>

            <th>Cheque No.</th>

            <th>Total Recovery Amt(with interest amt)</th> 

            <th>Total Recovery EMI Till Date</th>

            <th>Closing Amount</th>

            <th>Balance EMI</th>

            <th>EMI Should be received till date</th>

            <th>Future EMI Due Till Date(Total)</th>

            <th>Date</th> 

            <th>Co-Applicant Name</th>

            <th>Co-Applicant Number</th>

            <th>Guarantor Name</th>

            <th>Guarantor Number</th>

            <th>Applicant Address</th> 

            <th>First EMI Date</th>

             <th>Loan End Date</th>

            <th>Total Deposit Till Date</th>      

        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td>{{ $key+1 }}</td>
            @php
            if($row->status == 0){
                $status = 'Inactive';
            }elseif($row->status == 1){
                $status = 'Approved';
            }elseif($row->status == 2){
                $status = 'Rejected';
            }elseif($row->status == 3){
                $status = 'Completed';
            }elseif($row->status == 4){
                $status = 'ONGOING';
            }
            @endphp
            <td>{{ $status }}</td>
            @php
                if(count($row['LoanApplicants']) > 0 ){
                    $applicantName = getMemberData($row['LoanApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanApplicants'][0]->member_id)->last_name;
                }
            @endphp
            <td>{{ $applicantName }}</td>
            @php
            if(count($row['LoanApplicants']) > 0){
                $applicantMobile = getMemberData($row['LoanApplicants'][0]->member_id)->mobile_no;
            }
            @endphp

            <td>{{ $applicantMobile }}</td>
            <td>N/A</td>
            <td>{{ $row->account_number }}</td>
            <td>{{ getBranchDetail($row->branch_id)->name }}</td>
            <td>{{ getBranchDetail($row->branch_id)->sector }}</td>
            <td>{{ $row->deposite_amount }}</td>
            <td>{{ date("d/m/Y", strtotime(convertDate($row->approve_date))) }}</td>
            <td>{{ $row->emi_amount }}</td>
            <td>{{ $row->emi_period }}</td>
            @php
                if($row->emi_option == 1){
                    $eType = 'Months';
                }elseif($row->emi_option == 2){
                    $eType = 'Weeks';
                }elseif($row->emi_option == 3){
                    $eType = 'Daily';
                }
            @endphp
            <td>{{ $eType }}</td>
            <td>Group Loan</td>
            <td>{{ date("d/m/Y", strtotime(convertDate($row->created_at))) }}</td>
            @php
            $mode = App\Models\Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('payment_mode');
            if($mode){
                if($mode->payment_mode == 1){
                    $pMode = 'Cash';
                }elseif($mode->payment_mode == 2){
                    $pMode = 'Cheque';
                }elseif($mode->payment_mode == 3){
                    $pMode = 'DD';
                }elseif($mode->payment_mode == 4){
                    $pMode = 'Online Transaction';
                }elseif($mode->payment_mode == 5){
                    $pMode = 'SSB';
                }
            }else{
                $pMode = 'N/A';
            }
            @endphp
            <td>{{ $pMode }}</td>
            @php
            $mode = App\Models\Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('cheque_dd_no');
            if($mode){
                $cheque = $mode->cheque_dd_no;
            }else{
                $cheque = 'N/A';
            }
            @endphp
            <td>{{ $cheque }}</td>
            @php
            $amount = App\Models\LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');
            @endphp
            <td>{{ $amount }}</td>
            <td>{{ $row->credit_amount }}</td>
            @php
            if($row->emi_option == 1){
                $closingAmountROI = $row->due_amount*$row->ROI/1200;  
            }elseif($row->emi_option == 2){
                $closingAmountROI = $row->due_amount*$row->ROI/5200;
            }elseif($row->emi_option == 3){
                $closingAmountROI = $row->due_amount*$row->ROI/36500;
            }
            $closingAmount = round($row->due_amount+$closingAmountROI);
            @endphp
            <td>{{ $closingAmount }}</td>
            @php
            $d1 = explode('-',$row->created_at);
            $d2 = explode('-',date("Y-m-d"));
            $firstMonth = $d1[1];
            $secondMonth = $d2[1];
            $monthDiff = $secondMonth-$firstMonth;
            $ramount = App\Models\LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');
            $camount  = $monthDiff*$row->emi_amount;
            if($ramount < $camount){
                $isPending = 'Yes';
            }else{
                $isPending = 'No';
            }
            @endphp
            <td>{{ $isPending }}</td>
            @php
            $d1 = explode('-',$row->created_at);
            $d2 = explode('-',date("Y-m-d"));
            $firstMonth = $d1[1];
            $secondMonth = $d2[1];
            $monthDiff = $secondMonth-$firstMonth;
            $camount  = $monthDiff*$row->emi_amount;
            @endphp
            <td>{{ $camount }}</td>
            <td>{{ $camount }}</td>
            <td>{{ date("d/m/Y") }}</td>
            @php
            if(count($row['LoanCoApplicants']) > 0){
                $coappName = getMemberData($row['LoanCoApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanCoApplicants'][0]->member_id)->last_name;
            }else{
                $coappName = 'N/A';
            }
            @endphp
            <td>{{ $coappName }}</td>
            @php
            if(count($row['LoanCoApplicants']) > 0){
                $coappmName = getMemberData($row['LoanCoApplicants'][0]->member_id)->mobile_no; 
            }else{
                $coappmName = 'N/A';
            }
            @endphp
            <td>{{ $coappmName }}</td>
            @php
            if(count($row['LoanGuarantor']) > 0){
                $gName = getMemberData($row['LoanGuarantor'][0]->member_id)->first_name.' '.getMemberData($row['LoanGuarantor'][0]->member_id)->last_name;
            }else{
                $gName = 'N/A';
            } 
            @endphp
            <td>{{ $gName }}</td>
            @php
            if(count($row['LoanGuarantor']) > 0){
                $gmNumber = getMemberData($row['LoanGuarantor'][0]->member_id)->mobile_no; 
            }else{
                $gmNumber = 'N/A';
            } 
            @endphp
            <td>{{ $gmNumber }}</td>
            @php
            if(count($row['LoanApplicants']) > 0){
                $address = getMemberData($row['LoanApplicants'][0]->member_id)->address;
            }else{
                $address = 'N/A';
            }
            @endphp
            <td>{{ $address }}</td>
            @php
            $record = App\Models\LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->orderby('created_at','asc')->first('created_at');
            if($record && isset($record)){
                $feDate = date("d/m/Y", strtotime(convertDate($record->created_at)));
            }else{
                $feDate = 'N/A';
            }
            @endphp
            <td>{{ $feDate }}</td>
            <td>{{ date("d/m/Y", strtotime(convertDate($row->closing_date))) }}</td>
            @php
            $amount = App\Models\LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');
            @endphp
            <td>{{ $amount }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

