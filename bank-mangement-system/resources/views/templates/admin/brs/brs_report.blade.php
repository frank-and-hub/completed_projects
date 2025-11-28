<!DOCTYPE html>
<html>
<head>
  <style>
    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }
    td,
    th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }
    h3,
    h6 {
      text-align: center;
    }
    .topLine h5,
    .right {
      text-align: right;
    }
    .right {
      float: right
    }
    .left {
      float: left
    }
    .topTbl td,
    th {
      text-align: center;
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
</head>
<body>
  <div class="row topLine">
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <h3>{{$account_details[0]['getCompanyDetail'][0]['name']}}</h3>
    </div>
    <div class="col-sm-3">
      <h5>Run Date :-
        <?php echo date("d/m/Y"); ?>
      </h5>
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <h6>Corp. Office: {{$account_details[0]['getCompanyDetail'][0]['address']}}</h6>
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <h6>Phone No.: {{$account_details[0]['getCompanyDetail'][0]['mobile_no']}}
        </h6>
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <h6>Year: {{$selectedYear}}, Month: {{$selectedMonthName}}</h6>
    </div>
    <div class="col-sm-3">
    </div>
    <div class="col-sm-4">
    </div>
    <div class="col-sm-4">
      <h6>Bank: {{ $account_details[0]['samraddhbank']['bank_name']}}, Account Number:
        <?php if(!empty($account_details)) { echo $account_details[0]->account_no; } ?>
      </h6>
    </div>
  </div><br />
  <table class="topTbl">
    <tr>
      <th></th>
      <th>Particulars</th>
      <th>Account (RS)</th>
    </tr>
    <tr>
      <td></td>
      <td>Opening Balance as per Bank Statement</td>
      <td>{{$previousOpeningBalanceStatement}}</td>
    </tr>
    <tr>
      <td></td>
      <td>Closing Balance as per Bank Statement</td>
      <td>
        <?php echo $Currentclosingbankstatement; ?>
      </td>
    </tr>
    <tr>
      <td><span class="left">5a</span> <span class="right">Less</span></td>
      <td>Credited by bank but not debited in Daybook</td>
      <td>
        <?php echo $rest_all_total; ?>
      </td>
    </tr>
    <tr>
      <td><span class="left">5b</span> <span class="right">Add</span></td>
      <td>Debited by bank but not Credited in Daybook</td>
      <td>
        <?php echo $Bank_chagres_total; ?>
      </td>
    </tr>
    <tr>
      <td><span class="left">5c</span> <span class="right">Add</span></td>
      <td>Debited in Daybook but Not Credited by Bank</td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by_total; ?>
      </td>
    </tr>
    <tr>
      <td><span class="left">5d</span> <span class="right">Less</span></td>
      <td>Credited in Daybook but Not Debited by Bank</td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by_total; ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>Opening Bank Balance as per Daybook</td>
      <td>{{$previousOpeningDaybookBalance}}</td>
    </tr>
    <tr>
      <td></td>
      <td>Closing Bank Balance as per Daybook</td>
      <td>
        <?php echo $closing_balance; ?>
      </td>      
    </tr>
  </table>
  </br />
  <p>5a-details of credited by bank not debited in Daybook:- </p>
  <table>
    <tr>
      <th>SR.No.</th>
      <th>Particulars</th>
      <th>Rcpt/Vchr No.</th>
      <th>Chq No.</th>
      <th>Deposit Date</th>
      <th>Amount</th>
    </tr>
    <?php if(!empty($rest_all)){ for($a=0; $a<count($rest_all); $a++){?>
    <tr>
      <td>
        <?php echo $a + 1; ?>
      </td>
      <td>
        <?php echo $rest_all[$a]["particular"]; ?>
      </td>
      <td>
        <?php echo $rest_all[$a]["transction_no"]; ?>
      </td>
      <td>
        <?php echo $rest_all[$a]["cheque_no"]; ?>
      </td>
      <td>
        <?php echo $rest_all[$a]["entry_date"]; ?>
      </td>
      <td>
        <?php echo $rest_all[$a]["amount"]; ?>
      </td>
    </tr>
    <?php } } ?>
    <tr>
      <td colspan="5">Total</td>
      <td colspan="1">
        <?php echo $rest_all_total; ?>
      </td>
    </tr>
  </table>
  </br />
  <p>5b-details of debited by bank not credited in Daybook:- </p>
  <table>
    <tr>
      <th>SR.No.</th>
      <th>Particulars</th>
      <th>Rcpt/Vchr No.</th>
      <th>Chq No.</th>
      <th>Deposit Date</th>
      <th>Amount</th>
    </tr>
    <?php if(!empty($Bank_chagres)){ for($b=0; $b<count($Bank_chagres); $b++){?>
    <tr>
      <td>
        <?php echo $b + 1; ?>
      </td>
      <td>
        <?php echo $Bank_chagres[$b]["particular"]; ?>
      </td>
      <td>
        <?php echo $Bank_chagres[$b]["transction_no"]; ?>
      </td>
      <td>
        <?php echo $Bank_chagres[$b]["cheque_no"]; ?>
      </td>
      <td>
        <?php echo $Bank_chagres[$b]["entry_date"]; ?>
      </td>
      <td>
        <?php echo $Bank_chagres[$b]["amount"]; ?>
      </td>
    </tr>
    <?php } } ?>
    <tr>
      <td colspan="5">Total</td>
      <td colspan="1">
        <?php echo $Bank_chagres_total; ?>
      </td>
    </tr>
  </table><br />
  <p>5c-details of Debited in Daybook but Not Credited by Bank:- </p>
  <table>
    <tr>
      <th>SR.No.</th>
      <th>Particulars</th>
      <th>Rcpt/Vchr No.</th>
      <th>Chq No.</th>
      <th>Deposit Date</th>
      <th>Amount</th>
    </tr>
    <?php if(!empty($Debited_in_Daybook_but_Not_Credited_by)){ for($c=0; $c<count($Debited_in_Daybook_but_Not_Credited_by); $c++){?>
    <tr>
      <td>
        <?php echo $c + 1; ?>
      </td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by[$c]["particular"]; ?>
      </td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by[$c]["transction_no"]; ?>
      </td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by[$c]["cheque_no"]; ?>
      </td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by[$c]["entry_date"]; ?>
      </td>
      <td>
        <?php echo $Debited_in_Daybook_but_Not_Credited_by[$c]["amount"]; ?>
      </td>
    </tr>
    <?php } } ?>
    <tr>
      <td colspan="5">Total</td>
      <td colspan="1">
        <?php echo $Debited_in_Daybook_but_Not_Credited_by_total; ?>
      </td>
    </tr>
  </table>
  <p>5d-details of Credited in Daybook but Not Debited by Bank:- </p>
  <table>
    <tr>
      <th>SR.No.</th>
      <th>Particulars</th>
      <th>Rcpt/Vchr No.</th>
      <th>Chq No.</th>
      <th>Deposit Date</th>
      <th>Amount</th>
    </tr>
    <?php if(!empty($Credited_in_Daybook_but_Not_Debited_by)){ for($dd=0; $dd<count($Credited_in_Daybook_but_Not_Debited_by); $dd++){?>
    <tr>
      <td>
        <?php echo $dd + 1; ?>
      </td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by[$dd]["particular"]; ?>
      </td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by[$dd]["transction_no"]; ?>
      </td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by[$dd]["cheque_no"]; ?>
      </td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by[$dd]["entry_date"]; ?>
      </td>
      <td>
        <?php echo $Credited_in_Daybook_but_Not_Debited_by[$dd]["amount"]; ?>
      </td>
    </tr>
    <?php } } ?>
    <tr>
      <td colspan="5">Total</td>
      <td colspan="1">
        <?php echo $Credited_in_Daybook_but_Not_Debited_by_total; ?>
      </td>
    </tr>
  </table> <br /><br />
  <p><b>Note: </b>This is report is for statutory Auditors and cannot send to command office</p>
  <p><input type="button" name="printData" id="printData" value="Print" /></p>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  $(document).ready(function () {
    $("#printData").click(function () {
      window.print();
    });
  });
</script>
</html>