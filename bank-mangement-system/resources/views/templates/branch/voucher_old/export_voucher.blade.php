<h6 class="card-title font-weight-semibold">Voucher Management | Voucher Report</h6>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
    <tr>
        <th>S/N</th>
         <th>BR Name</th> 
        <th>BR Code</th>
        <th>SO Name</th>
        <th>RO Name</th>
        <th>ZO Name</th>
        <th>Date</th> 
        <th>Received Mode</th>
        <th>Received Amount</th>
        <th>Account Head</th>
        <th>Director</th>     
        <th>ShareHolder</th>

         <th>Employee Code</th> 
        <th>Employee Name</th>
        <th>Bank Name</th>
        <th>Bank A/c</th>
        <th>Eli Loan</th>                                    
<!--         <th>DayBook </th>
 -->        <th>Cheque No.</th> 
        <th>Cheque Date </th>
        <th>UTR/Transaction No. </th>
        <th>Transaction Date</th>
        <th>Party Bank Name </th>     
        <th>Party Bank A/c</th>

        <th>Receive Bank</th>
        <th>Receive Bank A/c</th>
        <th> Transaction Slip</th>
    </tr>
</thead>

<tbody>
  @foreach($data as $index => $row)  
   <?php
        $branch = $row['rv_branch']->name;
        $branch_code = $row['rv_branch']->branch_code;
        $so_name = $row['rv_branch']->sector;
        $ro_name = $row['rv_branch']->regan;
        $zo_name = $row['rv_branch']->regan;
        $date = date("d/m/Y", strtotime($row->date));
        $rv_amount = number_format((float)$row->amount, 2, '.', '');
        $account_head = getAcountHeadNameHeadId($row->account_head_id);
        $emp_code = '';
          if($row['rv_employee'])
        {
           $emp_code = $row['rv_employee']->employee_code;
        }
        $emp_name ='';
         if($row['rv_employee'])
        {
            $emp_name = $row['rv_employee']->employee_name;
        }
         
       
        

  ?>
    <tr>
        <td>{{$index+1}}</td>
        <td>{{$branch}}</td>
        <td>{{$branch_code}}</td>
        <td>{{$so_name}}</td>
        <td>{{$ro_name}}</td>
        <td>{{$zo_name}}</td>
        <td>{{$date}}</td>
        
        <td>@if($row->received_mode==1) Cheque @elseif($row->received_mode==2) Online @else Cash @endif</td>
        <td>{{$rv_amount}}</td>
        <td>{{$account_head}}</td>
        <td> @if($row->type==1)
             {{getAcountHeadNameHeadId($row->director_id)}} @else N/A @endif</td>
         <td> @if($row->type==2)
             {{getAcountHeadNameHeadId($row->shareholder_id)}} @else N/A @endif</td>
        <td>@if($emp_code){{$emp_code}}@endif</td>
        <td>@if($emp_name){{$emp_name}}@endif</td>
        <td>@if($row->type==4)
            {{getSamraddhBankAccountId($row->bank_ac_id)->bank_name}}
            @else N/A @endif</td>
        <td>@if($row->type==4)
            {{getSamraddhBankAccountId($row->bank_ac_id)->account_no}}
            @else N/A @endif</td>
        <td>@if($row->eli_loan_id)
            {{ getAcountHeadNameHeadId($row->eli_loan_id)}}
            @else N/A @endif</td>
<!--         <td >@if($row->received_mode==0)  @if($row->daybook_type==1) Cash @else Cash @endif @else N/A @endif</td>  
 -->        <td> @if($row->received_mode==1) {{$row['rvCheque']->cheque_no }}@elseif($row->received_mode==2) {{$row->online_tran_no }}@else N/A @endif</td> 
        <td>@if($row->received_mode==1) {{ date("d/m/Y", strtotime($row->cheque_date)) }} @elseif($row->received_mode==2) {{ date("d/m/Y", strtotime($row->online_tran_date)) }} @else N/A @endif </td>
        <td>@if($row->received_mode!=0) {{$row->online_tran_no }} @else N/A @endif </td>
        <td>@if($row->received_mode!=0) {{date("d/m/Y", strtotime($row->online_tran_date))}} @else N/A @endif </td>
        <td>@if($row->received_mode==1) {{ $row->cheque_bank_name }} @elseif($row->received_mode==2) {{ $row->online_tran_bank_name }} @else N/A @endif </td>
        <td>@if($row->received_mode==1) {{$row->cheque_bank_ac_no }} @elseif($row->received_mode==2) {{ $row->online_tran_bank_ac_no }} @else N/A @endif </td>
        <td>@if($row->received_mode!=0) {{ getSamraddhBank($row->receive_bank_id)->bank_name }} @else N/A @endif </td>
        <td>@if($row->received_mode!=0) {{ getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no }} @else N/A @endif </td>
         <td>@if($row->slip)
            {{$row->slip}}
            @else N/A @endif</td>
    </tr>
     
  @endforeach
</tbody>
</table>
