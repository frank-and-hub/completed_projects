<h6 class="card-title font-weight-semibold">Report Management | Maturity Report</h6>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<?php

//echo "<pre>";print_r($data);die;

?>





<thead>

    <tr>

          <th  style="font-weight: bold;">S/N</th>
          <th style="font-weight: bold;">Branch Name</th>
          <th style="font-weight: bold;">Branch Code</th>
          <th style="font-weight: bold;">Zone</th>

         <th  style="font-weight: bold;">Account No.</th> 

        <th  style="font-weight: bold;">Member Name</th>

        <th  style="font-weight: bold;">Member ID</th>

        <th  style="font-weight: bold;">Plan</th>

        <th  style="font-weight: bold;">Tenure</th>

          <th  style="font-weight: bold;">Deposite Amount</th>

        <th  style="font-weight: bold;">Deno</th>                                    
        <th  style="font-weight: bold;">Maturity Type</th>
        <th  style="font-weight: bold;">Maturity Amount</th>
        <th  style="font-weight: bold;">Maturity Payable Amount</th>


        <th  style="font-weight: bold;">Maturity Date</th> 

        <th  style="font-weight: bold;">Associate code</th>

        <th  style="font-weight: bold;">Associate Name</th>

        <th  style="font-weight: bold;">Opening Date</th>

        <th  style="font-weight: bold;">Due Amount</th>     
         <th  style="font-weight: bold;">Interest</th>     

        <th  style="font-weight: bold;">Final Payable Amount</th>
        <th style="font-weight: bold;">Payment Mode</th>

         
									
									<th style="font-weight: bold;">Payment Date</th>
									
									
									
									<th style="font-weight: bold;">Cheque No./RTGS No.</th>

                                    <th style="font-weight: bold;">RTGS Charge</th>

									<th style="font-weight: bold;">SSB Account No.</th>	

									<th style="font-weight: bold;">Bank Name</th>
									
									<th style="font-weight: bold;">Bank Account Number</th>

    </tr>

</thead>



<tbody>

    @foreach($data as $index => $row)

   <?php
	if($row['demandadvice']){
	$transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);;
	
	
	$ac = App\Models\SavingAccount::where('member_id',$row['member']->id)->first();

	$rtgs_chrg = App\Models\Transcation::where('id',$row['demandadvice']->transction_no)->first();
	}
	$interest='N/A';
	if(isset($row['demandadvice']->id))
	{
		$interest = App\Models\AllHeadTransaction::where('head_id',36)->where('type',13)->whereIn('sub_type',[136,133,134])->where('type_transaction_id',$row['demandadvice']->id)->first();
		if($interest)
		{
			$interest= $interest->amount;
		}
		
	}
	if($row->id){
    $investmentAmount =  App\Models\Daybook::where('investment_id',$row->id)->whereIn('transaction_type',[2,4])->sum('deposit');
    $current_balance = $investmentAmount;
	}else{
	     $current_balance = 0;
	}
	if(isset($row['demandadvice']->final_amount))       
     {
        $FINAL =number_format((float)$row['demandadvice']->final_amount, 2, '.', '');;

     }
     else{
        $FINAL = '';
     }
     $branchDetail = getBranchDetail($row->branch_id);
	?>



     <tr>

    <td>{{ $index+1 }}</td>
    <td>{{ $branchDetail->name }}</td>
    <td>{{ $branchDetail->branch_code }}</td>
    <td>{{ $branchDetail->zone }}</td>

     <td>{{ $row->account_number }}</td>

    <td>{{$row['member']->first_name}}{{$row['member']->last_name}}</td> 

     <td>{{$row['member']->member_id}}</td>

     <td>{{getPlanDetail($row->plan_id)->name}}</td>

     <td>{{$row->tenure}}</td>
     <TD>{{$current_balance}}</TD>
      <td>{{number_format((float)$row->deposite_amount, 2, '.', '')}}</td>
      <td>@if($row['demandadvice'])
				
				@if($row['demandadvice']->payment_type==0)
					Expense
				@elseif($row['demandadvice']->payment_type ==1)
				
					Maturity
									
				
				@elseif($row['demandadvice']->payment_type ==2)
				
					PreMaturity
									
				
				@elseif($row['demandadvice']->payment_type ==3)
				
					Death Help
					
				@elseif($row['demandadvice']->payment_type ==4)
				
					Emergancy Maturity
					
				@else
					N/A
				
				
				@endif
				@else
					N/A
				
			@endif	
	   </td>

       <td>{{number_format((float)$row->maturity_amount, 2, '.', '')}}</td>

              <td>@if($row['demandadvice']){{number_format((float) $row['demandadvice']->maturity_amount_payable, 2, '.', '')}}@endif</td>           


     <td>@if($row->maturity_date){{date('d/m/Y', strtotime($row->created_at. ' + '.($row->tenure).' year'))}}@endif</td>   

    

    <td>{{ getSeniorData($row->associate_id,'associate_no') }}</td>

    <td>{{ getSeniorData($row->associate_id,'first_name').' '.getSeniorData($row->associate_id,'last_name')}}</td>

     <td>{{ date("d/m/Y", strtotime($row->created_at)) }}</td>

      <td>{{number_format((float)$row->due_amount, 2, '.', '') }}</td>
       <td>{{number_format((float)$interest, 2, '.', '') }}</td>

       <td>{{$FINAL}}</td>
        <td>@if($row['demandadvice'])
				
					@if($row['demandadvice']->payment_mode == 0)
					 Cash
					
					@elseif($row['demandadvice']->payment_mode == 1)
					
						Cheque
					
					@elseif($row['demandadvice']->payment_mode == 2)
					
						Online Transfer
					
					@elseif($row['demandadvice']->payment_mode == 3)
					
						SSB Transfer
					@endif
			@endif
					</td>
				
				
				
				
	   <td>@if($row['demandadvice'])
	   {{date('d/m/Y', strtotime($row['demandadvice']->date))}}
		   @endif
	   </td>
	   <td>@if($row['demandadvice'])
			@if($row['demandadvice']->payment_mode == 1)	
				@if($transaction)
					 {{$transaction->cheque_no}}
				@endif
				
			@elseif($row['demandadvice']->payment_mode == 2)
				@if($transaction)
				{{$transaction->transction_no}}
				@endif
			@endif
		   @endif
		     <td>@if($row['demandadvice'])
			@if( $row['demandadvice']->payment_mode == 2)	
				@if($transaction)
				<?php 	$chrg = App\Models\AllHeadTransaction::where('transction_no',$transaction->transction_no)->first();?>
				@if($chrg)
					 {{$chrg->amount}}@endif
				@endif
				
			
			@endif
		   @endif
	   </td>

	   <td>@if($row['demandadvice'])
			@if($row['demandadvice']->payment_mode == 3)	
			@if($ac)
						{{$ac->account_no}}
	
					@else
						{{$row['demandadvice']->ssb_account}}
					
					@endif
			@endif
		   @endif
	   </td>
	   <td>@if($row['demandadvice'])
			@if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)	
				@if($transaction)
					 {{$transaction->transction_bank_from}}
				@endif
				
			
			@endif
		   @endif
	   </td>
	   <td>@if($row['demandadvice'])
			@if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)	
				@if($transaction)
					 {{$transaction->transction_bank_ac_from}}
				@endif
				
			
			@endif
		   @endif
	   </td>
     

    @endforeach

</tbody>

</table>

