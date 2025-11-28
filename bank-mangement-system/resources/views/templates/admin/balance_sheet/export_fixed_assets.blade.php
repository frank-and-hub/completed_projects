<!-- <h6 class="card-title font-weight-semibold">Account Head Report</h6> -->


<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
	<thead>
		<tr>
		<th>S/N</th>
	   <!--  <th>Account Head</th>
		<th>Sub Account Head</th> -->
		<th>Party Name</th>
		<th>Transaction type</th>
		<th>Voucher Number</th>
		<th>CR</th>
		<th>DR</th>

		<th>Balance</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $index => $row)
			<tr> 
				<td>{{$index+1}}</td>
				<td>{{$row['party_name']}}</td>
				<td>{{$row['transaction_type']}}</td>
				<td>{{$row['voucher_number']}}</td>
				<td>{{$row['cr']}}</td>
				<td>{{$row['dr']}}</td>
				<td>{{$row['amount']}}</td>
			</tr>	
		@endforeach
	</tbody>
</table>

