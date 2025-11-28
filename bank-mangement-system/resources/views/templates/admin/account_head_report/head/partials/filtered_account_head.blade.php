                  
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Head List </h6>
                    </div>
                    @if($count > 0)
					<div class="filtered-account-headlisting">
                       
                        <table id="head_listing" class="table">
                            <thead>
                                <tr>
                                    <th>Head Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($heads as $head)
                                    <tr style="color:#089a08">
                                    <td >{{$head->sub_head}}</td>
                                    @if($head->labels != 1)
                                    <td>@if($head->parent_id){{getAcountHeadData($head->parent_id)}}@else @endif</td>@endif
                                    <td>@if($head->status == 0)
                                        Active
                                        @else
                                        InActive
                                        @endif
                                    </td>
                                    @if($head->parent_id)
                                    <td>
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                            @if(check_my_permission( Auth::user()->id,"146") == "1"  )    
                                            <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$head->head_id,$head->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>
                                            @endif 
                                            @if(check_my_permission( Auth::user()->id,"145") == "1" &&  $count > 0 )
                                            <a class="dropdown-item" href="{!! route('admin.edit.head',$head->id,'/',$head->labels) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>
                                            @endif
                                        </div>
                                    </td>
                                    @else
                                    <td class="subhead" data-value="{{$head->head_id}}"><i  class=" {{$head->head_id}}-icon  fas fa-angle-up"></i>
                                    </td>
                                    @endif
                                </tr>
                                <?php  
                                $arrayCategories = array();
                                $data=getFixedAsset($head->head_id,$companyLists);
                                ?>
                                @if(count($data)>0) 
                                @foreach($data as $child_asset)
                                <?php
                                // echo "<pre>";
                                // print_r($child_asset);
                                $sub_child=getsubChildFixedAsset($child_asset->head_id,$companyLists);
                                $headno = 'head'.$child_asset->labels ;
                                // echo "<pre>";
                                // print_r($child_asset->head_id);
                                $countTransaction =App\Models\AllHeadTransaction::getCompanyRecords("CompanyId",$companyListing)->where('head_id',$child_asset->head_id)->count();
                                ?>
                                <tr style="display: none;"  class="{{$child_asset->parent_id}}-child_head head_2" data-value="{{$child_asset->head_id}}">
                                    <td class="child_head" style="padding-left: 35px" data-value="{{$child_asset->head_id}}">{{$child_asset->sub_head}}  @if(count($sub_child)>0) <i class="{{$child_asset->head_id}}-icon fas fa-angle-up" id="head2" ></i>@endif</td>
                                    <td>@if($child_asset->status == 0)
                                        Active
                                        @else
                                        InActive
                                        @endif

                                    </td>
                                        @if(check_my_permission( Auth::user()->id,"145") == "1"  || check_my_permission( Auth::user()->id,"146") == "1" || check_my_permission( Auth::user()->id,"147") == "1" )  
                                    <td>
                                        <div class="list-icons">
                                            <div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                            @if(check_my_permission( Auth::user()->id,"146") == "1"  )
                                                @if($child_asset->head_id!="6")
                                                <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$child_asset->head_id,$child_asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a> 
                                                @endif
                                            @endif
                                            @if(check_my_permission( Auth::user()->id,"145") == "1"  &&  $count > 0 )
                                            <a class="dropdown-item" href="{!! route('admin.edit.head',[$child_asset->id,$child_asset->labels]) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>
                                            @endif
                                        
                                            <!-- @if($countTransaction == 0)
                                                @if(check_my_permission( Auth::user()->id,"147") == "1"  )
                                                <a class="dropdown-item" href="{!! route('admin.delete.head',[$child_asset->id,$child_asset->labels]) !!}" title="Delete"><i class="icon-pencil7  mr-2"></i>Delete</a>    
                                                @endif
                                            @endif -->
                                            </div>
                                        </div>
                                    </td> 
                                 @else
                                 <td></td>
                                @endif
                                </tr>
                                <?php
                                $sub_child=getsubChildFixedAsset($child_asset->head_id,$companyLists);  
                                ?>
                                @if(count($sub_child) > 0)
                                @foreach($sub_child as $sub_child_asset)
                                    <?php 
                                    $sub_child_sub_asset=getsubChildsubAssetFixedAsset($sub_child_asset->head_id,$companyLists); 
                                    $headno = 'head'.$sub_child_asset->labels ;
                                    $countTransaction =App\Models\AllHeadTransaction::getCompanyRecords("CompanyId",$companyListing)->where('head_id',$sub_child_asset->head_id)->count();
                                    ?>
                                    <tr style="display: none;" class="{{$sub_child_asset->parent_id}}-sub_child_head head3 " data-value="{{$sub_child_asset->head_id}}">
                                        <td style="padding-left: 50px" class="sub_child_head" data-value="{{$sub_child_asset->head_id}}">{{$sub_child_asset->sub_head}}
                                        @if(count($sub_child_sub_asset) > 0)  <i class="{{$sub_child_asset->head_id}}-icon fas fa-angle-up" ></i>@endif
                                        </td>
                                    
                                    

                                        <td>
                                            @if($sub_child_asset->status ==0)

                                            Active

                                            @else

                                            InActive

                                            @endif
                                        </td>
                                        @if(check_my_permission( Auth::user()->id,"145") == "1"  || check_my_permission( Auth::user()->id,"146") == "1" || check_my_permission( Auth::user()->id,"147") == "1" )     
                                        <td>

                                            <div class="list-icons">

                                                <div class="dropdown">

                                                    <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">
                                                        @if(check_my_permission( Auth::user()->id,"146") == "1"  )    
                                                        @if($sub_child_asset->parent_id!="6")
                                                            <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$sub_child_asset->head_id,$sub_child_asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a> 
                                                        @endif
                                                    @endif   
                                                        @if(check_my_permission( Auth::user()->id,"145") == "1" &&  $count > 0  )
                                                    <a class="dropdown-item" href="{!! route('admin.edit.head',[$sub_child_asset->id,$sub_child_asset->labels]) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>
                                                    @endif

                                                    
                                                    <!-- @if($countTransaction == 0)
                                                        @if(check_my_permission( Auth::user()->id,"147") == "1"  )
                                                        <a class="dropdown-item" href="{!! route('admin.delete.head',[$sub_child_asset->id,$sub_child_asset->labels]) !!}" title="Delete"><i class="icon-pencil7  mr-2"></i>Delete</a>    
                                                        @endif
                                                    @endif -->



                                                </div>

                                            </div>

                                        </td>
                                        @else
                                        <td></td>
                                        @endif

                                    </tr>

                                    <?php

                                    $sub_child_sub_asset=getsubChildsubAssetFixedAsset($sub_child_asset->head_id,$companyLists);  

                                    ?>

                                    @if(count($sub_child_sub_asset)>0)

                                    @foreach($sub_child_sub_asset as $asset )
                                    <?php

                                    $head5=getheadlabel5($asset->head_id,$companyLists);  
                                    $headno = 'head'.$asset->labels ;
                                    $countTransaction =App\Models\AllHeadTransaction::getCompanyRecords("CompanyId",$companyListing)->where('head_id',$asset->head_id)->count();  

                                    ?>
                                    <tr style="display: none;" class="{{$asset->parent_id}}-sub_child_head2 head3" data-value="{{$asset->head_id}}">
                                        <td style="padding-left: 75px" class="sub_child_head2" data-value="{{$asset->head_id}}">{{$asset->sub_head}} @if(count($head5) > 0)  <i class="{{$asset->head_id}}-icon fas fa-angle-up" ></i>@endif
                                        

                                        

                                        <td>@if($asset->status == 0)

                                            Active

                                            @else

                                            InActive

                                            @endif</td>
                                        @if(check_my_permission( Auth::user()->id,"145") == "1"  || check_my_permission( Auth::user()->id,"146") == "1" || check_my_permission( Auth::user()->id,"147") == "1" )    
                                        <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">
                                            @if(check_my_permission( Auth::user()->id,"146") == "1"  )
                                                @if($asset->parent_id!="17")
                                                <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$asset->head_id,$asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a>
                                                @endif 
                                            @endif   
                                            @if(check_my_permission( Auth::user()->id,"145") == "1"  &&  $count > 0 )
                                            <a class="dropdown-item" href="{!! route('admin.edit.head',[$asset->id,$asset->labels]) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>
                                            @endif
                                            
                                            <!-- @if($countTransaction == 0)
                                                @if(check_my_permission( Auth::user()->id,"147") == "1"  )
                                                    <a class="dropdown-item" href="{!! route('admin.delete.head',[$asset->id,$asset->labels]) !!}" title="Delete"><i class="icon-pencil7  mr-2"></i>Delete</a>    
                                                @endif
                                            @endif -->
                                    

                                            </div>

                                        </div>

                                        </td>
                                        @else
                                            <td></td>
                                        @endif

                                    </tr>
                                    <?php
                                    $head5=getheadlabel5($asset->head_id,$companyLists);  
                                    ?>

                                    @if(count($head5)>0)

                                    @foreach($head5 as $assets )
                                    <?php  
                                        $headno = 'head'.$assets->labels ;
                                        $countTransaction =App\Models\AllHeadTransaction::getCompanyRecords("CompanyId",$companyListing)->where('head_id',$assets->head_id)->count();
                                    ?>
                                    <tr style="display: none;" class="{{$assets->parent_id}}-head5 head3">
                                        <td style="padding-left:100px">{{$assets->sub_head}}</td>
                                        <td>@if($asset->status == 0)

                                            Active

                                            @else

                                            InActive

                                            @endif</td>
                                            @if(check_my_permission( Auth::user()->id,"145") == "1"  || check_my_permission( Auth::user()->id,"146") == "1" || check_my_permission( Auth::user()->id,"147") == "1" )  
                                        <td><div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">
                                            @if(check_my_permission( Auth::user()->id,"146") == "1"  )
                                                <a class="dropdown-item" href="{!!route('admin.accountHeadLedger',[$assets->head_id,$asset->labels])!!}" target="blank"><i class="icon-list mr-2"></i>Transactions</a> 


                                              

                                            @endif  
                                                @if(check_my_permission( Auth::user()->id,"145") == "1" &&  $count > 0  )
                                            <a class="dropdown-item" href="{!! route('admin.edit.head',[$assets->id,$asset->labels]) !!}" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>
                                            @endif

                                            </div>

                                            </div>

                                        </td>
                                        @else
                                        <td></td>
                                        @endif
                                    </tr>
                                
                                    @endforeach    

                                @endif

                                @endforeach

                                @endif

                                @endforeach
                                
                                @endif

                                @endforeach

                            @endif

                            @endforeach
                            
                            </tbody>                  
                        
                        
                        </table>
                       
                    </div>
                    @else
                    <div class="card-header header-elements-inline notfoundmsg">
                        <h6 class="card-title font-weight-semibold">No Heads Found. Please Select Other Company </h6>
                    </div>

                    @endif
                </div>

           
           
        
        