@extends('templates.admin.master')

@section('content')
<style type="text/css">
    


/* It's supposed to look like a tree diagram */
.tree, .tree ul, .tree li {
    list-style: none;
    margin: 0;
    padding: 0;
    position: relative;
}

.tree {
    margin: 0 0 1em;
    text-align: center;
}
.tree, .tree ul {
    display: table;
}
.tree ul {
  width: 100%;
}
    .tree li {
        display: table-cell;
        padding: .5em 0;
        vertical-align: top;
    }
        /* _________ */
        .tree li:before {
            outline: solid 1px #666;
            content: "";
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
        }
        .tree li:first-child:before {left: 50%;}
        .tree li:last-child:before {right: 50%;}

        .tree code, .tree span {
            border: solid .1em #666;
            border-radius: .2em;
            display: inline-block;
            margin: 0 .2em .5em;
            padding: .2em .5em;
            position: relative;
        }
        /* If the tree represents DOM structure */
       

            /* | */
            .tree ul:before,
            .tree code:before,
            .tree span:before {
                outline: solid 1px #666;
                content: "";
                height: .5em;
                left: 50%;
                position: absolute;
            }
            .tree ul:before {
                top: -.5em;
            }
            .tree code:before,
            .tree span:before {
                top: -.55em;
            }

/* The root node doesn't connect upwards */
.tree > li {margin-top: 0;}
    .tree > li:before,
    .tree > li:after,
    .tree > li > code:before,
    .tree > li > span:before {
      outline: none;
    }
