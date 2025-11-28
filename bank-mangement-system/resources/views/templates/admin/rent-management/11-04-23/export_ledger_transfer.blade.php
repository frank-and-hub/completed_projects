<h3> Rent Transfer List</h3>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
  //echo "<pre>";print_r($reports);die;
?>


<thead>
                                        <tr>                                    
                                        <th >S.No</th>
                                           
                                        <th >BR Name</th> 
                                        <th >BR Code</th>
                                        <th >SO Name</th>
                                        <th >RO Name</th>
                                        <th >ZO Name</th>
                                        <th >Rent Type</th>
                                        <th >Period From </th>
                                        <th >Period To</th>
                                        <th >Address</th>
                                        <th >Owner Name</th>
                                        <th  >Owner Mobile Number</th>
                                        <th >Owner Pan Card</th>
                                        <th >Owner Aadhar Card </th>
                                        <th >Owner SSB account </th> 

                                        <th > Owner Bank Name</th>
                                        <th  >Owner Bank A/c No.</th>
                                        <th  >Owner IFSC code </th>

                                        
                                        <th >Yearly Increment</th>
                                        <th >Office Square feet area</th>
                                        <th >Advance Payment Amount</th>
                                        <th >Security amount</th> 
                                        <th >Rent</th>
                                        <th >Actual Rent Amount</th>
                                        <th >Tds Amount</th>

                                        <th >Transfer Amount</th>

                                        <th >Employee Code</th>
                                        <th >Employee Name</th>
                                        <th >Employee Designation</th>
                                        <th >Employee Mobile No.</th>

                                        </tr> 
                                        
                                    </thead> 
                                    <tbody>
                                    @if(count($data)>0)
                                    <?php  
                                    $total_transfer=0;
                                    ?>
                                         @foreach($data as $index =>  $row) 
                                         <?php
                                            $total_transfer=$total_transfer+$row->transfer_amount;
                                         ?>
                                         
                                         <tr>
                                             <td >{{ $index+1 }}</td>
                                           

                                             <td >{{ $row['rentBranch']->name }}</td>
                                             <td >{{ $row['rentBranch']->branch_code }}</td>
                                             <td >{{ $row['rentBranch']->sector }}</td>
                                             <td >{{ $row['rentBranch']->regan }}</td>
                                             <td >{{ $row['rentBranch']->zone }} </td>

                                             <td >{{ getAcountHead($row['rentLib']->rent_type) }} </td>
                                             <td > {{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)))}} </td>
                                             <td >{{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)))}} </td>
                                             <td > {{$row['rentLib']->place}}</td>
                                             <td >{{$row['rentLib']->owner_name}} </td>
                                             <td >{{$row['rentLib']->owner_mobile_number}} </td>
                                             <td >{{$row['rentLib']->owner_pen_number}}</td>
                                             
                                             <td >{{$row['rentLib']->owner_aadhar_number}}</td>
                                             <td >
                                                @if($row['rentSSB'])
                                                {{$row['rentSSB']->account_no}}
                                                @endif
                                            </td>
                                             <td >{{$row->owner_bank_name}} </td>
                                             <td > {{$row->owner_bank_account_number}}</td>
                                             <td > {{$row->owner_bank_ifsc_code}} </td>
                                             <td > {{number_format((float)$row->yearly_increment, 2, '.', '')}}%</td>
                                             <td > {{$row->office_area}}</td>
                                             <td > {{number_format((float)$row['rentLib']->advance_payment, 2, '.', '')}}</td>

                                             <td >{{number_format((float)$row->security_amount, 2, '.', '')}} </td>
                                             <td > {{number_format((float)$row->rent_amount, 2, '.', '')}}</td>
                                             <td > {{number_format((float)$row->actual_transfer_amount + $row->tds_amount, 2, '.', '')}}</td>
                                             <td > {{number_format((float)$row->tds_amount, 2, '.', '')}}</td>
                                             
                                             
                                             
                                             <td >{{number_format((float)$row->transfer_amount, 2, '.', '')}} </td>
                                             <td > {{$row['rentEmp']->employee_code}} </td>
                                             <td > {{$row['rentEmp']->employee_name}} </td>
                                             <td > {{ getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name }} </td>
                                             <td > {{$row['rentEmp']->mobile_no}} </td> 
                                              



                                         </tr>                                      
                                         @endforeach 
                                         @endif 
                                         </tbody>
                                         <tfoot>
                                        
                                        <tr>
                                            <td colspan="9" align="right" ><strong>Total Transfer Amount</strong> </td>
                                            <td colspan="20" align="left" ><span id='total_payble'><strong> {{number_format((float)$total_transfer, 2, '.', '')}} </strong> </span> </td>
                                        </tr>
                                    </tfoot>

</table>
