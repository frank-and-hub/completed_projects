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
                            <form method="post" action="{!! route('admin.head.update') !!}" id="indirect_expense">
                                 @csrf
                                 <input type="hidden" name="id" value="{{$head->id}}">
                                 <input type="hidden" name="selectedOption" value="{{$head->id}}" id="selectedOption">
                                 <input type="hidden" name="labels" value="{{$labels}}" id="labels">
                            <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Head1 <sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="head1" id="head1" class="form-control">
									    <option value="">---Select Parent Subhead--- </option>
                                        @foreach($sub_expense as $s_asset)
                                          @if(isset($child_expense3))
                                                @if($child_expense3)
                                                <option value="{{$s_asset->head_id}}" {{$child_expense3->head_id == $s_asset->head_id ? 'selected':""}}>{{$s_asset->sub_head}}</option>
                                                @else
                                                 <option value="{{$s_asset->head_id}}" >{{$s_asset->sub_head}}</option>
                                            @endif
                                            @endif
                                           @endforeach
                                       </select>
                                    </div>
                                  <label class="col-form-label col-lg-2">Select Head2<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="head2" id="head2"  class="form-control">
                                          <option value="">---Select Child Subhead--- </option>
										 @foreach($head2 as $s_asset)
                                                @if($child_expense2)
                                                <option value="{{$s_asset->head_id}}" {{$child_expense2->id == $s_asset->id ? 'selected':""}}>{{$s_asset->sub_head}}</option>
                                                @else
                                                 <option value="{{$s_asset->head_id}}" >{{$s_asset->sub_head}}</option>
                                            @endif     
                                           @endforeach 
                                          
                                       </select>
                                    </div>
                                </div>
                                <input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date">
                                <div class="form-group row">
                                     <label class="col-form-label col-lg-2">Select Head 3<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="head3" id="head3"  class="form-control">
                                         <option value="">---Select Child Subhead--- </option>
                                        @foreach($head3 as $s_asset)
                                                @if($child_expense)
                                                <option value="{{$s_asset->head_id}}" {{$child_expense->id == $s_asset->id ? 'selected':""}}>{{$s_asset->sub_head}}</option>
                                                @else
                                                 <option value="{{$s_asset->head_id}}" >{{$s_asset->sub_head}}</option>
                                            @endif     
                                           @endforeach 
                                       </select>
                                    </div>
									<label class="col-form-label col-lg-2">Select Head 4<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="head4" id="head4"  class="form-control">
                                         <option value="">---Select Child Subhead--- </option>
                                         @foreach($head4 as $s_asset)
                                                @if($sub_child_expense)
                                                <option value="{{$s_asset->head_id}}" {{$sub_child_expense->id == $s_asset->id ? 'selected':""}}>{{$s_asset->sub_head}}</option>
                                                @else
                                                 <option value="{{$s_asset->head_id}}" >{{$s_asset->sub_head}}</option>
                                            @endif     
                                           @endforeach 
                                       </select>
                                    </div>
                                    
                                </div>
								<div class="form-group row">
                                     <label class="col-form-label col-lg-2">Head 5<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <input type="text" name="new_head" id="new_head" value="{{$sub_child_expense1->sub_head}}" class="form-control" >
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
    @include('templates.admin.account_head_report.head.partials.edit_script')
@stop