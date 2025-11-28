<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">



<thead>
  <tr>
    <th>#</th>
    <th>Plan</th>
    <th>Member</th>
    <th>Member Id</th>
    <th>Associate Code</th>
    <th>Account Number</th>
    <th>Created Date</th>
  </tr>
</thead>
<tbody>
@foreach($investmentMemberLists as $index => $investment)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $investment['plan']->name }}</td>
    <td>{{ $investment['member']->first_name }} {{ $investment['member']->last_name }}</td>
    <td>{{ $investment['member']->member_id }}</td>
    <td>{{ $investment['member']->associate_code }}</td>
    <td>{{ $investment->account_number }}</td>
    <td>{{ date("d/m/Y", strtotime($investment->created_at)) }}</td>
  </tr>
@endforeach
</tbody>
</table>
