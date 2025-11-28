<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
      <th>#</th>
      <th>Plan</th>
      <th>Member</th>
      <th>Created Date</th>
  </tr>
</thead>
<tbody>
@foreach($investmentMemberLists as $index => $member)

  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $member->plan->name }}</td>
    <td>{{ $member->member->first_name }} {{ $member->member->last_name }}</td>
      <td>{{ date("d/m/Y", strtotime( str_replace( '-', '/', $member->created_at ) ) ) }}</td>
  </tr>
@endforeach
</tbody>
</table>
