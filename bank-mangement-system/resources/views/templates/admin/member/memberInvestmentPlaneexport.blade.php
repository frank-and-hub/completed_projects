<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
    <tr>
        <th width = "5%">#</th>
        <th width = "10%">Member Name</th>
        <th width = "10%">Member ID</th>
        <th width = "10%">Plan Name</th>
        <th width = "10%">Account No</th>
        <th width = "10%">Balance</th>
        <th width = "10%">Deposit Amount</th>
        <th width = "10%">Transaction Type</th>
        <th width = "10%">Transaction Amount</th>
        <th width = "10%" >Date</th>
    </tr>
</thead>
<tbody>
@foreach($memberList as $index => $member)  
  <tr>
    <td width = "5%">{{ $index+1 }}</td>
    <td width = "10%">{{ $member->first_name }}{{ $member->last_name }}</td>
    <td width = "10%">{{ $member->member_id }}</td>
    <td width = "10%">{{ $member->name }} </td>
    <td width = "10%">{{ $member->account_number }}</td>
    <td width = "10%">{{ $member->current_balance }}</td>
    <td width = "10%">{{ $member->deposite_amount }}</td>
    <td width = "10%">{{ $member->transaction_type }}</td>
    <td width = "10%">{{ $member->amount }}</td>
    <td width = "10%">{{ date("d/m/Y", strtotime($member->created_at)) }}</td>
  </tr>
@endforeach
</tbody>
</table>
