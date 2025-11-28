<!-- <h6 class="card-title font-weight-semibold">Account Head Report</h6> -->


<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
	<thead>
		<tr>
		<th>S/N</th>
		<th>BR Name</th>
		<th>BR Code</th>
		<th>SO Name</th>
		<th>RO Name</th>
		<th>ZO Name</th>
		<th>Total Member</th>
		<th>Amount</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $index => $row)
			<tr> 
				<td>{{$index+1}}</td>
				<td>@if( $row['branch']){{ $row['branch']}}@endif</td>
				<td>@if( $row['branch_code']){{ $row['branch_code']}}@endif</td>
				<td>@if( $row['sector_name']){{ $row['sector_name']}}@endif</td>
				<td>@if( $row['region_name']){{ $row['region_name']}}@endif</td>
				<td>@if( $row['zone_name']){{ $row['zone_name']}}@endif</td>
				@if($row['total_member'] == "")
				<td>0.00</td>
				@else
				<td>@if( $row['total_member']){{ $row['total_member']}}@endif</td>
				@endif
				<td>@if( $row['amount']){{ $row['amount']}}@endif</td>
			</tr>	
		@endforeach
	</tbody>
</table>

