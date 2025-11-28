@extends('templates.admin.master')

@section('content')
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Debit Cards SSB History</h6>
				</div>
				<div class="">
					<table class="table ">
						<thead>
							<tr>
								<th>S/N</th> 
								<th>Card No.</th>
								<th>Member SSB Account</th>
								<th>Status</th>
								<th>Reason</th>
								<th>Approve Date</th>
								<th>Reject/Block Date</th>  
							</tr>
						</thead>
						<tbody>
							@php
							$i = 1
							@endphp
							@foreach($debit_data as $data)
								<tr>
									<td>{{$i}}</td>
									<td>{{$data->card_no}}</td>
									<td>{{$data->account_no}}</td>
									<td>
										@if($data->is_block == 2)
											<span class="badge bg-danger">Blocked</span>
										@else
											@switch($data->status)
												@case(0)
													<span class="badge bg-warning">Pending</span>
												@break
												@case(1)
													<span class="badge bg-success">Approved</span>
												@break
												@case(2)
													<span class="badge bg-danger">Rejected</span>
												@break
											@endswitch
										@endif
									</td>
									<td>
										@if($data->reason != '')
											{{$data->reason}}
										@else
											--------
										@endif
									</td>
									<td>
										@if($data->approve_date != '')
											<?=date('d/m/Y H:i:s', strtotime($data->approve_date));?>
										@else
											--------
										@endif
									</td>
									<td>
										@if($data->reject_block_date != '')
											<?=date('d/m/Y H:i:s', strtotime($data->reject_block_date));?>
										@else
											--------
										@endif
									</td>
								</tr>
								@php
								$i++
								@endphp
							@endforeach
						</tbody>                    
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@stop