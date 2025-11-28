@extends('templates.admin.master')



@section('content')


    <div class="content">

        <div class="row">

            

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Fixed Assets List</h6>

                        <div class="">

                           <a class="font-weight-semibold" href="{!! route('admin.create.fixed_asset') !!}"><i class="icon-file-plus mr-2"></i>Fixed Asset</a>

                        </div>

                    </div>

                    <div class="">

                        <table id="head_listing" class="table">

                            <thead>

                                <tr>

                                   <th>Parent Head Name</th>

                                    <th>Head Name</th>

                                    <th>Status</th>

                                    <th>Action</th>    

                                </tr>

                            </thead>  

                            <tbody>

                                <tr>

                                    <td>{{getAcountHead($head->parent_id)}}</td>

                                    <td>{{$head->sub_head}}</td>

                                    <td>@if($head->status == 0)

                                        Active

                                        @else

                                        InActive

                                        @endif

                                    </td>

                                    <!--  <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$head->head_id,$head->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>
                                        </div></div>
                                    </td> -->
                                    
                                </tr>

                                <?php  $arrayCategories = array();

                                $data=getFixedAsset($head->head_id); 

                              

                            ?>

                            @if(count($data)>0) 

                                @foreach($data as $child_asset)

                                <tr>

                                    <td>{{getAcountHeadData($child_asset->parent_id)}}</td>

                                    <td>{{$child_asset->sub_head}}</td>

                                    <td>@if($child_asset->status ==0)

                                        Active

                                        @else

                                        InActive

                                        @endif</td>

                                    <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">

                                         <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$child_asset->head_id,$child_asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>

                                        <a class="dropdown-item" href="{!! route('admin.edit.fixed_asset',$child_asset->id) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>

                                @if($child_asset->status == 0)

                                    <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$child_asset->id}});"><i class="icon-checkmark4 mr-2"></i>Inactive</button>  

                

                            @else{

                            <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$child_asset->id}});"><i class="icon-checkmark4 mr-2"></i>Active</button>   

                            @endif

                            </div>

                        </div></td>

                                </tr>

                                <?php

                                $sub_child=getsubChildFixedAsset($child_asset->head_id);  

                                ?>

                                @if(count($sub_child) > 0)

                                     @foreach($sub_child as $sub_child_asset)

                                <tr>

                                    <td>{{getAcountHeadData($sub_child_asset->parent_id)}}</td>

                                    <td>{{$sub_child_asset->sub_head}}</td>

                                    <td>@if($sub_child_asset->status == 0)

                                        Active

                                        @else

                                        InActive

                                        @endif</td>

                                    <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">

                                          <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$sub_child_asset->head_id,$sub_child_asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>

                                        <a class="dropdown-item" href="{!! route('admin.edit.fixed_asset',$sub_child_asset->id) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>

                                @if($sub_child_asset->status == 0)

                                    <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$sub_child_asset->id}});"><i class="icon-checkmark4 mr-2"></i>Inactive</button>  

                

                            @else{

                            <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$sub_child_asset->id}});"><i class="icon-checkmark4 mr-2"></i>Active</button>   

                            @endif

                            </div>

                        </div></td>

                                </tr>

                                <?php

                                $sub_child_sub_asset=getsubChildsubAssetFixedAsset($sub_child_asset->head_id);  

                                ?>

                                @if(count($sub_child_sub_asset)>0)

                                @foreach($sub_child_sub_asset as $asset )

                                <tr>

                                     <td>{{getAcountHeadData($asset->parent_id)}}</td>

                                    <td>{{$asset->sub_head}}</td>

                                    <td>@if($asset->status == 0)

                                        Active

                                        @else

                                        InActive

                                        @endif</td>

                                    <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">

                                         <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$asset->head_id,$asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>

                                        <a class="dropdown-item" href="{!! route('admin.edit.fixed_asset',$asset->id) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>

                                @if($asset->status == 0)

                                    <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$asset->id}});"><i class="icon-checkmark4 mr-2"></i>Inactive</button>  

                

                            @else{

                            <button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate({{$asset->id}});"><i class="icon-checkmark4 mr-2"></i>Active</button>   

                            @endif

                            </div>

                        </div></td>

                                </tr>

                                @endforeach    

                                @endif

                                @endforeach

                                @endif

                                @endforeach



                            @endif



                            </tbody>                  

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@stop

@section('script')

    @include('templates.admin.fixed_asset.partials.script')

@stop