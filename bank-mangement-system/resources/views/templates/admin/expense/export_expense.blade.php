<div class="">
                        <table id="expense_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Branch Code</th>
                                    <th>Branch Name</th>
									
									<th>Bill Date</th> 
									<th>Payment Date</th> 
                                    <th>Account Head</th> 
									<th>Sub Head1</th> 
									<th>Sub Head2</th>  
									<th>Particulars</th>
                                    <th>Receipt</th>
									<th>Amount</th>
                                </tr>
                            </thead> 
                            <tbody>
                                
@foreach($data as $index => $row)  
<?php $url=URL::to("/core/storage/images/expense/".$row->receipt.""); ?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $row['branch']->branch_code }}</td>
    <td>{{ $row['branch']->name }}</td>
    <td>{{date("d/m/Y ", strtotime($row->payment_date)) }}</td>
    <td>{{date("d/m/Y ", strtotime($row->bill_date)) }} </td>
    <td>@if($row->account_head_id){{ getAcountHeadNameHeadId($row->account_head_id) }}@endif</td>
    <td>@if($row->sub_head1){{ getAcountHeadNameHeadId($row->sub_head1) }} @endif</td>
    <td>@if($row->sub_head2){{ getAcountHeadNameHeadId($row->sub_head2) }}@endif </td>
    <td>{{ $row->particular }}</td>
    <td><a href="{{$url}}" target="blank"> {{$row->receipt }}</a>  </td>
    <td>{{ $row->amount }} </td>
    
    
  </tr>
@endforeach
</tbody>                   
                        </table>
                    </div>