 <table id="fixed_deposit" class="table datatable-show-all">

<thead>

    <tr>
        <th>S/N</th>
		<th>Date</th>
		<th>Type</th>
        <th>Description</th>
		<th>Received Bank</th>
		<th>Received Bank Account</th>
		<th>Payment Bank</th>
		<th>Payment Bank Account</th>
		<th>CR</th>
		<th>DR</th>
		<th>Balance</th>
    </tr>
</thead>                    
<tbody>
	@foreach($data as $value)
			<tr>
				<td>{{$value["DT_RowIndex"]}}</td>
				<td>{{$value["date"]}}</td>
				<td>{{$value["type"]}}</td>
				<td>{{$value["description"]}}</td>
				<td>{{$value["received_bank"]}}</td>
				<td>{{$value["account_number"]}}</td>
				<td>{{$value["payment_bank"]}}</td>
				<td>{{$value["payment_account_number"]}}</td>
				<td>{{$value["cr"]}}</td>
				<td>{{$value["dr"]}}</td>
				<td>{{$value["balance"]}}</td>
			</tr>
		@endforeach
</tbody>
</table>