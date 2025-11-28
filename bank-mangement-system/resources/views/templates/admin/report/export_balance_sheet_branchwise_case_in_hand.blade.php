<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>BR Name</th>
			<th>BR Code</th>
			<th>SO Name</th>
			<th>RO Name</th>
			<th>ZO Name</th>
			<th>Micro Balance</th>
			<th>Loan Balance</th>
			<th>Total Balance</th>
		</tr>
	</thead>   
	<tbody>
		@foreach($data as $index =>$row)
			<tr>
				<td>{{$index+1}}</td>
				<td>@if($row->name) {{$row->name}} @endif</td>
				<td>@if($row->branch_code) {{$row->branch_code}} @endif</td>
				<td>@if($row->sector) {{$row->sector}} @endif</td>
				<td>@if($row->regan) {{$row->regan}} @endif</td>
				<td>@if($row->zone) {{$row->zone}} @endif</td>
				<td>@if($row->closing_balance) {{$row->closing_balance}} @endif</td>
				<td>@if($row->loan_closing_balance) {{$row->loan_closing_balance}} @endif</td>
				<td>@if($row->closing_balance) {{$row->closing_balance + $row->loan_closing_balance}} @endif</td>
			</tr>
		@endforeach
	</tbody>


</table>