@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Permission</h6>
                            <div class="header-elements">
                            </div>
                    </div>
                    <form id="permissions" name="permissions" method="post">

                        <div class="modal-body">
                            @csrf
                            <table class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>Accessibility</th>
                                <th>Check Permission</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $i = 1;
                        @endphp
                        @foreach ( $permissions as $permission )

                                <tr>
                                    {{--<td><strong>{{ $permission['id'] }}</strong></td>--}}
                                    <td><strong> {{ $permission['name'] }}</strong></td>
                                    <td style="width:120px;"><input class="all-per main-per-{{ $permission['id'] }}" data-id="per-{{ $permission['id'] }}"
                                                                    type="checkbox" name="permissionName[{{ $permission['id'] }}][]"  <?php if(in_array($permission['id'], $userPermissions)){ echo "checked"; }?>> </td>
                                                                    
                                    <td style="width:100px;">@if ( array_key_exists('children', $permission)  )<span class="main-permission"><i class="fas fa-angle-right"></i></span>  @endif</td>
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
                                                            <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child"
                                                                    name="permissionName[{{ $innerPermission['id'] }}][]" <?php if(in_array($innerPermission['id'], $userPermissions)){ echo "checked"; }?>>
                                                        </td>
                                                        <td style="width:100px;"> @if ( array_key_exists('children', $innerPermission)  ) <span
                                                                    class="main-permission"><i class="fas
                                                        fa-angle-right"></i></span> @endif</td>
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
                                                                    <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child" name="permissionName[{{ $innerPermissionSecond['id'] }}][]" <?php if(in_array($innerPermissionSecond['id'], $userPermissions)){ echo "checked"; }?>>
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
                                                                            }}-child" name="permissionName[{{ $innerPermissionThree['id'] }}][]" <?php if(in_array($innerPermissionThree['id'], $userPermissions)){ echo "checked"; }?>>
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
                                                                                    <input type="checkbox" class="per-{{ $permission['id'] }} main-per-{{ $permission['id'] }}-child" name="permissionName[{{ $innerPermissionFour['id'] }}][]" <?php if(in_array($innerPermissionFour['id'], $userPermissions)){ echo "checked"; }?>>
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
                            <div class="text-right">
								<input type="hidden" name="user_id" id="user_id" value="{{$user_id}}"/>
                                <button type="button" id="per-update" class="btn bg-dark legitRipple">Submit<i class="icon-paperplane
                                ml-2"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="create-role" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="role" action="{{route('role.create')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Role Name:</label>
                            <div class="col-lg-8">
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="create" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="permission" action="{{route('permission.create')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Permission Name:</label>
                            <div class="col-lg-8">
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('templates.admin.user_management.partials.usermanagement_script')
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

