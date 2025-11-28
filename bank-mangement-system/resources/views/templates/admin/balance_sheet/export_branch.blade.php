<!-- <h6 class="card-title font-weight-semibold">Account Head Report</h6> -->

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
	<thead>
		<tr>
		<th>S/N</th>
		<th>BR Name</th>
		<th>BR Code</th>
		{{-- <th>SO Name</th> --}}
		{{-- <th>RO Name</th> --}}
		{{-- <th>ZO Name</th> --}}
		{{-- <th>Total Member</th> --}}
		<th>Opening Balance</th>
		<th>Amount</th>
		<th>Company</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $index => $row)
			<tr> 
				<td>{{$index+1}}</td>
				<td>{{ isset($row['branch']) ? $row['branch'] : 'N/A'}}</td>
				<td>{{ isset($row['branch_code']) ? $row['branch_code'] : 'N/A'}}</td>
				{{-- <td>{{ isset($row['sector_name']) ? $row['sector_name'] : 'N/A'}}</td> --}}
				{{-- <td>{{ isset($row['region_name']) ? $row['region_name'] : 'N/A'}}</td> --}}
				{{-- <td>{{ isset($row['zone_name']) ? $row['zone_name'] : 'N/A'}}</td> --}}
				{{-- <td>{{ isset($row['total_member']) ? $row['total_member'] : '0'}} </td> --}}
				<td>{{ isset($row['opening_balance']) ? $row['opening_balance'] : '0'}} </td>
				<td>&#8377; {{ isset($row['amount']) ? $row['amount'] : 'N/A' }}</td>
				<td>{{ isset($row['company']) ? $row['company'] : 'N/A' }}</td>
			</tr>	
		@endforeach
	</tbody>
</table>

