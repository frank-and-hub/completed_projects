<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
  <tr>
    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
    <th>Rent Type</th>
    <th>Period From</th>
    <th>Period To</th>
    <th>Address</th>
    <th>Owner Name</th>
    <th>Owner Mobile Number </th>
    <th>Owner Pan Card</th>
    <th>Owner Aadhar Card</th>
    <th>Owner SSB account</th>
    <th>Owner Bank name</th>
    <th>Owner Bank account Number</th>
    <th>Owner IFSC code</th>
    <th>Security amount </th>
    <th>Rent</th>
    <th>Yearly Increment</th>
    <th>Office Square feet area</th>
    <th>Employee Code</th>
    <th>Authorized Employee name</th>
    <th>Authorized Employee Designation</th>
    <th>Mobile Number</th>
    <th>Rent Agreement</th>
    <th>Agreement Status</th>
    <th>Created Date</th>
  </tr>
</thead>
<tbody>
@foreach($data as $index => $value)  
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $value['liabilityBranch']->name }}</td>
    <td>{{ $value['liabilityBranch']->branch_code }}</td>
    <td>{{ $value['liabilityBranch']->sector }}</td>
    <td>{{ $value['liabilityBranch']->regan }}</td>
    <td>{{ $value['liabilityBranch']->zone }}</td>
    <td>{{ getAcountHead($value->rent_type)}}</td>
    <td>{{ date("d/m/Y", strtotime(convertDate($value->agreement_from))) }}</td>
    <td>{{ date("d/m/Y", strtotime(convertDate($value->agreement_to))) }}</td>
    <td>{{ $value->place }}</td>
    <td>{{ $value->owner_name }}</td>
    <td>{{ $value->owner_mobile_number }}</td>
    <td>{{ $value->owner_pen_number }}</td>
    <td>{{ $value->owner_aadhar_number }}</td> 
    <td>
        @if($value->owner_ssb_id))
        {{getSsbAccountNumber($value->owner_ssb_id)->account_no}}
        @endif
    </td>

    <td>{{ $value->owner_bank_name }}</td>
    <td>{{ $value->owner_bank_account_number }}</td>
    <td>{{ $value->owner_bank_ifsc_code }}</td>
    <td>{{ $value->security_amount }}</td>
    <td>{{ $value->rent }}</td>
    <td>{{ $value->yearly_increment }} %</td>
    <td>{{ $value->office_area }}</td>
    <td>{{ $value['employee_rent']->employee_code }}</td>
    <td>{{ $value['employee_rent']->employee_name }}</td>
    <td>{{  getDesignationData('designation_name',$value['employee_rent']->designation_id)->designation_name }}</td>
    <td>{{ $value['employee_rent']->mobile_no}}</td>
    <td>{{ $value->rent_agreement_file_id }}</td>
    <td>{{ date("d/m/Y", strtotime($value->created_at)) }}</td>
    <td>
        @if($value->status == 0)
            Active
        @else
            Deactive
        @endif
    </td>   
  </tr>

@endforeach
</tbody>
</table>
