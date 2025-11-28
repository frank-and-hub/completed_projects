
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
    <tr>
    <th colspan="8"><h3> Employee's Transferred Salary List</h3></th>
    </tr>
    </thead>
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th >S.No</th>
                                        <th >Category</th> 
                                        <th >Designation</th>
                                        <th >BR Name</th> 
                                        <th>BR Code</th>
                                        <th>SO Name</th>
                                        <th>RO Name</th>
                                        <th>ZO Name</th>
                                        <th >Employee Name </th>
                                        <th >Employee Code </th>
                                        <th >Gross Salary</th>
                                        <th >Leave</th>
                                        <th >Total Salary</th>
                                        <th >Deduction</th>
                                        <th >Incentive / Bonus </th>
                                        <th >Advance Salary</th>
                                        <th >Settle Salary</th>
                                        <th >Transferred Salary </th> 
                                        <th >Transferred In </th> 
                                        <th >Employee SSB</th>  
                                        <th >Employee Bank Name</th>
                                        <th >Employee Bank A/c </th>
                                        <th >Employee Bank IFSC </th>
                                        <th >Transferred Date </th> 
                                        <th > Bank Name </th> 
                                        <th > Bank A/C </th>
                                        <th >Payment Mode</th> 
                                        <th >Cheque No.</th> 
                                        <th >Online UTR/tractions No.</th>  
                                        <th >RTGS/NEFT Charge</th> 
                                        <th >Is Transferred</th> 
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
$category = 'All';

                $branch=$row['salary_branch']->name;

                $branch_code=$row['salary_branch']->branch_code;
                $sector_name=$row['salary_branch']->sector;
                $region_name=$row['salary_branch']->regan;
                $zone_name=$row['salary_branch']->zone;
                if($row->category==1)
                {
                    $category = 'On-rolled';
                }
                if($row->category==2)
                {
                    $category = 'Contract'; 
                }
                $category_name=$category;
                
                $employee_name=$row['salary_employee']->employee_name;
                $employee_code=$row['salary_employee']->employee_code;
                $advance_payment="N/A";
                $settle_amount="N/A";
                if($row->settle_amount>0)
                {
                    $advance_payment=number_format((float)$row->advance_payment, 2, '.', ''); 
                    $settle_amount=number_format((float)$row->settle_amount, 2, '.', ''); 
                } 
                
                if($row->designation_id)
                {
                    $designation_name= getDesignationData('designation_name',$row->designation_id)->designation_name;
                }
                else
                {
                  $designation_name='All';  
                }

                $fix_salary=number_format((float)$row->fix_salary, 2, '.', ''); 
                $leave=number_format((float)$row->leave, 1, '.', '');
                $total_salary=number_format((float)$row->total_salary, 2, '.', ''); 
                $deduction=number_format((float)$row->deduction, 2, '.', ''); 
                $incentive_bonus=number_format((float)$row->incentive_bonus, 2, '.', ''); 
                $transferred_salary='N/A';
                if($row->transferred_salary>0)
                {
                    $transferred_salary=number_format((float)$row->transferred_salary, 2, '.', ''); 
                }
                
                $transferred_in= 'N/A';
                if($row->transferred_in==1)
                {
                    $transferred_in= 'SSB';
                }
                if($row->transferred_in==2)
                {
                    $transferred_in= 'Bank';
                }
                if($row->transferred_in==0 && $row->transferred_in!=NULL)
                {
                    $transferred_in= 'Cash';
                }
                if($row->transferred_date)
                {
                    $transferred_date= date("d/m/Y", strtotime($row->transferred_date));
                }
                else
                {
                  $transferred_date='N/A';  
                }
                $employee_ssb=$row->employee_ssb;
                $employee_bank=$row->employee_bank;
                $employee_bank_ac=$row->employee_bank_ac;
                $employee_bank_ifsc=$row->employee_bank_ifsc;
               /** $company_ssb=$row->company_ssb;*/
               $company_bank='N/A';
                $company_bank_ac='N/A';
               if($row->transferred_in==2)
              {
                $bankfrmDetail = \App\Models\SamraddhBank::where('id',$row->company_bank)->first();
                $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id',$row->company_bank_ac)->first();
                $company_bank=$bankfrmDetail->bank_name;
                $company_bank_ac=$bankacfrmDetail->account_no;
              }
                $payment_mode= 'N/A';
                if($row->transferred_in==2)
                {
                  if($row->payment_mode==1)
                  {
                      $payment_mode= 'Cheque';
                  }
                  if($row->payment_mode==2)
                  {
                      $payment_mode= 'Online';
                  }  
                }
                $company_cheque_id='N/A';
                if($row->payment_mode==1 && $row->transferred_in==2)
                {
                  $c= \App\Models\SamraddhCheque::where('id',$row->company_cheque_id)->first();
                  $company_cheque_id=$c->cheque_no;
                }
                $online_transaction_no='N/A';
                $online='N/A';
                $neft_charge='N/A';
                
                if($row->payment_mode==2 && $row->transferred_in==2)
                {
                  $online_transaction_no=$row->online_transaction_no;
                  $online=$row->online_transaction_no;
                  $neft_charge=number_format((float)$row->neft_charge, 2, '.', ''); 
                }
                if($row->is_transferred==0)
                {
                    $transfer_status= 'No';
                }
                else
                {
                    $transfer_status= 'Yes';
                } 
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $category}}</td>
    <td>{{ $designation_name}}</td>
    <td>{{ $branch}}</td>
    <td>{{ $branch_code}}</td>
    <td>{{ $sector_name}}</td>
    <td>{{ $region_name}}</td>
    <td>{{ $zone_name}}</td>
    <td>{{ $employee_name}}</td>
    <td>{{ $employee_code}}</td>
    <td>{{ $fix_salary}}</td>
    <td>{{ $leave}}</td>
    <td>{{ $total_salary}}</td>
    <td>{{ $deduction}}</td>
    <td>{{ $incentive_bonus}}</td>
    <td>{{ $advance_payment}}</td>
    <td>{{ $settle_amount}}</td>
    <td>{{ $transferred_salary}}</td>
    <td>{{ $transferred_in}}</td>
    <td>{{ $employee_ssb}}</td>
    <td>{{ $employee_bank}}</td>
    <td>{{ $employee_bank_ac}}</td>
    <td>{{ $employee_bank_ifsc}}</td>
    <td>{{ $transferred_date}}</td>
    <td>{{ $company_bank}}</td>
    <td>{{ $company_bank_ac}}</td>
    <td>{{ $payment_mode}}</td>
    <td>{{ $company_cheque_id}}</td>
    <td>{{ $online_transaction_no}}</td>
    <td>{{ $neft_charge}}</td>
    <td>{{ $transfer_status}}</td> 
  </tr>

@endforeach
</tbody>
</table>
