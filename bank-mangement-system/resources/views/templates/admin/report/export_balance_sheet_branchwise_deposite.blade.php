 <table id="fixed_deposit" class="table datatable-show-all">

<thead>

    <tr>
        <th>S/N</th>
        <th>Member Id</th>
        <th>Member Name</th>
		<th>Amount</th>
    </tr>
</thead>                    
<tbody>
	@foreach($data as $index=>$row)
		<tr>
			<td>{{$index+1}}</td>
			<td>{{ getMemberData($row->member_id)->member_id}}</td>
			<td>{{getMemberData($row->member_id)->first_name. ' '.getMemberData($row->member_id)->last_name}}</td>
			<td>{{$row->amount}}</td>
		</tr>
	@endforeach
</tbody>
</table>