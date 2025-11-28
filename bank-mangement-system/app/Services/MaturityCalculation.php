<?php
namespace App\Services;

class MaturityCalculation
{
    const DAYS_IN_MONTH = 30;
    const MONTHS_IN_YEAR = 12;

    /**
     * Calcualte Maturity Amount Based on provided request data
     * @param $request,
     * @return float  maturityAmount
     */

    public function calculateMaturityAmount(object $request)
    {
        // Assigns the values to the variable from the request
        // dd($request->all());
        $principleAmount = $request["amount"];
        $rate = $request["rate"];
        $time = $request["tenure"]/12;
        $planType = $request["plan_category_code"];
        $planSubType = $request["plan_sub_category_code"];
        $maturityAmount = 0;
        $freq = self::MONTHS_IN_YEAR;
        switch ($planType) {
            // For the D Category Plan
            case 'D':
                // Set the compounding interval to the number of days in a month
                $ci = $request['compounding']??self::DAYS_IN_MONTH;
                // Adjsut the rate accordingly
                $rate = $rate;
                // Calculate monthly Amount
                $monthlyAmount = $principleAmount * self::DAYS_IN_MONTH;
                // Calculate Maturity Amount for the D (D Category) plan
               
                $maturityAmount = $this->dailyMaturityCalculation(
                    $monthlyAmount,
                    $rate,
                    $time,
                    $freq,
                    $ci
                );
                break;
            // For the F category Plan
            case 'F':
                // Set the compounding interval
                $ci = 1;
                // Adjust the rate accordingly
                $rate = $rate / $ci;
                // Calculate Maturity Amount for the F category plan
                $maturityAmount = $this->FlexiMaturityCalcution(
                    $principleAmount,
                    $rate,
                    $time,
                    $planSubType
                );
                break;
            // For the M category plan
            case 'M':
                // Set the compounding interval
                $ci = $request['compounding']??1;
                // Adjust the rate accordingly
                $rate = $rate;
                // Calculate maturity Amount for the M Category Plan
                $maturityAmount = $this->dailyMaturityCalculation(
                    $principleAmount,
                    $rate,
                    $time,
                    $freq,
                    $ci
                );

                break;
            default:
                // Handle unknown plan Type
                $maturityAmount = 0;
        }
        return $maturityAmount;
    }

    /**
     * Calcualte maturity amount for the Daily (D) category plan
     * @param  $monthlyPricipal,$rate,$time,$freq
     * @return integer maturity amount
     */

    private function dailyMaturityCalculation(
        $monthlyPricipal,
        $rate,
        $time,
        $freq,
        $compounding
    ) {
        // Calcualte Maturity Amount
       $maturity = $monthlyPricipal * ((pow(($rate/($freq/$compounding))/100 + 1, (($freq/$compounding)*$time)) - 1) / (1-(pow(($rate/($freq/$compounding))/ 100 + 1,(-1/$compounding)))));


       

        return $maturity;
    }

    /**
     * Calcualte maturity amount for the Daily (F) category plan
     * @param  $monthlyPricipal,$rate,$time,$planSubType
     * @return integer maturity amount
     */

    private function FlexiMaturityCalcution(
        $principal,
        $rate,
        $time,
        $planSubType
    ) {
        // Check if the plan_ctaegory_code is I (mis) then principlaAmount is maturity Amount
        // Else  Calculate maturity amount
        return $planSubType === "I"
            ? $principal
            : $principal * pow(1 + $rate / 100, $time);
    }
}
