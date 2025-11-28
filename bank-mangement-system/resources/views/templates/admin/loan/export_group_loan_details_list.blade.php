<h6 class="card-title font-weight-semibold">Loan Management | Loan Registration Details</h6>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<?php

  //echo "<pre>";print_r($reports);die;

?>





<thead>

 <tr>

    <th>S/N</th>

    <th>Applicant/Group Leader Id</th>

    <th>Application Number</th>

     <th>BR Name</th>

    <th>BR Code</th>

    <th>SO Name</th>

    <th>RO Name</th>

    <th>ZO Name</th>

    <th>Member Id</th>

    <th>Member Name</th>

    <th>Associate Code</th>

    <th>Loan</th>

    <th>Transfer Amount</th>
    <th>Loan Amount</th>
    <th>File Charge Amount</th>
    <th>Bank Name</th>
    <th>Bank Account Number</th>
    <th>Ifsc Code</th>

    <th>Status</th>

    <th>Application Date</th>  

</tr>

</thead>

<tbody>

@foreach($data as $index => $row)  

<?php

    if ( $row->group_activity == 'Group loan application' ) {

        if ( $row->groupleader_member_id ) {

            $applicant_id = \App\Models\Member::find($row->groupleader_member_id)->member_id;

        } else {

            $applicant_id = '';

        }

    } else {

        if ( $row->applicant_id ) {

            $applicant_id = \App\Models\Member::find($row->applicant_id)->member_id;

        } else {

            $applicant_id = '';

        }

    }



    if($row->status == 0){

        $status = 'Pending';

    }elseif($row->status == 1){

        $status = 'Approved';

    }elseif($row->status == 2){

        $status = 'Rejected';

    }elseif($row->status == 3){

        $status = 'Clear';

    }elseif($row->status == 4){

        $status = 'Due';

    }
    if(isset($row['loanMemberBankDetails2']->bank_name))
    {
        $bankName = $row['loanMemberBankDetails2']->bank_name;
    }
    else{
        $bankName ='N/A';
    }
   
    if(isset($row['loanMemberBankDetails2']->account_no))
    {
        $bankAccount = $row['loanMemberBankDetails2']->account_no;
    }
    else{
        $bankAccount ='N/A';
    }
  
    if(isset($row['loanMemberBankDetails2']->ifsc_code))
    {
        $ifscCode = $row['loanMemberBankDetails2']->ifsc_code;
    }
    else{
        $ifscCode ='N/A';
    }
   

?>

<tr>

    <td>{{ $index+1 }}</td>
    
    
    
    <td>{{$applicant_id}}</td>
    <td>{{$row->group_loan_common_id}}</td>
     <td>{{ $row['gloanBranch']->name }} </td>

    <td>{{ $row['gloanBranch']->branch_code }} </td>

    <td>{{ $row['gloanBranch']->sector }} </td>

    <td>{{ $row['gloanBranch']->regan }} </td>

    <td>{{ $row['gloanBranch']->zone }} </td>

  


    <td>{{ $row['loanMember']->member_id }}</td>

    <td>{{ $row['loanMember']->first_name.' '.$row['loanMember']->last_name }}</td>

    <td>{{ getAssociateId($row->applicant_id) }}</td>

    @if($row['loan'])

        <td>{{ $row['loan']->name }}</td>

    @else

        <td>Group Loan</td>

    @endif

    <td>{{ $row->deposite_amount }} </td>
     <td>{{ $row->amount}} </td>
      <td>{{$row->file_charges}} </td>
      <td>{{$bankName}} </td>
      <td>{{$bankAccount}} </td>
      <td>{{$ifscCode}} </td>
     
    <td>{{ $status }} </td>

    <td>{{ date("d/m/Y", strtotime( $row['created_at'])) }}</td>

</tr>

@endforeach

</tbody>

</table>