<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
    <tr>
        <th>S/N</th>
		<th>Date</th>
		<th>Bill Number</th>
		<th>Expense</th>
		<th>Particulars</th>
		<th>Bill Amount</th>
		<th>Amount Due</th>
		<th>Payment</th>    
    </tr>
</thead>
<tbody>
@foreach($memberList as $index => $member) 

  <tr>
    <td  >{{ $index+1 }}</td>
    <td  >{{ date("d/m/Y", strtotime($member->re_date)) }}</td>
    <td  >{{ $member->member_id }}</td>
    <td  >{{ $member->first_name }} {{ $member->last_name }}</td>
	<td  >{{date('d/m/Y', strtotime($member->dob)) }}</td>
    <td  >{{ $member->mobile_no }}</td>
    <td  >{{ $member->associate_code }}</td>
    <td   >{{ getSeniorData($member->associate_id,'first_name').' '.getSeniorData($member->associate_id,'last_name') }}</td>
  </tr>
@endforeach
</tbody>
</table>
