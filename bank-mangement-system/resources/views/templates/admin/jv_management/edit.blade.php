@extends('templates.admin.master')

@section('content')

    <style>
        .search-table-outter {
            overflow-x: scroll;
        }

        .frm {
            min-width: 200px;
        }

        #bill_date {

            min-width: 100px;

        }

        h5 {
            background-color: gray;
            margin: 0 -10px 0;
            padding: 4px 0 4px 10px;
        }
    </style>

    <div class="loader" style="display: none;"></div>

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <!-- Basic layout-->

                <div class="card">

                    <div class="">

                        <div class="card-body">

                            <form method="post" action="{!! route('admin.jv.update') !!}" id="edit-jv-form">

                                @csrf

                                <input type="hidden" name="id" id="id" value="{{ $jv->id }}">
                                <div class="form-group row">
                                    @php
                                        $dropDown = $company;
                                        $filedTitle = 'Company';
                                        $name = 'company_id';
                                        $selectedBranch = $jv->branch_id;
                                        $selectedCompany = $jv->company_id;
                                    @endphp

                                    @include('templates.GlobalTempletes.new_role_type', [
                                        'dropDown' => $dropDown,
                                        'filedTitle' => $filedTitle,
                                        'name' => $name,
                                        'value' => '',
                                        'multiselect' => 'false',
                                        'design_type' => 4,
                                        'branchShow' => true,
                                        'branchName' => 'branch',
                                        'apply_col_md' => false,
                                        'multiselect' => false,
                                        'placeHolder1' => 'Please Select Company',
                                        'placeHolder2' => 'Please Select Branch',
                                        'selectedCompany' => $selectedCompany,
                                        'selectedBranch' => $selectedBranch,
                                    ])
                                    <input type="hidden" name="company_id" value="{{$selectedCompany}}">
                                    <input type="hidden" name="branch" value="{{$selectedBranch}}">
                                    <div class="col-md-4">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Date<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" name="cre_date" class="form-control cre_date"
                                                    autocomplete="off"
                                                    value="{{ date('d/m/Y', strtotime(convertDate($jv->date))) }}" readonly>

                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Journal<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" id="journal" name="journal" class="form-control"
                                                    readonly="" value="{{ $jv->jv_auto_id }}">

                                            </div>

                                        </div>
                                    </div>


                                    <div class="col-md-4">
                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Reference<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" name="reference" id="reference" class="form-control"
                                                    value="{{ $jv->reference }}">

                                            </div>

                                        </div>
                                    </div>


                                </div>


                                <div class="search-table-outter wrapper">
                                    <table class="table table-flush" id="expense1">

                                        <thead>

                                            <tr>

                                                <th>ACCOUNT HEAD 1 </th>

                                                <th>ACCOUNT HEAD 2</th>

                                                <th>ACCOUNT HEAD 3</th>

                                                <th>ACCOUNT HEAD 4</th>

                                                <th>ACCOUNT HEAD 5</th>

                                                <th>CONTACT </th>

                                                <th>DESCRIPTION</th>

                                                <th>DEBITS </th>

                                                <th>CREDITS</th>

                                            </tr>

                                        </thead>

                                        <tbody id="expense">
                                            @php
                                                $count = count($jv->jvJournalHeads);
                                            @endphp
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @php
                                                    $checkHL = \App\Models\AccountHeads::where('head_id', $hEntry->head_id)->first();
                                                    
                                                    if ($hEntry->type == 1) {
                                                        if ($hEntry->sub_type == 11 || $hEntry->sub_type == 12 || $hEntry->sub_type == 15 || $hEntry->sub_type == 17) {
                                                            if ($hEntry->head_id == 64 || $hEntry->head_id == 65 || $hEntry->head_id == 67) {
                                                                if ($hEntry->head_id == 64) {
                                                                    $loanType = 1;
                                                                } elseif ($hEntry->head_id == 65) {
                                                                    $loanType = 2;
                                                                } elseif ($hEntry->head_id == 67) {
                                                                    $loanType = 4;
                                                                }
                                                    
                                                                $contactList = \App\Models\Memberloans::select('id', 'account_number')
                                                                    ->where('loan_type', $loanType)
                                                                    ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                                    ->get();
                                                            } elseif ($hEntry->head_id == 97 || $hEntry->head_id == 31 || $hEntry->head_id == 33 || $hEntry->head_id == 90) {
                                                                $contactList = \App\Models\Memberloans::select('id', 'account_number')
                                                                    ->where('id', $hEntry->contact_id)
                                                                    ->get();
                                                            }
                                                        } elseif ($hEntry->sub_type == 13 || $hEntry->sub_type == 14 || $hEntry->sub_type == 16 || $hEntry->sub_type == 18) {
                                                            if (isset($hEntry->contact_id)) {
                                                                $contactList = \App\Models\Grouploans::select('id', 'account_number')
                                                                    ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                                    ->get();
                                                            }
                                                        }
                                                    } elseif ($hEntry->type == 2) {
                                                        if ($hEntry->head_id == 58) {
                                                            $planType = 7;
                                                        } elseif ($hEntry->head_id == 77 || $hEntry->head_id == 57) {
                                                            $planType = 9;
                                                        } elseif ($hEntry->head_id == 78) {
                                                            $planType = 8;
                                                        } elseif ($hEntry->head_id == 79) {
                                                            $planType = 4;
                                                        } elseif ($hEntry->head_id == 80) {
                                                            $planType = 2;
                                                        } elseif ($hEntry->head_id == 81) {
                                                            $planType = 5;
                                                        } elseif ($hEntry->head_id == 82) {
                                                            $planType = 11;
                                                        } elseif ($hEntry->head_id == 83) {
                                                            $planType = 10;
                                                        } elseif ($hEntry->head_id == 84) {
                                                            $planType = 6;
                                                        } elseif ($hEntry->head_id == 85) {
                                                            $planType = 3;
                                                        } else {
                                                            $planType = '';
                                                        }
                                                    
                                                        if ($planType) {
                                                            $contactList = \App\Models\Memberinvestments::select('id', 'account_number')
                                                                ->where('plan_id', $planType)
                                                                ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                                ->get();
                                                        } elseif ($hEntry->head_id == 36) {
                                                            $contactList = \App\Models\Memberinvestments::select('id', 'account_number')
                                                                ->where('plan_id', '!=', 1)
                                                                ->get();
                                                        } elseif ($hEntry->head_id == 89) {
                                                            $contactList = \App\Models\Memberinvestments::select('id', 'account_number')
                                                                ->where('id', $hEntry->contact_id)
                                                                ->get();
                                                        } else {
                                                            $contactList = \App\Models\Memberinvestments::select('id', 'account_number')->get();
                                                        }
                                                    } elseif ($hEntry->type == 3) {
                                                        $contactList = \App\Models\SavingAccount::select('id', 'account_no')
                                                            ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                            ->get();
                                                    } elseif ($hEntry->type == 4 || $hEntry->type == 9) {
                                                        $contactList = \App\Models\Member::select('id', 'member_id')
                                                            ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                            ->get();
                                                    } elseif ($hEntry->type == 5) {
                                                        $contactList = \App\Models\SamraddhBankAccount::select('id', 'account_no')
                                                            ->where('account_head_id', $hEntry->head_id)
                                                            ->where('id', $hEntry->contact_id)
                                                            ->get();
                                                    } elseif ($hEntry->type == 6) {
                                                        $contactList = \App\Models\Branch::select('id', 'name')
                                                            ->where('id', $hEntry->contact_id) /*->where('id',$jv->branch_id)*/
                                                            ->get();
                                                    } elseif ($hEntry->type == 7) {
                                                        $contactList = \App\Models\Employee::select('id', 'employee_name', 'employee_code')
                                                            ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                            ->get();
                                                    } elseif ($hEntry->type == 8) {
                                                        $contactList = \App\Models\RentLiability::select('id', 'owner_name')
                                                            ->where('id', $hEntry->contact_id) /*->where('branch_id',$jv->branch_id)*/
                                                            ->get();
                                                    } elseif ($hEntry->type == 10) {
                                                        $contactList = \App\Models\ShareHolder::with('getMember')
                                                            ->select('id', 'name')
                                                            ->where('head_id', $hEntry->head_id)
                                                            ->where('type', getHeadParentId($hEntry->head_id))
                                                            ->where('id', $hEntry->contact_id)
                                                            ->get();
                                                    } elseif ($hEntry->type == 11) {
                                                        $contactList = \App\Models\ShareHolder::with('getMember')
                                                            ->select('id', 'name')
                                                            ->where('head_id', $hEntry->head_id)
                                                            ->where('type', getHeadParentId($hEntry->head_id))
                                                            ->where('id', $hEntry->contact_id)
                                                            ->get();
                                                    } elseif ($hEntry->type == 12) {
                                                        if ($hEntry->head_id == 97) {
                                                            $contactList = \App\Models\LoanFromBank::select('id', 'loan_account_number')->get();
                                                        } else {
                                                            $contactList = \App\Models\LoanFromBank::select('id', 'loan_account_number')
                                                                ->where('account_head_id', $hEntry->head_id)
                                                                ->where('id', $hEntry->contact_id)
                                                                ->get();
                                                        }
                                                    } elseif ($hEntry->type == 19) {
                                                        $contactList = \App\Models\CompanyBound::select('id', 'fd_no')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->get();
                                                    }
                                                    
                                                    if ($checkHL->labels == 5) {
                                                        $head5Id = $checkHL->head_id;
                                                        $head4Id = getHeadParentId($head5Id);
                                                        $head3Id = getHeadParentId($head4Id);
                                                        $head2Id = getHeadParentId($head3Id);
                                                        $head1Id = getHeadParentId($head2Id);
                                                        $heads2 = \App\Models\AccountHeads::where('parent_id', $head1Id)->get();
                                                        $heads3 = \App\Models\AccountHeads::where('parent_id', $head2Id)->get();
                                                        $heads4 = \App\Models\AccountHeads::where('parent_id', $head3Id)->get();
                                                        $heads5 = \App\Models\AccountHeads::where('parent_id', $head4Id)->get();
                                                    } elseif ($checkHL->labels == 4) {
                                                        $head5Id = null;
                                                        $head4Id = $checkHL->head_id;
                                                        $head3Id = getHeadParentId($head4Id);
                                                        $head2Id = getHeadParentId($head3Id);
                                                        $head1Id = getHeadParentId($head2Id);
                                                    
                                                        $heads2 = \App\Models\AccountHeads::where('parent_id', $head1Id)->get();
                                                        $heads3 = \App\Models\AccountHeads::where('parent_id', $head2Id)->get();
                                                        $heads4 = \App\Models\AccountHeads::where('parent_id', $head3Id)->get();
                                                        $heads5 = '';
                                                    } elseif ($checkHL->labels == 3) {
                                                        $head5Id = null;
                                                        $head4Id = null;
                                                        $head3Id = $checkHL->head_id;
                                                        $head2Id = getHeadParentId($head3Id);
                                                        $head1Id = getHeadParentId($head2Id);
                                                    
                                                        $heads2 = \App\Models\AccountHeads::where('parent_id', $head1Id)->get();
                                                        $heads3 = \App\Models\AccountHeads::where('parent_id', $head2Id)->get();
                                                        $heads4 = '';
                                                        $heads5 = '';
                                                    } elseif ($checkHL->labels == 2) {
                                                        $head5Id = null;
                                                        $head4Id = null;
                                                        $head3Id = null;
                                                        $head2Id = $checkHL->head_id;
                                                        $head1Id = getHeadParentId($head2Id);
                                                    
                                                        $heads2 = \App\Models\AccountHeads::where('parent_id', $head1Id)->get();
                                                        $heads3 = '';
                                                        $heads4 = '';
                                                        $heads5 = '';
                                                    } elseif ($checkHL->labels == 2) {
                                                        $head5Id = null;
                                                        $head4Id = null;
                                                        $head3Id = null;
                                                        $head2Id = null;
                                                        $head1Id = $checkHL->head_id;
                                                        $heads2 = '';
                                                        $heads3 = '';
                                                        $heads4 = '';
                                                        $heads5 = '';
                                                    }
                                                @endphp
                                                <tr>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class="error-msg frm">
                                                            <select name="account_head[{{ $key + 1 }}]"
                                                                id="account_head"
                                                                class="form-control account-head-{{ $key + 1 }} account_head_more account_head_dropdown frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}" required="">
                                                                <option value="">Select Account Head1</option>
                                                                @foreach ($account_heads as $heads)
                                                                    @if ($heads->head_id == 1 || $heads->head_id == 4)
                                                                        <option
                                                                            @if ($head1Id == $heads->head_id) selected @endif
                                                                            data-parent-id="{{ $heads->parent_id }}"
                                                                            value="{{ $heads->head_id }}">
                                                                            {{ $heads->sub_head }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class="error-msg frm">
                                                            <select name="sub_head1[{{ $key + 1 }}]" id="sub_head1"
                                                                class="form-control sub-head1-{{ $key + 1 }} {{ $key + 1 }}-sub_head1_more sub_head1_more account_head_dropdown frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}">
                                                                @if ($heads2)
                                                                    @if ($head1Id == 1)
                                                                        @foreach ($heads2 as $heads)
                                                                            @if ($heads->head_id == 8)
                                                                                <option selected
                                                                                    data-parent-id="{{ $heads->parent_id }}"
                                                                                    value="{{ $heads->head_id }}">
                                                                                    {{ $heads->sub_head }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        @foreach ($heads2 as $heads)
                                                                            <option
                                                                                @if ($head2Id == $heads->head_id) selected @endif
                                                                                data-parent-id="{{ $heads->parent_id }}"
                                                                                value="{{ $heads->head_id }}">
                                                                                {{ $heads->sub_head }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    <option value="">Select Account Head2</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <select name="sub_head2[{{ $key + 1 }}]" id="sub_head2"
                                                                class="form-control sub-head2-{{ $key + 1 }} {{ $key + 1 }}-sub_head2_more sub_head2_more account_head_dropdown frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}">
                                                                @if ($heads3)
                                                                    @if ($head1Id == 1 && $head2Id == 8)
                                                                        @foreach ($heads3 as $heads)
                                                                            @if ($heads->head_id == 20)
                                                                                <option selected
                                                                                    data-parent-id="{{ $heads->parent_id }}"
                                                                                    value="{{ $heads->head_id }}">
                                                                                    {{ $heads->sub_head }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        @foreach ($heads3 as $heads)
                                                                            <option
                                                                                @if ($head3Id == $heads->head_id) selected @endif
                                                                                data-parent-id="{{ $heads->parent_id }}"
                                                                                value="{{ $heads->head_id }}">
                                                                                {{ $heads->sub_head }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    <option value="">Select Account Head3</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class="error-msg frm">
                                                            <select name="sub_head3[{{ $key + 1 }}]" id="sub_head3"
                                                                class="form-control sub-head3-{{ $key + 1 }} {{ $key + 1 }}-sub_head3_more sub_head3_more account_head_dropdown frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}">
                                                                @if ($heads4)
                                                                    @if ($head1Id == 1 && $head2Id == 8 && $head3Id == 20)
                                                                        @foreach ($heads4 as $heads)
                                                                            @if ($heads->head_id == 406)
                                                                                <option selected
                                                                                    data-parent-id="{{ $heads->parent_id }}"
                                                                                    value="{{ $heads->head_id }}">
                                                                                    {{ $heads->sub_head }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        @foreach ($heads4 as $heads)
                                                                            <option
                                                                                @if ($head4Id == $heads->head_id) selected @endif
                                                                                data-parent-id="{{ $heads->parent_id }}"
                                                                                value="{{ $heads->head_id }}">
                                                                                {{ $heads->sub_head }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    <option value="">Select Account Head4</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <select name="sub_head4[{{ $key + 1 }}]" id="sub_head4"
                                                                class="form-control sub-head4-{{ $key + 1 }} {{ $key + 1 }}-sub_head4_more sub_head4_more account_head_dropdown frm"
                                                                data-row="1" data-value="{{ $key + 1 }}">
                                                                @if ($heads5)
                                                                    @foreach ($heads5 as $heads)
                                                                        <option
                                                                            @if ($head5Id == $heads->head_id) selected @endif
                                                                            data-parent-id="{{ $heads->parent_id }}"
                                                                            value="{{ $heads->head_id }}">
                                                                            {{ $heads->sub_head }}
                                                                        </option>
                                                                    @endforeach
                                                                @else
                                                                    <option value="">Select Account Head5</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <select name="contact[{{ $key + 1 }}]" id="contact"
                                                                class="form-control head-contact account_head_dropdown contact-{{ $key + 1 }} frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}">

                                                                <option value="">Select Contact Number</option>
                                                                @if (isset($contactList))
                                                                    @foreach ($contactList as $value)
                                                                        @if ($hEntry->type == 1)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->account_number }}</option>
                                                                        @elseif($hEntry->type == 2)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->account_number }}</option>
                                                                        @elseif($hEntry->type == 3)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->account_no }}</option>
                                                                        @elseif($hEntry->type == 4)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->member_id }}</option>
                                                                        @elseif($hEntry->type == 5)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->account_no }}</option>
                                                                        @elseif($hEntry->type == 6)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @elseif($hEntry->type == 7)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->employee_name }}</option>
                                                                        @elseif($hEntry->type == 8)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->owner_name }}</option>
                                                                        @elseif($hEntry->type == 9)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->member_id }}</option>
                                                                        @elseif($hEntry->type == 10)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @elseif($hEntry->type == 11)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @elseif($hEntry->type == 12)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->loan_account_number }}</option>
                                                                        @elseif($hEntry->type == 14)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->loan_account_number }}</option>
                                                                        @elseif($hEntry->type == 19)
                                                                            <option
                                                                                @if ($hEntry->contact_id == $value->id) selected @endif
                                                                                value="{{ $value->id }}">
                                                                                {{ $value->fd_no }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <input type="hidden" name="contact_account[1]"
                                                            class="contact-account-1">
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <input type="text" name="description[{{ $key + 1 }}]"
                                                                id="description"
                                                                class="form-control description-{{ $key + 1 }} frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}"
                                                                value="{{ $hEntry->description }}" required="" />
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <input type="text" name="debit[{{ $key + 1 }}]"
                                                                id="debit"
                                                                class="form-control debit-amount debit-{{ $key + 1 }} frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}"
                                                                value="{{ number_format((float) $hEntry->debits_amount, 2, '.', '') }}"
                                                                @if ($hEntry->debits_amount == '') readonly @endif />
                                                        </div>
                                                    </td>

                                                    <td class="row-{{ $key + 1 }}-section">
                                                        <div class=" error-msg frm">
                                                            <input type="text" name="credit[{{ $key + 1 }}]"
                                                                id="credit"
                                                                class="form-control credit-amount credit-{{ $key + 1 }} frm"
                                                                data-row="{{ $key + 1 }}"
                                                                data-value="{{ $key + 1 }}"
                                                                value="{{ number_format((float) $hEntry->credits_amount, 2, '.', '') }}"
                                                                @if ($hEntry->credits_amount == '') readonly @endif />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <div class="">
                                        <button type="button" class="btn btn-primary" id="add_row"
                                            data-row="{{ $count }}"><i class="icon-add"> ADD MORE</i></button>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap mt-4">


                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="Member">
                                        <h5 class="text-white">Member Detail</h5>

                                        <div class="m-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 4)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\Member::select('id', 'member_id', 'first_name', 'last_name')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp

                                                    <div class="m-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Member Id:</th>
                                                                    <td class="p-2">{{ $contactList->member_id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Member Name:</th>
                                                                    <td class="p-2">{{ $contactList->first_name }}
                                                                        {{ $contactList->last_name }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="emp">
                                        <h5 class="text-white">Employee Detail</h5>
                                        <div class="e-box ">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 7)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\Employee::select('id', 'employee_name', 'employee_code')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="e-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Employee Code:</th>
                                                                    <td class="p-2">{{ $contactList->employee_code }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Employee Name:</th>
                                                                    <td class="p-2">{{ $contactList->employee_name }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="rent">
                                        <h5 class="text-white">Rent Detail</h5>
                                        <div class="r-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 8)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\RentLiability::select('id', 'owner_name')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Rent Id:</th>
                                                                    <td class="p-2">{{ $contactList->id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Rent Owner:</th>
                                                                    <td class="p-2">{{ $contactList->owner_name }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="associate">
                                        <h5 class="text-white">Associate Detail</h5>
                                        <div class="a-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 9)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\Member::select('id', 'associate_no', 'first_name', 'last_name')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Associate Id:</th>
                                                                    <td class="p-2">{{ $contactList->associate_no }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Associate Name:</th>
                                                                    <td class="p-2">
                                                                        {{ $contactList->first_name }}{{ $contactList->last_name }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="director">
                                        <h5 class="text-white">Director Detail</h5>
                                        <div class="d-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 11)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif

                                                    @php
                                                        $contactList = \App\Models\ShareHolder::select('id', 'name')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Director Id:</th>
                                                                    <td class="p-2">{{ $contactList->id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Director Name:</th>
                                                                    <td class="p-2">{{ $contactList->name }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="shareholder">
                                        <h5 class="text-white">Shareholder Detail</h5>
                                        <div class="s-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 10)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\ShareHolder::select('id', 'name')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Shareholder Id:</th>
                                                                    <td class="p-2">{{ $contactList->id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Shareholder Name:</th>
                                                                    <td class="p-2">{{ $contactList->name }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="investment">
                                        <h5 class="text-white">Investment Detail</h5>
                                        <div class="re-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 2)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\Memberinvestments::with('member', 'plan')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Account No.:</th>
                                                                    <td class="p-2">{{ $contactList->account_number }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Member Name:</th>
                                                                    <td class="p-2">
                                                                        {{ $contactList['member']->first_name }}{{ $contactList['member']->last_name }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Plan Name:</th>
                                                                    <td class="p-2">{{ $contactList['plan']->name }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1 " id="load_account">
                                        <h5 class="text-white">Loan Account Detail</h5>
                                        <div class="la-box">
                                            <?php
                                            // dd($jv);
                                            ?>
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 1)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $loanType = '';
                                                        if ($hEntry->head_id == 64 || $hEntry->head_id == 65 || $hEntry->head_id == 67 || $hEntry->head_id == 90) {
                                                            $contactList = \App\Models\Memberloans::with('loanMember')
                                                                ->where('id', $hEntry->contact_id)
                                                                ->first();
                                                        
                                                            if ($contactList->loan_type == 1) {
                                                                $loanType = 'Personal Loan';
                                                            } elseif ($contactList->loan_type == 2) {
                                                                $loanType = 'Staff Loan';
                                                            } elseif ($contactList->loan_type == 3) {
                                                                $loanType = 'Group Loan';
                                                            } elseif ($contactList->loan_type == 4) {
                                                                $loanType = 'Loan Against Investment Plan(DL) ';
                                                            }
                                                        } else {
                                                            $contactList = \App\Models\Grouploans::with('loanMember')
                                                                ->where('id', $hEntry->contact_id)
                                                                ->first();
                                                        
                                                            $loanType = 'Group Loan';
                                                        }
                                                    @endphp

                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Account No.:</th>
                                                                    <td class="p-2">
                                                                        @if (isset($contactList->account_number))
                                                                            {{ $contactList->account_number }}
                                                                        @else
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Loan Type:</th>
                                                                    <td class="p-2">{{ $loanType }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Member Name:</th>
                                                                    <td class="p-2">
                                                                        @if ($contactList)
                                                                            {{ $contactList['loanMember']->first_name }}{{ $contactList['loanMember']->last_name }}
                                                                        @else
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="saving_account">
                                        <h5 class="text-white">Saving Account Detail</h5>
                                        <div class="sa-box">
                                            @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                                @if ($hEntry->type == 3)
                                                    @if ($key > 0)
                                                        <hr>
                                                    @endif
                                                    @php
                                                        $contactList = \App\Models\SavingAccount::with('customerSSB')
                                                            ->where('id', $hEntry->contact_id)
                                                            ->first();
                                                    @endphp
                                                    <div class="r-box-{{ $key + 1 }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Account No.:</th>
                                                                    <td class="p-2">{{ $contactList->account_no }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Member Name:</th>
                                                                    <td class="p-2">
                                                                        {{ $contactList['customerSSB']->first_name }}{{ $contactList['customerSSB']->last_name }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="t col-lg-3 rounded border ml-1 mt-1" id="company_bond_detail">
                                    <h5 class="text-white">Company Bond Detail</h5>
                                    <div class="company_bond-box">
                                        @foreach ($jv->jvJournalHeads as $key => $hEntry)
                                            @if ($hEntry->type == 19)
                                                @if ($key > 0)
                                                    <hr>
                                                @endif
                                                @php
                                                    $contactList = \App\Models\CompanyBound::where('id', $hEntry->contact_id)->first();
                                                @endphp
                                                <div class="r-box-{{ $key + 1 }}">
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <th>FD No.:</th>
                                                                <td class="p-2">{{ $contactList->fd_no }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Bank Name:</th>
                                                                <td class="p-2">{{ $contactList->bank_name }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>



                                <div class="d-flex justify-content-between mt-4">

                                    <div class="t col-lg-7  rounded border">

                                        <div class="d-flex justify-content-between my-2">

                                            <div class="total-label">Sub Total</div>

                                            <div class="total-amount debit-sub-total">
                                                {{ number_format($debitAmount, 2) }}
                                            </div>

                                            <div class="total-amount credit-sub-total">
                                                {{ number_format($creditAmount, 2) }}
                                            </div>

                                        </div>

                                        <div class="d-flex justify-content-between my-2">

                                            <div class="total-label">Total (Rs.)</div>

                                            <div class="total-amount debit-total"> {{ number_format($debitAmount, 2) }}
                                            </div>

                                            <div class="total-amount credit-total"> {{ number_format($creditAmount, 2) }}
                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <div class="text-right mt-4">

                                    <input type="submit" name="submitform" value="Submit"
                                        class="btn btn-primary submit">



                                </div>
                        </div>
                        </form>
                    </div>
                </div>

            </div>



        </div>

    </div>

    </div>

    </div>

@stop

@section('script')

    @include('templates.admin.jv_management.partials.create_script')
    <script>
        $(document).ready(function() {
            $('#company_id ,#branch').prop('disabled', true);
        });
    </script>
@stop
