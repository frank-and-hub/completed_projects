<div class="col-md-12" id="insurance_file_charge">

    <div class="card bg-white">
        <div class="card-body">
            <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Loan Insurance Charges </h3>
            <div class="table-responsive py-4">
                <table class="table table-flush" id="ins_charge_d" style="margin-bottom: 0.4rem !important ">
                    <thead class="thead-light">
                        <tr>
                            <th style="border: 1px solid #ddd; width:20%;">Emi Option</th>
                            <th style="border: 1px solid #ddd; ">Tenure</th>
                            <th style="border: 1px solid #ddd; width:20%;">Charge Type</th>
                            <th style="border: 1px solid #ddd;">Charge</th>
                            <th style="border: 1px solid #ddd;">Min Amount </th>
                            <th style="border: 1px solid #ddd;">Max Amount</th>
                            <th style="border: 1px solid #ddd;">Effective From</th>
                            <th style="border: 1px solid #ddd; ">Effective To</th>
                            @if ($type == 'editForm' && $resourceType != 'plan')
                                <th style="border: 1px solid #ddd;">
                                    <button type="button" class="btn btn-primary tenureM"
                                        data-OldData="{{ json_encode($old, true) }}" data-toggle="modal"
                                        data-target="#tenureModel" data-type="charge" data-chargeMode="2"
                                        data-title="Edit Insurance Charge"> <i class="icon-add"></i></button>
                                </th>
                            @endif
                            <!-- <button type="button" class="btn btn-primary" id="ins_charge_btn"><i class="icon-add"></i></button> -->
                        </tr>
                    </thead>
                    <tbody>
                        @if ($type == 'editForm')
                            @foreach ($insCharge as $value)
                                @php
                                    if ($value->emi_option == 1) {
                                        $label = 'Monthly';
                                    } elseif ($value->emi_option == 2) {
                                        $label = 'Weekly';
                                    } elseif ($value->emi_option == 3) {
                                        $label = 'Daily';
                                    }
                                    
                                @endphp
                                <input type="hidden" name="insurancecharges_id[]" value="{{ $value->id }}">
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">

                                        <input type="text" name="emi_option"
                                            class="form-control emi_option_{{ $value->id }}" autocomplete="off"
                                            data-value="{{ $value->emi_option }}" value="{{ $label }}" readonly>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <input type="text" name="tenure"
                                            class="form-control tenure_{{ $value->id }}" autocomplete="off"
                                            value="{{ $value->tenure }}" readonly>
                                    </td>

                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;"
                                        class="ins_charge_type_{{ $value->id }}"
                                        data-value="{{ $value->charge_type }}">

                                        <input type="text" name="ins_charge_type" id="ins_charge_type"
                                            class="form-control ins_charge_type_{{ $value->id }}"
                                            data-value="{{ $value->charge_type }}"
                                            value="{{ $value->charge_type == 0 ? 'Percentage' : 'Fixed' }} "
                                            readonly>

                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <div class="">
                                            <input type="text" name="ins_charge" id="ins_charge"
                                                class="form-control ins_charge_{{ $value->id }}"
                                                value="{{ $value->charge }}" readonly>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <div class="">
                                            <input type="text" name="ins_min_amount" id="ins_min_amount"
                                                min="0" class="form-control ins_min_amount_{{ $value->id }}"
                                                autocomplete="off" value="{{ $value->min_amount }}" readonly>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <div class="">
                                            <input type="text" name="ins_max_amount" id="ins_max_amount"
                                                min="0" class="form-control ins_max_amount_{{ $value->id }}"
                                                autocomplete="off" value="{{ $value->max_amount }}" readonly>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <div class="">
                                            <input type="text" name="ins_effective_from" id=""
                                                class="form-control ins_effective_from_{{ $value->id }}"
                                                autocomplete="off"
                                                value="@if (isset($value->effective_from)) {{ date('d/m/Y', strtotime($value->effective_from)) }} @endif"
                                                disabled>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                        <div class="">
                                            <input type="text" name="ins_effective_to" id="ins_effective_to"
                                                class="form-control ins_effective_to_{{ $value->id }}"
                                                autocomplete="off"
                                                value="@if (isset($value->effective_to)) {{ date('d/m/Y', strtotime($value->effective_to)) }} @endif"
                                                disabled>
                                        </div>
                                    </td>

                                    @if ($resourceType != 'plan')
                                        <td
                                            style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;display:flex;justify-content: space-between;">
                                            @if (!empty($record[$value->charge]))
                                                @if (count($record[$value->charge]) == 0)
                                                    <button type="button" class="btn btn-danger ins_charge_btn_trash"
                                                        id="ins_charge_btn_trash" data-Id="{{ $value->id }}"><i
                                                            class="icon-trash ">{{ count($record[$value->charge]) }}</i></button>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-danger ins_charge_btn_trash"
                                                    id="ins_charge_btn_trash" data-Id="{{ $value->id }}"><i
                                                        class="icon-trash"></i></button>
                                            @endif
                                            <button type="button" class="btn btn-primary tenureM ml-2"
                                                data-toggle="modal" data-target="#tenureModel" data-type="charge"
                                                data-chargeMode="2" data-title="Edit Insurance Charge"
                                                data-OldData="{{ json_encode($old, true) }}"
                                                data-Id="{{ $value->id }}"> <i class="icon-pencil"></i></button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12">
                                        <select class="form-control select" name="insurance_charges_emi_option"
                                            id="insurance_charges_emi_option">
                                            <option value="">Select Emi Option</option>
                                            <option value="1">Monthly
                                            </option>
                                            <option value="2">Weekly
                                            </option>
                                            <option value="3">Daily
                                            </option>
                                        </select>
                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <input type="text" name="insurance_charges_tenure"
                                        id="insurance_charges_tenure" class="form-control" autocomplete="off"
                                        reqiured>
                                </td>

                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="col-lg-12">
                                        <select class="form-control ins_charge_type charge_type select"
                                            name="ins_charge_type" id="ins_charge_type">
                                            <option value="">Select Charge Type</option>

                                            <option value="0">Percentage</option>

                                            <option value="1">Fixed</option>

                                        </select>
                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="">
                                        <input type="text" name="ins_charge" id="ins_charge"
                                            class="form-control ins_charge charge">
                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="">
                                        <input type="text" name="ins_min_amount" id="ins_min_amount"
                                            class="form-control " autocomplete="off">
                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="">
                                        <input type="text" name="ins_max_amount" id="ins_max_amount"
                                            class="form-control  " autocomplete="off">

                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="">
                                        <input type="text" name="ins_effective_from" id="ins_effective_from"
                                            class="form-control effective_from" autocomplete="off" disabled>
                                    </div>
                                </td>
                                <td style="border: 1px solid #ddd;padding: 0.5rem 0.5rem;">
                                    <div class="">
                                        <input type="text" name="ins_effective_to" id="ins_effective_to"
                                            class="form-control effective_from" autocomplete="off">
                                    </div>
                                </td>




                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if ($type == 'editForm')
    @include('templates.admin.loan.tenure_modal')
@endif
