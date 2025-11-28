<table id="report_detail" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Date</th>
			<th>Branch</th>
			<th>V.NO./AC.NO./M.ID</th>
			<th>Name</th>
			<th>Payment Type</th>
			<th>Transaction Type</th>
			<th>CR</th>
			<th>DR</th>
			<th>Balance</th>
		</tr>
	</thead>
	<tbody>
	
	@foreach($data as $index =>$row)
		<tr>
			<td>{{$index + 1}}</td>
			<td>{{$row["date"]}}</td>
			<td>{{$row["branch"]}}</td>
			<td>{{$row["voucher_number"]}}</td>
			<td>{{$row["owner_name"]}}</td>
			<td>{{$row["payment_type"]}}</td>
			<td>{{$row["transaction_type"]}}</td>
			<td>{{$row["cr"]}}</td>
			<td>{{$row["dr"]}}</td>
			<td>{{$row["balance"]}}</td>
		</tr>
	@endforeach
	</tbody>	
								  

</table>