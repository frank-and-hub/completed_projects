<!-- Button trigger modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Company Assign</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check">
                                    <input class="form-check-input" id="checkall" type="checkbox" value="1">
                                </div>
                            </th>
                            <th scope="col">Company</th>
                            <th scope="col">Old Business</th>
                            <th scope="col">New Business</th>
                            <th scope="col">Primary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($allcompany))
                        @php $count = 1;
                        @endphp
                        <input type="hidden" name="branch_id" value="{{$branch->id}}" id="branch_id">
                        <input type="hidden" name="csrf-token" value="{{csrf_token()}}">
                        @foreach($allcompany as $key => $com)
                        @php 
                            $compnybranch = \App\Http\Controllers\Admin\BranchController::CompanyDataGet($branch->id,$com->id);
                        @endphp
                        <tr>
                            <th scope="row">
                                <div class="form-check">
                                    <input class="form-check-input allcompanycheck" data-id="{{$com->id}}"  id="company_{{$com->id}}" type="checkbox" @if(!empty($compnybranch) && $compnybranch->company_id == $com->id) checked  @endif value="{{$com->id}}">
                                </div>
                            </th>
                            <td>{{$com->name}}</td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input oldbusspopupcheck oldvaluechecked oldcheck_{{$com->id}}" data-id="old_{{$com->id}}" disabled @if(!empty($compnybranch) && $compnybranch->is_old_business == '1') checked  @endif type="checkbox" value="1">
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input newvaluechecked newcheck_{{$com->id}}" data-id="new_{{$com->id}}" @if(!empty($compnybranch) && $compnybranch->is_new_business == '1') checked  @endif type="checkbox" value="1">
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" data-id="primary_{{$com->id}}" name="primarybox" @if(!empty($compnybranch) && $compnybranch->is_primary == '1') checked  @endif id="primarybox">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="javascript:void(0)" class="btn btn-primary" id="assignsubmit">Submit</a>
            </div>
        </div>
    </div>
</div>