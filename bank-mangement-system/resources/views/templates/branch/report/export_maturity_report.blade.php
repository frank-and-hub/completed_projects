<h6 class="card-title font-weight-semibold">Report Management | Maturity Report</h6>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<?php

//echo "<pre>";print_r($data);die;

?>





<thead>

    <tr>

        <th  >S/N</th>
		<th >Company Name</th>  
		<th >Branch Name</th>  
		{{-- <th >Branch code</th>   --}}
		{{-- <th >Branch zone</th>   --}}
         <th  >Account No.</th> 


        <th  >Customer ID</th>
        <th  >Member ID</th>
		<th  >Member Name</th>

        <th  >Plan</th>

        <th  >Tenure</th>

        <th  >Deposite Amount</th>                                        
        <th  >Deno</th>                                        
        <th  >Maturity Type</th>
        <th  >Maturity Amount</th>
       	<th  >Maturity Payable Amount</th>

        <th  >Maturity Date</th> 

        <th  >Associate code</th>

        <th  >Associate Name</th>

        <th  >Opening Date</th>

        <th  >Due Amount</th>     

        <th  >Total Amount</th>
        
          <th >Payment Mode</th>
									
		<th >Payment Date</th>
		
		
		
		<th >Cheque No./RTGS No.</th>

		<th >RTGS Charge</th>

		<th >SSB Account No.</th>	

		<th >Bank Name</th>
		
		<th >Bank Account Number</th>      
	    

    </tr>

</thead>



<tbody>

    @foreach($data as $index => $row)

   <?php

		if($row['sumdeposite']->sum('deposit')){
						
			$current_balance = $row['sumdeposite']->sum('deposit');
		}else{
			$current_balance = 0;
		}
	
	$transaction =(isset($row['demandadvice']['demandAmount'][0])) ? $row['demandadvice']['demandAmount'][0] : '0';
	$transaction2 = isset($row['demandadvice']['demandTransactionAmount'][0]) ? $row['demandadvice']['demandTransactionAmount'][0] : 0;
	$ac = App\Models\SavingAccount::where('member_id',$row['member']->id)->first();

	$rtgs_chrg = isset($transaction) ?? 0 ;
	
	?>



     <tr>

    <td>{{ $index+1 }}</td>
	   <td>
		{{$row['company']?$row['company']->name:'N/A'}}
	   </td>

     <td>{{ $row['branch']->name }}</td>
     {{-- <td>{{ $row['branch']->branch_code }}</td> --}}
     {{-- <td>{{  $row['branch']->zone }}</td> --}}
     <td>{{ $row->account_number }}</td>


     <td>{{$row['member']->member_id}}</td>
     <td>{{$row['memberCompany']->member_id}}</td>
	 <td>{{$row['member']->first_name}}{{$row['member']->last_name}}</td> 

       <td>{{$row['plan']->name}}</td>

     <td>{{$row->tenure}}</td>

      <td>{{$row['sumdeposite']->sum('deposit')}}</td>
      <td>{{number_format((float)$row->deposite_amount, 2, '.', '')}}</td>
	  @php
	 ;
			$payment_type = [
				0=>'Expense',
				1=>'Maturity',
				2=>'PreMaturity',
				3=>'Death Help',
				4=>'Emergancy Maturity',
			];
	  @endphp
      <td>{{(isset($payment_type[$row['demandadvice']->payment_type])) ? $payment_type[$row['demandadvice']->payment_type] : 'N/A'}}</td>


       <td>{{number_format((float)$row->maturity_amount, 2, '.', '')}}</td>
              <td>@if($row['demandadvice']){{number_format((float) $row['demandadvice']->maturity_amount_payable, 2, '.', '')}}@endif</td>           

     
     <td>@if($row->maturity_date){{date("d/m/Y", strtotime($row->maturity_date))}}@endif</td>  

    

    <td>{{ $row['associateMember']->associate_no??'N/A' }}</td>

    <td>{{ (isset($row['associateMember']->first_name)) ? $row['associateMember']->first_name.' '.$row['associateMember']->last_name : ''}}</td>

     <td>{{ date("d/m/Y", strtotime($row->created_at)) }}</td>

      <td>{{number_format((float)$row->due_amount, 2, '.', '') }}</td>

       <td>{{ $current_balance??'N/A'}}</td>

	   @php
		$payment_mode =  [
			0=>'Cash',
			1=>'Cheque',
			2=>'Online Transfer',
			3=>'SSB Transfer',
		];
	   @endphp

    <td>{{$payment_mode[$row['demandadvice']->payment_mode ]??'N/A'}}</td>		
				
				
	   <td>@if($row['demandadvice']) {{date('d/m/Y', strtotime($row['demandadvice']->date))}} @endif </td>
	   <td>@if($row['demandadvice']) @if($row['demandadvice']->payment_mode == 1)	 @if($transaction) {{$transaction->cheque_no??'N/A'}} @endif @elseif($row['demandadvice']->payment_mode == 2) @if($transaction) {{$transaction2->transction_no??'N/A'}} @endif @endif  @endif
		<td>@if($row['demandadvice']) @if( $row['demandadvice']->payment_mode == 2)	 @if($transaction) {{$transaction->amount??'N/A'}} @endif @endif @endif </td>

	   <td> @if($row['demandadvice'])@if($row['demandadvice']->payment_mode == 3)	@if($ac){{$ac->account_no}} @else{{$row['demandadvice']->ssb_account??'N/A'}} @endif @endif @endif </td>
	   <td> @if($row['demandadvice']) @if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)	{{$row['demandadvice']->bank_name??'N/A'}} @endif @endif </td>
	   <td> @if($row['demandadvice']) @if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)	{{$row['demandadvice']->bank_account_number??'N/A'}} @endif @endif </td>

    @endforeach

</tbody>

</table>

