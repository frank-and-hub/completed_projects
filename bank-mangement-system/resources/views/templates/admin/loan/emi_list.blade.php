

<div class="content">
    

        <div class="row" > 
            <div class="col-lg-12" id="print_passbook">                
             
                        <div class="table-responsive">
                         
                            <table id="emi_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                     <th>S/N</th>
                                    <th>Transaction ID</th>
									 <th>Account Number</th>
                                    <th>Transaction Date</th> 
                                    <th>Sanction Amount</th>
                                    {{-- <th>Penalty</th> --}}
                                    <th>Deposit</th>
                                    <th>ROI Amount</th>
                                    <th>Principal Amount</th>
                                    <!-- <th>Opening balance</th> -->
                                    <th>Action</th>
                                     
                                </tr>
                            </thead>  
                            <tbody>
                                @foreach($record as $index => $row)
                                    <?php //echo "<pre>"; print_r($row->loan_sub_type); die; ?>
                                    <tr>
									  <td>{{$index+1}}</td>
									  <td>{{$row->id}}</td>
                                       <td>{{$row->account_number}}</td>
                                        <td>{{date("d/m/Y", strtotime($row->payment_date))}}</td>
                                        <td>@if($row->loan_sub_type == 0){{$row->amount}}@else N/A @endif</td>
                                        {{-- <td>{{ getLoanEmiPenaltyRecord($row->id) }}</td> --}}

                                        <td>@if($row->loan_sub_type == 0){{$row->deposit}}@else N/A @endif</td>
                                        <td>@if($row->loan_sub_type == 0){{$row->roi_amount}} @else N/A @endif</td>
                                        <td>@if($row->loan_sub_type == 0){{ $row->principal_amount}} @else N/A @endif</td>
                                        <!-- <td>@if($row->loan_sub_type == 0){{$row->opening_balance}} @else N/A @endif</td> -->
                                        <td>
                                            @if($index+1 == 1 )
                                                <a href="{{url('admin/delete/emi/'.$row->loan_id.'/'.$row['loan_plan']->loan_type.'/'.$row->id) }}" class="btn btn-primary delete-loan-emi">Delete
                                                </a>
                                            @else 
                                                N/A 
                                            @endif
                                        </td>
                                    </tr>
                                    
                                
                                @endforeach
                                   
                                        
                                    
                            </tbody>
                            </tbody>
                        </table>
                        
                </div> 
            </div> 
        </div>
   
</div>


