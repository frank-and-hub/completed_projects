<h6 class="card-title font-weight-semibold">Fund Transfer | Bank Ledger Report</h6>
<table>
    <tr>

        <th style="font-weight: bold;">From Date</th>

        <th style="font-weight: bold;">To Date</th>

        <th style="font-weight: bold;">Bank Account Number</th>

        <th style="font-weight: bold;">Bank Name</th>
    </tr>
    <tbody>
        <tr>
            <td>{{ $startDate }}</td>
            <td>{{ $endDate }}</td>
            <td>{{ getSamraddhBankAccountId($bank_id)->account_no }}</td>
            <td>{{ getSamraddhBank($bank_id)->bank_name }}</td>
        </tr>
    </tbody>
</table>

<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">

    <thead>

        <tr>

            <th style="font-weight: bold;">S/N</th>

            <th style="font-weight: bold;">Date</th>

            <th style="font-weight: bold;">Branch Code</th>

            <th style="font-weight: bold;">Branch Name</th>

            <th style="font-weight: bold;">Company Name</th>

            <th style="font-weight: bold;">Member Name</th>

            <!-- <th style="font-weight: bold;">Member Id/Member A/C</th> -->

            <th style="font-weight: bold;">Account No.</th>

            <th style="font-weight: bold;">Particulars</th>

            <!-- <th style="font-weight: bold;">Account Head </th>

            <th style="font-weight: bold;">Account Head Name</th>  -->

            <th style="font-weight: bold;">Cheque No./UTR No.</th>

            <th style="font-weight: bold;">CR Amount</th>

            <th style="font-weight: bold;">DR Amount</th>

            <th style="font-weight: bold;">Balance</th>



        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td></td>
            <td>Opening Balance</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $balance }}</td>
        </tr>
        <?php
$sno = 1;
$totalbalance = $balance;

foreach ($data as $value)
{
  $data = getCompleteDetail($value);
  $memberAccount = $data['memberAccount'];
  $type = $data['type'];
  $plan_name = $data['plan_name'];
  $rentType = $data['rent_type'];
  $memberName = $data['memberName'];
  $memberId = $data['memberId'];
  if($value->payment_type=="CR")
    {
        $t = number_format((float)$totalbalance + $value->amount, 2, '.', '');
        $totalbalance =$t;
    }
    elseif ($value->payment_type=="DR") {
            $t = number_format((float)$totalbalance - $value->amount, 2, '.', '');
            $totalbalance =$t;

    }
?>

        <tr>
            <td>{{ $sno }}</td>
            <td>{{ date('d/m/Y', strtotime(convertDate($value->entry_date))) }}</td>
            <td>
                @if ($value['Branch'])
                    {{ $value['Branch']['branch_code'] }}
                @endif
            </td>
            <td>
                @if ($value['Branch'] && $value['type'] != '8')
                    {{ $value['Branch']['name'] }}
                @else
                    Bank To Bank
                @endif
            </td>
            <td>
                @if ($value)
                    {{ $value['CompanyName']['name'] }}
                @endif
            </td>
            <td>{{ $memberName }}</td>
            <td>{{ $memberAccount }}</td>
            <td>{{ $type }}</td>
            <td>
                @if ($value->cheque_no)
                    {{ $value->cheque_no }}
                @elseif($value->transction_no)
                    {{ $value->transction_no }}
                @endif
            </td>
            <td>
                @if ($value->payment_type == 'CR')
                    {{ number_format((float) $value->amount, 2, '.', '') }}
                @endif
            </td>
            <td>
                @if ($value->payment_type == 'DR')
                    {{ number_format((float) $value->amount, 2, '.', '') }}
                @endif
            </td>
            <td>{{ number_format((float) $totalbalance, 2, '.', '') }}</td>
        </tr>


        <?php
    $sno = $sno + 1;
}

?>
        <tr>
            <td></td>
            <td></td>
            <td>Closing Balance</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $totalbalance ?? $closing }}</td>

        </tr>
    </tbody>
</table>
