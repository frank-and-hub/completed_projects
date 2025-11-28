<h6 class="card-title font-weight-semibold">Loan Management | Loan Registration Details</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
 <tr>
    <th>S/N</th>
    <th>Applicant/Group Leader Id</th>
    <th>A/C No.</th>
    <th>BR Name</th>
    <th>BR Code</th>
    <th>SO Name</th>
    <th>RO Name</th>
    <th>ZO Name</th>
    <th>Member Id</th>
    <th>Member Name</th>
    <th>Total Deposit</th>
    <th>Last Recovery Date</th>
    <th>Associate Code</th>
    <th>Associate Name</th>
    <th>Loan Type</th>
    <th>Transfer Amount</th>
	<th>Loan Amount</th>
	<th>File Charge Amount</th>
    <th>Status</th>
    <th>Approved Date</th>
    <th>Application Date</th> 
</tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
    if ( $row->group_activity == 'Group loan application' ) {
        if (isset( $row->groupleader_member_id) ) {
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

    $member = \App\Models\Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);
    $associate_name = $member->first_name.' '.$member->last_name;

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
?>
<tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $applicant_id }}</td>
    <td>{{ $row->account_number }}</td>
   <td>{{ $row['loanBranch']->name }}</td>

    <td>{{ $row['loanBranch']->branch_code }}</td>
    <td>{{ $row['loanBranch']->sector }}</td>
    <td>{{ $row['loanBranch']->regan }}</td>
    <td>{{ $row['loanBranch']->zone }}</td>

    <td>@if(isset( $row['loanMember']->member_id )){{ $row['loanMember']->member_id }}@else @endif</td>
    <td>@if( $row['loanMember']){{ $row['loanMember']->first_name.' '.$row['loanMember']->last_name }}@else @endif</td>
    <td>
     
            {{ getAllDeposit($row['loanMember']->id,$applicationDate) }}
        
    </td>
    <td>{{ date("d/m/Y", strtotime( $row->closing_date)) }}</td>

    <td>{{ getMemberData($row->associate_member_id)->associate_no }}</td>
    <td> {{ $associate_name }} </td>
    @if($row['loan'])
        <td>{{ $row['loan']->name }}</td>
    @else
        <td>Group Loan</td>
    @endif
    <td>{{ $row->deposite_amount }} </td>
	 <td>{{ $row->amount}} </td>
	  <td>{{$row->file_charges}} </td>
    <td>{{ $status }} </td>

    @if($row['approve_date'])
        <td>{{ date("d/m/Y", strtotime( $row['approve_date'])) }} </td>
    @else
        <td>N/A</td>
    @endif     

    <td>{{ date("d/m/Y", strtotime( $row['created_at'])) }}</td>
</tr>
@endforeach
</tbody>
</table>