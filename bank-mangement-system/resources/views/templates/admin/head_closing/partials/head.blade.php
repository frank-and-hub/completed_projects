<div class="card pt-3 pb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-semibold"><strong>Name of the Company:</strong> {{ $companyDetail->name }}</h3>
        <h6 class="card-title font-weight-semibold pt-2"><strong>Address:</strong> {{ $companyDetail->address }}</h6>
        @if ($type == 1)
        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export
            xslx</button>
            @endif
    </div>

</div>
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-semibold">Head Closing List ({{ $start_y }} - {{ $end_y }})</h6>
    </div>
    <div class="card-body">
        {{Form::open(['url'=>'#','method'=>'POST','name'=>'myform','id'=>'myform'])}}
            <input type="hidden" name="sdate" value="{{ $start_y }}">
            <input type="hidden" name="edate" value="{{ $end_y }}">
            <input type="hidden" name="company_id" value="{{ $company_id }}">
            <input type="hidden" name="branchId" value="{{ $branchId }}">
            @if ($type == 1)
            <button style="float:right;" type="button" class="btn btn-danger" id="reset_finance_head" onClick="resetFinanceHead()">Delete </button>
        @endif
            <table id="head_listing" class="table">

                <thead>

                    <tr>
                        <th>Head Name</th>
                        <th> Amount </th>
                        <!-- <th>DR Amount </th>   -->



                    </tr>

                </thead>

                <tbody>
                    <?php $p = 40; ?>

                    @foreach ($data as $head)
                        <?php
                        $colorss = '';
                        if ($type == 1) {
                            $closingDetail1 = getheadClosingValue2($start_y, $end_y, $head->head_id, $company_id,$branchId);
                        
                            $amount = 0.0;
                            if ($closingDetail1) {
                                $amount = $closingDetail1->amount;
                            }
                            $mystring1 = $amount;
                        
                            if (strpos($mystring1, '-') !== false) {
                                $colorss = 'red';
                            } else {
                                if ($amount > 0) {
                                    $colorss = 'green';
                                }
                            }
                        }
                        
                        ?>
                        <?php
                        $data = headTree($head->head_id, $company_id);
                        
                        ?>
                        <tr style="color:#089a08">
                            <td>{{ strtoupper($head->sub_head) }}</td>
                            @if ($type == 1)
                                <td style="color:{{ $colorss }} ">{{ number_format((float) $amount, 2, '.', '') }}
                                </td>
                            @else
                                <td><input type="text" value="0.00" name="head_amount[]" class="aa head_{{ $head->head_id }} " @if (count($data) > 0) readonly1 @endif>
                                    <!-- <td><input type="text" value="0.00"  name="head_amount_dr[]" class="aa head_{{ $head->head_id }} " @if (count($data) > 0) readonly1 @endif> -->

                                    <div class="row pl-2" style="color:red;">
                                    </div>
                                    <input type="hidden" name=head_id[] value="{{ $head->head_id }}">
                                </td>
                            @endif
                        </tr>

                        @if (count($data) > 0)
                            @foreach ($data as $taxonomy)
                                <?php
                                $colora = '';
                                if ($type == 1) {
                                    $closingDetail = getheadClosingValue2($start_y, $end_y, $taxonomy->head_id, $company_id,$branchId);
                                    
                                    $amount1 = 0.0;
                                    if ($closingDetail) {
                                        $amount1 = $closingDetail->amount;
                                    }
                                    $mystring = $amount1;
                                
                                    if (strpos($mystring, '-') !== false) {
                                        $colora = 'red';
                                    } else {
                                        if ($amount > 0) {
                                            $colora = 'green';
                                        }
                                    }
                                }
                                
                                ?>


                                <tr>
                                    <td style="padding-left: {{ $p }}px">
                                        {{ strtoupper($taxonomy->sub_head) }}</td>
                                    @if ($type == 1)
                                        <td style="color:{{ $colora }} ">
                                            {{ number_format((float) $amount1, 2, '.', '') }} </td>
                                    @else
                                        <td><input type="text" value="0.00" data-parent="{{ $head->sub_head }}" name="head_amount[]" class="aa head_{{ $taxonomy->parent_id }} head_1_{{ $taxonomy->head_id }}" data-type="head_{{ $taxonomy->parent_id }}" @if (count($taxonomy->subcategory) > 0) readonly1 @endif>
                                            <!-- <td><input type="text" value="0.00" data-parent="{{ $head->sub_head }}" name="head_amount_dr[]" class="aa head_{{ $taxonomy->parent_id }} head_1_{{ $taxonomy->head_id }}" data-type="head_{{ $taxonomy->parent_id }}" @if (count($taxonomy->subcategory) > 0) readonly1 @endif> -->

                                            <div class="row pl-2" style="color:red;">
                                            </div>
                                            <input type="hidden" name=head_id[] value="{{ $taxonomy->head_id }}">
                                        </td>
                                    @endif
                                </tr>
                                @if (count($taxonomy['subcategory']) > 0)
                                    @include('templates.admin.head_closing.partials.head_tree', [
                                        'subcategories' => $taxonomy,
                                        'p' => $p,
                                        'start_y' => $start_y,
                                        'end_y' => $end_y,
                                        'type' => $type,
                                        'company_id' => $company_id,
                                        'branch_id' =>$branchId,
                                    ])
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    @if ($type != 1)
                        <tr>
                            <td colspan="2" style="text-align: right;"><input type="submit" name="save" value="Save" class="btn btn-primary" id="myformsubmit">
                                <button type="button" class="btn btn-gray" id="my_form_reset" onClick="myFormreset()"> Reset </button>
                            </td>
                        </tr>
                    @endif
                </tbody>


            </table>
            {{Form::close()}}
        
    </div>
</div>
