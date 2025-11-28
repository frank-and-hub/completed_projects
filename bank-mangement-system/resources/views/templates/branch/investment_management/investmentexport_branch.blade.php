<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">


<thead>
  <tr>
    <th>#</th>                
    <th>Created Date</th>
    <th>Form No</th>
    <th>Plan</th>
    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
    <th>Member</th>
    <th>Member Id</th>
    <th>Associate Code</th>
    <th>Associate Name</th>
    <th>Account Number</th>
    <th>Tenure</th>
    <!--<th>Balance</th>-->
    <th>Deposite Amount</th>
                                    <th>Address</th>
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>  
                                    <th>First ID Proof</th>                               
                                    <th>Second ID Proof</th> 
  </tr>
</thead>
<tbody>
@foreach($investmentMemberLists as $index => $investment)  
<?php 
  
    $idProofDetail= \App\Models\MemberIdProof::where('member_id',$investment['member']->id)->first();

                 $firstId=getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;
                 $secondId=getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;

                $address=$investment['member']->address;
                $state=getStateName($investment['member']->state_id);
                $district=getDistrictName($investment['member']->district_id);
                $city=getCityName($investment['member']->city_id);
                $village=$investment['member']->village;
                $pin_code=$investment['member']->pin_code;

?> 
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ date("d/m/Y", strtotime($investment->created_at)) }}</td>
     <td>{{ $investment->form_number }}</td>
    <td>{{ $investment['plan']->name }}</td>
    <td  >{{ $investment['branch']->name }}</td>
    <td  >{{ $investment['branch']->branch_code }}</td>
    <td  >{{ $investment['branch']->sector }}</td>
    <td  >{{ $investment['branch']->regan }}</td>
    <td  >{{ $investment['branch']->zone }}</td>
    <td>{{ $investment['member']->first_name }} {{ $investment['member']->last_name }}</td>
    <td>{{ $investment['member']->member_id }}</td>
    @if($investment['associateMember'])
        <td>{{ $investment['associateMember']['associate_no'] }}</td>
        <td>{{ $investment['associateMember']['first_name'] }} {{ $investment['associateMember']['last_name'] }}</td>
    @else
        <td></td>
        <td></td>
    @endif
    <td>{{ $investment->account_number }}</td>
    <td><?php if($investment->plan_id==1){ $tenure = 'N/A'; } else{ $tenure = $investment->tenure.' Year';}?> {{ $tenure}}</td>
    <!--<td>{{ $investment->current_balance }}</td>-->
    <td>{{ $investment->deposite_amount }}</td>

    <td>{{$address}}</td>
    <td>{{$state}}</td>
    <td>{{$district}}</td>
    <td>{{$city}}</td>
    <td>{{$village}}</td>
    <td>{{$pin_code}}</td>
    <td>{{$firstId}}</td>
    <td>{{$secondId}}</td>
    

    
  </tr>
@endforeach
</tbody>
</table>
