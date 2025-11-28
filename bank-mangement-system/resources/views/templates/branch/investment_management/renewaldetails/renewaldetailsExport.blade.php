  <table id="renewaldetails-listing" class="table table-flush">

       <thead class="">

            <tr>

            <th>S/N</th>

            <th>Created Date</th>

            <th>BR Name</th>

            <th>BR Code</th>

            <th>SO Name</th>

            <th>RO Name</th>  

            <th>ZO Name</th>

            <th>Member ID</th>

            <th>Account Number</th>

            <th>Member(Account Holder Name)</th> 

            <th>Plan</th>

            <th>Tenure</th>

            <th>Amount</th>

            <th>Associate Code</th>

            <th>Associate Name</th>

             <th>Payment Mode</th>
            

            </tr>

        </thead>
        
        <tbody>
            <?php 
$sno = 1;
foreach ($data as $index => $row){
$planId=$row['investment']['plan_id'];
$planName='';
if($planId>0){

				$PlanDetail=getPlanDetail($planId);

				 if(!empty($PlanDetail)){

				 $planName=$PlanDetail->toArray()['name'];

				 }

				}
				$tenure='';
				if($planId==1)

                {

                  $tenure = 'N/A';

                }

                else

                {

                  $tenure = $row['investment']['tenure'].' Year';

                }
                $mode = '';

                 if($row->payment_mode == 0)
                 {
                    $mode = "Cash";
                 }
                 elseif($row->payment_mode == 1)
                 {
                     $mode = "Cheque";
                 }
                  elseif($row->payment_mode == 2)
                 {
                     $mode = "DD";
                 }
                  elseif($row->payment_mode == 3)
                 {
                     $mode = "Online";
                 }
                 elseif($row->payment_mode == 4)
                 {
                     $mode = "By Saving Account";
                 }
                 elseif($row->payment_mode == 5)
                 {
                     $mode = "From Loan Amount";
                 }
 ?>
<tr>
 <td>{{$index+1}}</td>
<td>{{date("d/m/Y", strtotime( $row->created_at))}}</td>
<td>{{$row['dbranch']['name']}}</td>
<td>{{$row['dbranch']['branch_code']}}</td>
<td>{{$row['dbranch']['sector']}}</td>
<td>{{$row['dbranch']['sector']}}</td>
<td>{{$row['dbranch']['zone']}}</td>
<td>{{$row['member']['member_id']}}</td>
<td>{{$row['account_no']}}</td>
<td>{{$row['member']['first_name'].' '.$row['member']['last_name']}}</td>
<td>{{$planName}}</td>
<td>{{$tenure}}</td>
<td>{{$row['amount']}}</td>
<td>{{$row['associateMember']['associate_no']}}</td>
<td>{{$row['associateMember']['first_name'].' '.$row['associateMember']['last_name']}}</td>
<td>{{$mode}}</td>





</tr>

<?php
    
    }?>
        </tbody>

                    </table>
