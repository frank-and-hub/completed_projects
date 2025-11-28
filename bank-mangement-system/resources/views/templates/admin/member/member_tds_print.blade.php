<style type="text/css" media="print">

    @page 

    {

        size: auto;   /* auto is the initial value */

        margin: 0mm;  /* this affects the margin in the printer settings */

    }

</style>

    

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

    <thead>

        <tr>

            <th>S/N</th>

            <th>Branch Name</th>

            <th>Branch Code</th>

            <th>Account Number</th>

            <th>Account Holder Name</th>

            <th>Scheme</th>

            <th>Status</th>

            <th>Int. Paid</th>

            <th>Int. Accrual</th> 

            <th>Tax Deducted</th> 

            <th>Overhead Tax Deducted</th> 

            <th>Currency</th> 

        </tr>

    </thead>

    <tbody>

        @foreach($records[0]['associateInvestment'] as $key => $investment)
        @if($investment->plan_id !=1)
        <tr>

            <td>{{ $key+1 }}</td>

            <td>{{ getBranchName($investment->branch_id)->name }}</td>

            <td>{{ getBranchCode($investment->branch_id)->branch_code }}</td>

            <td>{{ $investment->account_number }}</td>

            <td>{{ $records[0]->first_name }} {{ $records[0]->last_name }}</td>

            <td>{{ getPlanDetail($investment->plan_id)->name }}</td>

            @if($investment->is_mature == 0)

                <td>Closed</td>

            @else

                @php

                    $interestAmount = \App\Models\MemberInvestmentInterest::where('investment_id',$investment->id);

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

                    $interestTdsAmount = \App\Models\MemberInvestmentInterestTds::where('investment_id',$investment->id);

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

                <td>Open</td>

            @endif

            <td>{{ number_format(($interestAmount-$interestTdsAmount),2) }}</td>

            @if($investment->is_mature == 0)
            <td>{{ number_format(($interestAmount-$interestTdsAmount),2) }}</td>
            @else
            <td>0</td>
            @endif


            <td>{{ number_format($interestTdsAmount,2) }}</td>

            <td>{{ 0 }}</td>

            <td>INR</td>

        </tr>
        @endif
        @endforeach

    </tbody>

</table>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>



<script>



window.onafterprint = window.close;



window.print();



</script>