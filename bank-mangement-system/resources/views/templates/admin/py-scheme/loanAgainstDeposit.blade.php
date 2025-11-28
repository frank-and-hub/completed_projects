@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                {{-- -----------------------------------------------------------model start------------------------------------------------------- --}}
                <div class="modal fade" id="loanAgainstModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="loanAgainstModalLabel">Loan Against Deposit</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                {{-- ---------------------------------Form start ----------------------------------- --}}
                                <form name="loanAgainstForm" id="loanAgainstForm" method="POST" action="">
                                    @csrf
                                    <input type="hidden" id="editInput" name="editid" value="">
                                    <input type="hidden" name="created_at" class="created_at" id="created_at"
                                        value="2023-2-28 16:40:03">
                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Plan<sup class="reqstar text-danger"> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="hidden" name="plan_id" class="form-control"
                                                value="{{ $planId }}">
                                            <input type="text" class="form-control readonly" value="{{ $planName }}"
                                                readonly>
                                            @error('plan_id')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Plan code<sup class="reqstar text-danger"> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="number" name="plan_code" class="form-control readonly"
                                                value="{{ $planCode }}" readonly>
                                            @error('plan_code')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Tenure<sup class="reqstar text-danger"> *</sup></label>
                                        <div class="col-lg-9">
                                            <select class="form-control " name="tenure" id="tenure">
                                                <option disabled selected>Please Select Tenure</option>
                                                @foreach ($tenures as $tenure)
                                                    <option value="{{ $tenure['tenure'] }}">{{ $tenure['tenure'] }}</option>
                                                @endforeach

                                            </select>
                                            @error('tenure')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Months From<sup class="reqstar text-danger"> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="number" name="monthsFrom" id="monthsFrom" class="form-control"
                                                min="0">
                                            @error('monthsFrom')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Months To<sup class="reqstar text-danger"> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="number" name="monthsTo" id="monthsTo" class="form-control"
                                                min="0">
                                            @error('monthsTo')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <label class="col-form-label col-lg-3">Loan Against Deposit Percentage<sup class=" text-danger starneed"> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="number" name="loan_percentage" id="loan_percentage" min="0"
                                                max="100" class="form-control">
                                            @error('loan_percentage')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Effective From<sup class="reqstar text-danger "> *</sup></label>
                                        <div class="col-lg-9">
                                            <input type="text" data-val="" name="tenure_effective_from"
                                                id="tenure_effective_from" class="form-control tenure_effective_from"
                                                autocomplete="off">
                                            @error('tenure_effective_from')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Effective To</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="tenure_effective_to" id="tenure_effective_to"
                                                class="form-control tenure_effective_To" autocomplete="off">
                                            @error('tenure_effective_to')
                                                <label class="error">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn bg-dark">Submit<i
                                                class="icon-paperplane ml-2"></i></button>
                                    </div>
                                </form>
                                {{-- ---------------------------------Form end ----------------------------------- --}}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ----------------------------------------------- model end------------------------------------------------------- --}}
            </div>
        </div>

        <div class="card bg-white">
            <div class="card-body">
                <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Loan Against Deposit</h3>
                <div class="table-responsive py-4">
                    <div class="">
                        <div class="form-group row tenureData">
                            <label class="col-form-label col-lg-1">Plan </label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control" value="{{ $planName }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row tenureData">
                            <label class="col-form-label col-lg-1">Plan code</label>
                            <div class="col-lg-3">
                                <input type="number" class="form-control" value="{{ $planCode }}" readonly>
                            </div>
                        </div>
                    </div>
                    {{-- ----------------------------------------------table listing start------------------------------------------- --}}
                    <table class="table table-flush" id="file_charge_d" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                            <tr>
                                <th style="border: 1px solid #ddd;">Tenure</th>
                                <th style="border: 1px solid #ddd;">Months From</th>
                                <th style="border: 1px solid #ddd;">Months To</th>
                                <th style="border: 1px solid #ddd;">Loan Against Deposit Percentage</th>
                                <th style="border: 1px solid #ddd;">Effective From</th>
                                <th style="border: 1px solid #ddd; width:20%;">Effective To</th>

                                <th style="border: 1px solid #ddd;">
                                    <span id="add_btn_span"><button type="button" class="btn btn-primary tenureM"
                                            data-toggle="modal" data-target="#loanAgainstModal" data-type="charge"
                                            data-chargemode="1" data-title="Edit File Charge" data-olddata="[]"
                                            data-toggle="tooltip" title="Add"> <i
                                                class="icon-add"></i></button></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($loanAgainst))
                                <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                    <td colspan="7" class="text-center">No data available.</td>
                                </tr>
                            @else
                                @foreach ($loanAgainst as $loan)
                                    <tr>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <input type="text" class="form-control tenure_{{ $loan['id'] }}"
                                                data-value="1" value="{{ $loan['tenure'] }}" readonly>

                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <div class="">
                                                <input type="text"
                                                    class="form-control months_from_{{ $loan['id'] }}"
                                                    value="{{ $loan['month_from'] }}" readonly>
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <div class="">
                                                <input type="text" class="form-control months_to_{{ $loan['id'] }}"
                                                    value="{{ $loan['month_to'] }}" readonly>
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <div class="">
                                                <input type="text" min="0"
                                                    class="form-control percentage_{{ $loan['id'] }}"
                                                    autocomplete="off" value="{{ $loan['loan_per'] }}" readonly="">
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <div class="">
                                                <input type="text" min="0"
                                                    class="form-control effectiveFrom_{{ $loan['id'] }}"
                                                    autocomplete="off"
                                                    value="{{ date('d/m/Y', strtotime($loan['effective_from'])) }}"
                                                    readonly="">
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                            <div class="">
                                                <input type="text" min="0"
                                                    class="form-control effectiveTo_{{ $loan['id'] }}"
                                                    autocomplete="off"
                                                    value="{{ $loan['effective_to'] ? date('d/m/Y', strtotime($loan['effective_to'])) : '' }}"
                                                    readonly="">
                                            </div>
                                        </td>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 1.25rem; width:16%;">
                                            <div class="row">
                                                @if ($loan['status'] == 1)
                                                <a class="btn btn-success status_button"  href="{{route('loanAgainst.status',['id'=>$loan['id']])}}"><i class="fa fa-check-circle" ></i></a> 
                                                @endif
                                             
                                                {{-- <div data-id="{{ $loan['id'] }}"
                                                    class="btn btn-danger delete_btn col-lg-4" data-toggle="tooltip"
                                                    title="Delete"><i class="icon-trash"></i>
                                                </div> --}}
                                                @if (empty($loan['effective_to']))
                                                    <div class="btn btn-primary edit_btnn col-lg-4 ml-1"
                                                        data-value="{{ $loan['id'] }}" data-toggle="tooltip"
                                                        title="Edit"><i class="icon-pencil"></i>
                                                    </div>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                        </tbody>
                    </table>
                    {{-- ----------------------------------------------table listing end------------------------------------------- --}}
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    @include('templates.admin.py-scheme.partials.loanAgainstDepositScript')
@stop
