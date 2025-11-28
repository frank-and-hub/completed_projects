@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
                @endif
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Edit User</h6>
                    </div>
                    <form action="{{route('admin.user.update')}}" method="post" id="user-update">
                        @csrf

                        <input type="hidden" name="userid" id="userid" value="{{ $id }}">
                        <input type="hidden" name="employeeid" id="employeeid" value="{{ $employee->id }}">
                        
                        <div class="modal-body">

                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">User Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="username" id="username" class="form-control" value="{{ $users->username }}" readonly="">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">Employee Code</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ $employee->employee_code }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">Employee Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="employee_name" id="employee_name" class="form-control" value="{{ $employee->employee_name }}" readonly="">
                                        </div>
                                    </div>       

                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">User Id</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="user_id" id="user_id" class="form-control"  value="{{ $employee->employee_user_id }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">Password</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="password" id="password" class="form-control" value="">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">Set Permission</label>
                                        <div class="col-lg-10">
                                            <input type="checkbox" name="set_permission" id="set_permission" class="form-control" @if(!empty($userPermissions)) checked="" @endif  style="margin-top: -23px;">
                                        </div>
                                    </div>
                                </div>
                            

                            <table class="table datatable-show-all permission-table"  @if(empty($userPermissions)) style="display: none;" @endif>
                                <thead>
                                    <tr>
                                        <th>Accessibility</th>
                                        <th>Check Permission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $i = 1;
                                    @endphp
                                    @foreach ( $permissions as $permission )
                                        <tr>
                                            <td><strong> {{ $permission['name'] }}</strong></td>
                                            <td style="width:120px;">
                                                <input class="all-per main-per-{{ $permission['id'] }}" data-id="per-{{ $permission['id'] }}" type="checkbox" name="userpermission[]" value="{{ $permission['name'] }}" @if(in_array($permission['name'],$userPermissions)) checked="" @endif> </td>
                                            <td style="width:100px;"><span class="main-permission"><i class="fas fa-angle-right"></i></span></td>
                                        </tr>
                                        <tr style="display:none">
                                            <td class="subTable" colspan="3">
                                                <table class="table sub-datatable-show-all">
                                                    <tbody>
                                                    @if ( array_key_exists('children', $permission)  )
                                                        @foreach( $permission['children'] as $innerPermission)
                                                            <tr>
                                                                {{--<td>{{$i}}</td>--}}

                                                                <td>{{$innerPermission['name']}}</td>

                                                                <td style="width:100px;">
                                                                    <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child" name="userpermission[]" value="{{$innerPermission['name']}}" @if(in_array($innerPermission['name'],$userPermissions)) checked="" @endif >
                                                                </td>
                                                                <td style="width:100px;"> 
                                                                @if ( array_key_exists('children', $innerPermission)  ) 
                                                                    <span class="main-permission">
                                                                        <i class="fas fa-angle-right"></i>
                                                                    </span> 
                                                                @endif
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $i++;
                                                            @endphp
                                                            @if ( array_key_exists('children', $innerPermission)  )
                                                                <tr style="display:none">
                                                                    <td class="subTable" colspan="3">
                                                                        <table class="table sub-datatable-show-all">
                                                                            <tbody>
                                                                                @foreach( $innerPermission['children'] as $innerPermissionSecond)
                                                                    @php
                                                                        $j = 1;
                                                                    @endphp
                                                                    <tr>
                                                                        {{--<td>{{$i}}</td>--}}

                                                                        <td>{{$innerPermissionSecond['name']}}</td>

                                                                        <td style="width:100px;">
                                                                            <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child" name="userpermission[]" value="{{$innerPermissionSecond['name']}}" @if(in_array($innerPermission['name'],$userPermissions)) checked="" @endif  >
                                                                        </td>
                                                                        <td style="width:100px;">@if ( array_key_exists('children', $innerPermissionSecond)  ) <span
                                                                                    class="main-permission"><i
                                                                                        class="fas fa-angle-right"></i></span> @endif</td>
                                                                    </tr>
                                                                    @php
                                                                        $i++;
                                                                    @endphp
                                                                    @if ( array_key_exists('children', $innerPermissionSecond)  )
                                                                        <tr style="display:none">
                                                                            <td class="subTable" colspan="3">
                                                                                <table class="table sub-datatable-show-all">
                                                                                    <tbody>
                                                                                    @foreach( $innerPermissionSecond['children'] as $innerPermissionThree)

                                                                            <tr>
                                                                                {{--<td>{{$i}}</td>--}}

                                                                                <td>{{$innerPermissionThree['name']}}</td>

                                                                                <td style="width:100px;">
                                                                                    <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id']
                                                                                    }}-child" name="userpermission[]" value="{{$innerPermissionThree['name']}}" @if(in_array($innerPermission['name'],$userPermissions)) checked="" @endif >
                                                                                </td>
                                                                                <td style="width:100px;">@if ( array_key_exists('children', $innerPermissionThree)  ) <span
                                                                                            class="main-permission"><i class="fas fa-angle-right"></i></span> @endif</td>
                                                                            </tr>
                                                                            @php
                                                                                $i++;
                                                                            @endphp
                                                                            @if ( array_key_exists('children', $innerPermissionThree)  )
                                                                                <tr style="display:none">
                                                                                    <td class="subTable" colspan="3">
                                                                                        <table class="table sub-datatable-show-all">
                                                                                            <tbody>
                                                                                            @foreach( $innerPermissionThree['children'] as $innerPermissionFour)
                                                                                    <tr>
                                                                                        {{--<td>{{$i}}</td>--}}

                                                                                        <td>{{$innerPermissionFour['name']}}</td>

                                                                                        <td style="width:100px;">
                                                                                            <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child" name="userpermission[]"  value="{{$innerPermissionFour['name']}}" @if(in_array($innerPermission['name'],$userPermissions)) checked="" @endif  >
                                                                                        </td>
                                                                                        <td style="width:100px;"></td>
                                                                                    </tr>
                                                                                    @php
                                                                                        $i++;
                                                                                    @endphp
                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif

                                                    </tbody>
                                                </table>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>                    
                            </table>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark">Update<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@include('templates.admin.user.partials.script')
<style rel="stylesheet">
.subTable .table th, .subTable .table td { padding:0.75rem 0 0.75rem 1.25rem;}

.main-permission.open { transform: rotate(90deg); display: inline-block; }
.main-permission{cursor: pointer;
    padding: 2px;
    display: inline-block;
    background: #3E3A82;
    border-radius: 100%;
    width: 25px;
    height: 25px;
    line-height: normal;
    text-align: center;
    font-size: 18px;
    color: #fff;}
</style>
@stop
