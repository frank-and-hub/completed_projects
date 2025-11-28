<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Date</th>
			<th>Payment Type</th>
			<th>Member Id</th>
			<th>Member Name</th>
			<th>Amount</th>
		</tr>
	</thead>   
	<tbody>
		@foreach($data as $index =>$row)
			<tr>
				<td>{{$index+1}}</td>
				<td>@if($row->created_at) {{date("d/m/Y", strtotime($row->created_at))}} @endif</td>
				
				@if($row->payment_type == 'CR')
					<td>Credit</td>
				@else
					<td>Debit</td>
				@endif
				<td>@if($row->member_id) {{getMemberData($row->member_id)->member_id}} @endif</td>
				<td>@if($row->first_name) {{getMemberData($row->member_id)->first_name.' '.getMemberData($row->member_id)->last_name}} @endif</td>
				<td>@if($row->amount) {{$row->amount}} @endif</td>
			</tr>
		@endforeach
	</tbody>


</table>