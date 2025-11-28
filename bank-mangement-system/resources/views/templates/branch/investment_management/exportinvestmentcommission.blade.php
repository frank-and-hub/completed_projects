<h6 class="card-title font-weight-semibold">Account Number : {{ $investment->account_number }}</h6>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
    <tr>
        <th>#</th>
        <th> Date </th>
        <th> Associate ID</th>
        <th> Associate Name</th>
        <th> Associate Carder</th>
        <th> Total Amount</th>
        <th> Commission Amount</th>
        <th> Percentage</th>
        <th> Carder Name</th>
        <th> EMI No</th>
        <th> Commission Type</th>
        <th> Associate Exists</th>
        <th> Payment Type</th>
        <th> Payment Distribute</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $index => $value)
        <?php
                $member_name = getMemberDetails($value->member_id);
        ?>
        <tr>
            <td>{{ $index+1 }}</td>
            <td>{{ date("d/m/Y", strtotime($value->created_at)) }}</td>
            <td>{{ $member_name->associate_no }}</td>
            <td>{{ $member_name->first_name }} {{ $member_name->last_name }}</td>
            <td>{{ getCarderName($member_name->current_carder_id) }}</td>
            <td>{{ $value->total_amount }}</td>
            <td>{{ $value->commission_amount }}</td>
            <td>{{ $value->percentage }}</td>
            <td>@if($value->type==5) Collection Charge @else {{ getCarderName($value->carder_id) }} @endif</td>
            <td>
                <?php
                    $get_plan = getInvestmentDetails($value->type_id);
                    if($get_plan->plan_id==7)
                    {
                    	if($value->month>1)
                    	{
                    		$emi_no=$value->month.' Days';
                        } else {
                    		$emi_no=$value->month.' Day';
                        }
                    } else {
                    	if($value->month>1)
                    	{
                    		$emi_no=$value->month.' Months';
                        } else {
                    		$emi_no=$value->month.' Month';
                        }
                    }
                ?>
                {{ $emi_no }}

            </td>
            <td >
                @if($value->commission_type==0)
                    Self
                @else
                    Link Member
                @endif
            </td>
            <td >
                @if($value->associate_exist==0)
                    Yes
                @else
                    No
                @endif
            </td>
            <td >
                @if($value->pay_type==1)
                    OverDue
                @elseif($value->pay_type==2)
                    Due Date
                @else
                    Advance
                @endif
            </td>
            <td >
                @if($value->is_distribute==0)
                    No
                @else
                    Yes
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
