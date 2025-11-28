<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

<thead>
    <tr>
        <th  >#</th>
        <th  >Join Date</th>
        <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
        <th  >Member ID</th>
        <th  >Name</th>
        <th>Member DOB</th>
        <th>Gender</th>
        <th  >Account No</th>
        <th  >Mobile No</th>
        <th  >Associate Code</th>
        <th  >Associate Name</th>
        <th>Nominee Name</th>
									<th>Relation</th>
									<th>Nominee Age</th>
                                    <th>Nominee Gender</th>
        <th   >Status</th>
        <th>Is Uploaded</th> 


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
@foreach($memberList as $index => $member) 
<?php 

    $NomineeDetail = $member['memberNomineeDetails'];
    $relation_id = $NomineeDetail->relation;

	if($relation_id)
	{
	   $relation = $NomineeDetail['nomineeRelationDetails']->name; 
	}
   
                 $firstId= $member['memberIdProof']['idTypeFirst']->name .' - '.$member['memberIdProof']->first_id_no;
                 $secondId= $member['memberIdProof']['idTypeSecond']->name .' - '.$member['memberIdProof']->second_id_no; 

                $address=$member->address;
                if($member['states']){

                    $state = $member['states']->name; 
                }else{
                    $state = ' ';
                }

                if($member['district']){
                    $district = $member['district']->name;
                }else{
                    $district = ' ';
                }

                if($member['city']){
                    $city = $member['city']->name; 
                }else{
                    $city = ' ';
                }
                
                $village=$member->village;
                $pin_code=$member->pin_code;

?> 
  <tr>


    <td  >{{ $index+1 }}</td>
    <td  >{{ date("d/m/Y", strtotime($member->re_date)) }}</td>
    <td  >{{ $member['branch']->name }}</td>
    <td  >{{ $member['branch']->branch_code }}</td>
    <td  >{{ $member['branch']->sector }}</td>
    <td  >{{ $member['branch']->regan }}</td>
    <td  >{{ $member['branch']->zone }}</td>
    <td  >{{ $member->member_id }}</td>
    <td  >{{ $member->first_name }} {{ $member->last_name }}</td>
    <?php
    $accountNo='';

    if($member['savingAccount_Custom'])
    {
        $accountNo= $member['savingAccount_Custom']->account_no; 
    }
               

                ?>
     <td  >{{date('d/m/Y', strtotime($member->dob)) }}</td>
        @if($member->gender == 1)  
        <td>Male</td>
        @else
        <td>Female</td>
        @endif
            

    <td  >{{ $accountNo }}</td>
    <td  >{{ $member->mobile_no }}</td>
    <td  >{{ $member->associate_code }}</td>
    <td   >{{ $member['children']->first_name.' '.$member['children']->last_name }}</td>
    <td>{{ $NomineeDetail->name }} </td>
	<td>{{$relation}} </td>
	<td>{{$NomineeDetail->age}} </td>

    @if($NomineeDetail->gender == 1)
        <td>Male</td>
    @else
        <td>Female</td>
    @endif
    <td  >
      @if($member->is_block)
        Block
      @else
        @if($member->status==1)
          Active
        @else
          Inactive
        @endif
      @endif
    </td>
    <td  >
      <?php $is_upload='Yes';
                if($member->signature=='')
                 {
                    $is_upload = 'No'; 
                 }
                 if($member->photo=='')
                 {
                    $is_upload = 'No'; 
                 }
                 ?>
                 {{$is_upload }}
    </td>


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
