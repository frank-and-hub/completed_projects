<style type="text/css" media="print">
    @page 
    {
        size: auto;   /* auto is the initial value */
        margin: 0mm;  /* this affects the margin in the printer settings */
		size: landscape;
    }
	
</style>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


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
        <th  >Account No</th>
        <th  >Mobile No</th>
        <th  >Associate Code</th>
        <th  >Associate Name</th>
		<th>Nominee Name</th>
									<th>Relation</th>
									<th>Nominee Age</th>
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
	$relation_id = getMemberNomineeDetail($member->id)->relation;
	if($relation_id)
	{
	$relation = getMemberNomineeRelation($relation_id)->name;
	}
    $idProofDetail= \App\Models\MemberIdProof::where('member_id',$member->id)->first();

                 $firstId=getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;
                 $secondId=getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;

                $address=$member->address;
                $state=getStateName($member->state_id);
                $district=getDistrictName($member->district_id);
                $city=getCityName($member->city_id);
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
                if(getMemberSsbAccountDetail($member->id))
                {
                    $accountNo=getMemberSsbAccountDetail($member->id)->account_no;
                }

                ?>
	 <td  >{{date('d/m/Y', strtotime($member->dob)) }}</td>			
    <td  >{{ $accountNo }}</td>
    <td  >{{ $member->mobile_no }}</td>
    <td  >{{ $member->associate_code }}</td>
    <td   >{{ getSeniorData($member->associate_id,'first_name').' '.getSeniorData($member->associate_id,'last_name') }}</td>
	<td>{{getMemberNomineeDetail($member->id)->name}} </td>
	<td>{{$relation}} </td>
	<td>{{getMemberNomineeDetail($member->id)->age}} </td>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
window.print();
</script>