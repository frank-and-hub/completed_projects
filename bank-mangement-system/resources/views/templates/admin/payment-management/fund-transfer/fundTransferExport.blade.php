<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">



<thead>
    <tr>
        <th>#</th>
        <th>Request Type</th>
        <th>Branch Name</th>
        <th>Branch Code</th>
        <th>Loan Daybook Amount</th>
        <th>Transfer Amount</th>
        <th>Transfer Date</th>
        <th>From Bank</th>
        <th>To Bank</th>
        <th>Transfer bank Account</th>
        <th>Transfer Mode</th>
        <th>Transfer Cheque No/UTR No</th>
        <th>RTGS/NEFT Charge </th>
        <th>Receive Bank Name </th>
        <th>Receive Bank A/c </th>
        <th>Receive Cheque No/UTR No</th>
        <th>Receive Amount</th>
        <th>Bank Slip</th>
        <th>Status</th>        
    </tr>
</thead>
<tbody>
@foreach($fundTransfer as $index => $row)  
  <tr>
    <td>{{ $index+1 }}</td>

    <td>
      @if($row->transfer_type == 0)
        Branch To Ho
      @else
        Bank To Bank
      @endif
    </td>

    <td>
      @if(getBranchName($row->branch_id))
        {{ getBranchName($row->branch_id)->name }}
      @else
        N/A
      @endif
    </td>
    <td>{{ $row->branch_code }}</td>
    <td>{{ $row->loan_day_book_amount }}</td>
    <td>
      @if($row->transfer_type == 0)
        {{ $row->amount }}
      @else
        {{ $row->transfer_amount }}
      @endif
    </td>
    <td>{{ date("d-m-Y", strtotime(convertDate($row->transfer_date_time))) }}</td>
    <td>
      @if(getSamraddhBank($row->from_bank_id) && $row->from_bank_id != '')
        {{ getSamraddhBank($row->from_bank_id)->bank_name }}
      @else
        N/A
      @endif
    </td>
    <td>
      @if(getSamraddhBank($row->from_bank_id) && $row->from_bank_id != '')
        {{ getSamraddhBank($row->from_bank_id)->bank_name }}
      @else
        N/A
      @endif
    </td>
    <td>{{ $row->from_bank_account_number }}</td>
    <td>
      @if($row->transfer_type == 0)
        @if($row->transfer_mode == 0)
          Loan
        @else
          Micro
        @endif
      @else
        @if($row->btb_tranfer_mode == 0)
          Cheque
        @else
          Online Transfer
        @endif
      @endif

      @if($row->transfer_mode == 0)
        Loan
      @else
        Micro
      @endif
    </td>
    <td>{{ $row->from_cheque_utr_no }}</td>
    <td>{{ $row->rtgs_neft_charge }}</td>
    <td>
      @if(getSamraddhBank($row->to_bank_id) && $row->to_bank_id != '')
        {{ getSamraddhBank($row->to_bank_id)->bank_name }}
      @else
        N/A
      @endif
    </td>
    <td>{{ $row->to_bank_account_number }}</td>
    <td>{{ $row->to_cheque_utr_no }}</td>
    <td>
      @if($row->transfer_type == 0)
        {{ $row->amount }}
      @else
        {{ $row->receive_amount }}
      @endif
    </td>
     <td>@if(getFirstFileData($row->bank_slip_id))
        {{ getFirstFileData($row->bank_slip_id)->file_name}}

        @else
        N/A
        @endif
    </td>
    <td>
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
