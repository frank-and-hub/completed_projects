<?php
$p = $p + 25;
$no = 1;
$crTotal = 0;
$drTotal = 0;
?>

@foreach ($subcategories->sortBy('sub_head') as $subcategory)
    @php
    
        array_push($childHead, $subcategory->head_id);
        
        $style = 'color:#021b02;font-weight: 700;';
        if ($subcategory->labels == 1) {
            $style = 'color:#089a08;font-weight: 900;';
        }
        if ($subcategory->labels == 2) {
            $style = 'color:#224be1;font-weight: 700;';
        }
        if ($subcategory->labels == 3) {
            $style = 'color: #ffa500;font-weight: 700;';
        }
        if ($subcategory->labels == 4) {
            $style = 'color: #6dcef5;font-weight: 700;';
        }
        if ($subcategory->labels == 5) {
            $style = 'color: #17ef05;font-weight: 700;';
        }
        if ($subcategory->labels == 6) {
            $style = 'color: #e43636;font-weight: 700;';
        }
        
        $subCount = count($subcategory->subcategory);
        
        $closingAmount = (isset($oldheadclosing[$subcategory->head_id])) ? $oldheadclosing[$subcategory->head_id] : $previousData[$subcategory->head_id] ?? 0;
    $drAmount =(isset($HeadBalance[$subcategory->head_id]['dr_amount'])) ? $HeadBalance[$subcategory->head_id]['dr_amount'] : 0;
    $crAmount =(isset($HeadBalance[$subcategory->head_id]['cr_amount'])) ? $HeadBalance[$subcategory->head_id]['cr_amount'] : 0;
    $closingAmount = $subcategory->is_trial == 1  ? 0 : $closingAmount;
    @endphp
    <tr>
        <td style="padding-left: {{ $p }}px;{{ $style }}">
            <?
               $array = [
                  'branch_id'=>$branch_id,
                  'company_id'=>$company_id,
                  'financial_year'=>$financial_year,
                  'name'=>$subcategory->sub_head,
                  'head_id'=>$subcategory->head_id,
                  'lebel'=>$subcategory->labels,
                  'child_id'=>gettype($subcategory->child_head) == 'array' ? implode(',', $subcategory->child_head) : $subcategory->child_head,
               ];
                $array = encrypt($array);
                $href = route('admin.trail_balance.sub_head',$array);
            ?>
            <a href="{{ $href }}" style="padding-left: {{ $p }}px;{{ $style }}">
                {{ $no }}. {{ strtoupper($subcategory->sub_head) }}
            </a>
        </td>
        <td>{{ number_format((float) ($subcategory->is_trial == 1) ? 0 : $closingAmount, 2, '.', '') }}</td>
        <td>{{ number_format((float)$drAmount, 2, '.', '')}}</td>
         <td>{{ number_format((float)$crAmount, 2, '.', '')}}</td>
        <td>{{ $subcategory->cr_nature == 1 ?  $closingAmount + $crAmount - $drAmount : $closingAmount + $drAmount - $crAmount }}</td>
        <input type="hidden" name="crnature[]" id="crnature" value="{{ $subcategory->cr_nature }}">
        <input type="hidden" name="drnature[]" id="drnature" value="{{ $subcategory->dr_nature }}">
    </tr>
    <?php $no++; ?>
@endforeach
