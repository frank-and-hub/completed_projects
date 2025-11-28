@extends('admin.layout.master')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @push('plugin-styles')
        <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
        <!-- Add Select2 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @endpush

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Notification</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notification Send</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Send Notification</h6>
                    <form method="Post" action="{{route('admin.save.notification')}}" >
                      @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control"> 
                                
                            </div>
                            <input type="hidden" name="userid" value="{{$userid}}">
                             <input type="hidden" name="schid" value="{{$schid}}">
                            <div class="col-sm-6">
                               
                                   
                                    
                                    <label class="form-label">Tag</label>
                                   <select name="teg" class="form-control select2">
                                        <option value="">Select teg</option>
                                       <option value="All Notifications">All Notifications</option>
                                            <option value="New Scholarships">New Scholarships</option>
                                            <option value="Featured Scholarships">Featured Scholarships</option>
                                            <option value="Relevant Scholarships">Relevant Scholarships</option>
                                            <option value="Newsletter">Newsletter</option>
                                            
                                            <option value="Application Updates">Application Updates</option>
                                            <option value="Scholarship News">Scholarship News</option>
                                            <option value="Blog Updates">Blog Updates</option>
                                            <option value="Account Notifications">Account Notifications</option>
                                    </select>
                               
                                
                            </div>

                            <div class="col-sm-6">
                                
                                    
                                   
                                    <label class="form-label">Description</label>
                                    <textarea name="descrription" class="form-control"></textarea> 
                               
                            </div>
                            <div class="col-sm-6">
                                
                                    <label class="form-label">Author Name</label>
                         <input type="text" class="form-control" placeholder="Author Name" name="author_name">

                                    
                              
                            </div>
                            
                            
                        </div>
                        <div class="col-sm-3" style="float: right">
                          <button type="submit" class="btn btn-primary submit">Save</button>
                          
                      </div>
                    </form>

                    <!-- Add more select elements if needed -->

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Notification List</h6>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Title</th>
                                    <th>Tag</th>
                                    <th>Author Name</th>
                                    <th>Description</th>
                                    
                                    <th>Action</th>
                                </tr>
                            </thead>
                           <tbody>
                                @foreach ($applicantsDetails as $key => $value)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $value->title }}</td>
                                        <td>{{ $value->teg }}</td>
                                        <td>{{ $value->author_name }}</td>
                                        <td>{{ $value->description }}</td>
                                     
                                       
                                        <td>
                                            
                                          <a href="{{ route('admin.deletexx.notification', $value->id) }}" title="Delete" onclick="return confirm('Are you sure you want to delete?')">
    <i class="fas fa-trash"></i>
</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')


@endpush
