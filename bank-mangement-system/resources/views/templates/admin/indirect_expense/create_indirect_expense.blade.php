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
                            <form method="post" action="{!! route('admin.save.indirect_expense') !!}" id="indirect_expense">
                                 @csrf
                             <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Indirect Expense<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="indirect_expense" id="indirect_expense" class="form-control">
                                           <option value="86" selected="true">Indirect Expense</option>
                                       </select>
                                    </div>
                                  <label class="col-form-label col-lg-2">Select Indirect Expense (Head 3)<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="child_indirect_expense" id="child_indirect_expense"  class="form-control">
                                        <option value="">---Select Child Indirect Expense--- </option>
                                           @foreach($indirect_expense as $expense)
                                                <option value="{{$expense->head_id}}">{{$expense->sub_head}}</option>
                                           @endforeach
                                       </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                     <label class="col-form-label col-lg-2">Select Sub Expense (Head 4)<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="sub_child_indirect_expense" id="sub_child_indirect_expense"  class="form-control">
                                          <option value="">---Select Sub Child Indirect Expense--- </option> 
                                       </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Title<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="title" name="title" class="form-control " >
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