<?php 
$p=$p+20;
$companyList = $company_id;
$arrayCompanyList = explode(' ', $companyList);
$companyList = array_map(function ($value) {
    return intval($value);
}, $arrayCompanyList);
$dataa = $subcategories->subcategory()->whereJsonContains("company_id", $companyList)->where('status',0)->get();;
?>
@foreach($dataa  as $subcategory)
<?php
$coloraa='';
if($type==1)
{
	$closingDetail=getheadClosingValue2($start_y,$end_y,$subcategory->head_id,$company_id,$branch_id);
	$amount=0.00; 
        if($closingDetail)
        {
        	$amount=$closingDetail->amount;             
        }
        $mystring = $amount;
        if(strpos($mystring, '-') !== false){
            $coloraa='red';
        } else{
          if($amount>0)
          {
            $coloraa='green';
          }
        }
}
?>
<tr > 
   <td style="padding-left: {{$p}}px">{{strtoupper($subcategory->sub_head)}}</td>
      @if($type==1)
      <td style="color:{{$coloraa}} "> {{  number_format((float)$amount, 2, '.', '') }} </td> 
      @else
         <td><input type="text" name="head_amount[]" value="0.00" data-parent="{{$taxonomy->sub_head}}" class="aa head_{{$subcategory->parent_id}} head_1_{{$subcategory->head_id}}"  data-type="head_{{$subcategory->parent_id}}" @if(count($subcategory->subcategory)>0)  readonly1 @endif>
         <!-- <td><input type="text" name="head_amount_dr[]" value="0.00" data-parent="{{$taxonomy->sub_head}}" class="aa head_{{$subcategory->parent_id}} head_1_{{$subcategory->head_id}}"  data-type="head_{{$subcategory->parent_id}}" @if(count($subcategory->subcategory)>0)  readonly1 @endif> -->
         <div class="row pl-2" style="color:red;">
            </div>
      <input type="hidden" name=head_id[] value="{{$subcategory->head_id}}" >
      </td>
      @endif
</tr>
@if(count($subcategory->subcategory)>0)
            @include('templates.admin.head_closing.partials.head_tree',['subcategories' => $subcategory,'p'=>$p,'start_y' =>$start_y, 'end_y'=>$end_y,'type'=>$type,'company_id'=>$company_id])
         @endif
@endforeach 