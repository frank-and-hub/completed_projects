<h3> Rent Payment List</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr> 
                                <th >S.No</th> 
                                        <th >BRd Name</th> 
                                        <th >BR Code</th>
                                        <th >SO Name</th>
                                        <th >RO Name</th>
                                        <th >ZO Name</th>
                                        <th >Rent Type</th>
                                        <th >Period From </th>
                                        <th >Period To</th>
                                        <th >Address</th>
                                        <th >Owner Name</th>
                                        <th  >Owner Mobile Number</th>
                                        <th >Owner Pan Card</th>
                                        <th >Owner Aadhar Card </th>
                                        <th >Owner SSB account </th> 
                                        <th > Owner Bank Name</th>
                                        <th  >Owner Bank A/c No.</th>
                                        <th  >Owner IFSC code </th>
                                        <th >Security amount</th>                                   
                                        <th >Yearly Increment</th>
                                        <th >Office Square feet area</th>
                                        <th >Rent</th>
                                        <th >Actual Transfer Amount</th>   
                                        <th >Transfer Amount</th>
                                        <th >Advance Payment</th>  
                                        <th >Settle Amount</th>  
                                        <th >Transfer Status</th>
                                        <th >Transfer Date</th>                                      
                                        <th >Transfer Mode</th>  
                                        <th >V No.</th>  
                                        <th >V Date</th>  
                                        <th >Bank Name</th>  
                                        <th >Bank A/No.</th> 
                                        <th >Payment Mode</th>   
                                        <th >Cheque No. </th>  
                                        <th >Online Transaction No.</th>  
                                        <th >NEFT Charge</th> 
                                        <th >Employee Code</th>
                                        <th >Employee Name</th>
                                        <th >Employee Designation</th>
                                        <th >Employee Mobile No.</th> 
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
                $actual='N/A';
                if($row->actual_transfer_amount){
                    $actual = number_format((float)$row->actual_transfer_amount + $row->tds_amount, 2, '.', '');
                }
                $current_advance_payment='N/A';
                if($row->current_advance_payment){
                    $current_advance_payment = number_format((float)$row->current_advance_payment, 2, '.', '');
                }
                $advance='N/A';
                if($row->advance_payment){
                    $advance = number_format((float)$row->advance_payment, 2, '.', '');
                }
                $settle='N/A';
                if($row->settle_amount){
                    $settle = number_format((float)$row->settle_amount, 2, '.', '');
                }
                $transfer_date='N/A';
                if($row->transferred_date){
                    $transfer_date = date("d/m/Y", strtotime(convertDate($row->transferred_date)));
                }
                $v_date='N/A';
                if($row->v_date){
                    $v_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                }
                $v_no='N/A';
                if($row->v_no){
                    $v_no =$row->v_no;
                }
                $mode = 'N/A';
                if($row->transfer_mode == 1){
                    $mode = 'SSB';
                }
                if($row->transfer_mode == 2){
                    $mode = 'Bank';
                }
                $bank='N/A';
                if($row->company_bank_id) {
                    $bank =$row['rentBank']->bank_name;
                }
                $bank_ac='N/A';
                if($row->company_bank_ac_id){
                    $bank_ac =$row['rentBankAccount']->account_no;
                }
                $payment_mode = 'NA'; 
                if($row->payment_mode==1){
                    $payment_mode = 'Cheque';
                }
                if($row->payment_mode==0){
                    $payment_mode = 'Online';
                }
                $cheque='N/A';
                if($row->company_cheque_id){
                    $cheque =$row->company_cheque_no;
                }
                $online_no='N/A';
                if($row->online_transaction_no){ 
                    $online_no =$row->online_transaction_no;
                }
                $neft='N/A';
                if($row->neft_charge){
                    $neft =$row->neft_charge;
                }
                $status = 'Pending';
                if($row->status == 1){
                    $status = 'Transferred ';
                }
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $row['rentBranch']->name }}</td>
    <td>{{ $row['rentBranch']->branch_code }}</td>
    <td>{{ $row['rentBranch']->sector }}</td>
    <td>{{ $row['rentBranch']->regan }}</td>
    <td>{{ $row['rentBranch']->zone }}</td>
    <td>{{ getAcountHead($row['rentLib']->rent_type)}}</td>
    <td>{{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)))}}</td>
    <td>{{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)))}}</td>
    <td>{{$row['rentLib']->place}}</td>
    <td>{{$row['rentLib']->owner_name}}</td>
    <td>{{$row['rentLib']->owner_mobile_number}}</td>
    <td>{{$row['rentLib']->owner_pen_number}}</td>
    <td>{{$row['rentLib']->owner_aadhar_number}}</td>
    <td>
        @if($row['rentSSB'])
        {{$row['rentSSB']->account_no}}
        @endif
    </td>

    <td>{{$row->owner_bank_name}}</td>
    <td>{{$row->owner_bank_account_number}}</td>
    <td>{{$row->owner_bank_ifsc_code}}</td>
    <td>{{number_format((float)$row->security_amount, 2, '.', '')}}</td>
    <td>{{number_format((float)$row->yearly_increment, 2, '.', '').'%'}}</td>

    <td>{{$row['rentLib']->office_area}}</td>
    <td>{{number_format((float)$row->rent_amount, 2, '.', '')}}</td>
    <td>{{$actual}}</td>
    <td>{{number_format((float)$row->transfer_amount, 2, '.', '')}}</td>
    <td>{{$advance}}</td>
    <td>{{$settle}}</td>
    <td>{{$status}}</td>

    <td>{{$transfer_date}}</td>
    <td>{{$mode}}</td>
    <td>{{$v_no}}</td>
    <td>{{$v_date}}</td>
    <td>{{$bank}}</td>
    <td>{{$bank_ac}}</td>
    <td>{{$payment_mode}}</td>

    <td>{{$cheque}}</td>
    <td>{{$online_no}}</td>
    <td>{{$neft}}</td>

    <td>{{$row['rentEmp']->employee_code}}</td>
    <td>{{$row['rentEmp']->employee_name}}</td>
    <td>{{getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name}}</td>
    <td>{{$row['rentEmp']->mobile_no}}</td>

  </tr>

@endforeach
</tbody>
</table>
