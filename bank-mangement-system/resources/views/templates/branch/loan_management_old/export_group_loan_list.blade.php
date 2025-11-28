<h6 class="card-title font-weight-semibold">Loan Management | Loan List</h6>

<table border = "1" width = "100%" >

<?php

  //echo "<pre>";print_r($reports);die;

?>





<thead>

 <tr>

    <th>S/N</th>

    <th>BR Name</th>
    <th>BR Code</th>
    <th>SO Name</th>
    <th>RO Name</th>
    <th>ZO Name</th> 
    <th>A/C No.</th>

    <th>Member Name</th>

    <th>Member ID</th>
                                <th>Total Deposit</th>

    <th>Loan Type</th>

    <th>Tenure</th>
      <th>Emi Amount</th>
	<th>Transfer Amount</th>
    <th>Loan Amount</th>

    <th>File Charges</th>

    <th>File Charge Payment Mode</th>

    <th>Outstanding Amount</th>

    <th>Last Recovery Date</th>

    <th>Associate Code</th>

    <th>Associate Name</th>

    <th>Total Payment</th>

    <th>Application Date</th>

    <th>Approved Date</th>

    <th>Sanction Date</th>


    <th>Status</th>   

</tr>

</thead>

<tbody>

@foreach($data as $index => $row)  

<?php

    if($row->loan_type == 1){

        $plan_name = 'Personal Loan';

    }elseif($row->loan_type == 2){

        $plan_name = 'Staff Loan(SL)';

    }elseif($row->loan_type == 3){

        $plan_name = 'Group Loan';        

    }elseif($row->loan_type == 4){

        $plan_name = 'Loan against Investment plan(DL) ';    

    }else{

        $plan_name = 'Group Loan';

    }



    if($row->emi_option == 1){

        $tenure =  $row->emi_period.' Months';

    }elseif ($row->emi_option == 2) {

        $tenure =  $row->emi_period.' Weeks';

    }elseif ($row->emi_option == 3) {

        $tenure =  $row->emi_period.' Days';

    }



    if($row->status == 0){

        $status = 'Pending';

    }else if($row->status == 1){

        $status = 'Approved';

    }else if($row->status == 2){

        $status = 'Rejected';

    }else if($row->status == 3){

        $status = 'Clear';

    }else if($row->status == 4){

        $status = 'Due';

    }

     $totalbalance = $row->emi_period * $row->emi_amount;
     $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;

    $member = \App\Models\Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);

    $associate_name = $member->first_name.' '.$member->last_name;

    $applicationDate=date('Y-m-d');

?>

<tr>

    <td>{{ $index+1 }}</td>

    <td>{{ $row['gloanBranch']->name }} </td>

    <td>{{ $row['gloanBranch']->branch_code }} </td>

    <td>{{ $row['gloanBranch']->sector }} </td>

    <td>{{ $row['gloanBranch']->regan }} </td>

    <td>{{ $row['gloanBranch']->zone }} </td>



    <td>{{ $row->account_number }}</td>

    <td>{{ $row['loanMember']->first_name.' '.$row['loanMember']->last_name }} </td>

    <td>{{ $row['loanMember']->member_id }} </td>
    <td> {{getAllDeposit($row['loanMember']->id,$applicationDate)}} </td>

    <td>{{ $plan_name }}</td>

    <td>{{ $tenure }} </td>
     <td>{{ $row->emi_amount }}</td>

    <td>{{ $row->deposite_amount }}</td>
	
	 <td>{{ $row->amount}}</td>

    <td>{{ $row->file_charges }}</td>

    @if($plan_name != 'Group Loan')

        <td>{{ fileChargePaymentMode($row->id,10) }} </td>

    @else

        <td>{{ fileChargePaymentMode($row->id,6) }} </td>

    @endif

    <td>{{ $Finaloutstanding_amount }} </td>

    <td>{{ date("d/m/Y", strtotime( $row->closing_date)) }} </td>

    <td>{{ getMemberData($row->associate_member_id)->associate_no }} </td>

    <td>{{ $associate_name }} </td>

    <td>{{loanOutsandingAmount($row->id,$row->account_number) }} </td>

    <td>{{ date("d/m/Y", strtotime( $row['created_at'])) }} </td>

    @if($row['approved_date'])
        <td>{{ date("d/m/Y", strtotime( $row['approved_date'])) }} </td>
    @else
        <td>N/A</td>
    @endif

    @if($row['approve_date'])
        <td>{{ date("d/m/Y", strtotime( $row['approve_date'])) }} </td>
    @else
        <td>N/A</td>
    @endif

    <td>{{ $status }} </td>s

</tr>

@endforeach

</tbody>

</table>

