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

        <th>Member Mobile Number</th>

    <th>Associate Code</th>

    <th>Associate Name</th>

    <th>Account Number</th>

    <th>Tenure</th>

    <th>Balance</th>

    <th>ELI Amount</th>

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

@foreach($investmentMemberLists as $index => $value)  

<?php 

  // if($value->plan_id==1)

  //               {
  //                   $ssbDeposit = \App\Models\SavingAccountTranscation::where('account_no',$value->account_number)->sum('deposit');  
  //                 $ssbWithdrawal = \App\Models\SavingAccountTranscation::where('account_no',$value->account_number)->sum('withdrawal');
  //                   $current_balance =number_format((float)$ssbDeposit - $ssbWithdrawal, 2, '.', '') ;

                

  //               }

  //               else

  //               {

  //                   $dayBook = \App\Models\Daybook::where('investment_id',$value->id)->where('account_no',$value->account_number)->orderby('created_at','desc')->first();
  //                   if($dayBook)
  //                   {
  //                       $current_balance = $dayBook->opening_balance;
  //                   }
  //                   else{
  //                       $current_balance = 0;
  //                   }
                  

  //               }

    // $idProofDetail= \App\Models\MemberIdProof::where('member_id',$investment['member']->id)->first();



    //              $firstId=getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;

    //              $secondId=getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;



    //             $address=$investment['member']->address;

    //             $state=getStateName($investment['member']->state_id);

    //             $district=getDistrictName($investment['member']->district_id);

    //             $city=getCityName($investment['member']->city_id);

    //             $village=$investment['member']->village;

    //             $pin_code=$investment['member']->pin_code;



?> 

  <tr>

    <td>{{ $index+1 }}</td>

    <td>{{ $value['date'] }}</td>

     <td>{{ $value['form_number'] }}</td>

    <td>{{ $value['plan_name'] }}</td>

    <td  >{{  $value['branch_name']  }}</td>

    <td  >{{ $value['branch_code'] }}</td>

    <td  >{{ $value['sector'] }}</td>

    <td  >{{ $value['regan'] }}</td>

    <td  >{{ $value['zone'] }}</td>

    <td>{{ $value['member_name'] }}</td>

    <td>{{ $value['member_id'] }}</td>

      <td>{{ $value['mobile_no'] }}</td>
  
    <td>{{ $value['asso_name'] }}</td>
      <td>{{ $value['associate_code'] }}</td>

     <td>{{ $value['account_number'] }}</td>

    <td> {{ $value['tenure']}}</td>

    <td>


         {{ $value['current_balance'] }}           

    </td>

    <td>{{$value['investment_id']}}</td>

    <td>{{ $value['deposite'] }}</td>
    <td>{{$value['address']}}</td>

    <td>{{$value['state']}}</td>

    <td>{{$value['district']}}</td>

    <td>{{$value['city']}}</td>

    <td>{{$value['village']}}</td>

    <td>{{$value['pin_code']}}</td>

    <td>{{$value['firstId']}}</td>

    <td>{{$value['secondId']}}</td>

    



    

  </tr>

@endforeach

</tbody>

</table>

