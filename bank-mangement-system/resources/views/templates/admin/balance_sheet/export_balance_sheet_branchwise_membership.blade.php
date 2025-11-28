<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
<table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Member Id</th>
			<th>Member Name</th>
			<th>CR</th>
			<th>DR</th>
			<th>Balance</th>
		</tr>
	</thead>   
	<tbody>
	@foreach($data as $value)
			<tr>
				<td>{{$value["DT_RowIndex"]}}</td>
				<td>{{$value["member_id"]}}</td>
				<td>{{$value["member_name"]}}</td>
				<td>{{$value["cr"]}}</td>
				<td>{{$value["dr"]}}</td>
				<td>{{$value["balance"]}}</td>
			</tr>
		@endforeach
	</tbody>


</table>