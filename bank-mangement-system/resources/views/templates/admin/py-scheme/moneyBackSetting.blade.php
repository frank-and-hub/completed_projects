@extends('templates.admin.master')



@section('content')

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                {{-- ----------------------------------Modal Start------------------------------------- --}}

                <div class="modal fade" id="moneybackmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"

                    aria-hidden="true">

                    <div class="modal-dialog" role="document">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title" id="moneybackmodelLabel">Money Back Setting</h5>

                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                                    <span aria-hidden="true">×</span>

                                </button>

                            </div>

                            <div class="modal-body">

                                {{-- ----------------------------------Form Start------------------------------------- --}}

                                <form name="loanform" id="moneyForm" method="POST" action="">

                                    @csrf

                                    <input type="hidden" id="editInput" name="editid" value="">

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Plan<sup class="reqstar text-danger"> *</sup></label>

                                        <div class="col-lg-9">

                                            <input type="hidden" name="plan_id" class="form-control"

                                                value="{{ $planId }}">

                                            <input type="text" class="form-control readonly" value="{{ $planName }}"

                                                readonly>

                                            @error('plan_id')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Plan code<sup class="reqstar text-danger"> *</sup></label>

                                        <div class="col-lg-9">

                                            <input type="number" name="plan_code" class="form-control readonly"

                                                value="{{ $planCode }}" readonly>

                                            @error('plan_code')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Tenure<sup class="reqstar text-danger"> *</sup></label>

                                        <div class="col-lg-9">

                                            <select class="form-control " name="tenure" id="tenure">

                                                <option disabled selected>Please Select Tenure</option>

                                                @foreach ($tenures as $tenure)

                                                    <option value="{{ $tenure['tenure'] }}">{{ $tenure['tenure'] }}</option>

                                                @endforeach



                                            </select>

                                            @error('tenure')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>



                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Months<sup class="reqstar text-danger"> *</sup></label>

                                        <div class="col-lg-9">

                                            <input type="number" name="months" id="months" class="form-control"

                                                min="0">

                                            @error('months')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Money Back Percentage<sup class=" text-danger starneed"> *</sup></label>

                                        <div class="col-lg-9">

                                            <input type="number" name="money_percentage" id="money_percentage"

                                                min="0" max="100" class="form-control">

                                            @error('money_percentage')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Effective From<sup class="reqstar text-danger"> *</sup></label>

                                        <div class="col-lg-9">

                                            <input type="text" 

                                            data-val="" name="tenure_effective_from" id="tenure_effective_from"

                                                class="form-control tenure_effective_from" required=""

                                                autocomplete="off">

                                            @error('tenure_effective_from')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="form-group row tenureData">

                                        <label class="col-form-label col-lg-3">Effective To</label>

                                        <div class="col-lg-9">

                                            <input type="text" name="tenure_effective_to" id="tenure_effective_to"

                                                class="form-control tenure_effective_To" autocomplete="off">

                                            @error('tenure_effective_to')

                                                <label class="error" for="tenure">{{ $message }}</label>

                                            @enderror

                                        </div>

                                    </div>

                                    <div class="text-right">

                                        <button type="submit" class="btn bg-dark legitRipple">Submit<i

                                                class="icon-paperplane ml-2"></i></button>

                                    </div>

                                </form>

                                {{-- ----------------------------------Form End------------------------------------- --}}

                            </div>

                        </div>

                    </div>

                </div>

                {{-- ----------------------------------Modal End------------------------------------- --}}

            </div>

        </div>

        <div class="card bg-white">

            <div class="card-body">

                <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Money Back Setting</h3>

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

                    <table class="table table-flush" id="file_charge_d" style="margin-bottom: 0.4rem !important ">

                        <thead class="thead-light">

                            <tr>

                                <th style="border: 1px solid #ddd;">Tenure</th>

                                <th style="border: 1px solid #ddd;">Months</th>

                                <th style="border: 1px solid #ddd;">Money Back Per</th>

                                <th style="border: 1px solid #ddd;">Effective From</th>

                                <th style="border: 1px solid #ddd;">Effective To</th>



                                <th style="border: 1px solid #ddd;">

                                    <span id="add_btn_span"><button type="button"

                                            class="btn btn-primary tenureM legitRipple" data-toggle="modal"

                                            data-target="#moneybackmodel" data-type="charge" data-chargemode="1"

                                            data-title="Edit File Charge" data-olddata="[]" data-toggle="tooltip"

                                            title="Add"> <i class="icon-add"></i></button></span>

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @if (empty($moneyBackSetting))

                                <tr class="no-data table-secondary" style="border: 1px solid #ddd;">

                                    <td colspan="7" class="text-center">No data available.</td>

                                </tr>

                            @else

                                @foreach ($moneyBackSetting as $money)

                                    <tr>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                            <input type="text" class="form-control tenure_{{ $money['id'] }}"

                                                data-value="1" value="{{ $money['tenure'] }}" readonly>



                                        </td>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                            <div class="">

                                                <input type="text" class="form-control months_{{ $money['id'] }}"

                                                    value="{{ $money['months'] }}" readonly>
                                                <input type="hidden" name="month" class="form-control" value="{{ $money['months'] }}">
                                            </div>

                                        </td>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                            <div class="">

                                                <input type="text" min="0"

                                                    class="form-control percentage_{{ $money['id'] }}"

                                                    autocomplete="off" value="{{ $money['money_back_per'] }}"

                                                    readonly="">

                                            </div>

                                        </td>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                            <div class="">

                                                <input type="text" min="0"

                                                    class="form-control effectiveFrom_{{ $money['id'] }}"

                                                    autocomplete="off"

                                                    value="{{ date('d/m/Y', strtotime($money['effective_from'])) }}"

                                                    readonly="">

                                            </div>

                                        </td>

                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                            <div class="">

                                                <input type="text" min="0"

                                                    class="form-control effectiveTo_{{ $money['id'] }}"

                                                    autocomplete="off"

                                                    value="{{ $money['effective_to'] ? date('d/m/Y', strtotime($money['effective_to'])) : '' }}"

                                                    readonly="">

                                            </div>

                                        </td>



                                        <td style="border: 1px solid #ddd;padding: 0.5rem 1.25rem; width:16%;">

                                            <div class="row">

                                            @if ($money['status'] == 1)

                                            <a class="btn btn-success status_button"  href="{{route('MoneyBack.status',['id'=>$money['id']])}}"><i class="fa fa-check-circle" ></i></a> 

                                            @endif

                                            {{-- <div data-id="{{ $money['id'] }}" class="btn btn-danger delete_btn"

                                                data-toggle="tooltip" title="Delete"><i class="icon-trash"></i></div> --}}

                                                @if (empty($money['effective_to']))

                                                <div class="btn btn-primary edit_btnn col-lg-4 ml-1"

                                                    data-value="{{ $money['id'] }}" data-toggle="tooltip"

                                                    title="Edit"><i class="icon-pencil"></i>

                                                </div>

                                            @endif

                                            {{-- <div class="btn btn-primary edit_btnn" data-value="{{ $money['id'] }}"

                                                data-toggle="tooltip" title="Edit"><i class="icon-pencil"></i></div> --}}

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            @endif



                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    </div>

    </div>

    @include('templates.admin.py-scheme.partials.moneySettingScript')

@stop

