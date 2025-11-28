<!--  <h6 class="card-title font-weight-semibold"></h6>  -->
 
 <table id="interest_on_deposit" class="table datatable-show-all">
	<thead>
		<tr>
			 <th>S/N</th>
            <th>Date</th>
            <th>Member Id</th>
            <th>Member Name</th>
            <th>Account Number</th>
            <th>Type</th>
            <th>CR</th>
            <th>DR</th>
            <th>Balance</th>
		</tr>
	</thead>   
	<tbody>
		@foreach($data as $index =>$row)
			<tr>
				<td>{{$index+1}}</td>
				<td>@if( $row['date']){{ $row['date']}}@endif</td>
				<td>@if( $row['member_id']){{ $row['member_id']}}@endif</td>
				<td>@if( $row['member_name']){{ $row['member_name']}}@endif</td>
				<td>@if( $row['account_number']){{ $row['account_number']}}@endif</td>
				<td>@if( $row['transaction_type']){{ $row['transaction_type']}}@endif</td>
				
				<td>@if( $row['cr']){{ $row['cr']}}@endif</td>
				<td>@if( $row['dr']){{ $row['dr']}}@endif</td>
				
				<td>@if( $row['balance']){{ $row['balance']}}@endif</td>
			</tr>
		@endforeach
	</tbody>


</table>