.tree-overflow {
    overflow: auto;
}
.tree-overflow .tree li code {
    min-width: 230px;
	background: #f1f1f1;
	color: #000;
}
.carder{min-width: 75px !important;}
.tree-overflow .tree li code.inacitve_child_code {
    background: #ee7979;
    color: #fff;
}
tr.child_inactive,tr.parent_inactive,.parent_inactive_li  { background: #ee7979;
    color: #fff;}

</style>
    <div class="content">
        <div class="row">
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Associate</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.associate.tree') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                
                                <div class="col-md-5">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Associate code  </label>
                                        <div class="col-lg-8 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  value="@isset($code) {{ $code }} @endisset" > 
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 " > 
                                            <button type="Submit" class=" btn bg-dark legitRipple" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @isset($associate)
            <div class="col-md-12" id="show_tree">

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Tree Listing </h6> 
                        <div class="">
                            <form action="{!! route('admin.associate.exportAssociateTree') !!}" method="post" enctype="multipart/form-data" id="export_form" name="export_form">
                        @csrf
                        <input type="hidden" name="member_id" id="member_id" value="{{ $associate->id }}">
                         <input type="hidden" name="member_export" id="member_export" value="">

                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        <!--<button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </form>
                    </div>
                    </div>
                    <div class="">
                        <table id="member_listing" class="table ">
                            <thead>
                                <tr>  
                                <th>Customer ID</th>                                  
                                    <th>Associate Code</th>
                                    <th>Associate Name</th> 
                                    <th>Associate Carder </th>
                                    <th>Senior Code</th>
                                    <th>Senior Name</th> 
                                    <th>Senior Carder </th>
                                    <th>Status </th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead> 
                            <tbody>
                                <tr class="parent-associate @if($associate->is_block==0)@if($associate->associate_status==1) parent_active @else parent_inactive @endif @else parent_inactive @endif">
                                    
                                <td>{{ $associate->member_id }}</td>
                                <td>{{ $associate->associate_no }}</td>
                                    <td>{{ $associate->first_name }} {{ $associate->last_name }}</td>
                                    <td>{{ getCarderName($associate->current_carder_id) }}</td> 
                                    <td>{{ $associate->associate_senior_code }}</td>
                                    <td>{{ getSeniorData($associate->associate_senior_id,'first_name') }}  {{ getSeniorData($associate->associate_senior_id,'last_name') }}</td>
                                    <td>{{ getCarderName(getSeniorData($associate->associate_senior_id,'current_carder_id')) }}</td>
                                    <td>@if($associate->is_block==0)@if($associate->associate_status==1) Active @else Inactive @endif @else Blocked @endif</td>
                                    <td> <a href="{!! route('admin.associate.treeview',$associate->id) !!}"  title="View Tree" target="_blanck"><i class="fas fa-tree"></i></a></td>

                                </tr>
                        @for ($i = 1; $i <= $associate->current_carder_id; $i++)
                            <?php  $arrayCategories = array();
                                $data=associateTree($associate->id,$i) 
                            ?>
                            @if(count($data)>0)
                            @foreach($data as $taxonomy)

                                    <tr @if($taxonomy->member->is_block==0)
                                        @if($taxonomy->member->associate_status==1) class='child_active' @else class='child_inactive'  @endif
                                        @else class='child_inactive'  @endif > 
                                    <td>{{ $taxonomy->member->member_id  }}</td>
                                    <td>{{ $taxonomy->member->associate_no  }}</td>
                                    <td>{{ $taxonomy->member->first_name }} {{ $taxonomy->member->last_name }}</td>
                                    <td>{{ getCarderName($taxonomy->member->current_carder_id) }}</td> 
                                    <td>{{ $taxonomy->member->associate_senior_code }}</td>
                                    <td>{{ getSeniorData($taxonomy->member->associate_senior_id,'first_name') }}  {{ getSeniorData($taxonomy->member->associate_senior_id,'last_name') }}</td>
                                    <td>{{ getCarderName(getSeniorData($taxonomy->member->associate_senior_id,'current_carder_id')) }}</td>
                                    <td>
                                        @if($taxonomy->member->is_block==0)
                                        @if($taxonomy->member->associate_status==1)
                                            Active 
                                        @else Inactive 
                                    @endif  @else Blocked 
                                    @endif</td>
                                    <td> <a href="{!! route('admin.associate.treeview',$taxonomy->member->id) !!}"  title="View Tree" target="_blanck"><i class="fas fa-tree"></i></a></td>

                                </tr>
                                @if(count($taxonomy->subcategory)>0)
                                    @include('templates.admin.associate.partials.treeviewlisting',['subcategories' => $taxonomy->subcategory])
                                @endif
                                
                            @endforeach
                            @endif
                        @endfor
                            </tbody>                   
                        </table>
                    </div>
                    
                </div>

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Tree View</h6> 
                    </div>
                    <div class="tree-overflow"> 
                        <ul class="tree">
                            <li @if($associate->is_block==0) @if($associate->associate_status==1) class='acitve_li' @else class='inacitve_li' @endif @else class='inacitve_li' @endif>
                            <code class="@if($associate->is_block==0)@if($associate->associate_status==1) parent_active_li @else parent_inactive_li @endif @else parent_inactive_li @endif">{{ $associate->member_id }}<br>{{ $associate->associate_no }}<br>
                                {{ $associate->first_name }} {{ $associate->last_name }}<br> 
                                {{ getCarderName($associate->current_carder_id) }}
                                <br>
                                @if($associate->is_block==0)
                                @if($associate->associate_status==1)
                                                                  Active
                                                                @else
                                                                  Inactive
                                                                @endif
                                @else
                                Blocked
                                @endif
                            </code> 
                                    <ul>  
                                       @for ($i = 1; $i <= $associate->current_carder_id; $i++)
                                   
                                            <li><code class="carder">Carder {{$i}}</code>
                                                <?php  $arrayCategories = array();
                                                $data=associateTree($associate->id,$i) 
                                                ?>
                                                @if(count($data)>0)
                                                <ul>
                                                    @foreach($data as $taxonomy)
                                                    
                                                        <li @if($taxonomy->member->is_block==0) @if($taxonomy->member->associate_status==1) class='acitve_child_code' @else class='inacitve_child_code' @endif @else class='inacitve_child_code' @endif>
                                                            <code @if($taxonomy->member->is_block==0) @if($taxonomy->member->associate_status==1) class='acitve_child_code' @else class='inacitve_child_code' @endif @else class='inacitve_child_code' @endif>
                                                            {{ $taxonomy->member->associate_no  }}
                                                            <br>
                                                            {{ $taxonomy->member->first_name }} {{ $taxonomy->member->last_name }}
                                                            <br>
                                                            {{ getCarderName($taxonomy->member->current_carder_id) }} 
                                                            <br>
                                                            @if($taxonomy->member->is_block==0)
                                                                @if($taxonomy->member->associate_status==1)
                                                                  Active
                                                                @else
                                                                  Inactive
                                                                @endif
                                                                @else
                                                                Blocked @endif
                                                        </code>
                                                            @if(count($taxonomy->subcategory)>0)
                                                                    @include('templates.admin.associate.partials.treeview',['subcategories' => $taxonomy->subcategory])
                                                            @endif
                                                        </li>
                                                        
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </li>
                                         @endfor
                                    </ul>
                            </li>
                        </ul>                           
                    </div>
                </div>
            </div>
            @endisset
            @if($code!='') 
            @empty($associate)
            
                <div class="col-md-12" id="show_tree">
                    <div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>
                </div> 

            
            @endempty
            @endif
        </div>
    </div>
@include('templates.admin.associate.partials.tree_js')
@stop