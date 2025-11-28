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
        <div class="col-md-12" id="show_tree">
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
                                                            {{ $taxonomy->member->member_id  }}
                                                            <br>
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
    </div>
</div>
@include('templates.admin.associate.partials.tree_js')
@stop