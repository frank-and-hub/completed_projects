<table border = "1" width = "50%" style="border-collapse: collapse;font-size:12px;">



<thead>
    <tr>
        <th>#</th>
        <th>Request Type</th>
        <th>Branch Name</th>
        <th>Branch Code</th>
		<th>Branch cash In Hand Amount</th>
		  <th>Transfer Amount</th>
          <th>Transfer date</th>
		   <th>transfer mode</th>
		  
        <th>Receive Amount</th>
		      
		<th>Receive bank name</th>
        <th>recieved bank a/c</th>
        <th>bank slip </th>
		 <th>request date</th> 
        <th>Status</th>        
    </tr>
</thead>
<tbody>
@foreach($fundTransfer as $index => $row)  
  <tr>
    <td >{{ $index+1 }}</td>
	
		
	
    <td>@if($row->transfer_type==0)
          Branch to Bank Deposit

        @elseif($row->transfer_type==1)
          Bank To Bank
        else
          None
        @endif</td>
      <td >
      @if(getBranchName($row->branch_id))
        {{ $row['BranchNameByBrachAutoCustom']->name }}
      @else
        N/A
      @endif
    </td>
    <td >{{ $row->branch_code }}</td>
	 <td >{{ $row->micro_day_book_amount }}</td>
	<td >
      @if($row->transfer_type == 0)
        {{ $row->amount }}
      @else
        {{ $row->transfer_amount }}
      @endif
    </td>
	  <td >{{ date("d-m-Y", strtotime(convertDate($row->transfer_date_time))) }}</td> 
	      <td >
      @if($row->transfer_mode == 0)
        Cash
      @else
        Cash
      @endif
    </td>
	<!--
    <td >{{ $row->micro_day_book_amount }}</td>
 

  
    <td >@if($row->transfer_type == 1){{ $row->to_bank_account_number }}@else @if($row->head_office_bank_account_number) {{$row->head_office_bank_account_number}}  @else @endif @endif</td>

    <td >{{ $row->from_cheque_utr_no }}</td>
  
      <td >{{ date("d-m-Y", strtotime(convertDate($row->transfer_date_time))) }}</td>
	    <td >
      @if($row->transfer_mode == 0)
        Cash
      @else
        Cash
      @endif
    </td>
    <td >{{ $row->to_bank_account_number }}</td>
    <td >{{ $row->to_cheque_utr_no }}</td>
	-->
	
 
    <td >
      @if($row->transfer_type == 0)
        {{ $row->amount }}
      @else
        {{ $row->transfer_amount }}
      @endif
    </td>
	  <td >
      @if($row['samraddhBankCustom'] && $row->from_bank_id != '')
        {{ $row['samraddhBankCustom']->bank_name }}
      @else
        N/A
      @endif
    </td>
	
    <td >@if($row->transfer_type == 1){{ $row->to_bank_account_number }}@else @if($row->head_office_bank_account_number) {{$row->head_office_bank_account_number}}  @else @endif @endif</td>
   
       <td  >@if($row['file'])
        {{ $row['file']->file_name}}

        @else
        N/A
        @endif
    </td>
    <td >{{ date("d-m-Y", strtotime(convertDate($row->transfer_date_time))) }}</td>
     <td >
      @if($row->status == 0 )
        Pending
      @elseif( $row->status == 1 )
        Approved
      @else
        '';
      @endif 
    </td>
  </tr>
@endforeach
</tbody>
</table>
