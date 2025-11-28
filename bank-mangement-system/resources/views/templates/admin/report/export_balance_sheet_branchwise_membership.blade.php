<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			<th>S/N</th>
			<th>Member Id</th>
			<th>Member Name</th>
			<th>Amount</th>
		</tr>
	</thead>   
	<tbody>
		@foreach($data as $index =>$row)
		<?php 
			$detail = App\Models\Member::where('member_id',$row->member_id)->first();
		?>
			<tr>
				<td>{{$index+1}}</td>
				<td>@if($row) {{$row->member_id}} @endif</td>
				<td>@if($row) {{$detail->first_name.' '.$detail->last_name}} @endif</td>
				<td>10.00</td>
			</tr>
		@endforeach
	</tbody>


</table>