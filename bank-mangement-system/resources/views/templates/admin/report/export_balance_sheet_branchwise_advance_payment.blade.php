<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Owner/Employee Name</th>
			<th>Employee Code</th>
			<th>Amount</th>
		</tr>
	</thead>   
	<tbody>
		@foreach($data as $index =>$row)
			<tr>
				<td>{{$index+1}}</td>
				@if($row->sub_payment_type == "3")
				<td>{{$row->owner_name}}</td>
				<td></td>
				@else
				<td>{{$row->employee_name}}</td>
				<td>{{$row->employee_code}}</td>	
				@endif
				
				@if($row->amount!= "" && $row->amount!= "null")
					<td>@if($row->amount) {{$row->amount}} @endif</td>
				@else
					<td>@if($row->advanced_amount) {{$row->advanced_amount}} @endif</td>
				@endif	
			</tr>
		@endforeach
	</tbody>


</table>