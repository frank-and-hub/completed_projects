<?php
    
             $existsopening = App\Models\BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->exists();
            if($existsopening)
            {
              $cashInhandOpening =   App\Models\BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->orderBy('entry_date','DESC')->first();
              $cashInhandOpening = $cashInhandOpening->opening_balance;

            }
            else{

                $cashInhandOpening =   App\Models\BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $startDate)->orderBy('entry_date','DESC')->first();
                if(isset( $cashInhandOpening->balance))
                {
                    $cashInhandOpening = $cashInhandOpening->balance;   
                }
                else{
                    $cashInhandOpening  = 0;
                }
              

            }
           
               $cashInhandclosing = App\Models\BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $endDate)->orderBy('entry_date','DESC')->first();

               $cashInhandclosing = $cashInhandclosing->balance ;



?>

<table >

  <tr>
   
      <table>
        <tr>
          <th colspan="3"><strong >Cash Allocation1</strong></th>
        </tr>
        <tr>
          <td>Opening</td>
          <td></td>
          <td>{{number_format((float)$cashInhandOpening, 2, '.', '')}}</td>
        </tr>
        <tr>
            <td></td>
            <td>Received</td>
            <td> Payment</td>
        </tr>
        <tr>
            <td>Cash</td>
           <td>{{number_format((float)$cash_in_hand['CR'], 2, '.', '')}}</td>
           <td>{{number_format((float)$cash_in_hand['DR'], 2, '.', '')}}</td> 
        </tr>
        <tr>
            <td>Closing</td>
            <td></td>
            <td>{{number_format((float)$cashInhandclosing, 2, '.', '')}}</td> 
        </tr>

      </table>
   
      <table>
        <tr>
          <th colspan="3"><strong >Cheque</strong></th>
        </tr>
        <tr>
          <td>Opening</td>
          <td></td>
          <td>{{number_format((float)getchequeopeningBalance($startDate,$branch_id), 2, '.', '')}}</td> 
        </tr>
        <tr>
            <td></td>
            <td>Received</td>
            <td> Payment</td>
        </tr>
        <tr>
           <td >Cheque</td>
          <td>{{number_format((float)$cheque['CR'], 2, '.', '')}}</td>
            <td>{{number_format((float)$cheque['DR'], 2, '.', '')}}</td> 
           
        </tr>
        <tr>
            <td>Closing</td>
            <td></td>
            <td>{{number_format((float)getchequeclosingBalance($endDate,$branch_id), 2, '.', '')}}</td> 
        </tr>
    
      </table>
   
  
    
     
   
  </tr>
</table>