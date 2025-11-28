@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <!-- <h6 class="card-title font-weight-semibold">Account Heads</h6> -->
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            @if($head3['sub_head'] != '')
                                <li class="breadcrumb-item"><a href="{{ URL::to("admin/accounthead/".$head3['id']."") }}">{{ $head3['sub_head'] }}</a></li>
                            @endif

                            @if($head2['sub_head'] != '' && $head1['sub_head'] != '')
                                <li class="breadcrumb-item"><a href="{{ URL::to("admin/accounthead/".$head2['id']."") }}">{{ $head2['sub_head'] }}</a></li>
                            @elseif($head2['sub_head'] != '' )
                                <li class="breadcrumb-item">{{ $head2['sub_head'] }}</li>
                            @endif

                            @if($head1['sub_head'] != '')
                                <li class="breadcrumb-item">{{ $head1['sub_head'] }}</li>
                            @endif
                          </ol>
                        </nav>
                           <!--  <div class="header-elements">
                                <a class="font-weight-semibold" href="{{ route('admin.addaccounthead') }}"><i class="icon-file-plus mr-2"></i>Create Account Head</a>
                            </div> -->
                    </div>
                    <table class="table datatable-show-all" id="account-hea">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Head Title</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($accountHeads) > 0)
                                @foreach($accountHeads as $key => $val)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $val->sub_head }}</td>
                                    <td>
                                        <div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ URL::to("admin/accounthead/".$val->id."") }}"><i class="fa fa-list-alt" aria-hidden="true"></i>View Sub Head
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="3" style="text-align: center;">No data available in table</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.category.partials.script')
@endsection
