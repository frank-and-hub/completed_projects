@extends('templates.admin.master')
@section('content')
<??>
    <div class="content">
        <div class="card bg-white">
            <div class="card-body">
                <h3 class="card-title mb-3"> Plan Detail</h3>
                <div class="row">
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Name:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['name'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Short Name:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['short_name'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Percentage:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['percent'] ?? 'N/A' }}</div>
                        </div>
                    </div> --}}
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Minimum Deposit:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['min_deposit'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Amount:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['amount'] ?? 'N/A' }}</div>
                        </div>
                    </div> --}}
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Plan Code:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['plan_code'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Maximum Deposit:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['max_deposit'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Plan Category:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['category_name'][0]['name'] ?? 'N/A' }} (
                                {{ $plan[0]['plan_category_code'] ?? 'N/A' }} )</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Hybrid Tenure:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['hybrid_tenure'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Multiple Deposit:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['multiple_deposit'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Prematurity:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['prematurity'] ? 'Allowed' : 'Not Allowed' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Loan Against Deposit:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['loan_against_deposit'] ? 'Allowed' : 'Not Allowed' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Death Help:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['death_help'] ? 'Allowed' : 'Not Allowed' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">SSB Required:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['is_ssb_required'] ? 'Allowed' : 'Not Allowed' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Plan Sub Category Code:</label>
                            <div class="col-lg-7 ">{{ $plan[0]['sub_category_name'][0]['name'] ?? 'N/A' }} (
                                {{ $plan[0]['plan_sub_category_code'] ?? 'N/A' }} )</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class=" row">
                            <label class=" col-lg-5">Status</label>
                            <div class="col-lg-7 ">{!! $plan[0]['status']
                                ? '<span class="badge badge-success">Active</span>'
                                : '<span class="badge badge-secondary">Deactive</span>' !!}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($plan[0]['plan_category_code'] != 'S')
            <div class="card bg-white">
                <div class="card-body"> 
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Plan Tenure Details    <a href="{{ route('admin.py-plans.tenure',$plan[0]['id'])}}" class="font-weight-semibold"><i class="fa fa-plus  "></i></a></h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 15%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Compounding</th>
                                    <th style="border: 1px solid #ddd; width: 12%">ROI</th>
                                    <th style="border: 1px solid #ddd; width: 12%">SPL ROI</th>
                                    <th style="border: 1px solid #ddd;">Effective From</th>
                                    <th style="border: 1px solid #ddd;">Effective To</th>
                                    <th style="border: 1px solid #ddd;">Month From</th>
                                    <th style="border: 1px solid #ddd; ">Month To</th>
                                    <th style="border: 1px solid #ddd; width: 16%">Status</th>
                                    <th style="border: 1px solid #ddd; width: 16%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['plan_tenures']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="10" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['plan_tenures'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        switch ($item['compounding']) {
                                                            case '3':
                                                                $com = 'Quarterly';
                                                                break;
                                                            case '6':
                                                                $com = 'Half Yearly';
                                                                break;
                                                            case '12':
                                                                $com = 'Annually';
                                                                break;
                                                        }
                                                    @endphp
                                                    {{ $com ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['roi'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['spl_roi'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_from']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_to']);
                                                    @endphp
                                                    @if ($item['effective_to'] != '')
                                                        {{ date('d/m/Y', strtotime($date)) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_from'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_to'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="row">
                                                    <div class="col-lg-4 ml-1  d-flex align-items-center">
                                                        {!! $item['status']
                                                            ? '<span class="badge badge-success">Active</span>'
                                                            : '<span class="badge badge-secondary">Deactive</span>' !!}
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                    @if ($item['status'] == 1)
                                                        <a class="btn btn-success status_button ml-3"
                                                            href="{{ route('admin.tenure.status', ['id' => $item['id']]) }}"><i
                                                                class="fa fa-check-circle"></i></a>
                                                    @else
                                                        <a class="btn btn-danger status_button ml-3"
                                                                href="{{ route('admin.tenure.status', ['id' => $item['id']]) }}"><i
                                                                    class="fa fa-ban"></i></a>
                                                    @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if ($plan[0]['plan_sub_category_code'] == 'K')
            <div class="card bg-white">
                <div class="card-body">
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Plan Deno Detail   <a href="{{route('planDeno',$plan[0]['slug'])}}" class="font-weight-semibold"><i class="fa fa-plus  "></i></a></h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 20%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 20%">Denomination</th>
                                    <th style="border: 1px solid #ddd;">Effective From</th>
                                    <th style="border: 1px solid #ddd;">Effective To</th>
                                    <th style="border: 1px solid #ddd; width: 20%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['plan_deno']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="7" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['plan_deno'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['denomination'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_from']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_to']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {!! $item['status']
                                                        ? '<span class="badge badge-success">Active</span>'
                                                        : '<span class="badge badge-secondary">Deactive</span>' !!}
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
        @endif
        @if ($plan[0]['plan_sub_category_code'] == 'B')
            <div class="card bg-white">
                <div class="card-body">
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Money Back Detail   <a href="{{route('moneyBack.list',$plan[0]['slug'])}}" class="font-weight-semibold"><i class="fa fa-plus  "></i></a></h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 15%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 15%">Months</th>
                                    <th style="border: 1px solid #ddd; width: 20%">Money Back Percentage</th>
                                    <th style="border: 1px solid #ddd; width: 20%">Effective From</th>
                                    <th style="border: 1px solid #ddd; width: 15%">Effective To</th>
                                    <th style="border: 1px solid #ddd; width: 15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['money_back']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="7" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['money_back'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['months'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['money_back_per'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_from']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_to']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {!! $item['status']
                                                        ? '<span class="badge badge-success">Active</span>'
                                                        : '<span class="badge badge-secondary">Deactive</span>' !!}
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
        @endif
        @if ($plan[0]['loan_against_deposit'] == 1)
            <div class="card bg-white">
                <div class="card-body">
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Loan Against Investment Detail <a href="{{ route('loanAgainst.list',$plan[0]['slug']) }}" class="font-weight-semibold"><i class="fa fa-plus  " ></i></a>
                    </h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 16%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Months From</th>
                                    <th style="border: 1px solid #ddd;">Months To</th>
                                    <th style="border: 1px solid #ddd;">Loan Percentage</th>
                                    <th style="border: 1px solid #ddd;">Effective From</th>
                                    <th style="border: 1px solid #ddd; width: 9%">Effective To</th>
                                    <th style="border: 1px solid #ddd; width: 17%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['loan_against']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="7" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['loan_against'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_from'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_to'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['loan_per'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_from']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_to']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {!! $item['status']
                                                        ? '<span class="badge badge-success">Active</span>'
                                                        : '<span class="badge badge-secondary">Deactive</span>' !!}
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
        @endif
        @if ($plan[0]['death_help'] == '1')
            <div class="card bg-white">
                <div class="card-body"> 
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Death Help Detail   <a href="{{route('deathHelp.list', $plan[0]['slug'])}}" class="font-weight-semibold"><i class="fa fa-plus  "></i></a></h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 16%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Months From</th>
                                    <th style="border: 1px solid #ddd;">Months To</th>
                                    <th style="border: 1px solid #ddd;">Death Help Per.</th>
                                    <th style="border: 1px solid #ddd; width: 9%">Effective From</th>
                                    <th style="border: 1px solid #ddd; width: 17%">Effective To</th>
                                    <th style="border: 1px solid #ddd; width: 17%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['death_help_settin']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="7" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['death_help_settin'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_from'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['month_to'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['death_help_per'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_from']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    @php
                                                        $date = str_replace('-"', '/', $item['effective_to']);
                                                    @endphp
                                                    {{ date('d/m/Y', strtotime($date)) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {!! $item['status']
                                                        ? '<span class="badge badge-success">Active</span>'
                                                        : '<span class="badge badge-secondary">Deactive</span>' !!}
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
        @endif
        @if ($plan[0])
            @if($plan[0]['commissiondetail'])
            <div class="card bg-white">

                <div class="card-body"> 
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Plan Commission  </h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Tenure</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Tenure From</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Tenure To</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Effective From</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Effective To</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Commission Per</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Associate Per</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Carder</th>
                                    <th style="border: 1px solid #ddd; width: 12.5%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($plan[0]['commissiondetail']))
                                    <tr class="no-data table-secondary" style="border: 1px solid #ddd;">
                                        <td colspan="7" class="text-center">No data available in the table.</td>
                                    </tr>
                                @else
                                    @foreach ($plan[0]['commissiondetail'] as $item)
                                        <tr>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure_from'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['tenure_to'] ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ date('d/m/Y', strtotime(str_replace('-"', '/', $item['effective_from']))) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ $item['effective_to'] != null ? date('d/m/Y', strtotime(str_replace('-"', '/', $item['effective_to']))) : 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ ($item['collector_per']) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ ($item['associate_per']) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {{ ($item['carder']['name']) ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                                <div class="col-lg-12 ">
                                                    {!! $item['status']
                                                        ? '<span class="badge badge-success">Active</span>'
                                                        : '<span class="badge badge-secondary">Deactive</span>' !!}
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
            @endif
        @endif
    </div>
    </div>
    </div>
    @include('templates.admin.py-scheme.partials.moneySettingScript')
@stop