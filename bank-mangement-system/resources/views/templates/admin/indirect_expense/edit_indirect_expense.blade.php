@extends('templates.admin.master')

@section('content')

   
    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" >
                            <form method="post" action="{!! route('admin.indirect_expense.update') !!}" id="indirect_expense">
                                 @csrf
                                 <input type="hidden" name="id" value="{{$head->head_id}}">
                                 <input type="hidden" name="selectedOption" value="{{$head->parent_id}}" id="selectedOption">
                             <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Expense<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="indirect_expense" id="indirect_expense" class="form-control">
                                           <option value="9" selected="true">Indirect Expense</option>
                                       </select>
                                    </div>
                                  <label class="col-form-label col-lg-2">Select Sub Expense(Head 3)<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="child_indirect_expense" id="child_indirect_expense"  class="form-control">
                                        <option value="">---Select Sub Expense--- </option>
                                           @foreach($sub_expense as $s_asset)
                                                @if($child_expense)
                                                <option value="{{$s_asset->head_id}}" {{$child_expense->id == $s_asset->id ? 'selected':""}}>{{$s_asset->sub_head}}</option>
                                                @else
                                                 <option value="{{$s_asset->head_id}}" >{{$s_asset->sub_head}}</option>
                                            @endif     
                                           @endforeach
                                       </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                     <label class="col-form-label col-lg-2">Select Sub Child Expense (Head 4)<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="sub_child_indirect_expense" id="sub_child_indirect_expense"  class="form-control">
                                          <option value="">---Select Sub Child Expense--- </option>
                                          @if($head->labels > 4)
                                         <option value="{{$sub_child_expense->head_id}}" selected="true">{{getAcountHeadData($sub_child_expense->head_id)}}</option> @endif
                                       </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Title<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="title" name="title" class="form-control " value="{{$head->sub_head}}">
                                    </div>
                                </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /basic layout -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('templates.admin.indirect_expense.partials.script')
@stop