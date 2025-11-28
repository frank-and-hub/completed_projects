@foreach($responseArray as $key => $val)
@if($val)
<div class="card-header header-elements-inline">
    @foreach($val as $index1=>$row)
    @if($index1 == 0)
        <h6 class="card-title font-weight-semibold">{{ getPlanDetail($row->plan_id)->name }}</h6>
    @endif
    @endforeach   
</div>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
    <thead>

        <tr>

            <th>S/N</th>
            <th>Date</th>
            <th>Plan Name</th>
            <th>Account Number</th>
            <th>Interest Amount</th>
            <th>TDS Deduction</th>
            <th>Cr.</th>
            <th>Dr.</th>
            <th>Balance</th>    

        </tr>
    </thead>

    <tbody>
        
            @foreach($val as $index=>$row)

                @php
                    $interestAmount = App\Models\MemberInvestmentInterest::where('investment_id',$row->id);

                    if($startDate !=''){
                        $interestAmount = $interestAmount->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                    }

                    if($planId !=''){
                        $interestAmount = $interestAmount->where('plan_type','=',$planId);
                    }

                    if($branch_id !=''){
                        $interestAmount = $interestAmount->where('branch_id','=',$branch_id);
                    }

                    $interestAmount = $interestAmount->sum('interest_amount');



                    $interestTdsAmount = App\Models\MemberInvestmentInterestTds::where('investment_id',$row->id);

                    if($startDate !=''){
                        $interestTdsAmount = $interestTdsAmount->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                    }

                    if($planId !=''){
                        $interestTdsAmount = $interestTdsAmount->where('plan_type','=',$planId);
                    }

                    if($branch_id !=''){
                        $interestTdsAmount = $interestTdsAmount->where('branch_id','=',$branch_id);
                    }

                    $interestTdsAmount = $interestTdsAmount->sum('tdsamount_on_interest');

                @endphp

                <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{ date("d/m/Y", strtotime(convertDate($row->created_at))) }}</td>
                    <td>{{ getPlanDetail($row->plan_id)->name }}</td>
                    <td>{{ $row->account_number }}</td>
                    <td>{{ number_format($interestAmount,2) }}</td>
                    <td>{{ number_format($interestTdsAmount,2) }}</td>
                    @php
                    $tdsAmount = $interestAmount-$interestTdsAmount;
                    @endphp
                    <td>{{ number_format($tdsAmount,2) }}</td>
                    @if($row->is_mature == 0)
                    <td>{{ number_format($tdsAmount,2) }}</td>
                    <td>0</td>
                    @else
                    <td>0</td>
                    <td>{{ number_format($tdsAmount,2) }}</td>
                    @endif
                </tr>
            @endforeach
    </tbody>                                    
</table>
@endif
@endforeach