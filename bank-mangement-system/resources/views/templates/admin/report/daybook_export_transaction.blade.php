<h4 class="card-title font-weight-semibold">Details 2 </h4>
<table border="1" width="100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
  <tr>
     <th style="font-weight: bold;">Tr.No</th>
   <th style="font-weight: bold;">Tr.Date</th>
   <th style="font-weight: bold;">Receipt.No</th>
   <th style="font-weight: bold;">Account Number</th>
   <th style="font-weight: bold;">Plan Name</th>
   <th style="font-weight: bold;">Associate</th>
   <th style="font-weight: bold;">Member/Employee/Owner/Plan/Loan</th>
   
   <th style="font-weight: bold;">Narration</th>
   <th style="font-weight: bold;">CR Narration</th>
   <th style="font-weight: bold;">DR Narration</th>
   <th style="font-weight: bold;">CR Amount</th>
   <th style="font-weight: bold;">DR Amount</th>
   <th style="font-weight: bold;">Balance</th>

  <!--  <th>Account Head Code</th>
   <th>Account Head Name</th> -->
     <th style="font-weight: bold;">Ref Id</th>
      <th style="font-weight: bold;">Payment Type</th>
   <th style="font-weight: bold;">Tag</th>

</tr>
</thead>
<tbody>
@foreach($data as $index => $value)
    <tr>

       <td>{{$index+1}}</td>
       <td>{{$value['tr_date']}}</td>
       <td>{{$value['bt_id']}}</td>
       <td>{{$value['member_account']}} </td>
       <td>{{$value['plan_name']}} </td>
      <td>{{$value['a_name']}}</td>
      <td>{{$value['memberName']}} </td>
       <td>{{$value['type']}}</td>
       <td>{{$value['description_cr']}}</td>
       <td>{{$value['description_dr']}}</td>
       <td>{{$value['cr_amnt']}}</td>
       <td>{{$value['dr_amnt']}}</td>
       <td>{{$value['balance']}}</td>
       
        <td>{{$value['ref_no']}}</td>
       <td>{{$value['pay_mode']}} </td>
       <td>{{$value['tag']}}</td>
   </tr>
   @endforeach
</tbody>
</table>