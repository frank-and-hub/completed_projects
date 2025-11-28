<?php
namespace App\Http\Traits;
trait EmiDatesTraits
{
    public function nextEmiDates($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 months'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function nextEmiDatesDays($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 days'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function nextEmiDatesWeekly($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime(convertDate($LoanCreatedDate . ' + 7 days')));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function nextEmiDatesWeek($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 7 days'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function getOutstandingAmount($emiPeriod, $emiOption, $CreatedAt, $amount, $Roi, $emiAmount)
    {
        $data = array();
        $out_standing_amount = $amount;
        $principalAmount = array();
        $roi = array();
        for ($i = 1; $i <= $emiPeriod; $i++) {
            if ($emiOption == 1) {
                $data[$i]['emi_date'] = date('d/m/Y', strtotime($CreatedAt . ' + ' . ($i) . ' month'));
                $data[$i]['roi'] = ((($Roi) / 12) * $out_standing_amount) / 100;
            } elseif ($emiOption == 2) {
                $data[$i]['emi_date'] = date('d/m/Y', strtotime($CreatedAt . ' + ' . ($i) . ' weeks'));
                $data[$i]['roi'] = ((($Roi) / 52.14) * $out_standing_amount) / 100;
            } elseif ($emiOption == 3) {
                $data[$i]['emi_date'] = date('d/m/Y', strtotime($CreatedAt . ' + ' . ($i) . ' days'));
                $data[$i]['roi'] = ((($Roi) / 365) * $out_standing_amount) / 100;
            }
            $data[$i]['principalAmount'] = $emiAmount - $data[$i]['roi'];
            $data[$i]['outStandingAmount'] = $out_standing_amount - $data[$i]['principalAmount'];
            $out_standing_amount = $data[$i]['outStandingAmount'];
        }
        return $data;
    }
    public function emiDates($LoanCreatedDate, $duration, $emidate = NULL)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $duration; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 months'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $emiDateYear = date('Y', strtotime($emidate));
            $emiDateMonth = date('m', strtotime($emidate));
            $emiDateday = date('d', strtotime($emidate));
            if (($emiDateYear == $LoanCreatedYear) && ($emiDateMonth == $LoanCreatedMonth)) {
                $nextEmiDates['LoanCreatedYear'] = $LoanCreatedYear;
                $nextEmiDates['LoanCreatedMonth'] = $LoanCreatedMonth;
            }
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function emiDatesForAPis($LoanCreatedDate, $duration, $emidate = NULL)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $duration; $i++) {
            $createdDate = $LoanCreatedDate;
            $LoanCreatedDate = date('Y-m-d', strtotime(convertDate($LoanCreatedDate . ' + 1 months')));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $emiDateYear = date('Y', strtotime($emidate));
            $emiDateMonth = date('m', strtotime($emidate));
            $emiDateday = date('d', strtotime($emidate));
            if ($LoanCreatedMonth == 2) {
                if ($LoanCreateDate == 28 || $LoanCreateDate == 29) {
                    $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = date('Y-m-t', strtotime(convertDate($LoanCreatedDate)));
                    continue;
                }
            }
            if ($LoanCreateDate == 31) {
                $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = date('Y-m-t', strtotime(convertDate($LoanCreatedDate)));
            } else {
                $nextEmiDates['LoanCreatedYear'] = $LoanCreatedYear;
                $nextEmiDates['LoanCreatedMonth'] = $LoanCreatedMonth;
                $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
            }
        }
        return $nextEmiDates;
    }
    public function nextEmiDatesDaily($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 days'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }
    public function accruedInterestCalcualte($loanType, $emiAmount, $currentAccruedInterest)
    {
        // dd($emiAmount,$currentAccruedInterest);
        $data['accruedInterest'] = ($emiAmount > $currentAccruedInterest) ? $currentAccruedInterest : $emiAmount;
        $data['principal_amount'] = ($emiAmount > $currentAccruedInterest) ? $emiAmount - $currentAccruedInterest : 0;
        return $data;
    }
}