<style type="text/css">
  #expense {
    height: 44.5rem;
    overflow-x: hidden;
    overflow-y: auto;
    text-align: justify;
  }
</style>
<?
$f1 = 0;
$f2 = 0;
$companydetails = getCompanyDetail($company_id);

?>
<input type="hidden" name="index" id="index">
<!-- <div class="container">
<button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

<button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>

</div> -->
<div class="col-md-12">
   <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button><br>
  <h4 class="text-center font-weight-semibold" style="margin-left: 149px;">{{$companydetails->name}}</h4>
  <h6 class="text-center mx-auto" style="width:50%;">Corp. Office: {{$companydetails->address}} Info.: Phone No.: +91 {{$companydetails->mobile_no}} | Web: www.samraddhbestwin.com | Mail Us: {{$companydetails->email}}</h6>
</div>
<div class="col-md-12 mt-4">
  <h4 class="font-weight-semibold text-center">DAYBOOK</h4>
</div>
<div class="d-flex my-4 col-md-12">
  <div class="col-md-4">
    <h3 class="card-title font-weight-semibold">Cash Allocation</h3>
    <div class="card">
      <table class="table table-flush">
        <?php
        $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
        $balance_cash = 0;
        $C_balance_cash = 0;
        if ($getBranchOpening_cash->date == $start_date) {
          $balance_cash = $getBranchOpening_cash->total_amount;
        }        
        if ($getBranchOpening_cash->date < $start_date) {
          // dd($start_date, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id,(int)$company_id);
          $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($start_date, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id,(int)$company_id);
          $new_date = date('Y-m-d', strtotime('-1 day', strtotime(str_replace('/', '-', $start_date))));
          $new_end_date = date('Y-m-d', strtotime('-1 day', strtotime(str_replace('/', '-', $end_date))));
          $getTotal_FileCharge1 = getTotalFileCharge($new_date, $new_end_date, $branch_id);
          $balance_cash = $getBranchTotalBalance_cash;
        }
        $getTotal_DR = getBranchTotalBalanceAllTranDR($start_date, $end_date, $branch_id,$company_id);
        $getTotal_CR = getBranchTotalBalanceAllTranCR($start_date, $end_date, $branch_id,$company_id);
       
        $getTotal_FileCharge = getTotalFileCharge($start_date, $end_date, $branch_id);
        $totalBalance = $getTotal_CR - $getTotal_DR;
        $C_balance_cash = $balance_cash + $totalBalance;
        ?>
        <tr>
          <th colspan="2">Opening</th>
          <td></td>
          <td>{{number_format((float)$balance_cash, 2, '.', '')}}</td>
        </tr>
        <tr>
          <th colspan="2"></th>
          <th>Received</th>
          <th>Payment</th>
        </tr>
        <tr>
          <th colspan="2">Cash</th>
          <td>{{number_format((float)$cashInhand['CR'], 2, '.', '')}}</td>
          <td>{{number_format((float)$cashInhand['DR'], 2, '.', '')}}</td>
        </tr>
        <tr>
          <th colspan="2">Closing</th>
          <td></td>
          <td>{{number_format((float)$C_balance_cash, 2, '.', '')}}</td>
        </tr>
      </table>
    </div>
  </div>
  <div class="col-md-4">
    <h3 class="card-title font-weight-semibold">Cheque</h3>
    <div class="card ">
      <table class="table table-flush">
        <tr>
          <th colspan="2">Opening</th>
          <td></td>
          <td>{{number_format((float)getchequeopeningBalance($start_date,$branch_id,$company_id), 2, '.', '')}}</td>
        </tr>
        <tr>
          <th colspan="2"></th>
          <th>Received</th>
          <th>Payment</th>
        </tr>
        <tr>
          <th colspan="2">Cheque</th>
          <td>{{number_format((float)$cheque['CR'], 2, '.', '')}}</td>
          <td>{{number_format((float)$cheque['DR'], 2, '.', '')}}</td>
        </tr>
        <tr>
          <th colspan="2">Closing</th>
          <td></td>
          <td>{{number_format((float)getchequeclosingBalance($end_date,$branch_id,$company_id), 2, '.', '')}}</td>
        </tr>
      </table>
    </div>
  </div>
</div>
<div class="col-md-12">
  <h3 class="card-title font-weight-semibold">Bank</h3>
  <div class="card">
    <table class="table table-flush">
      <thead>
        <tr>
          <th>Bank Name</th>
          <th>Account Number</th>
          <th>Opening</th>
          <th>Receiving</th>
          <th>Payment</th>
          <th>Closing</th>
          <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
        </tr>
      </thead>
      <tbody>
        @foreach($bank as $value)
        <tr>
          <td>{{$value->bank_name}}</td>
          <td>@if(!empty($value['bankAccount'])) {{$value['bankAccount']->account_no}} @else N/A @endif</td>
          <td>0</td>
          <td>0</td>
          <td>0</td>
          <td>0</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="col-md-12">
  <div class="card" id="expense">
    <div class="table-responsive">
      <table class="table table-flush" id="t">
        <thead>
          <tr>
            <th>Tr.No</th>
            <th>Tr.Date</th>
            <th>Receipt.No</th>
            <th>Tr.By</th>
            <th>Account Number</th>
            <th>Company Name</th>
            <th>Plan Name </th>
            <th>Member/Employee/Owner</th>
            <th>Associate</th>
            <th>Narration</th>
            <th>CR Narration</th>
            <th>DR Narration</th>
            <th>CR Amount</th>
            <th>DR Amount</th>
            <th>Balance</th>
            <th>Ref Id</th>
            <th>Payment Type</th>
            <th>Tag</th>
            <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
          </tr>
        </thead>
        <tbody>
          <?php
          $is_eli = 0;
          $f1 = 0;
          $f2 = 0;
          $getBranchOpening = getBranchOpeningDetail($branch_id);
          $balance = 0;
          if ($getBranchOpening->date == $start_date) {
            $balance = $getBranchOpening->total_amount;
          }
          if ($getBranchOpening->date < $start_date) {
            $getBranchTotalBalance = getBranchTotalBalanceAllTran($start_date, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id,$company_id);
            $balance = $getBranchTotalBalance;
          }
          ?>
          <tr>
            <td class="child" colspan="12" style=" text-align: right; ">Opening Balance </td>
            <td class="child" colspan="4"> {{number_format((float)$balance, 2, '.', '')}}</td>
          </tr>
          <tr class="first_row">
            <td class="child" colspan="12" style=" text-align: right; ">Closing Balance </td>
            <td class="child closing_balance" colspan="4"> {{number_format((float)$C_balance_cash, 2, '.', '')}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <button class="btn btn-primary" id="load_more" style="display:none;float: center;">Load More...</button>
  </div>
</div>
<div class="card-body ">
  <div class="row">
    <div class="col-lg-12 text-center">
      <a href="{{ URL::to('admin/print/report/day_book_duplicate/?from_date='.$start_date.'&to_date='.$end_date.'&branch='.bin2hex($branch_id).'&company_id='.bin2hex($company_id))}}" target="_blank" type="submit" class="btn btn-primary">View Print<i class="icon-paperplane ml-2"></i></a>
    </div>
  </div>
  @include('templates.admin.report.dublicate.new_day_book_script')