<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($business_mode);die;
?>


<thead>
  <tr>
    <th>#</th>
    <th>BR Name</th>
            <th>BR Code</th>
            <th>SO Name</th>
            <th>RO Name</th>
            <th>ZO Name</th>
   
    <th>Associate Code</th>
    <th>Associate Name</th>
    <th>Associate Carder</th>
    @if($business_mode==0 || $business_mode==2)
    <th class="self">Quota Business Target (Self Business) Amt</th>
    <th class="self">Achieved Target (Self Business) Amt</th>
    <th class="self">Quota Business Target (Self Business) %</th>
    <th class="self">Achieved Target (Self Business) %</th>
    @endif
    <th>Senior Code</th>
    <th>Senior Name</th>
    <th>Senior Carder</th>
    @if($business_mode==1 || $business_mode==2)
    <th class="team">Quota Business Target (Team Business) Amt</th>
    <th class="team">Achieved Target (Team Business) Amt</th>
    <th class="team">Quota Business Target (Team Business) %</th>
    <th class="team">Achieved Target (Team Business) %</th>
    @endif
    <th>Joining Date</th>
    <th>Mobile Numb</th>
    <th>Status</th>
  </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php 
$targetSelf=getBusinessTargetAmt($row->current_carder_id)->self;
$tSelf= round($targetSelf, 2);
$achievedTragetSelf = getAchievedSelfBusiness($row->id,$startDate,$endDate);
  $aSelf=round($achievedTragetSelf, 2); 
  if($achievedTragetSelf>0)
  {
    $x=($achievedTragetSelf/$targetSelf)*100;
    $y=(100-$x);
  }
  else
  {
    $x=0;
    $y=(100-$x);
  }
  

  $targetSelfPer=round($y, 3);

  $achievedSelfPer=round($x, 3);

  if($row->current_carder_id>1)
  {
    $targetTeam=getBusinessTargetAmt($row->current_carder_id)->credit;
    $tTeam =round($targetTeam, 2);
    $achievedTargetTeam=getKotaBusinessTeam($row->id,$startDate,$endDate);
    $achievedTeam=round($achievedTargetTeam,2);

   

    if($achievedTargetTeam>0)
    {
       $x1=($achievedTargetTeam/$targetTeam)*100;
      $y1=(100.000-$x1);
    }
    else
    {
       $x1=0;
      $y1=(100.000-$x1);
    }


    $targetTeamPer=round($y1,3);
    $achievedTeamPer=round($x1,3);
  }
  else
  {
    $tTeam = 'N/A';
    $achievedTeam='N/A';
    $targetTeamPer='N/A';
    $achievedTeamPer='N/A';
  }
 // print_r($achievedTeamPer);die;
?>

  <tr>
    <td>{{ $index+1 }}</td>
     
    <td>{{ $row['associate_branch']->name }}</td>
    <td>{{ $row['associate_branch']->branch_code }}</td>   
    <td>{{ $row['associate_branch']->sector }}</td>   
    <td>{{ $row['associate_branch']->regan }}</td>   
    <td>{{ $row['associate_branch']->zone }}</td>   
    <td>{{ $row->associate_no }}</td>
    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
    <td>{{ getCarderNameFull($row->current_carder_id) }}</td>
    @if($business_mode==0 || $business_mode==2)    
    <td>{{ $tSelf }}</td>
    <td>{{ $aSelf }}</td>
    <td>{{ $targetSelfPer }}</td>
    <td>{{ $achievedSelfPer }}</td>
    @endif
    <td>{{ getSeniorData($row->associate_senior_id,'associate_no')}}</td>
    <td>{{ getSeniorData($row->associate_senior_id,'first_name')}} {{getSeniorData($row->associate_senior_id,'last_name') }}</td>
    <td>{{ getCarderNameFull(getSeniorData($row->associate_senior_id,'current_carder_id')) }}</td>
    @if($business_mode==1 || $business_mode==2)
    <td>{{ $tTeam }}</td>
    <td>{{ $achievedTeam }}</td>
    <td>{{ $targetTeamPer }}</td>
    <td>{{ $achievedTeamPer }}</td>
    @endif
    <td>{{ date("d/m/Y", strtotime(convertDate($row->associate_join_date))) }}</td>
    <td>{{ $row->mobile_no }}</td>
    @if($row->is_block==0)

      @if($row->associate_status==1)
        <td>Active</td>  
      @elseif($row->associate_status==0)
        <td>Inactive</td>  
      @endif
    @else
      Blocked
    @endif
  </tr>
@endforeach
</tbody>
</table>
