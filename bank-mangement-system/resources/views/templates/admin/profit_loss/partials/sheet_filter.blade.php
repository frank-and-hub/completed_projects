@php
$libalityMainAmount = 0;
$AssetMainAmount = 0;
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="d-flex justify-content-between mx-2 mb-2">
            <span class="font-weight-bold text_upper">INCOME</span>
            <span class="font-weight-bold text_upper ">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">

                    <tbody>
                    @forelse ($incomeHead[3] as $lib)
                            @php
                                $mainHead = 0;
                                $childHeadIds = array_unique(explode(',', ($lib->child_id)));
                                $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));

                                $mainHead += array_sum($headTwoTotal);
                                $libalityMainAmount += $mainHead;
                            @endphp
                            <tr>
                                <td class="text_upper">
                                    {{-- <a href="{{ URL::to('admin/profit-loss/detail/'.$lib->head_id.'/'.$lib->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">
                                    {{ $lib->sub_head }}
                                    </a> --}}
                                    <a href="{{ route('admin.profit-loss.labelTwo', ['head_id' => $lib->head_id,'labels'=>$lib->labels,'date'=>$start_date,'to_date'=>$to_date,'branch_id'=>$branch_id,'financial_year'=>$financial_year,'company_id'=>$company_id]) }}"> {{ $lib->sub_head }}

                                    </a>
                                </td>
                                <th class="text-dark" style="float:right;">&#x20B9;{{($lib->head_id == 6) ? $expenseAmount :  $mainHead }}</th>
                            </tr>

                        @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)
                            @php
                                $headAmouhnt = 0;
                                $childHeadIds = array_unique(explode(',', ($labelThree->child_id)));
                                $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));
                                $headAmouhnt += array_sum($headTwoTotal);
                            @endphp
                                <tr class="child_inactive" style="background-color: #dff9fb; border: 1px solid #ddd;">
                                    <td class="text_upper">
                                        {{-- <a href="{{ URL::to('admin/profit-loss/detail/'.$labelThree->head_id.'/'.$labelThree->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank">
                                        {{ $labelThree->sub_head }}
                                        </a> --}}
                                        <a href="{{ route('admin.profit-loss.labelTwo', ['head_id' => $labelThree->head_id,'labels'=>$labelThree->labels,'date'=>$start_date,'to_date'=>$to_date,'branch_id'=>$branch_id,'financial_year'=>$financial_year,'company_id'=>$company_id]) }}"> {{ $labelThree->sub_head }}

                                        </a>
                                    </td>
                                    <th class="text-dark" style="float:right;">&#x20B9;{{ $totalAmount[$labelThree->head_id] }}</th>

                                </tr>
                            @empty
                        @endforelse
                    @empty

                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="d-flex justify-content-between mx-2 mb-2">
            <span class="font-weight-bold text_upper">EXPENSE</span>
            <span class="font-weight-bold text_upper">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">

                    <tbody>
                        @forelse ($expenseHead[4] as $lib)
                        @php
                        $mainHead = 0;
                        $childHeadIds = array_unique(explode(',', ($lib->child_id)));
                        $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));

                        $mainHead += array_sum($headTwoTotal);
                        $AssetMainAmount += $mainHead;

                        @endphp
                        <tr>
                            <td class="text_upper">
                                <!-- <a href="{{ URL::to('admin/profit-loss/detail/'.$lib->head_id.'/'.$lib->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank"> -->

                                <a href="{{ route('admin.profit-loss.labelTwo', ['head_id' => $lib->head_id,'labels'=>$lib->labels,'date'=>$start_date,'to_date'=>$to_date,'branch_id'=>$branch_id,'financial_year'=>$financial_year,'company_id'=>$company_id]) }}"> {{ $lib->sub_head }}



                                </a>
                            </td>
                            <th class="text-dark" style="float:right;">&#x20B9;{{ $mainHead }}</th>
                        </tr>

                        @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)

                        @php
                        $headAmouhnt = 0;

                        $childHeadIds = array_unique(explode(',', ($labelThree->child_id)));
                        $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));

                        $headAmouhnt += array_sum($headTwoTotal);


                        @endphp
                        <tr class="child_inactive" style="background-color: #dff9fb; border: 1px solid #ddd;">
                            <td class="text_upper">
                                {{-- <a href="{{ URL::to('admin/profit-loss/detail/'.$labelThree->head_id.'/'.$labelThree->labels.'/?date='.$start_date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$financial_year)}}" target="_blank"> --}}
                                <a href="{{ route('admin.profit-loss.labelTwo', ['head_id' => $labelThree->head_id,'labels'=>$labelThree->labels,'date'=>$start_date,'to_date'=>$to_date,'branch_id'=>$branch_id,'financial_year'=>$financial_year,'company_id'=>$company_id]) }}"> {{ $labelThree->sub_head }}

                                </a>
                            </td>
                            <th class="text-dark " style="float:right;">&#x20B9;{{ $headAmouhnt }}</th>

                        </tr>
                        @empty

                        @endforelse
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.profit_loss.footer',['libalityMainAmount'=>$libalityMainAmount,'AssetMainAmount'=>$AssetMainAmount])