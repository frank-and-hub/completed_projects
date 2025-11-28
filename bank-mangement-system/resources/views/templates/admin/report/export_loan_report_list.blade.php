<h6 class="card-title font-weight-semibold">Report Management | Loan Report</h6>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

    <thead>

        <tr>

             <th style="font-weight: bold;">S/N</th>

            <th style="font-weight: bold;">Staus</th>

            <th style="font-weight: bold;">Applicant Name</th>

             <th style="font-weight: bold;">Applicant Id</th>

            <th style="font-weight: bold;">Applicant Phone Number</th>

            <th style="font-weight: bold;">Membership ID</th>

            <th style="font-weight: bold;">Account No.</th> 

            <th style="font-weight: bold;">Branch</th>   

            <th style="font-weight: bold;">Sector Branch</th>  

            <th style="font-weight: bold;">Member Id</th>                                    

            <th style="font-weight: bold;">Sanctioned Amount</th>

              <th style="font-weight: bold;">Transfer Amount</th>

            <th style="font-weight: bold;">Sanctioned Date</th>

            <th style="font-weight: bold;">EMI Amount</th>

            <th style="font-weight: bold;">No. of Installments</th>

            <th style="font-weight: bold;">Loan Mode</th> 

            <th style="font-weight: bold;">Loan Type</th>     

            <th style="font-weight: bold;">Loan Issued Date</th>

            <th style="font-weight: bold;">Loan Issued Mode</th>

            <th style="font-weight: bold;">Cheque No.</th>

            <th style="font-weight: bold;">Total Recovery Amt(with interest amt)</th> 

            <th style="font-weight: bold;">Total Recovery EMI Till Date</th>

            <th style="font-weight: bold;">Closing Amount</th>

            <th style="font-weight: bold;">Balance EMI</th>

            <th style="font-weight: bold;">EMI Should be received till date</th>

            <th style="font-weight: bold;">Future EMI Due Till Date(Total)</th>

            <th style="font-weight: bold;">Date</th> 

            <th style="font-weight: bold;">Co-Applicant Name</th>

            <th style="font-weight: bold;">Co-Applicant Number</th>

            <th style="font-weight: bold;">Guarantor Name</th>

            <th style="font-weight: bold;">Guarantor Number</th>

            <th style="font-weight: bold;">Applicant Address</th> 

            <th style="font-weight: bold;">First EMI Date</th>

             <th style="font-weight: bold;">Loan End Date</th>

            <th style="font-weight: bold;">Total Deposit Till Date</th>       

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
               if($row->loan_type == 3)
                {
                    if(isset($row->member_id)){

                        if(getMemberData($row->member_id)){

                            $applicantName =  getMemberData($row->member_id)->first_name.' '.getMemberData($row->member_id)->last_name;

                        }else{

                             $applicantName = 'N/A';

                        }

                    }else{

                         $applicantName = 'N/A';

                    }
                }
                else{
                        if(isset($row->applicant_id)){

                        if(getMemberData($row->applicant_id)){

                             $applicantName = getMemberData($row->applicant_id)->first_name.' '.getMemberData($row->applicant_id)->last_name;

                        }else{

                             $applicantName = 'N/A';

                        }

                    }else{

                         $applicantName = 'N/A';

                    }
                 }
            @endphp

            <td>{{ $applicantName }}</td>
            @php
                  if($row->loan_type == 3)
                {
                    $applicantId =  $row->group_loan_common_id;
                }
                else{
                    $applicantId =   App\Models\Member::find($row->applicant_id)->member_id;

                }
            @endphp
            <td>{{$applicantId}}</td>
            @php

            if($row->loan_type == 3)
                {
                   if(isset($row->member_id)){

                        if(getMemberData($row->member_id)){

                            $applicantMobile =  getMemberData($row->member_id)->mobile_no;

                        }else{

                            $applicantMobile =  'N/A';

                        }

                    }else{

                        $applicantMobile =  'N/A';

                    } 
                }
                else{
                    if(isset($row->applicant_id)){

                        if(getMemberData($row->applicant_id)){

                            $applicantMobile =  getMemberData($row->applicant_id)->mobile_no;

                        }else{

                            $applicantMobile =  'N/A';

                        }

                    }else{

                        $applicantMobile =  'N/A';

                    }
                }
                

            @endphp

            <td>{{ $applicantMobile }}</td>
            <td></td>
            <td>{{ $row->account_number }}</td>
            <td>{{ getBranchDetail($row->branch_id)->name }}</td>
            <td>{{ getBranchDetail($row->branch_id)->sector }}</td>
            @php
            if($row->loan_type == 3)
                {
                   $m_id =  getMemberData($row->member_id)->member_id;
                }
                else{
                     $m_id =  getMemberData($row->applicant_id)->member_id; 

                }
            @endphp
             <td>{{ $m_id }}</td>        
            <td>{{ $row->amount }}</td>
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
            <td>{{ $row['loan']->name }}</td>
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
                $pMode = '';
            }
            @endphp
            <td>{{ $pMode }}</td>
            @php
            $mode = App\Models\Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('cheque_dd_no');
            if($mode){
                $cheque = $mode->cheque_dd_no;
            }else{
                $cheque = '';
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
                if(getMemberData($row['LoanCoApplicants'][0]->member_id)){
                    $coappName = getMemberData($row['LoanCoApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanCoApplicants'][0]->member_id)->last_name;
                }else{
                    $coappName = '';
                }
            }else{
                $coappName = '';
            }

            @endphp
            <td>{{ $coappName }}</td>
            @php
            if(count($row['LoanCoApplicants']) > 0){

                if(getMemberData($row['LoanCoApplicants'][0]->member_id)){

                    $coappmName = getMemberData($row['LoanCoApplicants'][0]->member_id)->mobile_no;

                }else{

                    $coappmName = '';

                }

            }else{

                $coappmName = '';

            }
            @endphp
            <td>{{ $coappmName }}</td>
            @php
            if(count($row['LoanGuarantor']) > 0){

                if(getMemberData($row['LoanGuarantor'][0]->member_id)){

                    $gName = getMemberData($row['LoanGuarantor'][0]->member_id)->first_name.' '.getMemberData($row['LoanGuarantor'][0]->member_id)->last_name;

                }else{

                    $gName = '';

                }

            }else{

                $gName = '';

            }
            @endphp
            <td>{{ $gName }}</td>
            @php
            if(count($row['LoanGuarantor']) > 0){
                if(getMemberData($row['LoanGuarantor'][0]->member_id)){
                    $gmNumber = getMemberData($row['LoanGuarantor'][0]->member_id)->mobile_no;
                }else{
                    $gmNumber = '';
                }
            }else{
                $gmNumber = '';
            } 
            @endphp
            <td>{{ $gmNumber }}</td>
            @php
            if(count($row['LoanApplicants']) > 0){

                if(getMemberData($row['LoanApplicants'][0]->member_id)){

                    $address = getMemberData($row['LoanApplicants'][0]->member_id)->address;

                }else{

                    $address = '';

                }

            }else{

                $address = '';

            }
            @endphp
            <td>{{ $address }}</td>
            @php
            $record = App\Models\LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->orderby('created_at','asc')->first('created_at');
            if($record && isset($record)){
                $feDate = date("d/m/Y", strtotime(convertDate($record->created_at)));
            }else{
                $feDate = '';
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

