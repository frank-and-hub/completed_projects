@extends('templates.admin.master')

@section('content')
<style type="text/css">
  
</style>

<div class="content" >   
       
      <div class="row" id="advice"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td style="padding:10px;width: 60%;">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3 text-center" >Advice</h3>
                  <div class="row">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 20px">
                      <tr>
                         <td style="padding: 7px;width:12%"> Company Name : </td>
                        <td style="padding: 7px;width:20%"> {{$row['company']->name}}</td>
                        <td style="padding: 7px ;width:12%"> BR Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_branch']->name}}</td>                        
                        <td style="padding: 7px;width:12%"> SO Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_branch']->sector}}</td> 
                      </tr> 
                      <tr>
                        <td style="padding: 7px;width:12%"> RO Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_branch']->regan}} </td>
                        <td style="padding: 7px;width:12%"> ZO Name : </td>
                        <td style="padding: 7px;width:20%"> {{$row['salary_branch']->zone}}</td>
                        <td style="padding: 7px;width:12%"> Category : </td>
                        <?php
                        $category = 'All';
                        if($row->category==1)
                        {
                            $category = 'On-rolled';
                        }
                        if($row->category==2)
                        {
                            $category = 'Contract'; 
                        }
                        ?>
                        
                        <td style="padding: 7px;width:20%"> {{$category}}</td> 
                      </tr> 
                      <tr>
                        <td style="padding: 7px;width:12%"> Designation : </td>
                        <?php
                        if($row->designation_id)
                        {
                            $designation_name= getDesignationData('designation_name',$row->designation_id)->designation_name;
                        }
                        else
                        {
                          $designation_name='All';  
                        }
                        ?>
                        <td style="padding: 7px;width:20%">{{$designation_name}} </td>
                        <td style="padding: 7px;width:12%"> Employee Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_employee']->employee_name}} </td>
                        <td style="padding: 7px;width:12%"> Employee Code : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_employee']->employee_code}} </td> 
                      </tr>
                      <tr>
                      <td style="padding: 7px;width:12%"> BR Code : </td>
                        <td style="padding: 7px;width:20%">{{$row['salary_branch']->branch_code}} </td>  
                        <td style="padding: 7px;width:12%"> Gross Salary : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->fix_salary, 2, '.', '')}} &#x20B9; </td>                      
                        <td style="padding: 7px;width:12%"> Leave : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->leave, 1, '.', '')}} </td>
                        
                      </tr>
                      <tr>
                      <td style="padding: 7px;width:12%"> Total Salary</td>
                        <td style="padding: 7px;width:20%"> {{number_format((float)$row->total_salary, 2, '.', '')}} &#x20B9;</td> 
                        <td style="padding: 7px;width:12%"> Deduction  : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->deduction, 2, '.', '')}} &#x20B9; </td>
                        <td style="padding: 7px;width:12%"> Incentive / Bonus : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->incentive_bonus, 2, '.', '')}} &#x20B9; </td>
                        
                        
                      </tr>
                      <tr>
                      <td style="padding: 7px;width:12%"> Payable Amount : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->paybale_amount, 2, '.', '')}} &#x20B9; </td>
                        <td style="padding: 7px;width:12%"> ESI Amount : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->esi_amount, 2, '.', '')}} &#x20B9; </td>
                        <td style="padding: 7px;width:12%"> PF Amount : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->pf_amount, 2, '.', '')}} &#x20B9; </td>
                       
                      </tr>
                      <tr>
                      <td style="padding: 7px;width:12%"> TDS Amount : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->tds_amount, 2, '.', '')}} &#x20B9; </td>
                        <td style="padding: 7px;width:12%"> Final Payable Salary : </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->actual_transfer_amount, 2, '.', '')}} &#x20B9; </td>

                        <td style="padding: 7px;width:12%"> Transferred salary </td>
                        <td style="padding: 7px;width:20%"> {{number_format((float)$row->transferred_salary, 2, '.', '')}} &#x20B9;</td>
                        
                        
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Section Amount Mode: </td>
                        <?php
                        $transferred_in= 'N/A';
                        if($row->transferred_in==1)
                        {
                            $transferred_in= 'SSB';
                        }
                        if($row->transferred_in==2)
                        {
                            $transferred_in= 'Bank';
                        }
                        if($row->transferred_in==0)
                        {
                            $transferred_in= 'Cash';
                        } 
                      ?>


                        <td style="padding: 7px;width:20%"> {{ $transferred_in}}</td>
                        <?php
                        $payment_mode= 'N/A';
                        $cheque_no= 'N/A';
                        $online_transaction_no= 'N/A';
                        $neft_charge= number_format((float)$row->neft_charge, 2, '.', '')??'N/A';
                        $bank_name= 'N/A';
                        $bank_ac= 'N/A';
                        $emp_bank_name= 'N/A';
                        $emp_bank_ac= 'N/A';
                        $emp_bank_ifsc= 'N/A';
                        $emp_ssb_ac= 'N/A';
                        if($row->employee_ssb_id>0)
                        {
                          $emp_ssb_ac= $row['salarySSB']->account_no;
                        }
                        if($row->transferred_in==2)
                        {
                            $bank_name= $row['salaryBank']->bank_name;
                            $bank_ac= $row['salaryBankAccount']->account_no;
                            $emp_bank_name= $row->employee_bank;
                            $emp_bank_ac= $row->employee_bank_ac;
                            $emp_bank_ifsc= $row->employee_bank_ifsc;
                        }

                        if($row->payment_mode==1 && $row->payment_mode!=NULL)
                        {
                            $payment_mode= 'Cheque';
                            $cheque_no = $row['salaryCheque']->cheque_no;
                        }
                        
                        if($row->payment_mode==2 && $row->payment_mode!=NULL)
                        {
                            $payment_mode= 'Online';
                            $online_transaction_no= $row->online_transaction_no;
                            $neft_charge= number_format((float)$row->neft_charge, 2, '.', '') ?? 'N/A';
                        } 
                        
                      ?>

                        <td style="padding: 7px;width:12%"> SSB A/c No. </td>

                        <td style="padding: 7px;width:20%"> {{$emp_ssb_ac}} </td>
                        <td style="padding: 7px;width:12%"> Payment Mode :  </td>
                        <td style="padding: 7px;width:20%"> {{$payment_mode}}</td> 
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Employee Bank Name : </td>
                        <td style="padding: 7px;width:20%"> {{$emp_bank_name}}</td>
                        <td style="padding: 7px;width:12%"> Employee Bank A/c No. : </td>
                        <td style="padding: 7px;width:20%">{{$emp_bank_ac}} </td>
                        <td style="padding: 7px;width:12%"> Employee Bank IFSC : </td>
                        <td style="padding: 7px;width:20%">{{$emp_bank_ifsc}}</td> 
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Bank Name : </td>
                        <td style="padding: 7px;width:20%"> {{$bank_name}}</td>
                        <td style="padding: 7px;width:12%"> Bank A/c No.: </td>
                        <td style="padding: 7px;width:20%">{{$bank_ac}} </tdbank_name>
                        <td style="padding: 7px;width:12%">Cheque No.</td>
                        <td style="padding: 7px;width:20%">{{$cheque_no}}</td> 
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> UTR number / Transaction No. : </td>
                        <td style="padding: 7px;width:20%"> {{$online_transaction_no}}</td>
                        <td style="padding: 7px;width:12%"> RTGS/NEFT Charge : </td>
                        <td style="padding: 7px;width:20%">{{$neft_charge}} &#x20B9; </td>
                        <td style="padding: 7px;width:12%"> </td>
                        <td style="padding: 7px;width:20%"> </td> 
                      </tr>
                    </table>                  
                    
                   
                  </div> 
                </div>
              </div> 


            </td> 
          </tr>
        </table> 
        
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body ">
                <div class="row">
        <div class="col-lg-12 text-center">
              <button type="submit" class="btn btn-primary" onclick="printDiv('advice');"> Print<i class="icon-paperplane ml-2" ></i></button>
            </div> 
            </div>
          </div>
        </div>

      </div>  
</div>

@include('templates.admin.hr_management.salary.script_print')
@stop