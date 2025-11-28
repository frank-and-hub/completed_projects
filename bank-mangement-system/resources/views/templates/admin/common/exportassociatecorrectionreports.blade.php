<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php

$type='';
?>


<thead>
  <tr>
    <th>#</th>
    <th>Transaction Date</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
    <?php
                                    if($type == 0){
                                        $a = 'Member Id'; 
                                    }elseif($type == 1){
                                        $a = 'Associate Id'; 
                                    }elseif($type == 2){
                                        $a = 'Account No'; 
                                    }elseif($type == 3){
                                        $a = 'Account No'; 
                                    }
                                    else
                                    {
                                        $a = 'Account No'; 
                                    }
                                ?>
                                <td>{{ $a }}</td>
    <th>In context to</th>
    <th>Correction</th>
    <th>Status</th>
  </tr>
</thead>
<tbody>

@foreach($data as $index => $value) 
<?php 
if($value->correction_type == 0){
    $in_context = 'Member Registration';
}elseif ($value->correction_type == 1) {
    $in_context = 'Associate Registration';
}elseif ($value->correction_type == 2) {
    $in_context = 'Investment Registration';
}elseif ($value->correction_type == 3) {
    $in_context = 'Renewals Transaction';
}elseif ($value->correction_type == 4) {
    $in_context = 'Withdrawals';
}elseif ($value->correction_type == 5) {
    $in_context = 'Passbook print';
}elseif ($value->correction_type == 6) {
    $in_context = 'Certificate print';
}

if($value->status == 0){
    $status = 'Pending';
}elseif ($value->status == 1) {
    $status = 'Corrected';
}elseif ($value->status == 2) {
    $status = 'Rejected';
}


if($value->correction_type == 0){
    $account_no = getSeniorData($value->correction_type_id,'member_id');
}elseif ($value->correction_type == 1) {
    $account_no = getSeniorData($value->correction_type_id,'associate_no');
}elseif ($value->correction_type == 2) {
     $account_no = getMemberInvestment($value->correction_type_id);
    if(isset(($account_no->account_number)))
    {
       $account_no = $account_no->account_number; 
    }
    else{
         $account_no = ''; 
    }
}elseif ($value->correction_type == 3) {
    $inId=\App\Models\Daybook::where('id',$value->correction_type_id)->first();

    if($inId)

    {

        $invId=\App\Models\Memberinvestments::select('plan_id')->where('id',$inId->investment_id)->first();

        $account_no = $inId->account_no;  

    }

    else{

        $account_no = 'N/A';  

    }
}elseif ($value->correction_type == 4) {
   $account_no = 'N/A'; 
}elseif ($value->correction_type == 5) {

    $invId=\App\Models\Memberinvestments::select('id','account_number','plan_id')->where('id',$value->correction_type_id)->first();

    $account_no = $invId->account_number;

}elseif ($value->correction_type == 6) {

    $invId=\App\Models\Memberinvestments::select('id','account_number','plan_id')->where('id',$value->correction_type_id)->first();

    $account_no = $invId->account_number;

}


?> 
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime( str_replace('-','/',$value->created_at ) ) ) }}</td>
    <td  >{{ $value['branch']->name }}</td>
    <td  >{{ $value['branch']->branch_code }}</td>
    <td  >{{ $value['branch']->sector }}</td>
    <td  >{{ $value['branch']->regan }}</td>
    <td  >{{ $value['branch']->zone }}</td>
    <td>{{ $account_no }}</td>
    <td>{{ $in_context }}</td>
    <td>{{ $value->correction_description }}</td>
    <td>{{ $status }}</td>
  </tr>
@endforeach
</tbody>
</table>
